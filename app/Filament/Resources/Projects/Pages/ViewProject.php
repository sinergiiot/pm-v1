<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use App\Models\Meeting;
use App\Services\ZoomService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class ViewProject extends ViewRecord
{
    protected static ?string $projectTabPermission = 'ViewProjectResourceViewProject';

    public static function canAccess(array $parameters = []): bool
    {
        $user = Filament::auth()?->user();
        if (static::$projectTabPermission && $user) {
            return $user->can(static::$projectTabPermission);
        }
        return parent::canAccess($parameters);
    }
    protected static string $resource = ProjectResource::class;

    protected static ?string $navigationLabel = 'Overview';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-information-circle';

    public function generateShareToken(): void
    {
        $this->getRecord()->generateShareToken();
        $this->getRecord()->refresh();
        Notification::make()
            ->title('Share link generated')
            ->success()
            ->send();
    }

    public function regenerateShareToken(): void
    {
        $this->getRecord()->generateShareToken();
        $this->getRecord()->refresh();
        Notification::make()
            ->title('Share link regenerated. Old link no longer works.')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('shareLink')
                ->label('Share link')
                ->icon('heroicon-o-link')
                ->color('gray')
                ->modalHeading('Share project (read-only)')
                ->modalDescription('Klien dapat melihat task list dan progress lewat link ini. Tanpa login.')
                ->modalContent(fn (): View => view('filament.resources.projects.share-link-modal', [
                    'record' => $this->getRecord(),
                ]))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Tutup'),
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
            EditAction::make(),
        ];
    }
}
