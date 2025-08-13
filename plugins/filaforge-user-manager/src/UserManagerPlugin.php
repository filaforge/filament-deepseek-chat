<?php

namespace Filaforge\UserManager;

use Filament\Contracts\Plugin as PluginContract;
use Filament\Panel;

class UserManagerPlugin implements PluginContract
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'user-manager';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            \Filaforge\UserManager\Resources\UserResource::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}


