<?php

namespace App\Helpers;

use Filament\Support\Colors\ColorManager;

/**
 * Class Color
 *
 * This class is a support class offering color validation and retrieval for Filament Panel
 * configuration. It is used by the Brand model to validate color configurations.
 */
class Color
{
    public static function validColorConfiguration(string $panel, string $condition, string $color_name): bool
    {
        return self::validColor($color_name)
            && self::validCondition($condition)
            && self::validPanelName($panel);
    }

    public static function validColor(string $color): bool
    {
        return in_array($color, self::getColorNames());
    }

    public static function validCondition(string $condition): bool
    {
        return in_array($condition, self::getColorConditions());
    }

    public static function validPanelName(string $panel): bool
    {
        return in_array($panel, self::getPanelNames());
    }

    public static function getColorNames(): array
    {
        return array_keys(\Filament\Support\Colors\Color::all());
    }

    public static function getColorConditions(): array
    {
        return array_keys(ColorManager::getColors());
    }

    /**
     * This reads the Providers/Filament directory, enumerating the provider files found there.
     * It pulls a list of words extracted as "the first word of each file found", which should
     * correspond to the panel name.
     */
    public static function getPanelNames(): array
    {
        $directoryPath = app_path('Providers/Filament');
        $firstWords = [];

        if (is_dir($directoryPath)) {
            if ($dh = opendir($directoryPath)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file != '.' && $file != '..' && pathinfo($file, PATHINFO_EXTENSION) == 'php') {
                        $splitWords = preg_split('/(?=[A-Z])/', $file, -1, PREG_SPLIT_NO_EMPTY);
                        if (count($splitWords) > 0) {
                            // Convert the first word to lowercase and add to the list
                            $firstWords[] = strtolower($splitWords[0]);
                        }
                    }
                }
                closedir($dh);

            }
        }

        return $firstWords;
    }
}
