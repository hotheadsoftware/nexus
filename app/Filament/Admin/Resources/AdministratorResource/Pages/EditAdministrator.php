<?php

namespace App\Filament\Admin\Resources\AdministratorResource\Pages;

use App\Filament\Admin\Resources\AdministratorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAdministrator extends EditRecord
{
    protected static string $resource = AdministratorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
