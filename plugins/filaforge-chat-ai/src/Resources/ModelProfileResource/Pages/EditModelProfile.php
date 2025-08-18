<?php

namespace Filaforge\ChatAi\Resources\ModelProfileResource\Pages;

use Filaforge\ChatAi\Resources\ModelProfileResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditModelProfile extends EditRecord
{
    protected static string $resource = ModelProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
