<?php

namespace Filaforge\SystemMonitor\Providers;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SystemMonitorServiceProvider extends PackageServiceProvider
{
    public static string $name = 'system-monitor';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile('filaforge-system-monitor')
            ->hasViews()
            ->hasTranslations();
    }

    public function packageBooted(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
    }
}
