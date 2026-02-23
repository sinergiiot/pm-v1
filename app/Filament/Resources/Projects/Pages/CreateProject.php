<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set the creator_id to current user
        $currentUserId = Auth::id();
        $data['creator_id'] = $currentUserId;

        // Remove non-column fields so Eloquent doesn't choke
        unset($data['task_statuses'], $data['clusters'], $data['users']);

        return $data;
    }

    protected function afterCreate(): void
    {
        // Set creator role as 'owner' in pivot table
        $this->record->users()->updateExistingPivot(
            Auth::id(),
            ['role' => 'owner']
        );

        // Create task statuses for the newly created project
        $this->createTaskStatuses();

        // Create clusters for the newly created project
        $this->createClusters();
    }

    /**
     * Create task statuses only from custom repeater data.
     * No default statuses are created to avoid double input.
     */
    protected function createTaskStatuses(): void
    {
        $statuses = data_get($this->data, 'task_statuses', []);
        if (empty($statuses)) {
            return;
        }
        foreach ($statuses as $status) {
            $name = trim($status['name'] ?? '');
            if ($name === '' || $this->record->taskStatuses()->where('name', $name)->exists()) {
                continue;
            }
            $this->record->taskStatuses()->create([
                'name'  => $name,
                'color' => $status['color'] ?? '#6B7280',
                'order' => $status['order'] ?? 0,
            ]);
        }
    }

    /**
     * Create clusters only from custom repeater data.
     * No default clusters are created to avoid double input.
     */
    protected function createClusters(): void
    {
        $clusters = data_get($this->data, 'clusters', []);
        if (empty($clusters)) {
            return;
        }
        foreach ($clusters as $item) {
            $name = trim($item['name'] ?? '');
            if ($name === '') {
                continue;
            }
            if ($this->record->clusters()->where('name', $name)->exists()) {
                continue;
            }
            $this->record->clusters()->create([
                'name'   => $name,
                'prompt' => $item['prompt'] ?? null,
                'order'  => $item['order'] ?? 0,
            ]);
        }
    }
}
