<?php

namespace App\Providers\Filament;

use App\Filament\Overrides\LoginNotFound;
use App\Filament\Widgets\Account\CompanyList;
use App\Filament\Widgets\Account\StatsOverview;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Pages\Auth\Login;
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

class AccountPanelProvider extends PanelProvider
{
    public const PANEL = 'account';

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id(self::PANEL)
            ->path(self::PANEL)
            ->spa()
            ->login(! in_array(request()->getHost(), config('tenancy.central_domains'))
                ? LoginNotFound::class
                : Login::class
            )
            ->registration()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                CompanyList::class,
                StatsOverview::class,
            ])
            ->plugins([
                BreezyCore::make()
                    ->myProfile(shouldRegisterNavigation: true)
                    ->enableSanctumTokens(permissions: ['create', 'read', 'update', 'delete']),
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
            ]);
    }
}
