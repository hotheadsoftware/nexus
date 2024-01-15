<?php

namespace App\Console\Commands;

use App\Facades\Nexus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class NexusMakeAuthGuardAndProvider extends Command
{
    protected $signature = 'nexus:make-auth-guard-and-provider {model}';

    protected $description = 'Writes config/auth.php to accommodate Nexus Panel auth guard and provider';

    public function handle(): void
    {
        File::isDirectory(Nexus::$backupLocation) or File::makeDirectory(Nexus::$backupLocation, 0755, true, true);
        File::copy(config_path('auth.php'), Nexus::$backupLocation.'auth.php');

        $content = File::get(config_path('auth.php'));

        $model           = $this->argument('model');
        $model_reference = "App\\Models\\$model::class";
        $model_lower     = Str::lower($model);
        $model_plural    = Str::plural($model_lower);

        // Check to see if we have an existing guard configuration. If not,
        // we'll go ahead and add it now. You can safely customize those
        // values in config - we only check to avoid duplication here.

        try {

            if (! Str::contains($content, "'$model_lower' => [")) {
                $content = str_replace(
                    "'guards' => [",
                    "'guards' => [
        '$model_lower' => [
            'driver' => 'session',
            'provider' => '$model_plural',
        ],",
                    $content
                );
            }

            // Check to see if we have an existing provider or password configuration.
            // If they don't exist, we'll go ahead and add both now. You can safely
            // customize those values in config - we only check their existence.

            if (! Str::contains($content, "'$model_plural' => [")) {

                $content = str_replace(
                    "'providers' => [",
                    "'providers' => [
        '$model_plural' => [
            'driver' => 'eloquent',
            'model' => $model_reference,
        ],",
                    $content
                );

                $content = str_replace(
                    "'passwords' => [",
                    "'passwords' => [
        '$model_plural' => [
            'provider' => '$model_plural',
            'table'    => 'password_reset_tokens',
            'expire'   => 60,
            'throttle' => 60,
        ],",
                    $content
                );
            }

            File::put(config_path('auth.php'), $content);

        } catch (\Exception $e) {
            $this->error($e->getMessage());
            $this->call('nexus:restore-latest-auth-backup');
        }
    }
}
