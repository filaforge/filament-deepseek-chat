<?php

namespace Filaforge\OllamaChat\Filament\Resources;

use Filaforge\OllamaChat\Models\Message;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;

class MessageResource extends Resource
{
    protected static ?string $model = Message::class;
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left';
    protected static ?string $navigationGroup = 'AI';
    protected static ?string $navigationLabel = 'Ollama Messages';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('conversation_id')->relationship('conversation','id')->searchable()->required(),
            Forms\Components\Textarea::make('message')->rows(4)->required(),
            Forms\Components\TextInput::make('sender')->maxLength(50)->default('user')->required(),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('id')->sortable(),
            Tables\Columns\TextColumn::make('conversation_id')->label('Conv'),
            Tables\Columns\TextColumn::make('sender')->badge(),
            Tables\Columns\TextColumn::make('message')->limit(40),
            Tables\Columns\TextColumn::make('created_at')->since(),
        ])->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ]);
    }
}