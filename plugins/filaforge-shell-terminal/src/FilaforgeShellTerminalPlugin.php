<?php

namespace Filaforge\ShellTerminal;

use Filament\Contracts\Plugin as PluginContract;
use Filament\Panel;

class FilaforgeShellTerminalPlugin implements PluginContract
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'shell-terminal';
    }

    public function register(Panel $panel): void
    {
        $panel->pages([
            \Filaforge\ShellTerminal\Pages\ShellTerminalPage::class,
            \Filaforge\ShellTerminal\Pages\ShellTerminalSettingsPage::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
