<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();

        // Load users for the User Assignment step
        $data['users'] = $record->users()->pluck('users.id')->toArray();

        // Load task statuses
        $statuses = $record->taskStatuses()->orderBy('order')->get();
        $data['task_statuses'] = $statuses->isEmpty()
            ? []
            : $statuses->map(fn ($s) => [
                'name'  => $s->name,
                'color' => $s->color,
                'order' => $s->order ?? 0,
            ])->toArray();

        // Load clusters
        $clusters = $record->clusters()->orderBy('order')->get();
        $data['clusters'] = $clusters->isEmpty()
            ? []
            : $clusters->map(fn ($c) => [
                'name'   => $c->name,
                'prompt' => $c->prompt,
                'order'  => $c->order ?? 0,
            ])->toArray();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Remove non-column fields so Eloquent doesn't choke
        unset($data['task_statuses'], $data['clusters'], $data['users']);

        return $data;
    }

    protected function afterSave(): void
    {
        $record = $this->getRecord();

        // Sync users with pivot roles (creator = owner)
        $userIds = data_get($this->data, 'users', []);
        $creatorId = $record->creator_id ?? Auth::id();
        $sync = [];
        foreach ($userIds as $userId) {
            $sync[$userId] = ['role' => (int) $userId === (int) $creatorId ? 'owner' : 'member'];
        }
        $record->users()->sync($sync);

        // Replace task statuses with form values (same logic as CreateProject)
        $this->updateTaskStatuses();

        $this->updateClusters();
    }

    /**
     * Update task statuses from form: update existing by index, add new ones.
     * Does not delete statuses that have tasks (to avoid cascade).
     */
    protected function updateTaskStatuses(): void
    {
        $record = $this->getRecord();
        $formStatuses = data_get($this->data, 'task_statuses', []);
        if (empty($formStatuses)) {
            return;
        }
        $existing = $record->taskStatuses()->orderBy('order')->get();

        foreach ($formStatuses as $i => $status) {
            $data = [
                'name'  => $status['name'],
                'color' => $status['color'],
                'order' => $status['order'] ?? $i + 1,
            ];
            if ($existing->has($i)) {
                $existing[$i]->update($data);
            } else {
                // Jangan buat duplikat: skip jika nama sudah ada di project ini
                if (! $record->taskStatuses()->where('name', $data['name'])->exists()) {
                    $record->taskStatuses()->create($data);
                }
            }
        }
    }

    /**
     * Update clusters from form: update by index, add new. Do not delete clusters that have documents.
     */
    protected function updateClusters(): void
    {
        $record = $this->getRecord();
        $formClusters = data_get($this->data, 'clusters', []);
        if (empty($formClusters)) {
            return;
        }
        $existing = $record->clusters()->orderBy('order')->get();

        foreach ($formClusters as $i => $item) {
            $name = trim($item['name'] ?? '');
            if ($name === '') {
                continue;
            }
            $data = [
                'name'   => $name,
                'prompt' => $item['prompt'] ?? null,
                'order'  => $item['order'] ?? $i + 1,
            ];
            if ($existing->has($i)) {
                $existing[$i]->update($data);
            } else {
                if (! $record->clusters()->where('name', $name)->exists()) {
                    $record->clusters()->create($data);
                }
            }
        }
    }
}
