<?php

namespace Filaforge\OpensourceChat\Filament\Resources;

use Filaforge\OpensourceChat\Models\Setting;
use Filament\Resources\Resource;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Resources\Pages;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Open Source Chat';
    protected static ?int $navigationSort = 62;

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            TextInput::make('user_id')->numeric()->required(),
            TextInput::make('model_id')->maxLength(255)->nullable(),
            TextInput::make('base_url')->url()->nullable(),
            Toggle::make('stream')->default(true),
            TextInput::make('timeout')->numeric()->default(60),
            Textarea::make('system_prompt')->rows(3)->nullable(),
        ])->columns(2);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            TextColumn::make('user_id')->sortable(),
            TextColumn::make('model_id')->label('Model')->limit(40)->toggleable(),
            TextColumn::make('timeout')->sortable(),
            TextColumn::make('updated_at')->since()->label('Updated'),
        ])->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRecords::route('/'),
            'create' => Pages\CreateRecord::route('/create'),
            'edit' => Pages\EditRecord::route('/{record}/edit'),
        ];
    }
}
