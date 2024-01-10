<?php

namespace App\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Collection getPanelNames()
 * @method static Collection getPanelColors()
 * @method static array getShades(string $name)
 * @method static array getColorConditions()
 * @method static string rgbToHex($r, $g = null, $b = null)
 * @method static bool hexColor(string $color)
 * @method static void validateRgb($r, $g, $b)
 */
class Colors extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'nexus.colors';
    }
}
