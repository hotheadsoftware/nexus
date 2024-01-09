<?php

namespace App\Providers;

use App\Services\Colors;
use App\Services\Domains;
use App\Services\Roles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->setFacades();
        $this->setMacros();
        $this->setModelOptions();
        $this->setSanctumGuard();
    }

    protected function setModelOptions(): void
    {
        Model::shouldBeStrict();
        Model::unguard();
    }

    protected function setSanctumGuard(): void
    {
        // By default, we use the 'web' guard which points to the users table. This works fine
        // for our primary panel, but on all other panels I want to ensure that only the users
        // that belong to that panel can authenticate for the API.

        // This does force a convention upon our API routing: /api/{panel}/{version?}/{noun}.

        if (Request::segment(1) === 'api') {
            Config::set('sanctum.guard', [Request::segment(2)]);
        }
    }

    protected function setMacros(): void
    {
        \Illuminate\Http\Request::macro('inTenantContext', function () {
            return ! in_array(request()->getHost(), config('tenancy.central_domains'));
        });
    }

    protected function setFacades(): void
    {
        $this->app->bind('colors', function () {
            return new Colors();
        });

        $this->app->bind('domains', function () {
            return new Domains();
        });

        $this->app->bind('roles', function () {
            return new Roles();
        });
    }

}
