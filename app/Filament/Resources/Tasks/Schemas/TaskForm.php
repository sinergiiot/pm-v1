<?php

namespace App\Filament\Resources\Tasks\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Illuminate\Database\Eloquent\Builder;
use Filament\Schemas\Components\Utilities\Get;

class TaskForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Step::make('Task Identity')
                        ->description('Define the task details')
                        ->icon('heroicon-o-identification')
                        ->schema([
                            Select::make('project_id')
                                ->relationship('project', 'title')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($set) {
                                    $set('task_status_id', null);
                                    $set('task_priority_id', null);
                                    $set('epic_id', null);
                                }),
                            Select::make('epic_id')
                                ->relationship('epic', 'title', modifyQueryUsing: fn (Builder $query, Get $get) =>
                                    $query->where('project_id', $get('project_id')))
                                ->createOptionForm([
                                    Select::make('project_id')
                                        ->relationship('project', 'title')
                                        ->default(fn (Get $get) => $get('project_id'))
                                        ->searchable()
                                        ->preload()
                                        ->required(),
                                    TextInput::make('title')
                                        ->required()
                                        ->maxLength(255)
                                        ->label('Epic Title'),
                                ])
                                ->required()
                                ->searchable()
                                ->preload(),
                            Select::make('task_status_id')
                                ->relationship('taskStatus', 'name', modifyQueryUsing: fn (Builder $query, Get $get) =>
                                    $query->where('project_id', $get('project_id')))
                                ->required()
                                ->preload(),
                            Select::make('task_priority_id')
                                ->relationship('taskPriority', 'name', modifyQueryUsing: fn (Builder $query, Get $get) =>
                                    $query->where('project_id', $get('project_id')))
                                ->required()
                                ->preload(),
                            TextInput::make('title')
                                ->required(),
                            DatePicker::make('start_date')
                                ->label('Start Date')
                                ->native(false),
                            DatePicker::make('due_date')
                                ->label('Due Date')
                                ->native(false),
                            Textarea::make('description')
                                ->columnSpanFull(),
                    ])
                    ->columns(2),
                    Step::make('Task Assignment')
                        ->description('Assign team members to the task')
                        ->icon('heroicon-o-user-group')
                        ->schema([
                            Select::make('users')
                                ->relationship('users', 'name')
                                ->preload()
                                ->multiple()
                        ]),
                ])
                ->columnSpanFull()
                ->skippable(),
                ]);
    }
}
