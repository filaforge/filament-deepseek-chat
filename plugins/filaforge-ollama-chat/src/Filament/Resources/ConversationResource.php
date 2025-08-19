<?php

namespace Filaforge\OllamaChat\Filament\Resources;

use Filaforge\OllamaChat\Models\Conversation;
use Filament\Resources\Resource;
use Filament\Forms;
use Filament\Tables;

class ConversationResource extends Resource
{
    protected static ?string $model = Conversation::class;
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';
    protected static ?string $navigationGroup = 'AI';
    protected static ?string $navigationLabel = 'Ollama Conversations';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Textarea::make('conversation_data')->label('Data')->rows(6),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('id')->sortable(),
            Tables\Columns\TextColumn::make('user_id')->label('User'),
            Tables\Columns\TextColumn::make('updated_at')->since()->label('Updated'),
        ])->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ]);
    }
}