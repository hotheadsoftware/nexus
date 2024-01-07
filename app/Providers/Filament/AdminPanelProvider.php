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
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * Class AdminPanelProvider
 */
class AdminPanelProvider extends PanelProvider
{
    public const PANEL = 'admin';

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id(self::PANEL)
            ->path(self::PANEL)
            ->login()
            ->authGuard(self::PANEL)
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/'.ucfirst(self::PANEL).'/Resources'),
                for: 'App\\Filament\\'.ucfirst(self::PANEL).'\\Resources')
            ->discoverPages(in: app_path('Filament/'.ucfirst(self::PANEL).'/Pages'),
                for: 'App\\Filament\\'.ucfirst(self::PANEL).'\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/'.ucfirst(self::PANEL).'/Widgets'),
                for: 'App\\Filament\\'.ucfirst(self::PANEL).'\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
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
