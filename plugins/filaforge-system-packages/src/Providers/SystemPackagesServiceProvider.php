<?php

namespace Filaforge\SystemPackages\Providers;

use Filaforge\SystemPackages\Widgets\SystemPackagesWidget;
use Livewire\Livewire;
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
        Livewire::component('filaforge.system-packages.widgets.system-packages-widget', SystemPackagesWidget::class);
    }
}

