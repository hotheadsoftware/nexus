<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class NexusMakePanelUserModel extends Command
{
    protected $signature = 'nexus:make-panel-user-model {model} {--tenant}';

    protected $description = 'Adds a Laravel model for the panel user type, if it doesn\'t already exist.';

    public function handle(): void
    {
        $model  = $this->argument('model');
        $tenant = (bool) $this->option('tenant');

        $central_connection = $tenant ? '' : "\n        protected \$connection = 'central';\n";

        $this->info("Checking model existence...");

        if (file_exists(app_path("Models/{$model}.php"))) {
            $this->warn("Model {$model} already exists.");
            return;
        }

        $this->info("Creating model {$model}...");

        $this->call('make:model', [
            'name' => $model,
        ]);

        $this->info("Model {$model} created. Updating imports and class definition...");

        $modelPath = app_path("Models/{$model}.php");

        $content = File::get($modelPath);

        $model_imports = [
            'use Filament\Models\Contracts\FilamentUser;',
            'use Filament\Panel;',
            'use Illuminate\Foundation\Auth\User as Authenticatable;',
            'use Illuminate\Notifications\Notifiable;',
            'use Laravel\Sanctum\HasApiTokens;',
            'use OwenIt\Auditing\Auditable;',
            'use Rappasoft\LaravelAuthenticationLog\Traits\AuthenticationLoggable;',
            'use Spatie\Permission\Traits\HasRoles;',
        ];

        foreach($model_imports as $import) {
            $content = str_replace('use Illuminate\Database\Eloquent\Model;',
                "use Illuminate\Database\Eloquent\Model;\n$import",
                $content
            );
        }

        $content = str_replace("class $model extends Model",
            "class {$model} extends Authenticatable implements \OwenIt\Auditing\Contracts\Auditable, FilamentUser",
            $content
        );

        $content = str_replace('use HasFactory;',
            "    use Auditable,
        AuthenticationLoggable,
        HasApiTokens,
        HasRoles,
        Notifiable;
        $central_connection
        public function canAccessPanel(Panel \$panel): bool
        {
            return \$panel->getId() === {$model}PanelProvider::PANEL;
        }",
            $content
        );

        File::put($modelPath, $content);
    }
}
