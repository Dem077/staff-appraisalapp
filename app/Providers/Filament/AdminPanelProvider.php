<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\EditProfile;
use Filament\Support\Enums\Width;
use Filament\Pages\Dashboard;
use Awcodes\Overlook\Widgets\OverlookWidget;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Hasnayeen\Themes\ThemesPlugin;
use Njxqlus\FilamentProgressbar\FilamentProgressbarPlugin;
use DiogoGPinto\AuthUIEnhancer\AuthUIEnhancerPlugin;
use Awcodes\LightSwitch\LightSwitchPlugin;
use Awcodes\LightSwitch\Enums\Alignment;
use Awcodes\Overlook\OverlookPlugin;
use App\Filament\Admin\Resources\Users\UserResource;
use Joaopaulolndev\FilamentEditProfile\FilamentEditProfilePlugin;
use Hasnayeen\Themes\Http\Middleware\SetTheme;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Joaopaulolndev\FilamentEditProfile\Pages\EditProfilePage;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->spa()
            ->login()
            ->passwordReset()
            ->profile(EditProfile::class, isSimple: false)
            ->defaultThemeMode(ThemeMode::Light)
            ->font('Montserrat')
            ->colors([
                'primary' => Color::Blue,
            ])
            ->maxContentWidth(Width::SevenExtraLarge)
            ->sidebarCollapsibleOnDesktop()
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\\Filament\\Admin\\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\\Filament\\Admin\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverClusters(in: app_path('Filament/Admin/Clusters'), for: 'App\\Filament\\Admin\\Clusters')
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\\Filament\\Admin\\Widgets')
            ->widgets([
                OverlookWidget::class,
            ])
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Administration'),
            ])
            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->label(fn () => auth()->user()->name)
                    ->url(fn (): string => EditProfilePage::getUrl())
                    ->icon('heroicon-m-user-circle'),
                // 'profile' => \Filament\Navigation\MenuItem::make()
                //     ->label(fn () => auth()->user()->name)
                //     ->icon('heroicon-m-user-circle'),
            ])
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
                FilamentProgressbarPlugin::make()->color('#29b'),
                AuthUIEnhancerPlugin::make()
                    ->showEmptyPanelOnMobile(false)
                    ->formPanelPosition('right')
                    ->formPanelWidth('40%')
                    ->emptyPanelBackgroundImageOpacity('70%')
                    ->emptyPanelBackgroundImageUrl('https://picsum.photos/seed/picsum/1260/750.webp/?blur=1'),
                LightSwitchPlugin::make()
                    ->position(Alignment::BottomCenter)
                    ->enabledOn([
                        'auth.login',
                        'auth.password',
                    ]),
                OverlookPlugin::make()
                    ->includes([
                        UserResource::class,
                    ]),
                FilamentEditProfilePlugin::make()
                    ->slug('my-profile')
                    ->setTitle('My Profile')
                    ->shouldRegisterNavigation(false)
                    ->shouldShowDeleteAccountForm(false)
                    ->shouldShowSanctumTokens(false)
                    ->shouldShowBrowserSessionsForm()
                    ->shouldShowAvatarForm(),
            ])
            ->resources([
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
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
            ]);
    }
}
