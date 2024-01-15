<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class NexusMakePanelUserMigration extends Command
{
    protected $signature = 'nexus:make-panel-user-migration {model}';
    protected $description = 'Clones the users table migration to create a migration for the provided model.';

    public function handle(): void
    {
        $model          = Str::plural(strtolower(trim($this->argument('model'))));
        $migrationsPath = database_path('migrations');
        $targetPath = $migrationsPath . DIRECTORY_SEPARATOR . 'tenant' . DIRECTORY_SEPARATOR . date('Y_m_d_His') . "_create_{$model}_table.php";

        $files = File::files($migrationsPath);

        $created = false;
        foreach ($files as $file) {
            if (str_contains($file->getFilename(), 'create_users_table')) {
                File::copy($file->getPathname(), $targetPath);
                $created = true;
                $this->info("Migration copied to: {$targetPath}");
            }
        }

        if($created)
        {
            $file = File::get($targetPath);
            $file = str_replace('users', $model, $file);
            File::put($targetPath, $file);
            return;
        }

        $this->error("Couldn't find a users migration to copy!");
    }
}

