<?php

namespace App\Filament\Resources\Tasks\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;

class TaskInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(['default' => 1, 'lg' => 2])
            ->components([
                Section::make('Task details')
                    ->icon('heroicon-o-document-text')
                    ->description('Identity and metadata')
                    ->schema([
                        TextEntry::make('code')
                            ->label('Code')
                            ->badge()
                            ->color('gray'),
                        TextEntry::make('title')
                            ->label('Title')
                            ->weight('bold')
                            ->size('lg')
                            ->columnSpanFull(),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('project.title')
                                    ->label('Project')
                                    ->icon('heroicon-o-folder'),
                                TextEntry::make('epic.title')
                                    ->label('Epic')
                                    ->icon('heroicon-o-squares-2x2'),
                                TextEntry::make('taskStatus.name')
                                    ->label('Status')
                                    ->badge()
                                    ->icon('heroicon-o-arrow-path'),
                                TextEntry::make('taskPriority.name')
                                    ->label('Priority')
                                    ->badge()
                                    ->icon('heroicon-o-flag'),
                                TextEntry::make('due_date')
                                    ->label('Due date')
                                    ->date()
                                    ->placeholder('-')
                                    ->icon('heroicon-o-calendar'),
                                TextEntry::make('created_at')
                                    ->label('Created')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->icon('heroicon-o-clock'),
                            ]),
                    ])
                    ->columnSpan(['default' => 1, 'lg' => 1]),

                Section::make('Description')
                    ->icon('heroicon-o-document-duplicate')
                    ->description('Task description and notes')
                    ->schema([
                        TextEntry::make('description')
                            ->label('')
                            ->placeholder('No description.')
                            ->columnSpanFull()
                            ->markdown(),
                    ])
                    ->columnSpan(['default' => 1, 'lg' => 1]),

                Section::make('Comments')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->description('Discussion and notes on this task')
                    ->schema([
                        Form::make([
                            RichEditor::make('comment')
                                ->label('Add a comment')
                                ->required()
                                ->placeholder('Write a comment...')
                                ->columnSpanFull(),
                        ])
                            ->statePath('commentForm')
                            ->livewireSubmitHandler('addComment')
                            ->footer([
                                View::make('filament.resources.tasks.pages.task-comment-submit'),
                            ]),
                        View::make('filament.resources.tasks.pages.task-comments'),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
