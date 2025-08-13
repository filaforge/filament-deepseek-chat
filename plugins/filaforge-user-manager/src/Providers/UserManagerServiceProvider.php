<?php

namespace Filaforge\UserManager\Providers;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class UserManagerServiceProvider extends PackageServiceProvider
{
    public static string $name = 'user-manager';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasViews();
    }
}


