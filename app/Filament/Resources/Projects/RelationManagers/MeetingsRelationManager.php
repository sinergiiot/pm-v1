<?php

namespace App\Filament\Resources\Projects\RelationManagers;

use App\Models\Meeting;
use App\Services\ZoomService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\View\View;

class MeetingsRelationManager extends RelationManager
{
    protected static string $relationship = 'meetings';

    protected static ?string $title = 'Meetings';

    protected static ?string $modelLabel = 'Meeting';

    protected static ?string $pluralModelLabel = 'Meetings';

    public static function getRelationshipTitle(): string
    {
        return 'Meetings';
    }

    public function table(Table $table): Table
    {
        return $table
            ->modelLabel(static::$modelLabel)
            ->pluralModelLabel(static::$pluralModelLabel)
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('start')
                    ->label('Start')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('end')
                    ->label('End')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('zoom_join_url')
                    ->label('Zoom link')
                    ->formatStateUsing(fn (?string $state): string => $state ? 'Join' : '—')
                    ->url(fn (Model $record): ?string => $record->zoom_join_url, shouldOpenInNewTab: true),
            ])
            ->defaultSort('start', 'desc')
            ->recordActions([
                Action::make('copyInvitation')
                    ->label('Copy invitation')
                    ->icon('heroicon-o-clipboard-document')
                    ->modalHeading('Meeting invitation')
                    ->modalContent(fn (Model $record): View => view(
                        'filament.relation-managers.copy-invitation-modal',
                        ['invitationText' => $this->getInvitationText($record)]
                    ))
                    ->modalSubmitAction(false)
                    ->color('gray'),
                DeleteAction::make()
                    ->label('Delete meeting')
                    ->modalHeading('Delete meeting')
                    ->modalSubmitActionLabel('Delete')
                    ->before(function (Model $record): void {
                        if (filled($record->getAttribute('zoom_meeting_id'))) {
                            app(ZoomService::class)->deleteMeeting((string) $record->getAttribute('zoom_meeting_id'));
                        }
                    }),
            ]);
    }

    protected function getInvitationText(Model $record): string
    {
        assert($record instanceof Meeting);

        $lines = [
            $record->title,
            '',
            'When: ' . $record->start->format('l, F j, Y \a\t g:i A') . ' - ' . $record->end->format('g:i A'),
        ];

        if ($record->agenda) {
            $lines[] = '';
            $lines[] = 'Agenda: ' . $record->agenda;
        }

        if ($record->zoom_join_url) {
            $lines[] = '';
            $lines[] = 'Join Zoom: ' . $record->zoom_join_url;
        } else {
            $lines[] = '';
            $lines[] = '(No Zoom link for this meeting.)';
        }

        return implode("\n", $lines);
    }
}
