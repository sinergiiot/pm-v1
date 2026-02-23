<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use App\Models\Project;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListProjects extends ListRecords
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        $taskStatusConfig = [];
        $clusterConfig = [];

        return [
            CreateAction::make()
                ->mutateFormDataUsing(function (array $data) use (&$taskStatusConfig, &$clusterConfig): array {
                    $taskStatusConfig = $data['task_statuses'] ?? [];
                    $clusterConfig = $data['clusters'] ?? [];
                    $data['creator_id'] = Auth::id();
                    unset($data['task_statuses'], $data['clusters'], $data['users']);
                    return $data;
                })
                ->after(function (Project $record) use (&$taskStatusConfig, &$clusterConfig): void {
                    $record->users()->updateExistingPivot(
                        Auth::id(),
                        ['role' => 'owner']
                    );
                    $statuses = is_array($taskStatusConfig) ? $taskStatusConfig : [];
                    if (! empty($statuses)) {
                        foreach ($statuses as $status) {
                            $name = trim($status['name'] ?? '');
                            if ($name === '' || $record->taskStatuses()->where('name', $name)->exists()) {
                                continue;
                            }
                            $record->taskStatuses()->create([
                                'name'  => $name,
                                'color' => $status['color'] ?? '#6B7280',
                                'order' => $status['order'] ?? 0,
                            ]);
                        }
                    }
                    $clusters = is_array($clusterConfig) ? $clusterConfig : [];
                    if (! empty($clusters)) {
                        foreach ($clusters as $item) {
                            $name = trim($item['name'] ?? '');
                            if ($name === '' || $record->clusters()->where('name', $name)->exists()) {
                                continue;
                            }
                            $record->clusters()->create([
                                'name'   => $name,
                                'prompt' => $item['prompt'] ?? null,
                                'order'  => $item['order'] ?? 0,
                            ]);
                        }
                    }
                }),
        ];
    }
}
