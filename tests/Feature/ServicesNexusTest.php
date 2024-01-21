<?php

use App\Services\Nexus;


use Illuminate\Console\Command;

it('collects panel configuration inputs', function () {
    $command = Mockery::mock(Command::class);
    $command->shouldReceive('option')
        ->with('name')
        ->andReturn('TestPanel');
    $command->shouldReceive('option')
        ->with('tenant')
        ->andReturn(true);
    $command->shouldReceive('option')
        ->with('api_tokens')
        ->andReturn(true);
    $command->shouldReceive('option')
        ->with('model')
        ->andReturn('UserModel');
    $command->shouldReceive('option')
        ->with('login')
        ->andReturn(true);
    $command->shouldReceive('option')
        ->with('registration')
        ->andReturn(true);
    $command->shouldReceive('option')
        ->with('branding')
        ->andReturn(true);
    $command->shouldReceive('option')
        ->with('copy_branding')
        ->andReturn(false);
    $command->shouldReceive('option')
        ->with('copy_branding_from')
        ->andReturn('')
    ;

    $nexus = new Nexus();
    $config = $nexus->getPanelConfigurationInputs($command);

    expect($config->get('name'))->toEqual('TestPanel');
    expect($config->get('tenant'))->toEqual(true);
    expect($config->get('model'))->toEqual('UserModel');
    expect($config->get('login'))->toEqual(true);
    expect($config->get('registration'))->toEqual(true);
    expect($config->get('branding'))->toEqual(true);
    expect($config->get('copy_branding'))->toEqual(false);

});

use Illuminate\Filesystem\Filesystem;
use org\bovigo\vfs\vfsStream;

it('correctly lists panel names', function () {
    $root = vfsStream::setup('Providers/Filament');
    vfsStream::newFile('UserProvider.php')->at($root);
    vfsStream::newFile('AdminProvider.php')->at($root);

    $filesystem = Mockery::mock(Filesystem::class);
    $filesystem->shouldReceive('isDirectory')->andReturn(true);
    $filesystem->shouldReceive('files')->andReturn([
        new SplFileInfo($root->url() . '/UserProvider.php'),
        new SplFileInfo($root->url() . '/AdminProvider.php'),
    ]);

    $nexus = new Nexus($filesystem);
    $panelNames = $nexus->panelNames();

    expect($panelNames)->toContain('user', 'admin');
});
