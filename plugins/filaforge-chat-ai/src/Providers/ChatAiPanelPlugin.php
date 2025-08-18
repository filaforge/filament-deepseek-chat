<?php

namespace Filaforge\ChatAi\Providers;

use Filaforge\ChatAi\Pages\ChatAiChatPage;
use Filaforge\ChatAi\Pages\ChatAiConversationsPage;
use Filaforge\ChatAi\Pages\ChatAiSettingsPage;
use Filaforge\ChatAi\Resources\ModelProfileResource;
use Filament\Contracts\Plugin as PluginContract;
use Filament\Panel;

class ChatAiPanelPlugin implements PluginContract
{
	public static function make(): static
	{
		return app(static::class);
	}

	public function getId(): string
	{
		return 'chat-ai';
	}

	public function register(Panel $panel): void
	{
		// Register pages and resources
		$panel->pages([
			ChatAiChatPage::class,
			ChatAiConversationsPage::class,
		])->resources([
			ModelProfileResource::class,
		]);
	}

	public function boot(Panel $panel): void
	{
		// no-op
	}
}


