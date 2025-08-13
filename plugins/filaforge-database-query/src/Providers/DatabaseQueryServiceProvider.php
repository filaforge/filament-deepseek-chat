<?php

namespace Filaforge\DatabaseQuery\Providers;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class DatabaseQueryServiceProvider extends PackageServiceProvider
{
    public static string $name = 'database-query';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasViews();
    }
}


