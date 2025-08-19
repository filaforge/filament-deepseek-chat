<?php

namespace Filaforge\OllamaChat;

use Illuminate\Support\ServiceProvider;

class OllamaChatServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Views (namespaced as: ollama-chat::)
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'ollama-chat');

    // API routes for chat endpoint
    $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');

        // Config
        $this->publishes([
            __DIR__ . '/../config/ollama-chat.php' => config_path('ollama-chat.php'),
        ], 'ollama-chat-config');

        // Assets (CSS / JS)
        $this->publishes([
            __DIR__ . '/../resources/css/ollama-chat.css' => public_path('vendor/ollama-chat/ollama-chat.css'),
            __DIR__ . '/../resources/js/ollama-chat.js' => public_path('vendor/ollama-chat/ollama-chat.js'),
        ], 'ollama-chat-assets');

        // Migrations
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }

    // The Filament Panel plugin class (OllamaChatPanelPlugin) must be manually added to your Panel in a PanelProvider
    // similar to other plugins: ->plugins([\Filaforge\OllamaChat\Filament\OllamaChatPanelPlugin::make()])
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/ollama-chat.php', 'ollama-chat');
    }
}