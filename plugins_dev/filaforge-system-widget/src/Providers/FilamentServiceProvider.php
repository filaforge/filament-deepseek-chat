<?php

namespace Filaforge\SystemWidget\Providers;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SystemMonitorServiceProvider extends PackageServiceProvider
{
    public static string $name = 'system-widget';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile('system-widget')
            ->hasViews()
            ->hasTranslations()
            ->hasRoute('web');
    }
}
