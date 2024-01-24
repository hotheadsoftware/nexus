<?php

namespace App\Console\Commands;

use App\Services\Nexus;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class NexusMakePanelUserPulseResolver extends Command
{
    protected $signature = 'nexus:make-panel-user-pulse-resolver {model} {--tenant}';

    protected $description = 'Create a Pulse resolver for a panel user model.';

    protected static string $resolverFileDir = 'storage/app/nexus/pulse/';

    protected static string $resolverFilePathOld = 'storage/app/nexus/pulse/UserResolver.php.old';

    public function handle(): void
    {
        $resolverPath  = app_path('Pulse/UserResolver.php');
        $commentAnchor = '// do-not-remove-nexus-user-resolver-load-goes-here';

        Nexus::backupFile($resolverPath);

        try {

            if (file_exists($resolverPath)) {

                $model         = $this->argument('model');
                $tenant        = (bool) $this->option('tenant');
                $tenantPath    = $tenant ? 'Tenant\\' : '';
                $modelClass    = "App\\Models\\{$tenantPath}{$model}";
                $modelClassRef = "$model::class";
                $modelText     = "$modelClassRef => $model::findMany(\$keys
                ->filter(fn (\$key) => \$key[0] === $modelClassRef)->map(fn (\$key) => \$key[1])->values()),";

                $this->info("Creating Pulse resolver for $modelClass...");

                $useStatementAnchor = 'use Laravel\Pulse\Contracts\ResolvesUsers;';

                $readFile = File::get($resolverPath);

                if (Str::contains($readFile, $modelClass)) {
                    throw new Exception("Resolver for $modelClass already exists. Have you already run this command?");
                }

                if (! Str::contains($readFile, $useStatementAnchor)) {
                    throw new Exception("Missing the use statement anchor. Without that use statement, we don't know where to put our own.");
                }

                if (! Str::contains($readFile, $commentAnchor)) {
                    throw new Exception('Missing the comment anchor. This tells us where to place the new resolvers.');
                }

                $this->replaceInFile(
                    search: $useStatementAnchor,
                    replace: "use $modelClass;\n$useStatementAnchor",
                    path: $resolverPath);

                $this->replaceInFile(
                    search: $commentAnchor,
                    replace: $modelText.PHP_EOL.PHP_EOL.'            '.$commentAnchor.PHP_EOL,
                    path: $resolverPath
                );

                $userMappingText  = "User::class         => 'User',";
                $modelMappingText = "$modelClassRef          => '$model',";

                $this->replaceInFile(
                    search: $userMappingText,
                    replace: $modelMappingText.PHP_EOL.'            '.$userMappingText,
                    path: $resolverPath
                );

            }
        } catch (Exception $e) {
            $this->error($e->getMessage());
            Nexus::restoreFile($resolverPath);
        }

    }

    protected function replaceInFile(string $search, string $replace, string $path): void
    {
        $contents = File::get($path);
        $contents = str_replace($search, $replace, $contents);
        File::put($path, $contents);
    }
}
