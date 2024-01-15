<?php

namespace App\Services;

use Filament\Support\Colors\Color;
use Illuminate\Support\Collection;

/**
 * Class Color
 *
 * This class is a support class offering color validation and retrieval for Filament Panel
 * configuration. It is used by the Brand model to validate color configurations.
 */
class Nexus
{
    /**
     * This reads the Providers/Filament directory, enumerating the provider files found there.
     * It pulls a list of words extracted as "the first word of each file found", which should
     * correspond to the panel name.
     */
    public function panelNames(): Collection
    {
        $directoryPath = app_path('Providers/Filament');
        $firstWords    = collect([]);

        if (is_dir($directoryPath)) {
            if ($dh = opendir($directoryPath)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file != '.' && $file != '..' && pathinfo($file, PATHINFO_EXTENSION) == 'php') {
                        $splitWords = preg_split('/(?=[A-Z])/', $file, -1, PREG_SPLIT_NO_EMPTY);
                        if (count($splitWords) > 0) {
                            // Convert the first word to lowercase and add to the list
                            $firstWords->add(strtolower($splitWords[0]));
                        }
                    }
                }
                closedir($dh);

            }
        }

        return $firstWords;
    }
}
