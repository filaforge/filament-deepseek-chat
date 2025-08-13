<?php

namespace Filaforge\QuickLinks\Providers;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class QuickLinksServiceProvider extends PackageServiceProvider
{
    public static string $name = 'quick-links';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasViews()
            ->hasMigrations(['create_dashboard_bookmarks_table'])
            ->runsMigrations();
    }
}


