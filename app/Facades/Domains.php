<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Request;

class Domains extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'domains';
    }
}
