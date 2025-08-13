<?php

namespace Filaforge\SystemPackages\Providers;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SystemPackagesServiceProvider extends PackageServiceProvider
{
    public static string $name = 'system-packages';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasViews();
    }

    public function packageBooted(): void
    {
        //
    }
}

