<?php

namespace Filaforge\Wirechat;

use Filaforge\Wirechat\Pages\WirechatPage;
use Filament\Contracts\Plugin as PluginContract;
use Filament\Panel;

class WirechatPlugin implements PluginContract
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'wirechat';
    }

    public function register(Panel $panel): void
    {
        $panel->pages([
            WirechatPage::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}


