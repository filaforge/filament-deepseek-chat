<?php

namespace Filaforge\ApiExplorer\Providers;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ApiExplorerServiceProvider extends PackageServiceProvider
{
    public static string $name = 'api-explorer';

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
            Css::make('api-explorer', __DIR__ . '/../../resources/dist/api-explorer.css')->loadedOnRequest(),
        ], 'filaforge/api-explorer');
    }
}
