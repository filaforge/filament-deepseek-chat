<?php

namespace Filaforge\OpensourceChat;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\Schema;
use Filaforge\OpensourceChat\Models\ModelProfile;
use Filaforge\OpensourceChat\Console\Commands\SetupProviderCommand;

class OpensourceChatServiceProvider extends PackageServiceProvider
{
    public static string $name = 'opensource-chat';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile('opensource-chat')
            ->hasViews()
            ->hasTranslations()
            ->hasCommands([
                SetupProviderCommand::class,
            ])
            ->hasMigrations()
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->askToRunMigrations();
            });
    }

    public function packageBooted(): void
    {
        // Ensure migrations are loaded from package (idempotent)
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Register raw resources for now; build step can write to dist when bundling
        $cssPath = __DIR__.'/../resources/css/opensource-chat.css';
        $jsPath = __DIR__.'/../resources/js/opensource-chat.js';
        FilamentAsset::register([
            Css::make('opensource-chat-styles', $cssPath),
            Js::make('opensource-chat-scripts', $jsPath),
        ], package: 'filaforge/opensource-chat');

        // Seed a default local Ollama profile if none exists
        try {
            if (Schema::hasTable('oschat_model_profiles')) {
                $exists = ModelProfile::query()
                    ->where('provider', 'ollama')
                    ->where(function ($q) {
                        $q->where('model_id', 'like', 'llama3%')
                          ->orWhere('name', 'like', '%Ollama%');
                    })
                    ->exists();
                if (! $exists) {
                    ModelProfile::create([
                        'name' => 'Ollama (Local Llama3)',
                        'provider' => 'ollama',
                        'model_id' => config('opensource-chat.ollama.default_model_id', 'llama3:latest'),
                        'base_url' => config('opensource-chat.ollama.base_url', 'http://localhost:11434'),
                        'api_key' => null,
                        'stream' => true,
                        'timeout' => 120,
                        'system_prompt' => 'You are a helpful assistant.',
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // ignore during initial boot/migrate
        }
    }
}
