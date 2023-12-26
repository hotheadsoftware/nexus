<?php

namespace App\Models;

class Domain extends \Stancl\Tenancy\Database\Models\Domain
{
    public static function getBaseDomain(): string
    {
        return parse_url(config('app.url'), PHP_URL_HOST);
    }
}
