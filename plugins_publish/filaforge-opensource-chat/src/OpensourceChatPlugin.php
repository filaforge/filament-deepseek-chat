<?php

namespace Filaforge\OpensourceChat;

use Filament\Contracts\Plugin as PluginContract;
use Filament\Panel;
use Filaforge\OpensourceChat\Pages\OpenSourceChatPage;
use Filaforge\OpensourceChat\Pages\OpenSourceSettingsPage;

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
        $panel->pages([
            OpenSourceChatPage::class,
            OpenSourceSettingsPage::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
