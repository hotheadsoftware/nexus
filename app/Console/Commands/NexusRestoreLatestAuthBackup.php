<?php

namespace App\Console\Commands;

use App\Facades\Nexus;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class NexusRestoreLatestAuthBackup extends Command
{
    protected $signature = 'nexus:restore-latest-auth-backup';

    protected $description = 'Finds the latest backup of the auth.php file and restores it.';

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        $this->info('Restoring latest backup of auth.php...');

        if (! File::isDirectory(Nexus::$backupLocation)) {
            throw new Exception('Backup Directory not found!');
        }
        if (! File::exists(Nexus::$backupLocation.'auth.php')) {
            throw new Exception('No auth.php backup found!');
        }

        File::copy(Nexus::$backupLocation.'auth.php', config_path('auth.php'));

        $this->info('Latest backup restored.');
    }
}
