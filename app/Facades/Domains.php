<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Domains extends Facade
{
    protected static function getFacadeAccessor(): string
    {

        // Testing pint hook

                              return 'nexus.domains';
    }
}
