<?php

namespace Filaforge\UserManager\Resources\UserResource\Pages;

use Filaforge\UserManager\Resources\UserResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
use Filament\Forms;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password as PasswordFacade;
use Illuminate\Support\Str;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('inviteUser')
                ->label('Invite User')
                ->icon('heroicon-o-user-plus')
                ->form([
                    Forms\Components\TextInput::make('name')
                        ->label('Name')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Toggle::make('verified')
                        ->label('Mark as verified')
                        ->default(false),
                    Forms\Components\Toggle::make('send_verification_email')
                        ->label('Send verification email (if unverified)')
                        ->default(true),
                ])
                ->action(function (array $data): void {
                    $user = \App\Models\User::query()->forceCreate([
                        'name' => (string) ($data['name'] ?? ''),
                        'email' => (string) ($data['email'] ?? ''),
                        'password' => Hash::make(Str::random(32)),
                        'email_verified_at' => ($data['verified'] ?? false) ? now() : null,
                    ]);

                    PasswordFacade::sendResetLink(['email' => $user->email]);

                    if (($data['verified'] ?? false) === false && ($data['send_verification_email'] ?? true)) {
                        $user->sendEmailVerificationNotification();
                    }
                })
                ->successNotificationTitle('Invitation sent'),
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


