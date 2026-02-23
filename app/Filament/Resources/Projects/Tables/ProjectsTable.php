<?php

namespace App\Filament\Resources\Projects\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Filament\Support\Colors\Color;

class ProjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('users.name')
                    ->label('Created By')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Team Size')
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-o-user-group'),
                TextColumn::make('description')
                    ->searchable()
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('due_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('remaining_days')
                    ->label('Remaining Days')
                    ->getStateUsing(fn ($record) => $record->remainingDays())
                    ->badge()
                    ->color(fn ($state, $record): string => match (true) {
                        !$record->end_date => 'gray',
                        $state < 0 => 'danger',           // Overdue (negative days)
                        $state === 0 => 'warning',        // Due today
                        $state <= 7 => 'warning',         // Due within a week
                        $state <= 30 => 'info',           // Due within a month
                        default => 'success',              // More than 30 days
                    })
                    ->icon(fn ($state, $record): ?string => match (true) {
                        !$record->end_date => 'heroicon-o-minus-circle',
                        $state < 0 => 'heroicon-o-exclamation-triangle',
                        $state === 0 => 'heroicon-o-bell-alert',
                        $state <= 7 => 'heroicon-o-clock',
                        default => 'heroicon-o-calendar',
                    })
                    ->formatStateUsing(fn ($state, $record): string => match (true) {
                        !$record->end_date => 'No deadline',
                        $state < 0 => abs($state) . ' days overdue',
                        $state === 0 => 'Due today',
                        $state === 1 => '1 day left',
                        default => "{$state} days left",
                    })
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
