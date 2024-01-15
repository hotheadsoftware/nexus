<?php

namespace App\Providers;

use App\Services\Colors;
use App\Services\Domains;
use App\Services\Environment;
use App\Services\Nexus;
use App\Services\Roles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;
use Laravel\Telescope\TelescopeServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->registerNonProdServiceProviders();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->setFacades();
        $this->setModelOptions();
        $this->setSanctumGuard();
    }

    protected function registerNonProdServiceProviders(): void
    {
        if (in_array($this->app->environment(), Environment::ENVIRONMENT_NAMES['local'], true)) {
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    protected function setSanctumGuard(): void
    {
        // API Routes are identified by the first segment being 'api' and the second segment being the panel name.
        // We rely on a config key to exist matching the panel name.

        if (Request::segment(1) === 'api') {
            Config::set('sanctum.guard', [Request::segment(2)]);
        }
    }

    protected function setModelOptions(): void
    {
        Model::shouldBeStrict();
        Model::unguard();
    }

    protected function setFacades(): void
    {
        $this->app->singleton('nexus', function () {
            return new Nexus();
        });

        $this->app->bind('nexus.environment', function () {
            return new Environment($this->request);
        });

        $this->app->bind('nexus.domains', function () {
            return new Domains();
        });

        $this->app->singleton('nexus.colors', function () {
            return new Colors();
        });

        $this->app->bind('nexus.roles', function () {
            return new Roles();
        });
    }
}
