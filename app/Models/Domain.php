<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Domain extends \Stancl\Tenancy\Database\Models\Domain
{
    public static function getBaseDomain(): string
    {
        return parse_url(config('app.url'), PHP_URL_HOST);
    }


}
