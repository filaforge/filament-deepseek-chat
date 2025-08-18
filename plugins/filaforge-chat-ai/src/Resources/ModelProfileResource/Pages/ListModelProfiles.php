<?php

namespace Filaforge\ChatAi\Resources\ModelProfileResource\Pages;

use Filaforge\ChatAi\Resources\ModelProfileResource;
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
