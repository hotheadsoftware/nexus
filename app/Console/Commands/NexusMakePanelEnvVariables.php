<?php

namespace App\Console\Commands;

use App\Services\Nexus;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class NexusMakePanelEnvVariables extends Command
{
    protected $signature = 'nexus:make-panel-env-variables {model}';

    protected $description = 'Writes to .env and .env.example to accommodate the provided model.';

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        $model = $this->argument('model');

        $this->info('Writing to .env and .env.example...');

        // Backup .env and .env.example in case of error.
        File::isDirectory(Nexus::$backupLocation.'/env') || File::makeDirectory(Nexus::$backupLocation.'/env', 0777, true, true);
        File::copy(base_path('.env'), Nexus::$backupLocation . '/env/.env');
        File::copy(base_path('.env.example'), Nexus::$backupLocation . '/env/.env.example');

        try {

            $this->writeEnv($model,'.env');
            $this->writeEnv($model,'.env.example');

        } catch (Exception $e) {
            // Restore .env and .env.example to their preserved versions.
            File::copy(Nexus::$backupLocation . '/env/.env', base_path('.env'));
            File::copy(Nexus::$backupLocation . '/env/.env.example', base_path('.env.example'));
            $this->error($e->getMessage());
            throw $e;
        }

        $this->info('Done.');
    }

    protected function writeEnv(string $model, string $file): void
    {
        $model_upper = strtoupper($model);
        $model_lower = strtolower($model);

        // Read the .env file line by line
        $lines = file(base_path($file));

        // Open the .env file for writing
        $fp = fopen(base_path($file), 'w');

        $ready = false;
        foreach ($lines as $line) {

            // If ready is true, the previously-read line was the admin panel user pass.
            if ($ready) {
                fwrite($fp, "\n");
                fwrite($fp, "# This user was created by the nexus:make-panel command and is used to login to the\n");
                fwrite($fp, "# application. This is not a default panel or user type, so we can't provide any\n");
                fwrite($fp, "# guidance as to its purpose or permissions. It's up to you to decide that.\n");
                fwrite($fp, "\n");
                fwrite($fp, "{$model_upper}_PANEL_USER_NAME=\"$model\"\n");
                fwrite($fp, "{$model_upper}_PANEL_EMAIL_ADDRESS=\"$model_lower@localhost.com\"\n");
                fwrite($fp, "{$model_upper}_PANEL_USER_PASSWORD=\"password\"\n");
                // Stop the flag so we don't repeat this for every line.
                $ready = false;
            }

            fwrite($fp, $line); // write the currently expected line to file.

            if (Str::contains($line, 'ADMIN_PANEL_USER_PASSWORD')) {
                // We'll start writing our new stuff right after we see this.
                $ready = true;
            }
        }

        fclose($fp);
    }
}
