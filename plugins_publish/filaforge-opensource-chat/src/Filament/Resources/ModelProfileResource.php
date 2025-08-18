<?php

namespace Filaforge\OpensourceChat\Filament\Resources;

use Filaforge\OpensourceChat\Models\ModelProfile;
use Filament\Resources\Resource;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\KeyValue;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Resources\Pages;

class ModelProfileResource extends Resource
{
    protected static ?string $model = ModelProfile::class;
    protected static ?string $navigationIcon = 'heroicon-o-cog-8-tooth';
    protected static ?string $navigationGroup = 'System';
    protected static ?int $navigationSort = 60;

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            TextInput::make('name')->required()->maxLength(190),
            TextInput::make('provider')->default('opensource')->required(),
            TextInput::make('model_id')->label('Model ID')->required()->maxLength(255),
            TextInput::make('base_url')->url()->nullable(),
            TextInput::make('api_key')->password()->revealable()->nullable()->helperText('Override per-user key (optional)'),
            Toggle::make('stream')->default(true),
            TextInput::make('timeout')->numeric()->default(60)->minValue(5)->maxValue(600),
            TextInput::make('per_minute_limit')->numeric()->nullable()->minValue(1),
            TextInput::make('per_day_limit')->numeric()->nullable()->minValue(1),
            Textarea::make('system_prompt')->rows(3)->columnSpanFull(),
            KeyValue::make('extra')->columnSpanFull()->reorderable()->nullable(),
        ])->columns(2);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->sortable()->searchable(),
                TextColumn::make('provider')->badge()->color('gray'),
                TextColumn::make('model_id')->label('Model')->limit(40)->tooltip(fn($r)=>$r->model_id),
                IconColumn::make('stream')->boolean(),
                TextColumn::make('per_minute_limit')->label('Min'),
                TextColumn::make('per_day_limit')->label('Day'),
                TextColumn::make('updated_at')->since()->label('Updated'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
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
