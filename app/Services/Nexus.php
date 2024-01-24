<?php

namespace App\Services;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

/**
 * Class Color
 *
 * This class is a support class offering color validation and retrieval for Filament Panel
 * configuration. It is used by the Brand model to validate color configurations.
 */
class Nexus
{
    public static string $backupLocation = 'storage/app/nexus/backup/';

    private ?Filesystem $filesystem;

    public function __construct(?Filesystem $filesystem = null)
    {
        // If no filesystem is provided, use the Laravel Filesystem
        $this->filesystem = $filesystem ?? new Filesystem();
    }

    public function getPanelConfigurationInputs(Command $command): Collection
    {
        $config = collect();

        $config->put('name', $command->option('name') ?? text(
            label: "What's the name of this panel?",
            placeholder: 'App',
            default: '',
            required: true)
        );

        $config->put('tenant', $command->option('tenant') ?? select(
            label: 'Is this a tenant panel?',
            options: ['Yes', 'No'],
            default: 'Yes',
            required: true) == 'Yes'
        );

        $config->put('model', $command->option('model') ?? text(
            label: "If the model doesn't exist, we'll create it under the appropriate Namespace.",
            placeholder: 'User',
            default: '',
            required: true)
        );

        $config->put('login', $command->option('login') ?? select(
            label: 'Should this panel have a login form?',
            options: ['Yes', 'No'],
            default: 'Yes',
            required: true) == 'Yes'
        );

        $config->put('registration', $command->option('registration') ?? select(
            label: 'Should user registration be an option?',
            options: ['Yes', 'No'],
            default: 'Yes',
            required: true) == 'Yes'
        );

        $config->put('branding', $command->option('branding') ?? select(
            label: 'Should custom branding be enabled?',
            options: ['Yes', 'No'],
            default: 'Yes',
            required: true) == 'Yes'
        );

        if ($config->get('branding')) {

            $config->put('copy_branding', $command->option('copy_branding') ?? select(
                label: 'Should we copy an existing panel\'s custom branding?',
                options: ['Yes', 'No'],
                default: 'Yes',
                required: true) == 'Yes'
            );
        }

        if ($config->get('copy_branding')) {
            $config->put('copy_branding_from', $command->option('copy_branding_from') ?? select(
                label: 'Ok, which panel should we copy from?',
                options: $this->panelNames()->filter(function ($name) {
                    return $name !== 'admin';
                })->toArray(),
                required: true)
            );
        }

        $config->put('api_tokens', $command->option('api_tokens') ?? select(
            label: 'Will users of this panel need to generate API tokens?',
            options: ['Yes', 'No'],
            default: 'Yes',
            required: true) == 'Yes'
        );

        return $config;
    }

    /**
     * This reads the Providers/Filament directory, enumerating the provider files found there.
     * It pulls a list of words extracted as "the first word of each file found", which should
     * correspond to the panel name.
     */
    public function panelNames(): Collection
    {
        $directoryPath = base_path('app/Providers/Filament');
        $firstWords    = collect([]);

        if ($this->filesystem->isDirectory($directoryPath)) {
            $files = $this->filesystem->files($directoryPath);
            foreach ($files as $file) {
                $filename = $file->getFilename();
                if (pathinfo($filename, PATHINFO_EXTENSION) == 'php') {
                    $splitWords = preg_split('/(?=[A-Z])/', $filename, -1, PREG_SPLIT_NO_EMPTY);
                    if (count($splitWords) > 0) {
                        $firstWords->add(strtolower($splitWords[0]));
                    }
                }
            }
        }

        return $firstWords;
    }

    public static function getBackupStorageDirectory(): string
    {
        return storage_path('app/nexus/backup/');
    }

    public static function backupFile(string $originalPath): void
    {
        $backupPath = self::getBackupStorageDirectory().$originalPath;
        $backupDir  = dirname($backupPath);
        File::isDirectory($backupDir) || File::makeDirectory($backupDir, 0755, true, true);

        File::copy($originalPath, $backupPath);
    }

    public static function restoreFile(string $backupPath): void
    {
        if (File::exists($backupPath)) {
            $originalPath = str_replace(self::getBackupStorageDirectory(), '', $backupPath);
            File::copy($backupPath, $originalPath);
        }
    }
}
