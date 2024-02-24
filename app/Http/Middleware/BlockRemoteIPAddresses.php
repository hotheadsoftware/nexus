<?php

namespace App\Http\Middleware;

class BlockRemoteIPAddresses
{
    public function handle($request, $next)
    {
        /**
         * If the setting is false, we perform no blocking here.
         */
        if (! config('app.block_remote_ips')) {
            return $next($request);
        }

        /**
         * List of routes which should always be allowed to reach any endpoint.
         */
        $allowedRoutes = config('app.allowed_routes', []);

        /**
         * List of IP addresses which should always be allowed to reach any endpoint.
         */
        $allowedIPAddresses = config('app.allowed_ips', []);

        if (! in_array($request->ip(), $allowedIPAddresses)) {
            if (! in_array($request->route()->getName(), array_values($allowedRoutes))) {
                exit;
            }
        }

        return $next($request);
    }
}
