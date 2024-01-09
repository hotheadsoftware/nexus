<?php

namespace App\Jobs;

use App\Models\Brand;
use App\Services\Colors;
use Exception;
use Filament\Support\Colors\Color;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Stancl\Tenancy\Contracts\TenantWithDatabase;

class CreateTenantBrands implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected TenantWithDatabase $tenant;

    public function __construct(TenantWithDatabase $tenant)
    {
        $this->tenant = $tenant;
    }

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        $panels = Colors::getPanelNames()
            ->filter(function ($panel) {
                return $panel !== 'account';
            });

        foreach ($panels as $panel) {
            Brand::firstOrCreate([
                'tenant_id' => $this->tenant->id,
                'panel'     => $panel,
            ], [
                'name'   => $this->tenant->name.' '.ucfirst($panel),
                'logo'   => null,
                'colors' => [
                    'danger'  => Colors::rgbToHex(Color::Red['500']),
                    'primary' => Colors::rgbToHex(Color::Amber['500']),
                    'info'    => Colors::rgbToHex(Color::Sky['500']),
                    'success' => Colors::rgbToHex(Color::Green['500']),
                    'warning' => Colors::rgbToHex(Color::Orange['500']),
                    'gray'    => Colors::rgbToHex(Color::Gray['500']),
                ],
                'allow_registration' => true,
                'headline'           => 'Welcome to '.$this->tenant->name.'\'s '.ucfirst($panel).' Panel',
            ]);
        }
    }
}
