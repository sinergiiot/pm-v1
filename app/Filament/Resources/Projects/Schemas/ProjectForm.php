<?php

namespace App\Filament\Resources\Projects\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\ColorPicker;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Step::make('Project Identity')
                        ->description('Define the project details')
                        ->icon('heroicon-o-identification')
                        ->schema([
                            TextInput::make('title')
                                ->required()
                                ->maxLength(255)
                                ->placeholder('Enter project title')
                                ->columnSpanFull(),
                            Textarea::make('description')
                                ->rows(4)
                                ->placeholder('Describe your project')
                                ->columnSpanFull(),
                            DatePicker::make('due_date')
                                ->label('Due Date')
                                ->native(false)
                                ->placeholder('Select due date'),
                            DatePicker::make('end_date')
                                ->label('End Date')
                                ->native(false)
                                ->placeholder('Select end date')
                                ->afterOrEqual('due_date'),
                        ])
                        ->columns(2),

                    Step::make('Task Statuses')
                        ->description('Define the task statuses for the project')
                        ->icon('heroicon-o-check-circle')
                        ->schema([
                            Repeater::make('task_statuses')
                                ->schema([
                                    TextInput::make('name')
                                        ->required()
                                        ->maxLength(255),
                                    ColorPicker::make('color')
                                        ->required(),
                                    TextInput::make('order')
                                        ->required()
                                        ->numeric()
                                        ->default(0),
                                ])
                                ->columns(3)
                                ->defaultItems(5)
                                ->default([
                                    ['name' => 'Backlog', 'color' => '#6B7280', 'order' => 1],
                                    ['name' => 'Todo', 'color' => '#3B82F6', 'order' => 2],
                                    ['name' => 'In Progress', 'color' => '#F59E0B', 'order' => 3],
                                    ['name' => 'Review', 'color' => '#8B5CF6', 'order' => 4],
                                    ['name' => 'Done', 'color' => '#10B981', 'order' => 5],
                                ])
                                ->addActionLabel('Add Status')
                                ->reorderable()
                                ->columnSpanFull()
                                ->dehydrated(),
                        ])
                        ->columns(1),

                    Step::make('Clusters')
                        ->description('Define document clusters and their review prompts')
                        ->icon('heroicon-o-squares-2x2')
                        ->schema([
                            Repeater::make('clusters')
                                ->schema([
                                    TextInput::make('name')
                                        ->required()
                                        ->maxLength(255)
                                        ->placeholder('e.g. Cluster A'),
                                    Textarea::make('prompt')
                                        ->label('AI review prompt (optional)')
                                        ->placeholder('Prompt for AI to check document format...')
                                        ->rows(3)
                                        ->columnSpanFull(),
                                    TextInput::make('order')
                                        ->required()
                                        ->numeric()
                                        ->default(0),
                                ])
                                ->columns(2)
                                ->defaultItems(4)
                                ->default([
                                    ['name' => 'Risalah Meeting', 'prompt' => null, 'order' => 1],
                                    ['name' => 'Cluster A', 'prompt' => null, 'order' => 2],
                                    ['name' => 'Cluster B', 'prompt' => null, 'order' => 3],
                                    ['name' => 'Cluster C', 'prompt' => null, 'order' => 4],
                                ])
                                ->addActionLabel('Add cluster')
                                ->reorderable()
                                ->columnSpanFull()
                                ->dehydrated(),
                        ])
                        ->columns(1),

                    Step::make('User Assignment')
                        ->description('Assign team members to the project')
                        ->icon('heroicon-o-user-group')
                        ->schema([
                            Select::make('users')
                                ->relationship('users', 'name')
                                ->multiple()
                                ->preload()
                                ->searchable()
                                ->placeholder('Select additional team members')
                                ->helperText('💡 You will be automatically added as the project owner. Select additional team members here.')
                                ->columnSpanFull()
                                ->default(function () {
                                    return [\Illuminate\Support\Facades\Auth::id()];
                                }),
                        ]),
                ])
                ->columnSpanFull()
                ->skippable(),
            ]);
    }
}
