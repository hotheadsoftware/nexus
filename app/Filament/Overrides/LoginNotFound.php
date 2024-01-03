<?php

namespace App\Filament\Overrides;

use Filament\Pages\Auth\Login;

class LoginNotFound extends Login
{
    public function mount(): void
    {
        abort(404);
    }
}
