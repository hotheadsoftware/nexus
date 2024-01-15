<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class NexusRevertPanelProviderStub extends Command
{
    protected $signature = 'nexus:revert-panel-provider-stub';

    protected $description = 'Revert the panel provider stub to the original vendor stub';

    public function handle(): void
    {
        if (! File::isDirectory(NexusMakePanelProviderStub::$stubFileDir)) {
            $this->warn(NexusMakePanelProviderStub::$stubFileDir.' does not exist. Nothing to revert.');

            return;
        }

        File::copy(NexusMakePanelProviderStub::$stubFilePathOld, NexusMakePanelProviderStub::$stubFilePath);
        $this->info('Reverted Nexus Panel Provider Stub');
    }
}
