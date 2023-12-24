<?php

namespace App\Models;

use Exception;
use Filament\Support\Colors\Color;
use Filament\Support\Colors\ColorManager;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    /**
     * A list of Filament PHP Panels which are accessible to Tenant Domains
     * (ie, not the central domain). This allows some panel configuration to
     * be customized to the tenant's needs.
     */
    public const PANELS = ['manage'];

    protected function isValidPanel($panel) : bool {
        return in_array($panel, self::PANELS);
    }

    /**
     * @throws Exception
     */
    protected function getColors($panel) : array|null {
        if(!$this->isValidPanel($panel)) {
            throw new Exception('Invalid panel. Must be one of: ' . implode(', ', self::PANELS));
        }

        return $this?->colors[$panel];
    }

    /**
     * @throws Exception
     */
    protected function setColors(array $colors, $panel) : self {
        if(!in_array($panel, self::PANELS)) {
            throw new Exception('Invalid panel. Must be one of: ' . implode(', ', self::PANELS));
        }

        if(!$this->validColors($colors)) {
            throw new Exception('Invalid colors. Must be an array of valid colors.');
        }

        $this->colors[$panel] = $colors;

        return $this;
    }

    protected function validColors(array $colors) : bool {

        // Must be an associative array of color => value pairs.
        if(array_values($colors) === $colors) {
            return false;
        }

        $allowableKeys = array_keys(ColorManager::getColors());

        foreach($colors as $key => $value) {
            // Must be a valid color key.
            if(!in_array($key, $allowableKeys)) {
                return false;
            }

            // Must be a valid color value.
            if(!is_string($value)) {
                return false;
            }

            if(!in_array(strtolower($value),array_keys(Color::all()))) {
                return false;
            }
        }

        return true;
    }
}
