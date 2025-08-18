<?php

namespace App\Filament\Resources\ModelProfileResource\Pages;

use App\Filament\Resources\ModelProfileResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions; 

class ListModelProfiles extends ListRecords
{
    protected static string $resource = ModelProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
