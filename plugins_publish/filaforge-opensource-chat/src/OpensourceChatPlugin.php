<?php

namespace Filaforge\OpensourceChat;

use Filament\Contracts\Plugin as PluginContract;
use Filament\Panel;
use Filaforge\OpensourceChat\Pages\OpenSourceChatPage;
use Filaforge\OpensourceChat\Pages\OpenSourceSettingsPage;
use Filaforge\OpensourceChat\Providers\OpensourceChatPanelPlugin;

class OpensourceChatPlugin implements PluginContract
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
    // Delegate to Providers\OpensourceChatPanelPlugin for registration
    (new OpensourceChatPanelPlugin())->register($panel);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
