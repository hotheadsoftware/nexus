<?php

namespace App\Filament\Widgets;

use App\Models\Tenant;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $tenants = Tenant::all();

        return [
            Stat::make('Users', User::count()),
            Stat::make('Tenants', $tenants->count()),
            Stat::make('Domains', $tenants->sum(fn ($tenant) => $tenant->domains->count())),
        ];
    }
}
