<?php

namespace Filaforge\HuggingfaceChat\Providers;

use Filaforge\HuggingfaceChat\Pages\HfChatPage;
use Filaforge\HuggingfaceChat\Pages\HfSettingsPage;
use Filaforge\HuggingfaceChat\Pages\HfConversationsPage;
use Filament\Contracts\Plugin as PluginContract;
use Filament\Panel;

class HfChatPanelPlugin implements PluginContract
{
	public static function make(): static
	{
		return app(static::class);
	}

	public function getId(): string
	{
		return 'huggingface-chat';
	}

	public function register(Panel $panel): void
	{
		$panel->pages([
			HfChatPage::class,
			HfSettingsPage::class,
			HfConversationsPage::class,
		]);
	}

	public function boot(Panel $panel): void
	{
		// no-op
	}
}


