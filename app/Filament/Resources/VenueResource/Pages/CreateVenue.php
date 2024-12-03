<?php

namespace App\Filament\Resources\VenueResource\Pages;

use App\Filament\Resources\VenueResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateVenue extends CreateRecord
{
    protected static string $resource = VenueResource::class;

    protected function getRedirectUrl(): string
    {
        // Redirect to the index page of VenueResource
        return $this->getResource()::getUrl('index');
    }
}
