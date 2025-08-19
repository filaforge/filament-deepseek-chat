<?php

namespace Filaforge\QuickLinks;

use Filament\Contracts\Plugin as PluginContract;
use Filament\Panel;

class QuickLinksPlugin implements PluginContract
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'quick-links';
    }

    public function register(Panel $panel): void
    {
        $panel->widgets([
            \Filaforge\QuickLinks\Widgets\QuickLinksWidget::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}



