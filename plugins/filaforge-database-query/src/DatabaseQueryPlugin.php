<?php

namespace Filaforge\DatabaseQuery;

use Filaforge\DatabaseQuery\Filament\Pages\DatabaseQuery;
use Filament\Contracts\Plugin;
use Filament\Panel;

class DatabaseQueryPlugin implements Plugin
{
    public function getId(): string
    {
        return 'database-query';
    }

    public function register(Panel $panel): void
    {
        $panel->pages([
            \Filaforge\DatabaseQuery\Pages\DatabaseQuery::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }
}


