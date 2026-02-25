<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class ProjectProgressWidget extends Widget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'half';

    protected string $view = 'filament.widgets.project-progress-widget';

    public function getProjects()
    {
        return Project::active()
            ->whereHas('users', fn ($q) => $q->where('users.id', Auth::id()))
            ->withCount('tasks')
            ->get()
            ->map(function (Project $project) {
                $totalTasks = $project->tasks_count;
                
                if ($totalTasks === 0) {
                    $project->progress = 0;
                    return $project;
                }

                // Count tasks in the last status (Done)
                $completedTasks = $project->tasks()
                    ->whereHas('taskStatus', function ($q) use ($project) {
                        $q->whereRaw('task_statuses.order = (select max(`order`) from task_statuses as ts where ts.project_id = ?)', [$project->id]);
                    })->count();

                $project->progress = round(($completedTasks / $totalTasks) * 100);
                $project->completed_tasks = $completedTasks;
                $project->total_tasks = $totalTasks;
                $project->status_label = $project->getProjectStatusLabel();
                
                return $project;
            });
    }
}
