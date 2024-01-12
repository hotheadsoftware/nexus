<?php

namespace App\Providers\Filament;

use App\Facades\Colors;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Jeffgreco13\FilamentBreezy\BreezyCore;
use Stancl\Tenancy\Bootstrappers\DatabaseTenancyBootstrapper;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

class OperatePanelProvider extends PanelProvider
{
    public const PANEL = 'operate';

    public function register(): void
    {
        parent::register();

        $this->app->afterResolving(DatabaseTenancyBootstrapper::class, function () {
            tenant()?->brands->where('panel', self::PANEL)?->first()?->applyToPanel(self::PANEL, tenant());
        });
    }

    public function panel(Panel $panel): Panel
    {

        return $panel
            ->id(self::PANEL)
            ->path(self::PANEL)
            ->spa()
            ->login()
            ->registration()
            ->authGuard(self::PANEL)
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
                'danger'  => Color::Red,
                'primary' => Color::Stone,
                'info'    => Color::Blue,
                'success' => Color::Green,
                'warning' => Color::Orange,
                'gray'    => Color::Green,
            ])
            ->discoverResources(in: app_path('Filament/'.ucfirst(self::PANEL).'/Resources'),
                for: 'App\\Filament\\'.ucfirst(self::PANEL).'\\Resources')
            ->discoverPages(in: app_path('Filament/'.ucfirst(self::PANEL).'/Pages'),
                for: 'App\\Filament\\'.ucfirst(self::PANEL).'\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->plugins([
                BreezyCore::make()
                    ->myProfile(shouldRegisterNavigation: false)
                    ->enableSanctumTokens(permissions: ['create', 'read', 'update', 'delete']),
            ])
            ->discoverWidgets(in: app_path('Filament/'.ucfirst(self::PANEL).'/Widgets'),
                for: 'App\\Filament\\'.ucfirst(self::PANEL).'\\Widgets')
            ->widgets([
                // Add your home-page dashboard widgets here.
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
