<?php

namespace App\Observers;

use App\Jobs\SyncTaskToGoogleCalendar;
use App\Models\Task;
use Illuminate\Support\Facades\Cache;

class TaskObserver
{
    private const CACHE_KEY_PREFIX = 'task_assignees_';
    private const CACHE_TTL_SECONDS = 60;

    public function creating(Task $task): void
    {
        // No-op; cache is set in updating.
    }

    public function created(Task $task): void
    {
        SyncTaskToGoogleCalendar::dispatch($task, null, [], []);
    }

    public function updating(Task $task): void
    {
        if (! $task->exists) {
            return;
        }
        $assignees = $task->users()->get();
        $payload = $assignees->pluck('pivot.google_event_id', 'id')->all();
        Cache::put(self::CACHE_KEY_PREFIX . $task->id, $payload, self::CACHE_TTL_SECONDS);
    }

    public function updated(Task $task): void
    {
        $previous = Cache::pull(self::CACHE_KEY_PREFIX . $task->id);
        if (! is_array($previous)) {
            $previous = [];
        }
        $currentIds = $task->users()->pluck('users.id')->all();
        $previousIds = array_keys($previous);
        $removedIds = array_diff($previousIds, $currentIds);
        $removePayload = array_intersect_key($previous, array_flip($removedIds));
        $removePayload = array_filter($removePayload); // only those with an event id

        SyncTaskToGoogleCalendar::dispatch($task, null, $removePayload, []);
    }

    public function deleting(Task $task): void
    {
        $assignees = $task->users()->get();
        $deleteOnly = $assignees->pluck('pivot.google_event_id', 'id')->filter()->all();

        if (! empty($deleteOnly)) {
            SyncTaskToGoogleCalendar::dispatch(null, null, [], $deleteOnly);
        }
    }
}
