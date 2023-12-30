<?php

namespace App\Providers;

use Illuminate\Auth\SessionGuard;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // This helps us maintain some separation between the concepts of User (central) and Operator (tenant).
        // When we move into Phase 2 (Docker/K8S Stack), we will probably move away from this approach.
        // The goal here is to get to a point where we can safely use User everywhere we need it.

        SessionGuard::macro('operator', function () {
            return SessionGuard::user();
        });
    }
}
