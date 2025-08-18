<?php

namespace Filaforge\OllamaChat\Filament\Pages;

use Filament\Pages\Page;

class OllamaSettingsPage extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static string|\UnitEnum|null $navigationGroup = 'AI';
    protected static ?string $navigationLabel = 'Ollama Settings';
    protected string $view = 'ollama-chat::pages.settings';
}