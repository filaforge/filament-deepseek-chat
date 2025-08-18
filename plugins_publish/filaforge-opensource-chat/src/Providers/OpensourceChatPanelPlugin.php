<?php

namespace Filaforge\OpensourceChat\Providers;

use Filaforge\OpensourceChat\Pages\OpenSourceChatPage;
use Filaforge\OpensourceChat\Pages\OpenSourceSettingsPage;
use Filaforge\OpensourceChat\Pages\OsModelsPage;
use Filament\Contracts\Plugin as PluginContract;
use Filament\Panel;

class OpensourceChatPanelPlugin implements PluginContract
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'opensource-chat';
    }

    public function register(Panel $panel): void
    {
        $panel->pages([
            OpenSourceChatPage::class,
            OsModelsPage::class,
            OpenSourceSettingsPage::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        // no-op
    }
}
