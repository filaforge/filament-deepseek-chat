<?php

namespace Filaforge\DeepseekChat\Providers;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class DeepseekChatServiceProvider extends PackageServiceProvider
{
    public static string $name = 'deepseek-chat';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile('deepseek-chat')
            ->hasViews()
            ->hasTranslations()
            ->hasMigrations()
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->askToRunMigrations();
            });
    }

    public function packageBooted(): void
    {
        // Register the CSS asset
        FilamentAsset::register([
            Css::make('deepseek-chat', __DIR__ . '/../../resources/css/deepseek-chat.css'),
        ], package: 'filaforge/deepseek-chat');
    }
}
