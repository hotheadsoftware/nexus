<?php

namespace App\Console\Commands;


use App\Services\Nexus;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class NexusMakePanelConfig extends Command
{
    protected $signature = 'nexus:make-panel-config {model}';

    protected $description = 'Writes config/panels.php to accommodate the provided model.';

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        $model = strtolower($this->argument('model'));
        $model_upper = strtoupper($model);

        // Backup the file before proceeding.
        File::isDirectory(Nexus::$backupLocation.'/config') or File::makeDirectory(Nexus::$backupLocation.'/config', 0777, true, true);
        File::copy(config_path('panels.php'), Nexus::$backupLocation . '/config/panels.php');

        try {

            $content = File::get(config_path('panels.php'));

            $content = str_replace("],\n];", "],\n\n    '$model' => [
        'user' => [
            'name'     => env('{$model_upper}_PANEL_USER_NAME', ''),
            'email'    => env('{$model_upper}_PANEL_USER_EMAIL', ''),
            'password' => env('{$model_upper}_PANEL_USER_PASSWORD', ''),
        ],
    ],\n];", $content);

            File::put(config_path('panels.php'), $content);

        } catch (Exception $e) {
            File::copy(Nexus::$backupLocation . '/config/panels.php', config_path('panels.php'));
            $this->error($e->getMessage());
            throw $e;
        }
    }
}
