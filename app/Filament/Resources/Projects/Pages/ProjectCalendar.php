<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Tasks\TaskResource;
use App\Filament\Resources\Projects\ProjectResource;
use App\Models\Task;
use Filament\Facades\Filament;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;

class ProjectCalendar extends Page
{
    use InteractsWithRecord;

    protected static ?string $projectTabPermission = 'ViewProjectResourceProjectCalendar';

    public static function canAccess(array $parameters = []): bool
    {
        $user = Filament::auth()?->user();
        if (static::$projectTabPermission && $user) {
            return $user->can(static::$projectTabPermission);
        }
        return parent::canAccess($parameters);
    }

    protected static string $resource = ProjectResource::class;

    protected static ?string $title = 'Calendar';

    protected static ?string $navigationLabel = 'Calendar';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected string $view = 'filament.resources.projects.pages.project-calendar';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->mountCanAuthorizeAccess();
    }

    public function getTitle(): string|Htmlable
    {
        return static::$title ?? 'Calendar';
    }

    public function getHeading(): string|Htmlable
    {
        return static::$navigationLabel ?? 'Calendar';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $project = $this->getRecord();

        $taskEvents = $project->tasks()
            ->whereHas('users', fn ($q) => $q->where('users.id', Auth::id()))
            ->with(['epic', 'taskStatus', 'taskPriority'])
            ->whereNotNull('due_date')
            ->orderBy('due_date')
            ->get()
            ->map(function ($task) {
                $start = $task->due_date->format('Y-m-d');
                $end = $task->due_date->copy()->addDay()->format('Y-m-d');

                return [
                    'id' => (string) $task->id,
                    'title' => $task->title,
                    'start' => $start,
                    'end' => $end,
                    'allDay' => true,
                    'backgroundColor' => null,
                    'extendedProps' => [
                        'type' => 'task',
                        'code' => $task->code,
                        'status' => $task->taskStatus?->name,
                        'priority' => $task->taskPriority?->name,
                        'epic' => $task->epic?->title,
                        'taskUrl' => TaskResource::getUrl('view', ['record' => $task->id]),
                    ],
                ];
            });

        $meetingEvents = $project->meetings()
            ->orderBy('start')
            ->get()
            ->map(function ($meeting) {
                return [
                    'id' => 'meeting-' . $meeting->id,
                    'title' => $meeting->title,
                    'start' => $meeting->start->toIso8601String(),
                    'end' => $meeting->end->toIso8601String(),
                    'allDay' => false,
                    'backgroundColor' => '#2D8CFF',
                    'borderColor' => '#2563eb',
                    'extendedProps' => [
                        'type' => 'meeting',
                        'agenda' => $meeting->agenda,
                        'joinUrl' => $meeting->zoom_join_url,
                    ],
                ];
            });

        $events = $taskEvents->concat($meetingEvents)->values()->all();

        return [
            'events' => $events,
            'livewireId' => $this->getId(),
        ];
    }

    /**
     * Update a task's due date (called from calendar drag-and-drop).
     */
    public function updateTaskDueDate(int $taskId, string $date): void
    {
        $project = $this->getRecord();
        $task = Task::query()
            ->where('id', $taskId)
            ->where('project_id', $project->id)
            ->whereHas('users', fn ($q) => $q->where('users.id', Auth::id()))
            ->first();

        if (! $task) {
            return;
        }

        $task->update(['due_date' => $date]);
    }
}
