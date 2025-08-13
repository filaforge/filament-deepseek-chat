<?php

namespace Filaforge\ClipboardHistory\Providers;

use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Facades\FilamentAsset;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ClipboardHistoryServiceProvider extends PackageServiceProvider
{
    public static string $name = 'clipboard-history';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile()
            ->hasViews();
    }

    public function packageBooted(): void
    {
        FilamentAsset::register([
            AlpineComponent::make('clipboard-history', __DIR__ . '/../../resources/dist/clipboard-history.js'),
        ], 'filaforge/clipboard-history');
    }
}



