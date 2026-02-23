<?php

namespace App\Filament\Resources\Tasks\Pages;

use App\Filament\Resources\Tasks\TaskResource;
use App\Jobs\SyncTaskToGoogleCalendar;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Cache;

class EditTask extends EditRecord
{
    protected static string $resource = TaskResource::class;

    private const CACHE_KEY_PREFIX = 'task_assignees_';

    private const CACHE_TTL_SECONDS = 60;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function beforeSave(): void
    {
        $task = $this->getRecord();
        $assignees = $task->users()->get();
        $payload = $assignees->pluck('pivot.google_event_id', 'id')->all();
        Cache::put(self::CACHE_KEY_PREFIX . $task->id, $payload, self::CACHE_TTL_SECONDS);
    }

    protected function afterSave(): void
    {
        $task = $this->getRecord();
        $previous = Cache::pull(self::CACHE_KEY_PREFIX . $task->id);
        if (! is_array($previous)) {
            $previous = [];
        }
        $currentIds = $task->fresh()->users()->pluck('users.id')->all();
        $previousIds = array_keys($previous);
        $removedIds = array_diff($previousIds, $currentIds);
        $removePayload = array_intersect_key($previous, array_flip($removedIds));
        $removePayload = array_filter($removePayload);

        SyncTaskToGoogleCalendar::dispatch($task, null, $removePayload, []);
    }
}
