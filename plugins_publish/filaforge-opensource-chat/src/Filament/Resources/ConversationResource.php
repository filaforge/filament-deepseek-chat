<?php

namespace Filaforge\OpensourceChat\Filament\Resources;

use Filaforge\OpensourceChat\Models\Conversation;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages;

class ConversationResource extends Resource
{
    protected static ?string $model = Conversation::class;
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';
    protected static ?string $navigationGroup = 'System';
    protected static ?int $navigationSort = 61;

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            TextInput::make('user_id')->numeric()->required(),
            TextInput::make('title')->maxLength(190)->nullable(),
            Textarea::make('messages')->rows(10)->required()->helperText('Stored as JSON array'),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            TextColumn::make('id')->sortable(),
            TextColumn::make('user_id')->sortable(),
            TextColumn::make('title')->limit(40)->searchable(),
            TextColumn::make('updated_at')->since()->label('Updated'),
        ])
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
