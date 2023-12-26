<?php

namespace App\Filament\Widgets;

use App\Models\Tenant;
use Auth;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $user = Auth::user();
        $tenants = Tenant::with('domains')->where('user_id', $user->id)->get();

        return [
            Stat::make('Active Instances', $tenants->count())->description('@ $2500/mo'),
            Stat::make('Domains', $tenants->sum(fn ($tenant) => $tenant->domains->count())),
            Stat::make('Invoice', 2500)->description('Due 10/30/2023'),
        ];
    }
}
