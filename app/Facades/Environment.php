<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * We get these from Services/Environment.php...
 *
 * @method isLocal() bool
 * @method isProduction() bool
 * @method isStaging() bool
 * @method isTesting() bool
 * @method isDevelopment() bool
 */
class Environment extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'nexus.environment';
    }
}
