<?php

namespace App\Filament\Resources\DomainResource\Pages;

use App\Filament\Resources\DomainResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDomain extends CreateRecord
{
    protected static string $resource = DomainResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Determine whether to create this as a subdomain or a domain.
        $data['domain'] = $data['is_subdomain']
            ? $data['domain'].'.'.parse_url(config('app.url'), PHP_URL_HOST)
            : $data['domain'];

        return $data;
    }
}
