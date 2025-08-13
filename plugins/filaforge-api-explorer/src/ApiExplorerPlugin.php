<?php

namespace Filaforge\ApiExplorer;

use Filaforge\ApiExplorer\Pages\ApiExplorerPage;
use Filament\Contracts\Plugin;
use Filament\Panel;

class ApiExplorerPlugin implements Plugin
{
    public function getId(): string
    {
        return 'api-explorer';
    }

    public function register(Panel $panel): void
    {
        $panel->pages([
            ApiExplorerPage::class,
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
