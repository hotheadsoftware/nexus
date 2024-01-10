<?php

namespace App\Filament\Admin\Resources\BrandResource\Pages;

use App\Filament\Admin\Resources\BrandResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBrand extends EditRecord
{
    protected static string $resource = BrandResource::class;

    protected function getRedirectUrl(): ?string
    {
        return url(route('filament.admin.pages.dashboard'));
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
