<?php

namespace App\Facades;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Collection panelNames()
 * @method static Collection getPanelConfigurationInputs(Command $command)
 *
 * @property string $backupLocation
 */
class Nexus extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'nexus';
    }
}
