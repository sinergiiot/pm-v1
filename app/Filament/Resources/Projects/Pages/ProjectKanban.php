<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use App\Models\Task;
use Filament\Facades\Filament;
use App\Models\TaskStatus;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Relaticle\Flowforge\Board;
use Relaticle\Flowforge\BoardResourcePage;
use Relaticle\Flowforge\Column;
use Relaticle\Flowforge\Components\CardFlex;

class ProjectKanban extends BoardResourcePage
{
    use InteractsWithRecord;

    protected static ?string $projectTabPermission = 'ViewProjectResourceProjectKanban';

    public static function canAccess(array $parameters = []): bool
    {
        $user = Filament::auth()?->user();
        if (static::$projectTabPermission && $user) {
            return $user->can(static::$projectTabPermission);
        }
        return parent::canAccess($parameters);
    }

    protected static string $resource = ProjectResource::class;

    protected static ?string $title = 'Board';

    protected static ?string $navigationLabel = 'Board';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-view-columns';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function board(Board $board): Board
    {
        $project = $this->getRecord();

        // Build columns dynamically from this project's task statuses
        $columns = $project->taskStatuses()
            ->orderBy('order')
            ->get()
            ->map(fn (TaskStatus $status) =>
                Column::make((string) $status->id)
                    ->label($status->name)
                    ->color($status->color)
            )
            ->toArray();

        return $board
            ->query(
                $project->tasks()
                    ->whereHas('users', fn ($q) => $q->where('users.id', Auth::id()))
                    ->getQuery()
            )
            ->columnIdentifier('task_status_id')
            ->positionIdentifier('position')
            ->recordTitleAttribute('title')
            ->columns($columns)
            ->cardSchema(fn (Schema $schema) => $schema->components([
                TextEntry::make('users.name')
                    ->weight('bold')
                    ->hiddenLabel(),
                CardFlex::make([
                    TextEntry::make('taskPriority.name')
                        ->badge()
                        // ->icon('heroicon-o-flag')
                        ->hiddenLabel()
                        ->placeholder('-'),
                    TextEntry::make('due_date')
                        ->badge()
                        ->date()
                        ->icon('heroicon-o-calendar')
                        ->hiddenLabel()
                        ->placeholder('-'),
                    TextEntry::make('epic.title')
                        ->badge()
                        ->color('gray')
                        ->icon('heroicon-o-rectangle-stack')
                        ->hiddenLabel()
                        ->placeholder('-'),
                ])->wrap()->justify('start'),
            ]))
            ->columnActions([
                CreateAction::make()
                    ->label('Add Task')
                    ->model(Task::class)
                    ->form([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Grid::make(2)
                            ->schema([
                                Select::make('epic_id')
                                    ->label('Epic')
                                    ->relationship(
                                        'epic',
                                        'title',
                                        modifyQueryUsing: fn ($query) =>
                                            $query->where('project_id', $this->getRecord()->id)
                                    )
                                    ->searchable()
                                    ->preload(),
                                Select::make('task_priority_id')
                                    ->label('Priority')
                                    ->relationship(
                                        'taskPriority',
                                        'name',
                                        modifyQueryUsing: fn ($query) =>
                                            $query->where('project_id', $this->getRecord()->id)
                                    )
                                    ->preload(),
                                DatePicker::make('due_date')
                                    ->label('Due Date')
                                    ->native(false),
                                Select::make('users')
                                    ->label('Assignee')
                                    ->multiple()
                                    ->relationship(
                                        'users',
                                        'name',
                                        modifyQueryUsing: fn ($query) =>
                                            $query->whereHas('projects', fn ($q) => $q->where('projects.id', $this->getRecord()->id))
                                    )
                                    ->searchable()
                                    ->preload(),
                            ]),
                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->mutateFormDataUsing(function (array $data, array $arguments): array {
                        $data['project_id'] = $this->getRecord()->id;

                        if (isset($arguments['column'])) {
                            $data['task_status_id'] = (int) $arguments['column'];
                            $data['position'] = $this->getBoardPositionInColumn($arguments['column']);
                        }

                        return $data;
                    })
                    ->after(function (Task $record, array $data): void {
                        $record->users()->sync($data['users'] ?? []);
                    }),
            ])
            ->cardActions([
                EditAction::make()
                    ->model(Task::class)
                    ->form([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Grid::make(2)
                            ->schema([
                                Select::make('task_status_id')
                                    ->label('Status')
                                    ->options(function () {
                                        return $this->getRecord()
                                            ->taskStatuses()
                                            ->orderBy('order')
                                            ->pluck('name', 'id');
                                    })
                                    ->required(),
                                Select::make('epic_id')
                                    ->label('Epic')
                                    ->relationship(
                                        'epic',
                                        'title',
                                        modifyQueryUsing: fn ($query) =>
                                            $query->where('project_id', $this->getRecord()->id)
                                    )
                                    ->searchable()
                                    ->preload(),
                                Select::make('task_priority_id')
                                    ->label('Priority')
                                    ->relationship(
                                        'taskPriority',
                                        'name',
                                        modifyQueryUsing: fn ($query) =>
                                            $query->where('project_id', $this->getRecord()->id)
                                    )
                                    ->preload(),
                                DatePicker::make('start_date')
                                    ->label('Start Date')
                                    ->native(false),
                                DatePicker::make('due_date')
                                    ->label('Due Date')
                                    ->native(false),
                                Select::make('users')
                                    ->label('Assignee')
                                    ->multiple()
                                    ->relationship(
                                        'users',
                                        'name',
                                        modifyQueryUsing: fn ($query) =>
                                            $query->whereHas('projects', fn ($q) => $q->where('projects.id', $this->getRecord()->id))
                                    )
                                    ->searchable()
                                    ->preload(),
                            ]),
                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                DeleteAction::make()
                    ->model(Task::class),
            ])
            ->cardAction('edit');
            // ->searchable(['title', 'description']);
    }
}
