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
    public function register(): void
    {
        parent::register();

        /**
         * The goal here is re-boot the panel after tenancy has been initialized, so we can
         * fetch customizations from the tenant. Right now, I'm relying on the 'data' property
         * of the tenant, which automatically converts most model properties to JSON. I am
         * considering a new relationship / model for panel settings, but this is a good start.
         */

        $this->app->afterResolving(DatabaseTenancyBootstrapper::class, function () {
            $tenant = tenant();
            $this->app
                ->get('filament')
                ->getPanel('manage')
                ->colors([
                    'danger' => $tenant->colors['manage']['danger'] ?? Color::Red,
                    'primary' => $tenant->colors['manage']['primary'] ?? Color::Stone,
                    'info' => $tenant->colors['manage']['info'] ?? Color::Blue,
                    'success' => $tenant->colors['manage']['success'] ?? Color::Green,
                    'warning' => $tenant->colors['manage']['warning'] ?? Color::Orange,
                    'gray' => $tenant->colors['manage']['gray'] ?? Color::Green,
                ])
                ->brandLogo(tenant()?->logo ? asset(Storage::url('images/'.tenant()->id.'/'.tenant()->logo)) : '')
                ->boot();
        });
}

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('manage')
            ->path('manage')
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
            ->brandLogo(tenant()?->logo ? asset(Storage::url('images/'.tenant()->id.'/'.tenant()->logo)) : '')
            ->discoverResources(in: app_path('Filament/Manage/Resources'), for: 'App\\Filament\\Manage\\Resources')
            ->discoverPages(in: app_path('Filament/Manage/Pages'), for: 'App\\Filament\\Manage\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Manage/Widgets'), for: 'App\\Filament\\Manage\\Widgets')
            ->widgets([
//                Widgets\AccountWidget::class,
//                Widgets\FilamentInfoWidget::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
