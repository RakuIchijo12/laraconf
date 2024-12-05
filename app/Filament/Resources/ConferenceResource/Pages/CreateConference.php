<?php

namespace App\Filament\Resources\ConferenceResource\Pages;

use App\Filament\Resources\ConferenceResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateConference extends CreateRecord
{
    protected static string $resource = ConferenceResource::class;
    
    protected function getRedirectUrl(): string
    {
        // Redirect to the index page of VenueResource
        return $this->getResource()::getUrl('index');
    }
}
