<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use App\Models\Meeting;
use Filament\Facades\Filament;
use App\Services\ZoomService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class ProjectZoom extends Page implements \Filament\Tables\Contracts\HasTable
{
    use InteractsWithRecord;

    protected static ?string $projectTabPermission = 'ViewProjectResourceProjectZoom';

    public static function canAccess(array $parameters = []): bool
    {
        $user = Filament::auth()?->user();
        if (static::$projectTabPermission && $user) {
            return $user->can(static::$projectTabPermission);
        }
        return parent::canAccess($parameters);
    }
    use \Filament\Tables\Concerns\InteractsWithTable;

    protected static string $resource = ProjectResource::class;

    protected static ?string $title = 'Zoom';

    protected static ?string $navigationLabel = 'Zoom';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-video-camera';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->mountCanAuthorizeAccess();
    }

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder|Relation|null
    {
        return $this->getRecord()->meetings();
    }

    public function table(Table $table): Table
    {
        return $table
            ->modelLabel('Meeting')
            ->pluralModelLabel('Meetings')
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

    protected function getHeaderActions(): array
    {
        return [
            Action::make('scheduleZoomMeeting')
                ->label('Schedule Zoom Meeting')
                ->icon('heroicon-o-video-camera')
                ->color('primary')
                ->modalHeading('Schedule Zoom Meeting')
                ->modalDescription(
                    app(ZoomService::class)->isConfigured()
                        ? 'Create a Zoom meeting. It will appear on the project calendar with a join link.'
                        : 'Create a meeting schedule. Configure Zoom in Settings to get real Zoom join links.'
                )
                ->modalSubmitActionLabel('Create meeting')
                ->form([
                    TextInput::make('title')
                        ->label('Meeting title')
                        ->required()
                        ->maxLength(255),
                    Textarea::make('agenda')
                        ->label('Agenda')
                        ->rows(3)
                        ->columnSpanFull(),
                    DateTimePicker::make('start')
                        ->label('Start')
                        ->required()
                        ->native(false)
                        ->seconds(false),
                    DateTimePicker::make('end')
                        ->label('End')
                        ->required()
                        ->native(false)
                        ->seconds(false)
                        ->after('start'),
                ])
                ->action(function (array $data): void {
                    $project = $this->getRecord();
                    $zoom = app(ZoomService::class);

                    $zoomMeetingId = null;
                    $zoomJoinUrl = null;
                    $zoomStartUrl = null;

                    $zoomConfigured = $zoom->isConfigured();
                    if ($zoomConfigured) {
                        $result = $zoom->createMeeting([
                            'title' => $data['title'],
                            'agenda' => $data['agenda'] ?? null,
                            'start' => $data['start'],
                            'end' => $data['end'],
                        ]);
                        if ($result) {
                            $zoomMeetingId = $result['id'];
                            $zoomJoinUrl = $result['join_url'];
                            $zoomStartUrl = $result['start_url'];
                        }
                    }

                    Meeting::create([
                        'project_id' => $project->getKey(),
                        'title' => $data['title'],
                        'agenda' => $data['agenda'] ?? null,
                        'start' => $data['start'],
                        'end' => $data['end'],
                        'zoom_meeting_id' => $zoomMeetingId,
                        'zoom_join_url' => $zoomJoinUrl,
                        'zoom_start_url' => $zoomStartUrl,
                        'created_by' => Auth::id(),
                    ]);

                    if ($zoomJoinUrl) {
                        Notification::make()
                            ->title('Meeting scheduled')
                            ->body('The Zoom meeting has been created and added to the project calendar.')
                            ->success()
                            ->send();
                    } elseif ($zoomConfigured) {
                        Notification::make()
                            ->title('Meeting scheduled (no Zoom link)')
                            ->body('Meeting saved to calendar, but Zoom could not create the link. Check Zoom credentials in Settings and see storage/logs/laravel.log for details.')
                            ->warning()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Meeting scheduled')
                            ->body('The meeting has been added to the project calendar. Configure Zoom in Settings to get join links.')
                            ->success()
                            ->send();
                    }
                }),
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return static::$title ?? 'Zoom';
    }

    public function getHeading(): string|Htmlable
    {
        return static::$navigationLabel ?? 'Zoom';
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                EmbeddedTable::make(),
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
