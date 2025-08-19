<?php

namespace Filaforge\OllamaChat\Filament;

use Filament\Contracts\Plugin as PluginContract;
use Filament\Panel;
use Filaforge\OllamaChat\Filament\Pages\OllamaChatPage;

class OllamaChatPanelPlugin implements PluginContract
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'ollama-chat';
    }

    public function register(Panel $panel): void
    {
        $panel->pages([
            OllamaChatPage::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        // no-op for now
    }
}
