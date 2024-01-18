<?php

namespace App\Console\Commands;

use App\Services\Nexus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class NexusUpdatePanelSeeder extends Command
{
    protected $signature = 'nexus:update-panel-seeder {model} {--tenant}';

    protected $description = 'Adds the new user type to the appropriate user seeder.';

    public function handle(): void
    {
        $model       = $this->argument('model');
        $model_lower = strtolower($model);

        $tenant         = $this->option('tenant');
        $tenantUsePath  = $tenant ? 'Tenant\\' : '';
        $tenantFilePath = $tenant ? 'Tenant/' : '';

        $seederPathDir       = "database/seeders/$tenantFilePath";
        $seederPath          = "{$seederPathDir}UserSeeder.php";
        $seederBackupPathDir = Nexus::$backupLocation."$seederPathDir";
        $seederBackupPath    = Nexus::$backupLocation."$seederPath";

        if (! File::isDirectory($seederBackupPathDir)) {
            File::makeDirectory($seederBackupPathDir, 0755, true, true);
        }

        File::copy(base_path($seederPath), $seederBackupPath);

        try {

            $content = File::get(base_path($seederPath));

            $anchorComment = '// do-not-remove-this-nexus-anchor-user-seeder-use-statements';
            $content       = str_replace($anchorComment, "use App\\Models\\$tenantUsePath$model;\n$anchorComment",
                $content);

            if (! Str::contains($content, 'use Illuminate\\Support\\Facades\\Hash;')) {
                $content = str_replace($anchorComment, "use Illuminate\\Support\\Facades\\Hash;\n$anchorComment",
                    $content);
            }

            $anchorComment = '// do-not-remove-this-nexus-anchor-user-seeder-model-creation';
            $content       = str_replace($anchorComment, "$model::firstOrCreate([\n                'email' => config('nexus.$model_lower.user.email')\n            ], [
                'name'              => config('nexus.$model_lower.user.name'),
                'email'             => config('nexus.$model_lower.user.email'),
                'password'          => Hash::make(config('nexus.$model_lower.user.password')),
                'email_verified_at' => now(),
            ]);\n\n            $anchorComment", $content);

            File::put(base_path($seederPath), $content);

        } catch (\Exception $e) {
            File::copy($seederBackupPath, base_path($seederPath));
            $this->error($e);
            throw $e;
        }
    }
}
