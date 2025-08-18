<?php

namespace Filaforge\OllamaChat\Filament\Pages;

use Filament\Pages\Page;

class OllamaChatPage extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static string|\UnitEnum|null $navigationGroup = 'AI';
    protected static ?string $title = 'Ollama Chat';
    protected static ?string $navigationLabel = 'Ollama Chat';
    protected static ?int $navigationSort = 90;
    protected string $view = 'ollama-chat::pages.chat';
}