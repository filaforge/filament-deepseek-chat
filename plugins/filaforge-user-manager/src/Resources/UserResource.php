<?php

namespace Filaforge\UserManager\Resources;

use App\Models\User;
use BackedEnum;
use Filament\Forms as Forms;
use Filament\Resources\Resource;
use Filaforge\UserManager\Resources\UserResource\Pages;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password as PasswordFacade;
use Illuminate\Validation\Rules\Password;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = 'User Management';

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-users';

    protected static string | UnitEnum | null $navigationGroup = 'System';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->autocomplete('new-password')
                            ->minLength(8)
                            ->rule(Password::defaults())
                            ->maxLength(255)
                            ->dehydrateStateUsing(static fn (string $state): string => Hash::make($state))
                            ->dehydrated(static fn (?string $state): bool => filled($state))
                            ->required(static fn (string $operation): bool => $operation === 'create'),

                        Forms\Components\TextInput::make('password_confirmation')
                            ->label('Confirm Password')
                            ->password()
                            ->same('password')
                            ->dehydrated(false)
                            ->required(fn (Forms\Get $get): bool => filled($get('password'))),

                        Forms\Components\DateTimePicker::make('email_verified_at')
                            ->label('Email Verified At')
                            ->seconds(false)
                            ->nullable(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Verified')
                    ->boolean()
                    ->trueIcon('heroicon-o-check')
                    ->falseIcon('heroicon-o-x-mark')
                    ->getStateUsing(static fn (User $record): bool => (bool) $record->email_verified_at),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('verified')
                    ->label('Verified')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('email_verified_at'),
                        false: fn ($query) => $query->whereNull('email_verified_at'),
                        blank: fn ($query) => $query,
                    ),
            ])
            ->recordActions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
                \Filament\Actions\Action::make('verify')
                    ->label('Verify')
                    ->icon('heroicon-o-check')
                    ->visible(fn (User $record): bool => $record->email_verified_at === null)
                    ->requiresConfirmation()
                    ->action(fn (User $record) => $record->forceFill(['email_verified_at' => now()])->save())
                    ->successNotificationTitle('User verified'),
                \Filament\Actions\Action::make('unverify')
                    ->label('Unverify')
                    ->icon('heroicon-o-x-mark')
                    ->color('warning')
                    ->visible(fn (User $record): bool => $record->email_verified_at !== null)
                    ->requiresConfirmation()
                    ->action(fn (User $record) => $record->forceFill(['email_verified_at' => null])->save())
                    ->successNotificationTitle('User unverified'),
                \Filament\Actions\Action::make('sendResetLink')
                    ->label('Send password reset')
                    ->icon('heroicon-o-envelope')
                    ->action(function (User $record): void {
                        PasswordFacade::sendResetLink(['email' => $record->email]);
                    })
                    ->successNotificationTitle('Password reset email sent'),
                \Filament\Actions\Action::make('sendVerificationEmail')
                    ->label('Send verification email')
                    ->icon('heroicon-o-paper-airplane')
                    ->visible(fn (User $record): bool => $record->email_verified_at === null)
                    ->action(function (User $record): void {
                        $record->sendEmailVerificationNotification();
                    })
                    ->successNotificationTitle('Verification email sent'),
            ])
            ->groupedBulkActions([
                \Filament\Actions\DeleteBulkAction::make(),
                \Filament\Actions\BulkAction::make('bulkVerify')
                    ->label('Verify selected')
                    ->icon('heroicon-o-check')
                    ->requiresConfirmation()
                    ->action(function (\Illuminate\Support\Collection $records): void {
                        $records->each(fn (User $user) => $user->forceFill(['email_verified_at' => now()])->save());
                    })
                    ->successNotificationTitle('Selected users verified'),
                \Filament\Actions\BulkAction::make('bulkUnverify')
                    ->label('Unverify selected')
                    ->icon('heroicon-o-x-mark')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (\Illuminate\Support\Collection $records): void {
                        $records->each(fn (User $user) => $user->forceFill(['email_verified_at' => null])->save());
                    })
                    ->successNotificationTitle('Selected users unverified'),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email'];
    }
}


