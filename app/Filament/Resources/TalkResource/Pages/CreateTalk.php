<?php

namespace App\Filament\Resources\TalkResource\Pages;

use App\Filament\Resources\TalkResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTalk extends CreateRecord
{
    protected static string $resource = TalkResource::class;
    
    protected function getRedirectUrl(): string
    {
        // Redirect to the index page of VenueResource
        return $this->getResource()::getUrl('index');
    }
}
