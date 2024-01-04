<?php

namespace App\Providers\Filament;

use App\Helpers\ColorHelper;
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
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
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
                    ->registration($brand->allow_registration ?? false)
                    ->colors([
                        'danger'  => ColorHelper::getShades($brand->colors['manage']['danger'] ?? '') ?? Color::Red,
                        'primary' => ColorHelper::getShades($brand->colors['manage']['primary'] ?? '') ?? Color::Stone,
                        'info'    => ColorHelper::getShades($brand->colors['manage']['info'] ?? '') ?? Color::Blue,
                        'success' => ColorHelper::getShades($brand->colors['manage']['success'] ?? '') ?? Color::Green,
                        'warning' => ColorHelper::getShades($brand->colors['manage']['warning'] ?? '') ?? Color::Orange,
                        'gray'    => ColorHelper::getShades($brand->colors['manage']['gray'] ?? '') ?? Color::Green,
                    ])
                    ->brandLogo(fn() => view('filament.logo.manage'))
                    ->boot();
            }
        });
    }

    public function getColors($name): array
    {
        return ColorHelper::getShades($name);
    }

    public function panel(Panel $panel): Panel
    {

        return $panel
            ->id(self::PANEL)
            ->path(self::PANEL)
            ->spa()
            ->login()
            ->registration()
            ->authGuard('operator')
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
