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
         * Organized from lowest complexity to highest, we check against
         * allowed routes, allowed IP addresses, and allowed CIDR blocks.
         * All should be extremely fast with small arrays to check.
         */
        $allowedRoutes = config('app.allowed_routes', []);
        foreach ($allowedRoutes as $route) {
            if ($request->route()->getName() == $route) {
                return $next($request);
            }
        }

        $allowedIPAddresses = config('app.allowed_ips', []);
        foreach ($allowedIPAddresses as $ip) {
            if ($request->ip() === $ip) {
                return $next($request);
            }
        }

        $allowedCidrBlocks = config('app.allowed_cidr_blocks', []);
        foreach ($allowedCidrBlocks as $cidrBlock) {
            if ($this->ip_in_range($request->ip(), $cidrBlock)) {
                return $next($request);
            }
        }

        exit;
    }

    private function ip_in_range($ip, $cidr): bool
    {
        [$subnet, $mask] = explode('/', $cidr);
        if ((ip2long($ip) & ~((1 << (32 - $mask)) - 1)) == ip2long($subnet)) {
            return true;
        }

        return false;
    }
}
