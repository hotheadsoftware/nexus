<?php

namespace App\Models;

use App\Facades\Colors;
use Exception;
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Brand extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $connection = 'central';

    protected $casts = [
        'colors' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function logo(): string
    {
        try {
            return $this->getFirstMediaUrl('logo');
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * @param  string  $panelName
     * @param  Tenant  $tenant
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     *
     * This is called from tenant-context PanelProviders (see OperatePanelProvider.php)
     * to apply the brand configuration to the panel.
     */
    public function applyToPanel(string $panelName, Tenant $tenant): void
    {
        app()->get('filament')
            ->getPanel($panelName)
            ->registration($this->allow_registration ?? false)
            ->colors([
                'danger'  => Colors::getShades($this->colors['danger'] ?? '') ?? Color::Red,
                'primary' => Colors::getShades($this->colors['primary'] ?? '') ?? Color::Stone,
                'info'    => Colors::getShades($this->colors['info'] ?? '') ?? Color::Blue,
                'success' => Colors::getShades($this->colors['success'] ?? '') ?? Color::Green,
                'warning' => Colors::getShades($this->colors['warning'] ?? '') ?? Color::Orange,
                'gray'    => Colors::getShades($this->colors['gray'] ?? '') ?? Color::Green,
            ])
            ->brandLogo(fn() => view('filament.logo.tenant', [
                'brand'  => $this,
                'tenant' => $tenant,
            ])
            )
            ->boot();
    }
}
