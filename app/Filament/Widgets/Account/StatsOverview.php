<?php

namespace App\Filament\Widgets\Account;

use App\Models\Tenant;
use Auth;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = Auth::user();

        $tenants = Tenant::where('user_id', $user->id)->with('domains')->get();

        $stats = [];

        $stats[] = Stat::make('Instances', $tenants->count())
            ->description('Active Nexus Instances')
            ->descriptionColor('primary');

        $stats[] = Stat::make('Domains', $tenants->sum(fn ($tenant) => $tenant->domains->count()))
            ->description('Enabled Domains')
            ->descriptionColor('primary');

        return $stats;
    }
}
