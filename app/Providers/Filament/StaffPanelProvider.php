<?php

namespace App\Providers\Filament;

use Filament\Pages\Dashboard;
use Filament\Widgets\AccountWidget;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Awcodes\LightSwitch\LightSwitchPlugin;
use Awcodes\LightSwitch\Enums\Alignment;
use Hasnayeen\Themes\ThemesPlugin;
use Njxqlus\FilamentProgressbar\FilamentProgressbarPlugin;
use Hasnayeen\Themes\Http\Middleware\SetTheme;
use App\Filament\Staff\Pages\Auth\CustomLogin;
use App\Http\Middleware\ExternalApiAuthenticate;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class StaffPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('staff')
            ->path('staff')
            ->colors([
                'primary' => Color::Green,
            ])
            ->discoverResources(in: app_path('Filament/Staff/Resources'), for: 'App\\Filament\\Staff\\Resources')
            ->discoverPages(in: app_path('Filament/Staff/Pages'), for: 'App\\Filament\\Staff\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->login(CustomLogin::class)
            ->discoverWidgets(in: app_path('Filament/Staff/Widgets'), for: 'App\\Filament\\Staff\\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->renderHook(PanelsRenderHook::BODY_START, function (): string {

                    return Blade::render(<<<'HTML'
                            <style>
                                @font-face {
                                    font-family: 'Faruma';
                                    src: url('/fonts/Faruma.otf') format('opentype');
                                    font-weight: normal;
                                    font-style: normal;
                                }
                                .thaana-keyboard {
                                    font-family: 'Faruma';
                                }
                            </style>
                    HTML, ); // Pass the $doctorName variable to Blade::render

            })
            ->plugins([
                FilamentShieldPlugin::make()
                    ->gridColumns([
                        'default' => 2,
                        'lg' => 3,
                    ])
                    ->sectionColumnSpan(1)
                    ->checkboxListColumns([
                        'default' => 2,
                        'lg' => 3,
                    ])
                    ->resourceCheckboxListColumns([
                        'default' => 2,
                        'lg' => 3,
                    ]),
                LightSwitchPlugin::make()
                    ->position(Alignment::BottomCenter)
                    ->enabledOn([
                        'auth.login',
                        'auth.password',
                    ]),
                FilamentProgressbarPlugin::make()->color('rgba(7, 121, 50, 1)'),
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
            ->authGuard('staff')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
