<?php

namespace App\Filament\Resources\Projects\Schemas;

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
            ->columns(['default' => 1, 'lg' => 2])
            ->components([
                /* Kiri: Detail Proyek */
                Section::make('Detail Proyek')
                    ->icon('heroicon-o-document-text')
                    ->description('Informasi dasar proyek')
                    ->schema([
                        TextEntry::make('title')
                            ->label('Judul')
                            ->weight('bold')
                            ->size('lg')
                            ->columnSpanFull(),
                        TextEntry::make('description')
                            ->label('Deskripsi')
                            ->placeholder('-')
                            ->columnSpanFull()
                            ->markdown(),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('due_date')
                                    ->label('Tanggal Jatuh Tempo')
                                    ->date('d M Y')
                                    ->placeholder('-')
                                    ->icon('heroicon-o-calendar'),
                                TextEntry::make('end_date')
                                    ->label('Tanggal Selesai')
                                    ->date('d M Y')
                                    ->placeholder('-')
                                    ->icon('heroicon-o-calendar'),
                            ]),
                        // Grid::make(2)
                        //     ->schema([
                        //         IconEntry::make('is_active')
                        //             ->label('Status')
                        //             ->boolean()
                        //             ->trueIcon('heroicon-o-check-circle')
                        //             ->falseIcon('heroicon-o-x-circle')
                        //             ->trueColor('success')
                        //             ->falseColor('gray'),
                        //         TextEntry::make('creator.name')
                        //             ->label('Dibuat oleh')
                        //             ->placeholder('-')
                        //             ->icon('heroicon-o-user'),
                        //     ]),
                        // Grid::make(2)
                        //     ->schema([
                        //         TextEntry::make('created_at')
                        //             ->label('Dibuat pada')
                        //             ->dateTime('d M Y, H:i')
                        //             ->placeholder('-'),
                        //         TextEntry::make('updated_at')
                        //             ->label('Diperbarui pada')
                        //             ->dateTime('d M Y, H:i')
                        //             ->placeholder('-'),
                        //     ]),
                    ])
                    ->columnSpan(['default' => 1, 'lg' => 1]),

                /* Kanan: Anggota Tim */
                Section::make('Project Members')
                    ->icon('heroicon-o-user-group')
                    ->description('Daftar anggota dalam proyek ini')
                    ->schema([
                        RepeatableEntry::make('users')
                            ->label('')
                            ->hiddenLabel()
                            ->schema([
                                TextEntry::make('name')
                                    ->weight('medium')
                                    ->icon('heroicon-o-user-circle'),
                            ])
                            ->columns(1)
                            ->grid(1)
                            ->contained(false)
                            ->placeholder('Belum ada anggota tim.')
                            ->columnSpanFull(),
                    ])
                    ->columnSpan(['default' => 1, 'lg' => 1]),
            ]);
    }
}
