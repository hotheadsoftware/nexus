<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Paddle\Cashier;
use Laravel\Paddle\Customer;
use Laravel\Paddle\Subscription;
use Laravel\Paddle\Transaction;
use Laravel\Sanctum\HasApiTokens;
use OwenIt\Auditing\Auditable;
use Rappasoft\LaravelAuthenticationLog\Traits\AuthenticationLoggable;
use Spark\Billable;
use Spatie\Permission\Traits\HasRoles;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;

class Subscriber extends Authenticatable implements \OwenIt\Auditing\Contracts\Auditable, FilamentUser
{
    use Auditable, AuthenticationLoggable, HasApiTokens, HasRoles, Notifiable, Billable;

    protected $table = 'users';
    protected $connection = 'central';

    protected string $foreign_key = 'user_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function subscriptions(): hasMany
    {
        return $this->hasMany(Subscription::class, 'billable_id')
                    ->where('billable_type', Subscriber::class);
    }

    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class, 'billable_id')->where('billable_type', Subscriber::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'billable_id')
                    ->where('billable_type', Subscriber::class)
                    ->orderByDesc('billed_at');
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class, $this->foreign_key);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        $tenant_context = in_array(InitializeTenancyByDomain::class, $panel->getMiddleware());
        $tenant = tenant('id') !== null;

        return ($tenant_context && $tenant) || (!$tenant_context && !$tenant);
    }
}
