<?php

namespace App\Jobs;

use App\Models\Task;
use App\Models\User;
use App\Services\GoogleCalendarService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncTaskToGoogleCalendar implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Sync task to Google Calendar for (current) assignees, optionally remove events for detached users.
     *
     * @param  Task|null  $task  Task to sync (null when only deleting events, e.g. task was deleted).
     * @param  array<int>|null  $assigneeIds  User IDs to sync; null = use task's current assignees.
     * @param  array<int, string>  $removePayload  Detached users: [user_id => google_event_id] to delete event and clear pivot.
     * @param  array<int, string>  $deleteOnly  When task was deleted: [user_id => google_event_id] to delete events only.
     */
    public function __construct(
        public ?Task $task = null,
        public ?array $assigneeIds = null,
        public array $removePayload = [],
        public array $deleteOnly = []
    ) {}

    public function handle(GoogleCalendarService $calendar): void
    {
        foreach ($this->deleteOnly as $userId => $eventId) {
            $user = User::find($userId);
            if ($user && $eventId) {
                $calendar->deleteEventForUser($user, $eventId);
            }
        }

        if ($this->task === null) {
            return;
        }

        $task = $this->task->fresh();
        if (! $task) {
            return;
        }

        $assigneeIds = $this->assigneeIds ?? $task->users()->pluck('users.id')->all();

        foreach ($assigneeIds as $userId) {
            $user = User::find($userId);
            if (! $user) {
                continue;
            }

            $pivot = $task->users()->where('user_id', $userId)->first();
            $existingEventId = $pivot?->pivot?->google_event_id ?? null;

            if ($existingEventId) {
                $updated = $calendar->updateEventForTask($user, $task, $existingEventId);
                if (! $updated) {
                    $newEventId = $calendar->createEventForTask($user, $task);
                    if ($newEventId) {
                        $this->setPivotEventId($task->id, $userId, $newEventId);
                    }
                }
            } else {
                $newEventId = $calendar->createEventForTask($user, $task);
                if ($newEventId) {
                    $this->setPivotEventId($task->id, $userId, $newEventId);
                }
            }
        }

        foreach ($this->removePayload as $userId => $eventId) {
            $user = User::find($userId);
            if (! $user) {
                continue;
            }
            if ($eventId) {
                $calendar->deleteEventForUser($user, $eventId);
            }
            $this->setPivotEventId($task->id, $userId, null);
        }
    }

    protected function setPivotEventId(int $taskId, int $userId, ?string $eventId): void
    {
        DB::table('task_users')
            ->where('task_id', $taskId)
            ->where('user_id', $userId)
            ->update(['google_event_id' => $eventId]);
    }
}
