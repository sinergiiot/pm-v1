<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;

/**
 * @property-read Schema $form
 */
class Settings extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string | \UnitEnum | null $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'Settings';

    protected static ?string $title = 'Settings';

    protected static ?string $slug = 'settings';

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user !== null && $user->hasRole('super_admin');
    }

    public function mount(): void
    {
        $this->fillForm();
    }

    protected function fillForm(): void
    {
        $this->form->fill([
            'zoom_account_id' => Setting::get('zoom_account_id', ''),
            'zoom_client_id' => Setting::get('zoom_client_id', ''),
            'zoom_client_secret' => Setting::get('zoom_client_secret', ''),
            'app_name' => Setting::get('app_name', config('app.name')),
        ]);
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Settings')
                    ->tabs([
                        Tab::make('Zoom')
                            ->icon('heroicon-o-video-camera')
                            ->schema([
                                Section::make('Zoom API')
                                    ->description('Configure Zoom OAuth / Server-to-Server app credentials. Used to create scheduled meetings from the project calendar.')
                                    ->schema([
                                        TextInput::make('zoom_account_id')
                                            ->label('Zoom Account ID')
                                            ->placeholder('Your Zoom account ID')
                                            ->maxLength(255),
                                        TextInput::make('zoom_client_id')
                                            ->label('Zoom Client ID')
                                            ->placeholder('OAuth or S2S app Client ID')
                                            ->maxLength(255),
                                        TextInput::make('zoom_client_secret')
                                            ->label('Zoom Client Secret')
                                            ->password()
                                            ->placeholder('Leave blank to keep current value')
                                            ->maxLength(255)
                                            ->dehydrated(fn ($state) => filled($state)),
                                    ])
                                    ->columns(1),
                            ]),
                        Tab::make('General')
                            ->icon('heroicon-o-cog-8-tooth')
                            ->schema([
                                Section::make('General')
                                    ->schema([
                                        TextInput::make('app_name')
                                            ->label('Application name')
                                            ->placeholder(config('app.name'))
                                            ->maxLength(255),
                                    ])
                                    ->columns(1),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    /**
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return Action::make('save')
            ->label('Save')
            ->submit('save')
            ->keyBindings(['mod+s']);
    }

    public function getTitle(): string | Htmlable
    {
        return 'Settings';
    }

    public function getHeading(): string | Htmlable
    {
        return 'Settings';
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $keys = [
            'zoom_account_id',
            'zoom_client_id',
            'zoom_client_secret',
            'app_name',
        ];

        foreach ($keys as $key) {
            $value = $data[$key] ?? null;
            if ($key === 'zoom_client_secret' && blank($value)) {
                continue; // do not overwrite with empty
            }
            Setting::set($key, $value ?? '');
        }

        Notification::make()
            ->title('Settings saved successfully.')
            ->success()
            ->send();
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFormContentComponent(),
            ]);
    }

    public function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('save')
            ->footer([
                Actions::make($this->getFormActions())
                    ->alignment(\Filament\Support\Enums\Alignment::Start)
                    ->key('form-actions'),
            ]);
    }
}
