<?php

namespace App\Filament\Resources\Projects\Schemas;

use Filament\Infolists\Components\ViewEntry;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;

class ProjectInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(['default' => 1, 'sm' => 2, 'lg' => 3])
            ->components([
                /* Baris 1: Ringkasan Status & Progress */
                Section::make('Project Summary')
                    ->columnSpanFull()
                    ->columns(['default' => 2, 'md' => 4])
                    ->schema([
                        TextEntry::make('status')
                            ->label('Status Proyek')
                            ->getStateUsing(fn ($record) => $record->getProjectStatusLabel())
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'On Track' => 'success',
                                'Due soon' => 'warning',
                                'Overdue' => 'danger',
                                'Inactive' => 'gray',
                                default => 'gray',
                            }),
                        TextEntry::make('task_stats_total')
                            ->label('Total Tugas')
                            ->getStateUsing(fn ($record) => $record->getTaskStats()['total'])
                            ->icon('heroicon-m-clipboard-document-list')
                            ->color('primary'),
                        TextEntry::make('task_stats_done')
                            ->label('Selesai')
                            ->getStateUsing(fn ($record) => $record->getTaskStats()['completed'])
                            ->icon('heroicon-m-check-circle')
                            ->color('success'),
                        ViewEntry::make('task_progress')
                            ->label('Penyelesaian')
                            ->getStateUsing(fn ($record) => $record->getTaskStats())
                            ->view('filament.resources.projects.infolist.progress-bar')
                            ->columnSpan(1),
                    ]),

                /* Baris 2 Kiri: Detail Deskripsi */
                Section::make('Detail & Deskripsi')
                    ->icon('heroicon-o-document-text')
                    ->columnSpan(['default' => 1, 'lg' => 2])
                    ->schema([
                        TextEntry::make('title')
                            ->label('Judul Proyek')
                            ->weight('bold')
                            ->size('lg'),
                        TextEntry::make('description')
                            ->label('Tentang Proyek')
                            ->placeholder('Tidak ada deskripsi.')
                            ->markdown(),
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('due_date')
                                    ->label('Deadline')
                                    ->date('d M Y')
                                    ->icon('heroicon-o-calendar-days'),
                                TextEntry::make('end_date')
                                    ->label('Target Selesai')
                                    ->date('d M Y')
                                    ->icon('heroicon-o-calendar-days'),
                                TextEntry::make('creator.name')
                                    ->label('Pemilik Proyek')
                                    ->icon('heroicon-o-user'),
                            ]),
                    ]),

                /* Baris 2 Kanan: Team Members */
                Section::make('Project Members')
                    ->icon('heroicon-o-user-group')
                    ->columnSpan(['default' => 1, 'lg' => 1])
                    ->schema([
                        RepeatableEntry::make('users')
                            ->label('')
                            ->hiddenLabel()
                            ->schema([
                                ViewEntry::make('user_card')
                                    ->view('filament.resources.projects.infolist.member-card')
                            ])
                            ->contained(false)
                            ->grid(1)
                            ->placeholder('Belum ada anggota tim.')
                            ->columnSpanFull(),
                    ]),

                /* Baris 3: Aktivitas Terbaru */
                Section::make('Recent Activity')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('comments')
                            ->label('')
                            ->hiddenLabel()
                            ->getStateUsing(fn ($record) => $record->comments()->latest()->limit(5)->get())
                            ->schema([
                                ViewEntry::make('comment_item')
                                    ->view('filament.resources.projects.infolist.comment-entry')
                            ])
                            ->contained(false)
                            ->placeholder('Tidak ada aktivitas terbaru.'),
                    ]),
            ]);
    }
}
