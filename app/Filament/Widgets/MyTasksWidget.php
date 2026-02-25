<?php

namespace App\Filament\Widgets;

use App\Models\Task;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\Projects\ProjectResource;
use Filament\Actions\Action;

class MyTasksWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    
    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Tugas Saya (Belum Selesai)';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Task::query()
                    ->whereHas('users', fn ($q) => $q->where('users.id', Auth::id()))
                    ->whereHas('taskStatus', function ($q) {
                        $q->whereRaw('task_statuses.order < (select max(`order`) from task_statuses as ts where ts.project_id = task_statuses.project_id)');
                    })
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul Tugas')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn (Task $record): string => $record->project->title),
                Tables\Columns\TextColumn::make('taskPriority.name')
                    ->label('Prioritas')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Critical' => 'danger',
                        'High' => 'warning',
                        'Medium' => 'primary',
                        'Low' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Deadline')
                    ->date()
                    ->sortable()
                    ->color(fn ($state) => ($state && \Carbon\Carbon::parse($state)->isPast()) ? 'danger' : null),
                Tables\Columns\TextColumn::make('taskStatus.name')
                    ->label('Status')
                    ->badge()
                    ->color(fn (Task $record): string => $record->taskStatus->color ?? 'gray'),
            ])
            ->actions([
                Action::make('view')
                    ->label('Buka Project')
                    ->icon('heroicon-m-arrow-right-circle')
                    ->url(fn (Task $record): string => ProjectResource::getUrl('view', ['record' => $record->project_id])),
            ]);
    }
}
