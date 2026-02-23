<?php

namespace App\Observers;

use App\Jobs\SyncTaskToGoogleCalendar;
use App\Models\TaskPriority;

class TaskPriorityObserver
{
    public function deleting(TaskPriority $taskPriority): void
    {
        $deleteOnly = [];
        foreach ($taskPriority->tasks()->with('users')->get() as $task) {
            foreach ($task->users as $user) {
                $eventId = $user->pivot->google_event_id ?? null;
                if ($eventId) {
                    $deleteOnly[$user->id] = $eventId;
                }
            }
        }
        if (! empty($deleteOnly)) {
            SyncTaskToGoogleCalendar::dispatch(null, null, [], $deleteOnly);
        }
    }
}
