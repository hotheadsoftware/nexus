<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Stancl\Tenancy\Bootstrappers\DatabaseTenancyBootstrapper;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

class ManagePanelProvider extends PanelProvider
{
    public const PANEL = 'manage';

    public function register(): void
    {
        parent::register();

        $this->app->afterResolving(DatabaseTenancyBootstrapper::class, function () {
            $brand = tenant()->brands->where('panel', self::PANEL)->first();
            if ($brand) {
                $this->app
                    ->get('filament')
                    ->getPanel('manage')
                    ->colors([
                        'danger' => $brand->colors['manage']['danger'] ?? Color::Red,
                        'primary' => $brand->colors['manage']['primary'] ?? Color::Stone,
                        'info' => $brand->colors['manage']['info'] ?? Color::Blue,
                        'success' => $brand->colors['manage']['success'] ?? Color::Green,
                        'warning' => $brand->colors['manage']['warning'] ?? Color::Orange,
                        'gray' => $brand->colors['manage']['gray'] ?? Color::Green,
                    ])
                    ->brandLogo($brand->logo ? asset(Storage::url('images/'.$brand->tenant_id.'/'.$brand->logo)) : '')
                    ->boot();
            }
        });
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id(self::PANEL)
            ->path(self::PANEL)
            ->login()
            ->registration()
            ->middleware([
                PreventAccessFromCentralDomains::class,
                InitializeTenancyByDomain::class,
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
            ->colors([
                'danger' => Color::Red,
                'primary' => Color::Stone,
                'info' => Color::Blue,
                'success' => Color::Green,
                'warning' => Color::Orange,
                'gray' => Color::Green,
            ])
            ->discoverResources(in: app_path('Filament/Manage/Resources'), for: 'App\\Filament\\Manage\\Resources')
            ->discoverPages(in: app_path('Filament/Manage/Pages'), for: 'App\\Filament\\Manage\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Manage/Widgets'), for: 'App\\Filament\\Manage\\Widgets')
            ->widgets([
                // Add your home-page dashboard widgets here.
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
