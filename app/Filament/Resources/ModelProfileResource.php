<?php

namespace App\Filament\Resources;

use Filaforge\HuggingfaceChat\Models\ModelProfile;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\KeyValue;
use App\Filament\Resources\ModelProfileResource\Pages;

class ModelProfileResource extends Resource
{
    protected static ?string $model = ModelProfile::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-8-tooth';
    protected static string|\UnitEnum|null $navigationGroup = 'System';
    protected static ?int $navigationSort = 5;
    protected static ?string $navigationLabel = 'Model Profiles';
    protected static ?string $pluralModelLabel = 'Model Profiles';
    protected static ?string $modelLabel = 'Model Profile';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->schema([
            TextInput::make('name')->label('Profile Name')->required()->maxLength(190)->columnSpanFull(),
            TextInput::make('provider')->default('huggingface')->maxLength(100)->required(),
            TextInput::make('model_id')->label('Model ID')->required()->maxLength(190)->helperText('e.g. meta-llama/Meta-Llama-3-8B-Instruct'),
            TextInput::make('base_url')->label('Base URL')->placeholder('https://api-inference.huggingface.co')->columnSpanFull(),
            TextInput::make('api_key')->label('API Key Override')->password()->revealable()->helperText('Leave blank to use user key')->columnSpanFull(),
            Toggle::make('stream')->default(true),
            TextInput::make('timeout')->numeric()->default(60)->minValue(5)->maxValue(600),
            Textarea::make('system_prompt')->rows(3)->columnSpanFull(),
            KeyValue::make('extra')->keyLabel('Key')->valueLabel('Value')->columnSpanFull()->reorderable(),
            TextInput::make('per_minute_limit')->numeric()->minValue(1)->label('Per-Minute Limit')->helperText('Max messages per user per minute (blank = unlimited)'),
            TextInput::make('per_day_limit')->numeric()->minValue(1)->label('Per-Day Limit')->helperText('Max messages per user per day (blank = unlimited)'),
    ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('provider')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('model_id')->label('Model')->searchable()->limit(40),
                IconColumn::make('stream')->boolean(),
                TextColumn::make('per_minute_limit')->label('Min')->sortable(),
                TextColumn::make('per_day_limit')->label('Day')->sortable(),
                TextColumn::make('updated_at')->since()->label('Updated'),
            ])
            ->filters([])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListModelProfiles::route('/'),
            'create' => Pages\CreateModelProfile::route('/create'),
            'edit' => Pages\EditModelProfile::route('/{record}/edit'),
        ];
    }
}
