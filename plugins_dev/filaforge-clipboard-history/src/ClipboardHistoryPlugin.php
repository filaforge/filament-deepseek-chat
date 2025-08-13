<?php

namespace Filaforge\ClipboardHistory;

use Filament\Contracts\Plugin as PluginContract;
use Filament\Panel;

class ClipboardHistoryPlugin implements PluginContract
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'clipboard-history';
    }

    public function register(Panel $panel): void
    {
        $panel->widgets([
            \Filaforge\ClipboardHistory\Widgets\ClipboardHistoryWidget::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}



