<?php

namespace App\Console\Commands;

use App\Facades\Nexus;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class NexusMakePanel extends Command
{
    protected $signature = 'nexus:make-panel
                        {--name= : The name of the panel}
                        {--tenant= : Specify if this is a tenant panel}
                        {--model= : The model used for authentication}
                        {--login= : Indicate if the panel should have a login form}
                        {--registration= : Indicate if user registration is an option}
                        {--branding= : Specify if custom branding should be enabled}
                        {--copy_branding= : Indicate if existing panel branding should be copied}
                        {--copy_branding_from= : The name of the panel to copy branding from}
                        {--api_tokens= : Indicate if users need to generate API tokens}';

    protected $description = 'Creates a new Nexus Panel - Start Here!';

    protected ?Collection $configuration = null;

    // Abstract Syntax Tree for PHP Parsing.
    // Enables more reliable file modification than string replacement.
    protected array $ast = [];

    protected static string $stubFilePath = 'vendor/filament/support/stubs/PanelProvider.stub';

    public function handle(): void
    {
        try {

            $this->configuration = Nexus::getPanelConfigurationInputs($this);

            $modelAndTenant = [
                'model'    => $this->configuration->get('name'),
                '--tenant' => $this->configuration->get('tenant'),
            ];

            $this->call('nexus:make-panel-provider-stub',
                $this->configuration->mapWithKeys(function ($value, $key) {
                    return ['--'.$key => $value];
                })->toArray()
            );

            $this->call('make:filament-panel', [
                'id' => $this->configuration->get('name'),
            ]);

            $this->call('nexus:revert-panel-provider-stub');

            $this->call('nexus:make-panel-user-model', $modelAndTenant);

            $this->call('nexus:make-panel-user-migration', $modelAndTenant);

            $this->call('nexus:make-auth-guard-and-provider', $modelAndTenant);

            $this->call('nexus:make-panel-config', [
                'model' => $this->configuration->get('name'),
            ]);

            $this->call('nexus:make-panel-env-variables', [
                'model' => $this->configuration->get('name'),
            ]);

            $this->call('nexus:update-panel-seeder', $modelAndTenant);

            $this->call('nexus:seed-tenants');

        } catch (Exception $e) {
            $this->error($e->getMessage());
            exit;
        }
    }

    protected function getModelAndTenantConfig(): array
    {
        $modelAndTenant = [
            'model' => $this->configuration->get('name'),
        ];
        if ($this->configuration->get('tenant')) {
            $modelAndTenant['--tenant'] = true;
        }

        return $modelAndTenant;
    }

    protected function getInput(
        string $info,
        string $label,
        string $placeholder,
        string $type,
        string $default = ''
    ): string {
        $this->info($info);

        return match ($type) {
            'bool'  => select(label: $label, options: ['Yes', 'No'], default: 'Yes', required: true) == 'Yes',
            default => text(label: $label, placeholder: $placeholder, default: $default, required: true),
        };

    }
}
