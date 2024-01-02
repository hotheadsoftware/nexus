<?php

namespace App\Helpers;

use Filament\Pages\Auth\Login;

class NullAuthHelper extends Login
{
    public function mount(): void
    {
        abort(404);
    }
}
