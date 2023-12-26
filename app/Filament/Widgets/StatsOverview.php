<?php

namespace App\Filament\Widgets;

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

        return [
            Stat::make('Companies', $tenants->count())
                ->description('Active Nexus Instances')
                ->descriptionColor('primary'),
            Stat::make('Domains', $tenants->sum(fn ($tenant) => $tenant->domains->count()))
                ->description('Enabled Domains')
                ->descriptionColor('primary'),
            Stat::make('Next Invoice', "$2500")
                ->description('Due December 31')
                ->descriptionColor('danger'),
        ];
    }
}