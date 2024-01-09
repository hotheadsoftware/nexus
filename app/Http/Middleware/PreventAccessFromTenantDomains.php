<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route as Router;

/**
 * This will return a 404 response if the request is not from a central domain.
 * Apply this middleware to any filament panels or other routes where a tenant
 * should not have access.
 */
class PreventAccessFromTenantDomains
{
    /** @var callable */
    protected $central404;

    public function __construct(?callable $central404 = null)
    {
        $this->central404 = $central404 ?? function () {
            abort(404);
        };
    }

    public function handle(Request $request, Closure $next): mixed
    {
        // If a route is universal, let it pass regardless of domain.
        if ($this->routeHasMiddleware($request->route(), 'universal')) {
            return $next($request);
        }

        return $request->inTenantContext()
            ? $this->central404->__invoke($request)
            : $next($request);
    }

    public static function routeHasMiddleware(Route $route, $middleware): bool
    {
        if (in_array($middleware, $route->middleware(), true)) {
            return true;
        }

        $middlewareGroups = Router::getMiddlewareGroups();
        foreach ($route->gatherMiddleware() as $inner) {
            if (! $inner instanceof Closure && isset($middlewareGroups[$inner]) && in_array($middleware,
                $middlewareGroups[$inner], true)) {
                return true;
            }
        }

        return false;
    }
}
