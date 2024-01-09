<?php

namespace App\Services;

use BadMethodCallException;
use Illuminate\Http\Request;

/**
 * Class Environment
 *
 * This class is offers a quick means of determining which operating environment is active.
 * We can use this throughout the application for various purposes.
 *
 * @method isLocal() bool
 * @method isProduction() bool
 * @method isStaging() bool
 * @method isTesting() bool
 * @method isDevelopment() bool
 */
class Environment
{
    public function __construct(public Request $request)
    {
    }

    public const ENVIRONMENT_NAMES = [
        'local' => [
            'local',
            'loc',
        ],
        'production' => [
            'production',
            'prod',
            'main',
            'prd',
            'master',
        ],
        'staging' => [
            'staging',
            'stage',
            'stg',
        ],
        'testing' => [
            'testing',
            'test',
            'tst',
        ],
        'development' => [
            'development',
            'develop',
            'dev',
        ],
    ];

    public function __call($name, $arguments)
    {
        if (preg_match('/^is([A-Z][a-zA-Z0-9]*)$/', $name, $matches)) {
            $envType = strtolower($matches[1]);
            if (array_key_exists($envType, self::ENVIRONMENT_NAMES)) {
                return in_array(app()->environment(), self::ENVIRONMENT_NAMES[$envType], true);
            }
        }

        return match ($name) {
            'isCentral' => in_array($this->request->getHost(), config('tenancy.central_domains'), true),
            'isTenant'  => ! in_array($this->request->getHost(), config('tenancy.central_domains'), true),
            default     => throw new BadMethodCallException("Method {$name} does not exist"),
        };
    }
}
