<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use App\Filament\Resources\Tasks\TaskResource;
use App\Models\Task;
use Filament\Facades\Filament;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ProjectGantt extends Page
{
    use InteractsWithRecord;

    protected static ?string $projectTabPermission = 'ViewProjectResourceProjectGantt';

    public static function canAccess(array $parameters = []): bool
    {
        $user = Filament::auth()?->user();
        if (static::$projectTabPermission && $user) {
            return $user->can(static::$projectTabPermission);
        }
        return parent::canAccess($parameters);
    }

    protected static string $resource = ProjectResource::class;

    protected static ?string $title = 'Gantt';

    protected static ?string $navigationLabel = 'Gantt';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected string $view = 'filament.resources.projects.pages.project-gantt';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->mountCanAuthorizeAccess();
    }

    public function getTitle(): string|Htmlable
    {
        return static::$title ?? 'Gantt';
    }

    public function getHeading(): string|Htmlable
    {
        return static::$navigationLabel ?? 'Gantt';
    }

    /**
     * Data for dhtmlxGantt: data array (tasks) + links array (empty).
     * Each task: id, text, start_date, end_date, duration, progress, status, is_overdue.
     *
     * @return array{data: array<int, array<string, mixed>>, links: array<int, array<string, mixed>>}
     */
    public function getGanttData(): array
    {
        $project = $this->getRecord();
        $tasks = $project->tasks()
            ->whereHas('users', fn ($q) => $q->where('users.id', Auth::id()))
            ->with(['taskStatus', 'taskPriority'])
            ->orderBy('start_date')
            ->orderBy('due_date')
            ->orderBy('id')
            ->get();

        $data = [];
        $now = Carbon::now()->startOfDay();
        $approachingDays = 7;
        $approachingEnd = $now->copy()->addDays($approachingDays);

        foreach ($tasks as $task) {
            $start = $task->start_date ?? $task->due_date;
            $end = $task->due_date ?? $task->start_date;

            if (! $start || ! $end) {
                continue;
            }

            $startCarbon = Carbon::parse($start)->startOfDay();
            $endCarbon = Carbon::parse($end)->startOfDay();
            if ($startCarbon->gt($endCarbon)) {
                [$startCarbon, $endCarbon] = [$endCarbon, $startCarbon];
            }

            $duration = (int) $startCarbon->diffInDays($endCarbon) + 1;
            $statusName = $task->taskStatus?->name ?? '';

            // Bar status for legend/color (priority: overdue > done > approaching > in progress)
            $barStatus = 'in_progress';
            if ($endCarbon->lt($now)) {
                $barStatus = 'overdue';
            } elseif (stripos($statusName, 'done') !== false) {
                $barStatus = 'nearly_complete';
            } elseif ($endCarbon->lte($approachingEnd)) {
                $barStatus = 'approaching_deadline';
            }

            $data[] = [
                'id' => (int) $task->id,
                'text' => $task->title,
                'start_date' => $startCarbon->format('d-m-Y'),
                'end_date' => $endCarbon->format('d-m-Y'),
                'duration' => $duration,
                'progress' => 0,
                'status' => $statusName,
                'is_overdue' => $barStatus === 'overdue',
                'bar_status' => $barStatus,
            ];
        }

        return [
            'data' => $data,
            'links' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'ganttData' => $this->getGanttData(),
        ];
    }
}
