<?php

namespace Filaforge\UserManager\Resources\UserResource\Pages;

use Filaforge\UserManager\Resources\UserResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
use Filament\Forms;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('generateUsers')
                ->label('Generate Users')
                ->icon('heroicon-o-sparkles')
                ->form([
                    Forms\Components\TextInput::make('count')
                        ->label('How many?')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(1000)
                        ->default(10)
                        ->required(),
                    Forms\Components\Toggle::make('verified')
                        ->label('Mark as verified')
                        ->default(true),
                ])
                ->action(function (array $data): void {
                    $factory = \App\Models\User::factory();

                    if (($data['verified'] ?? false) === false) {
                        $factory = $factory->unverified();
                    }

                    $factory->count((int) ($data['count'] ?? 10))->create();
                })
                ->successNotificationTitle('Users generated'),
        ];
    }
}


