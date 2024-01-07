<?php

namespace App\Models;

use App\Providers\Filament\AdminPanelProvider;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use OwenIt\Auditing\Auditable;
use Rappasoft\LaravelAuthenticationLog\Traits\AuthenticationLoggable;
use Spatie\Permission\Traits\HasRoles;

class Administrator extends Authenticatable implements \OwenIt\Auditing\Contracts\Auditable, FilamentUser
{
    use Auditable,
        AuthenticationLoggable,
        HasApiTokens,
        HasRoles,
        Notifiable;

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === AdminPanelProvider::PANEL;
    }

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];
}
