<?php

namespace Filaforge\DeepseekChat\Providers;

use Filaforge\DeepseekChat\Pages\DeepseekChatPage;
use Filaforge\DeepseekChat\Pages\DeepseekSettingsPage;
use Filament\Contracts\Plugin as PluginContract;
use Filament\Panel;

class DeepseekChatPanelPlugin implements PluginContract
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
            DeepseekChatPage::class,
            DeepseekSettingsPage::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        // no-op
    }
}
