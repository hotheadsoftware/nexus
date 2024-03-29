<?php

/**
 * This ensures that Filament panel provider stub exists and contains the necessary
 * pieces for us to perform string replacement, so we can customize the panel with
 * details needed for Nexus operations.
 *
 * If this test sequence fails, it means that the stub file has been updated and
 * we need to update our replacement logic in app/Console/Commands/NexusMakePanel.php
 *
 * Troubleshooting: delete the vendor/ folder and re-run composer install. If that
 * doesn't work, we've probably encountered a breaking change in Filament. We'll
 * need to update the stub file and the stub manipulation logic found in our
 * NexusMakePanelProviderStub.php file.
 */
$stubFilePath    = 'vendor/filament/support/stubs/PanelProvider.stub';
$stubFileContent = file_get_contents($stubFilePath);

it('has the stub file in the right location', function () use ($stubFilePath) {

    $this->assertTrue(file_exists($stubFilePath));
});

it('has the necessary strings in place for us to find & replace', function () use ($stubFileContent) {
    $find = [
        'App\\\\Filament\\\\{{ directory }}\\\\Resources',
        'App\\\\Filament\\\\{{ directory }}\\\\Pages',
        'App\\\\Filament\\\\{{ directory }}\\\\Widgets',
        '{{ class }}',
        '{{ id }}',
    ];
    foreach ($find as $string) {
        $this->assertStringContainsString($string, $stubFileContent);
    }
});

it('looks like a new stub', function () use ($stubFileContent) {
    $find = [
        '# Custom Branding Goes Here',
        'PreventAccessFromCentralDomains::class',
        'InitializeTenancyByDomain::class',
        'Jeffgreco13\FilamentBreezy\BreezyCore',
        'public function register(): void',
    ];
    foreach ($find as $string) {
        $this->assertStringNotContainsString($string, $stubFileContent);
    }
});
