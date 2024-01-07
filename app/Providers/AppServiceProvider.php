<?php

namespace App\Providers;

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
        Model::shouldBeStrict();
        Model::unguard();

        // By default, we use the 'web' guard which points to the users table. This works fine
        // for our primary panel, but on all other panels I want to ensure that only the users
        // that belong to that panel can authenticate for the API.

        // This does force a convention upon our API routing: /api/{panel}/{version?}/{noun}.

        if (Request::segment(1) === 'api') {
            Config::set('sanctum.guard', [Request::segment(2)]);
        }
    }
}
