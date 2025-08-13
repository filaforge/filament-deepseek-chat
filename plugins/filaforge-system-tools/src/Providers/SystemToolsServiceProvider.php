<?php

namespace Filaforge\SystemTools\Providers;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SystemToolsServiceProvider extends PackageServiceProvider
{
    public static string $name = 'system-tools';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile('filament-system-tools')
            ->hasViews();
    }
}



