<?php

namespace Filaforge\WirechatDashboard\Providers;

use Filaforge\WirechatDashboard\Pages\WirechatDashboardPage;
use Filaforge\WirechatDashboard\Pages\DeepseekSettingsPage;
use Filament\Contracts\Plugin as PluginContract;
use Filament\Panel;

class WirechatDashboardPanelPlugin implements PluginContract
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'deepseek-chat';
    }

    public function register(Panel $panel): void
    {
        $panel->pages([
            WirechatDashboardPage::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        // no-op
    }
}
