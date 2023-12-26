<?php

namespace App\Models;

use Exception;
use Filament\Support\Colors\Color;
use Filament\Support\Colors\ColorManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Brand extends Model
{
    protected $connection = 'central';

    public const COLOR_CONDITIONS = [
        'danger',
        'warning',
        'success',
        'info',
        'primary',
        'secondary',
        'gray',
    ];

    protected $casts = [
        'colors' => 'array',
    ];

    public static function getColorNames(): array
    {
        return array_keys(Color::all());
    }

    public static function getColorConditions(): array
    {
        return array_keys(ColorManager::getColors());
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public static function validColor(string $color): bool
    {
        return in_array($color, self::getColorNames());
    }

    public static function validCondition(string $condition): bool
    {
        return in_array($condition, self::getColorConditions());
    }

    /**
     * @throws Exception
     */
    public function setColor($key, $value) : self
    {
        if(!self::validColor($key)) {
            throw new Exception("Invalid color name: $key");
        }

        if(!self::validCondition($value)) {
            throw new Exception("Invalid color condition: $value");
        }

        // Get the current colors array
        $colors = $this->colors ?? [];

        // Set the new color
        $colors[$key] = $value;

        // Update the colors attribute
        $this->colors = $colors;

        return $this;
    }
}
