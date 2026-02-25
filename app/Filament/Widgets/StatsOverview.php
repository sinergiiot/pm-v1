<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use App\Models\Task;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = Auth::user();
        
        if (!$user) {
            return [];
        }

        // Project engagement: projects where the user is a member
        $activeProjectsCount = Project::active()
            ->whereHas('users', fn ($q) => $q->where('users.id', $user->id))
            ->count();

        // My tasks that are not in the "Done" status (last status in order)
        // Since "Done" can vary per project, we filter by tasks assigned to user 
        // that are not in their project's last status.
        // For simplicity in a global stat, we'll count all assigned tasks that are not in a status named 'Done', 'Completed', or 'Selesai'
        // or just all tasks assigned that are not in the last status of their respective projects.
        
        $myTasksQuery = Task::whereHas('users', fn ($q) => $q->where('users.id', $user->id));
        
        $pendingTasksCount = (clone $myTasksQuery)
            ->whereHas('taskStatus', function ($q) {
                $q->whereRaw('task_statuses.order < (select max(`order`) from task_statuses as ts where ts.project_id = task_statuses.project_id)');
            })->count();

        $overdueTasksCount = (clone $myTasksQuery)
            ->where('due_date', '<', now()->startOfDay())
            ->whereHas('taskStatus', function ($q) {
                $q->whereRaw('task_statuses.order < (select max(`order`) from task_statuses as ts where ts.project_id = task_statuses.project_id)');
            })->count();

        $criticalTasksCount = (clone $myTasksQuery)
            ->whereHas('taskPriority', fn($q) => $q->where('name', 'Critical'))
            ->whereHas('taskStatus', function ($q) {
                $q->whereRaw('task_statuses.order < (select max(`order`) from task_statuses as ts where ts.project_id = task_statuses.project_id)');
            })->count();

        return [
            Stat::make('Tugas Saya', $pendingTasksCount)
                ->description('Tugas aktif yang belum selesai')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('primary'),
            Stat::make('Overdue', $overdueTasksCount)
                ->description('Sudah melewati deadline')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color($overdueTasksCount > 0 ? 'danger' : 'success'),
            Stat::make('Critical Tasks', $criticalTasksCount)
                ->description('Butuh perhatian segera')
                ->descriptionIcon('heroicon-m-fire')
                ->color($criticalTasksCount > 0 ? 'warning' : 'gray'),
            Stat::make('Proyek Aktif', $activeProjectsCount)
                ->description('Dalam pengerjaan')
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('amber'),
        ];
    }
}
