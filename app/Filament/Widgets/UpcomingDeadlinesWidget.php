<?php

namespace App\Filament\Widgets;

use App\Models\Task;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\Projects\ProjectResource;
use Filament\Actions\Action;

class UpcomingDeadlinesWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'half';

    protected static ?string $heading = 'Deadline Mendatang (7 Hari)';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Task::query()
                    ->whereHas('users', fn ($q) => $q->where('users.id', Auth::id()))
                    ->whereHas('taskStatus', function ($q) {
                        $q->whereRaw('task_statuses.order < (select max(`order`) from task_statuses as ts where ts.project_id = task_statuses.project_id)');
                    })
                    ->where('due_date', '>=', now()->startOfDay())
                    ->where('due_date', '<=', now()->addDays(7)->endOfDay())
                    ->orderBy('due_date')
            )
            ->columns([
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable()
                    ->weight('bold')
                    ->color('warning'),
                Tables\Columns\TextColumn::make('title')
                    ->label('Tugas')
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
            ])
            ->actions([
                Action::make('view')
                    ->label('Tampilkan')
                    ->icon('heroicon-m-eye')
                    ->url(fn (Task $record): string => ProjectResource::getUrl('view', ['record' => $record->project_id])),
            ])
            ->paginated(false);
    }
}
