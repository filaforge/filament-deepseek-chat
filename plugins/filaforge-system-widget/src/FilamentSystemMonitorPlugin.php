<?php

namespace Filaforge\SystemMonitor;

use Filaforge\SystemMonitor\Filament\Widgets\SystemMonitorWidget;
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
        $panel->widgets([
            SystemMonitorWidget::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        // No panel-specific boot logic required currently.
    }
}
