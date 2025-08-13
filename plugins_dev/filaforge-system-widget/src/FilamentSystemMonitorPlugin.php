<?php

namespace Filaforge\SystemWidget;

use Filaforge\SystemWidget\Filament\Widgets\SystemMonitorWidget;
use Filament\Contracts\Plugin as PluginContract;
use Filament\Panel;

class SystemWidgetPlugin implements PluginContract
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
    return 'system-widget';
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
