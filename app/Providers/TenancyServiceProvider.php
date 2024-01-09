<?php

declare(strict_types=1);

namespace App\Providers;

use App\Facades\Environment;
use App\Jobs\CreateTenantBrands;
use App\Jobs\CreateTenantDomain;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Stancl\JobPipeline\JobPipeline;
use Stancl\Tenancy\Events;
use Stancl\Tenancy\Jobs;
use Stancl\Tenancy\Listeners;
use Stancl\Tenancy\Middleware;

class TenancyServiceProvider extends ServiceProvider
{
    // By default, no namespace is used to support the callable array syntax.
    public static string $controllerNamespace = '';

    const TENANCY_IDENTIFICATION = Middleware\InitializeTenancyByDomain::class;

    public function events(): array
    {
        return [
            // Tenant events
            Events\CreatingTenant::class      => [],
            Events\TenantCreated::class       => [
                JobPipeline::make([
                    Jobs\CreateDatabase::class,
                    Jobs\MigrateDatabase::class,
                    Jobs\SeedDatabase::class,
                    CreateTenantDomain::class,
                    CreateTenantBrands::class,

                    // Other tenant preparation steps go here.

                ])->send(function (Events\TenantCreated $event) {
                    return $event->tenant;
                })->shouldBeQueued(false),
                // `false` by default, but you probably want to make this `true` for production.
            ],
            Events\SavingTenant::class        => [],
            Events\TenantSaved::class         => [],
            Events\UpdatingTenant::class      => [],
            Events\TenantUpdated::class       => [],
            Events\DeletingTenant::class      => [],
            Events\TenantDeleted::class       => [
                JobPipeline::make([
                    Jobs\DeleteDatabase::class,
                ])->send(function (Events\TenantDeleted $event) {
                    return $event->tenant;
                })->shouldBeQueued(false),
                // `false` by default, but you probably want to make this `true` for production.
            ],

            // Domain events
            Events\CreatingDomain::class      => [],
            Events\DomainCreated::class       => [],
            Events\SavingDomain::class        => [],
            Events\DomainSaved::class         => [],
            Events\UpdatingDomain::class      => [],
            Events\DomainUpdated::class       => [],
            Events\DeletingDomain::class      => [],
            Events\DomainDeleted::class       => [],

            // Database events
            Events\DatabaseCreated::class     => [],
            Events\DatabaseMigrated::class    => [],
            Events\DatabaseSeeded::class      => [],
            Events\DatabaseRolledBack::class  => [],
            Events\DatabaseDeleted::class     => [],

            // Tenancy events
            Events\InitializingTenancy::class => [],
            Events\TenancyInitialized::class  => [
                Listeners\BootstrapTenancy::class,
            ],

            Events\EndingTenancy::class => [],
            Events\TenancyEnded::class  => [
                Listeners\RevertToCentralContext::class,
                // Configure Spatie/Permission - Revert to Central Context
                function (Events\TenancyEnded $event) {
                    $permissionRegistrar           = app(PermissionRegistrar::class);
                    $permissionRegistrar->cacheKey = 'spatie.permission.cache';
                },
            ],

            Events\BootstrappingTenancy::class                   => [],
            Events\TenancyBootstrapped::class                    => [
                // Configure Spatie/Permission - Enable Tenant Context
                function (Events\TenancyBootstrapped $event) {
                    $permissionRegistrar           = app(PermissionRegistrar::class);
                    $permissionRegistrar->cacheKey = 'spatie.permission.cache.tenant.'.$event->tenancy->tenant->getTenantKey();
                },
            ],
            Events\RevertingToCentralContext::class              => [],
            Events\RevertedToCentralContext::class               => [],

            // Resource syncing
            Events\SyncedResourceSaved::class                    => [
                Listeners\UpdateSyncedResource::class,
            ],

            // Fired only when a synced resource is changed in a different DB than the origin DB (to avoid infinite loops)
            Events\SyncedResourceChangedInForeignDatabase::class => [],
        ];
    }

    public function register()
    {
        //
    }

    public function boot(): void
    {
        $this->bootEvents();
        $this->mapRoutes();

        $this->makeTenancyMiddlewareHighestPriority();
        $this->modifyStaticConfigs();
        $this->prepareLivewireForTenancy();
    }

    protected function bootEvents(): void
    {
        foreach ($this->events() as $event => $listeners) {
            foreach ($listeners as $listener) {
                if ($listener instanceof JobPipeline) {
                    $listener = $listener->toListener();
                }

                Event::listen($event, $listener);
            }
        }
    }

    protected function mapRoutes(): void
    {
        if (file_exists(base_path('routes/tenant.php'))) {
            Route::namespace(static::$controllerNamespace)
                ->group(base_path('routes/tenant.php'));
        }
    }

    protected function makeTenancyMiddlewareHighestPriority(): void
    {
        $tenancyMiddleware = [
            // Even higher priority than the initialization middleware
            Middleware\PreventAccessFromCentralDomains::class,

            Middleware\InitializeTenancyByDomain::class,
            Middleware\InitializeTenancyBySubdomain::class,
            Middleware\InitializeTenancyByDomainOrSubdomain::class,
            Middleware\InitializeTenancyByPath::class,
            Middleware\InitializeTenancyByRequestData::class,
        ];

        foreach (array_reverse($tenancyMiddleware) as $middleware) {
            $this->app[Kernel::class]->prependToMiddlewarePriority($middleware);
        }
    }

    private function prepareLivewireForTenancy(): void
    {
        if (! in_array(app()->request->getHost(), config('tenancy.central_domains'), true)) {

            // This initializer will throw an exception if the tenant cannot be identified.
            // If we can't identify the tenant, it would be preferable to return a 404
            // instead of returning a server error 500, which makes it look broken.

            Middleware\InitializeTenancyByDomain::$onFail = function () {
                abort(404);
            };

            // We know we're in a tenant context (not on a central domain), so we have to
            // to tell Livewire to call the InitializeTenancyByDomain middleware. This
            // bootstraps all connections to use tenant resources instead of shared.

            Livewire::setUpdateRoute(function ($handle) {
                return Route::post('/livewire/update', $handle)
                    ->middleware(
                        [
                            'web',
                            static::TENANCY_IDENTIFICATION,
                        ])->name('livewire.update');
            });
        }
    }

    private function modifyStaticConfigs(): void
    {
        //        Middleware\InitializeTenancyByDomain::$onFail = function ($e) {
        //            return true;
        //        };
    }
}
