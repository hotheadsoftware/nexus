<?php

namespace App\Models;

use App\Helpers\Color;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Brand extends Model
{
    protected $connection = 'central';

    protected $casts = [
        'colors' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * @throws Exception
     */
    public function setColor(string $panel, string $condition, string $color_name): self
    {
        if (! Color::validColorConfiguration($panel, $condition, $color_name)) {
            throw new Exception("Invalid color configuration: $panel, $condition, $color_name");
        }

        // Get the current colors array
        $colors = $this->colors ?? [];

        // Set the new color
        $colors[$panel][$condition] = $color_name;

        // Update the colors attribute
        $this->colors = $colors;

        return $this;
    }
}
