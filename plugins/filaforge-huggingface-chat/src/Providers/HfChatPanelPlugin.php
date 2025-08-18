<?php

namespace Filaforge\HuggingfaceChat\Providers;

use Filaforge\HuggingfaceChat\Pages\HfChatPage;
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
		]);
	}

	public function boot(Panel $panel): void
	{
		// no-op
	}
}


