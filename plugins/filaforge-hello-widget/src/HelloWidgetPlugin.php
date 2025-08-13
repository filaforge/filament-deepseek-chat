<?php

namespace Filaforge\HelloWidget;

use Filaforge\HelloWidget\Filament\Widgets\HelloWidget;
use Filament\Contracts\Plugin as PluginContract;
use Filament\Panel;

class HelloWidgetPlugin implements PluginContract
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'hello-widget';
    }

    public function register(Panel $panel): void
    {
        $panel->widgets([
            HelloWidget::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        // No boot steps required for this example.
    }
}
