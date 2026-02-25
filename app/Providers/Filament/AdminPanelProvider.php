<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

//Awin Theme
use Resma\FilamentAwinTheme\FilamentAwinTheme;

//Filament Socialite
use DutchCodingCompany\FilamentSocialite\FilamentSocialitePlugin;
use DutchCodingCompany\FilamentSocialite\Provider;
use Filament\Support\Colors;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;
use Illuminate\Contracts\Auth\Authenticatable;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Filament\View\FilamentView;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationItem;
use App\Models\Project;
use App\Filament\Resources\Projects\ProjectResource;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;

class AdminPanelProvider extends PanelProvider
{
    public function boot(): void
    {
        Filament::serving(function () {
            $panel = Filament::getCurrentPanel();

            if (! $panel || $panel->getId() !== 'admin') {
                return;
            }

            $user = Filament::auth()?->user();

            if (! $user || ! Schema::hasTable('projects')) {
                return;
            }

            $projects = Project::query()
                ->whereHas('users', fn ($query) => $query->whereKey($user->getKey()))
                ->orderBy('title')
                ->get();

            $panel->navigationItems([
                NavigationItem::make('Create Project')
                    ->icon('heroicon-o-plus-circle')
                    ->url(fn (): string => ProjectResource::getUrl('create'))
                    ->sort(-10)
                    ->visible(fn (): bool => Auth::user()?->can('Create:Project') ?? false),
            ]);

            if ($projects->isEmpty()) {
                return;
            }

            $panel->navigationItems(
                $projects->map(
                    fn (Project $project): NavigationItem => NavigationItem::make($project->title)
                        ->group('Projects')
                        ->icon('heroicon-o-rectangle-stack')
                        ->url(fn (): string => ProjectResource::getUrl('view', ['record' => $project]))
                        ->sort(3),
                )->all(),
            );
        });
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                \App\Filament\Widgets\StatsOverview::class,
                \App\Filament\Widgets\MyTasksWidget::class,
                \App\Filament\Widgets\ProjectProgressWidget::class,
                \App\Filament\Widgets\UpcomingDeadlinesWidget::class,
                // AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make()
                    ->navigationGroup('System'),
                FilamentAwinTheme::make(),
                FilamentSocialitePlugin::make()
                    // (required) Add providers corresponding with providers in `config/services.php`.
                    ->providers([
                        Provider::make('google')
                            ->label('Google')
                            ->icon('fab-google')
                            ->color(Color::hex('#000000'))
                            ->outlined(false)
                            ->stateless(false)
                            ->scopes(['https://www.googleapis.com/auth/calendar.events'])
                            ->with([
                                'access_type' => 'offline',
                                'prompt' => 'consent',
                            ]),
                    ])
                    ->slug('admin')
                    ->registration(true)
                    ->createUserUsing(function (string $provider, \Laravel\Socialite\Contracts\User $oauthUser) {
                        $user = \App\Models\User::where('email', $oauthUser->getEmail())->first();

                        if (! $user) {
                            abort(403, 'Akses ditolak. Email Anda belum didaftarkan oleh Administrator aplikasi ini.');
                        }

                        // Jika sudah didaftarkan lewat panel User (Whitelist),
                        // paket ini otomatis akan menautkan akun Google ke User ini.
                        return $user;
                    })
                    ->userModelClass(\App\Models\User::class)
                    ->socialiteUserModelClass(\App\Models\SocialiteUser::class)
            ])
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
                fn () => Blade::render('<div class="text-center mt-4 text-xs text-gray-500"><a href="{{ url("/privacy-policy") }}" class="underline hover:text-amber-600">Privacy Policy</a></div>')
            )
            ->viteTheme('resources/css/filament/admin/theme.css');
    }
}
