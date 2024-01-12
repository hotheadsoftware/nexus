<?php

namespace App\Console\Commands;

use File;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Class CreateNexusPanel
 *
 * The goal here is to rewrite the Filament PanelProvider.stub file with the defaults we want.
 * This will allow a user to quickly wire up a new panel without a lot of manual work.
 *
 * TODO - Current Status: This script successfully rewrites the PanelProvider.stub file and
 * triggers creation of a new Panel.
 *
 * We still need to complete numerous steps to make this nearly automatic:
 *
 * 1. Create a new Model using the panel name (ucfirst).
 * 2. Create a new Migration using the panel name (ucfirst).
 * 3. Move the migration into the tenant subfolder.
 * 4. Add the new Model to the tenant UserSeeder.
 * 5. Add a new auth guard & driver based on the model name.
 * 6. Write new KEY/VALUE pairs to the .env file.
 * 7. Write new config values to the config/panels.php file.
 * 8. Add the BreezyCore token integration (see docs).
 */
class CreateNexusPanel extends Command
{
    protected $signature = 'nexus:create-panel';

    protected $description = 'Command description';

    public function handle(): void
    {
        $filePath = 'vendor/filament/support/stubs/PanelProvider.stub';

        if (! File::exists($filePath)) {
            $this->error("File not found!");
            return;
        }

        $content = File::get($filePath);

        $all_imports = [
            'use Jeffgreco13\FilamentBreezy\BreezyCore;',
            'use Stancl\Tenancy\Bootstrappers\DatabaseTenancyBootstrapper;',
            'use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;',
            'use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;',
        ];

        foreach ($all_imports as $import) {
            if (! Str::contains($content, $import)) {
                $content = str_replace("ShareErrorsFromSession;\n", "ShareErrorsFromSession;\n$import\n", $content);
            }
        }

        $fourSpaces  = '    ';
        $eightSpaces = $fourSpaces.$fourSpaces;

        if (! Str::contains($content, 'register()')) {

            $content     = str_replace("PanelProvider\n{\n",
                "PanelProvider\n{\n".
                "{$fourSpaces}public const PANEL = '{{ id }}';\n\n".
                "{$fourSpaces}public function register(): void\n".
                "{$fourSpaces}{\n".
                "{$eightSpaces}parent::register();\n".
                "{$eightSpaces}\$this->app->afterResolving(DatabaseTenancyBootstrapper::class, function () {\n".
                "{$eightSpaces}{$fourSpaces}tenant()?->brands->where('panel', self::PANEL)?->first()?->applyToPanel(self::PANEL, tenant());\n".
                "{$eightSpaces}});\n".
                "$fourSpaces}\n", $content);
        }

        $replace_methods = [
            "->id('{{ id }}')"   => '->id(self::PANEL)',
            "->path('{{ id }}')" => '->path(self::PANEL)',
        ];

        foreach($replace_methods as $old => $new) {
            $content = str_replace($old, $new, $content);
        }

        $new_methods = [
            '->spa()'              => '->spa()',
            '->login()'            => '->login()',
            '->registration()'     => '->registration()',
            '->authGuard'          => '->authGuard(self::PANEL)',
        ];

        foreach($new_methods as $old => $new) {
            if (! Str::contains($content, $old)) {
                $content = str_replace("->path(self::PANEL)\n", "->path(self::PANEL)\n$fourSpaces$eightSpaces$new\n", $content);
            }
        }

        $middleware = [
            'PreventAccessFromCentralDomains::class,',
            'InitializeTenancyByDomain::class,',
        ];

        foreach ($middleware as $ware) {
            if (! Str::contains($content, $ware)) {
                $content = str_replace("middleware([\n", "middleware([\n$eightSpaces$eightSpaces$ware\n", $content);
            }
        }

        File::put($filePath, $content);

        $this->info("Rewriting Filament Stub: PanelProvider.stub");

        $this->call('make:filament-panel', [
            'id' => $this->ask('What is the ID?'),
        ]);
    }
}
