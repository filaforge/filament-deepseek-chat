<?php

namespace Filaforge\OpensourceChat;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;

class OpensourceChatServiceProvider extends PackageServiceProvider
{
    public static string $name = 'opensource-chat';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile()
            ->hasViews()
            ->hasTranslations()
            // Load migrations directly from package's database/migrations directory at runtime
            ->runsMigrations();
    }

    public function packageBooted(): void
    {
        // Register raw resources for now; build step can write to dist when bundling
        $cssPath = __DIR__.'/../resources/css/opensource-chat.css';
        $jsPath = __DIR__.'/../resources/js/opensource-chat.js';
        FilamentAsset::register([
            Css::make('opensource-chat-styles', $cssPath),
            Js::make('opensource-chat-scripts', $jsPath),
        ], package: 'filaforge/opensource-chat');
    }
}
