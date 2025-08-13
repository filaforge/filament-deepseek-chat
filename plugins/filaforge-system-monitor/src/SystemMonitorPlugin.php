<?php

namespace Filaforge\SystemMonitor;

use Filaforge\SystemMonitor\Widgets\SystemInfoWidget;
use Filaforge\SystemMonitor\Widgets\SystemMonitorWidget;
use Filaforge\SystemMonitor\Widgets\FilamentPackagesWidget;
use Filament\Contracts\Plugin as PluginContract;
use Filament\Panel;

class SystemMonitorPlugin implements PluginContract
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'system-monitor';
    }

    public function register(Panel $panel): void
    {
        // Register widgets for this plugin.
        $panel->widgets([
            SystemMonitorWidget::class,
            SystemInfoWidget::class,
            FilamentPackagesWidget::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}



