<?php

namespace App\Filament\Imports;

use App\Models\Epic;
use App\Models\Task;
use App\Models\TaskPriority;
use App\Models\TaskStatus;
use App\Models\User;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Database\Eloquent\Model;

class TaskImporter extends Importer
{
    protected static ?string $model = Task::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('title')
                ->label('Judul')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('epic_id')
                ->label('Epic (nama)')
                ->requiredMapping()
                ->rules(['required'])
                ->castStateUsing(function (?string $state, array $options): ?int {
                    if (blank($state)) {
                        return null;
                    }
                    $projectId = $options['project_id'] ?? null;
                    if (! $projectId) {
                        return null;
                    }
                    $epic = Epic::where('project_id', $projectId)->where('title', trim($state))->first();

                    return $epic?->id;
                }),
            ImportColumn::make('task_status_id')
                ->label('Status (nama)')
                ->requiredMapping()
                ->rules(['required'])
                ->castStateUsing(function (?string $state, array $options): ?int {
                    if (blank($state)) {
                        return null;
                    }
                    $projectId = $options['project_id'] ?? null;
                    if (! $projectId) {
                        return null;
                    }
                    $status = TaskStatus::where('project_id', $projectId)->where('name', trim($state))->first();

                    return $status?->id;
                }),
            ImportColumn::make('task_priority_id')
                ->label('Priority (nama)')
                ->requiredMapping()
                ->rules(['required'])
                ->castStateUsing(function (?string $state, array $options): ?int {
                    if (blank($state)) {
                        return null;
                    }
                    $projectId = $options['project_id'] ?? null;
                    if (! $projectId) {
                        return null;
                    }
                    $priority = TaskPriority::where('project_id', $projectId)->where('name', trim($state))->first();

                    return $priority?->id;
                }),
            ImportColumn::make('description')
                ->label('Deskripsi')
                ->ignoreBlankState(),
            ImportColumn::make('due_date')
                ->label('Due Date (Y-m-d)')
                ->castStateUsing(function (?string $state): ?string {
                    if (blank($state)) {
                        return null;
                    }
                    try {
                        $date = \Carbon\Carbon::parse(trim($state));
                        return $date->format('Y-m-d');
                    } catch (\Throwable) {
                        return null;
                    }
                }),
            ImportColumn::make('assignees')
                ->label('Assignee (nama, pisah koma)')
                ->ignoreBlankState()
                ->array(','),
        ];
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = $import->successful_rows . ' task berhasil diimpor.';
        if ($import->failed_rows > 0) {
            $body .= ' ' . $import->failed_rows . ' baris gagal.';
        }
        return $body;
    }

    public function resolveRecord(): ?Model
    {
        $projectId = $this->getOptions()['project_id'] ?? null;
        if (! $projectId) {
            return null;
        }
        $task = new Task;
        $task->project_id = $projectId;
        return $task;
    }

    public function beforeSave(): void
    {
        $record = $this->getRecord();
        if ($record instanceof Task && $record->task_status_id) {
            $record->position = Task::where('task_status_id', $record->task_status_id)->max('position') + 1;
        }
    }

    public function afterSave(): void
    {
        $record = $this->getRecord();
        $data = $this->getData();
        if (! $record instanceof Task || ! $record->exists) {
            return;
        }
        if (isset($data['assignees']) && is_array($data['assignees'])) {
            $userIds = collect($data['assignees'])
                ->filter(fn ($name) => filled(is_string($name) ? trim($name) : $name))
                ->map(fn ($name) => User::where('name', trim((string) $name))->value('id'))
                ->filter()
                ->unique()
                ->values()
                ->all();
            $record->users()->sync($userIds);
        }
    }
}
