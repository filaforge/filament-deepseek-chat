<?php

namespace Filaforge\HuggingfaceChat\Providers;

use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Illuminate\Support\Facades\Schema;
use Filaforge\HuggingfaceChat\Models\ModelProfile;

class HfChatServiceProvider extends PackageServiceProvider
{
	public static string $name = 'huggingface-chat';

	public function configurePackage(Package $package): void
	{
		$package
			->name(static::$name)
			->hasConfigFile('hf-chat')
			->hasViews()
			->hasTranslations()
			->hasMigrations()
			->hasInstallCommand(function (InstallCommand $command) {
				$command
					->publishConfigFile()
					->askToRunMigrations();
			});
	}

	public function packageBooted(): void
	{
		// Ensure migrations are loaded even if automatic registration fails in this monorepo context
		// (Some path repository setups can interfere with Spatie Package Tools' default hasMigrations behavior.)
		$this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
		FilamentAsset::register([
			Css::make('hf-chat', __DIR__ . '/../../resources/css/hf-chat.css'),
			Js::make('hf-chat', __DIR__ . '/../../resources/js/hf-chat.js'),
		], package: 'filaforge/huggingface-chat');

		// Seed a default Fireworks / OSS profile if none exists (idempotent)
		try {
			if (Schema::hasTable('hf_model_profiles')) {
				if (! ModelProfile::query()->where('model_id', 'openai/gpt-oss-120b:fireworks-ai')->exists()) {
					ModelProfile::create([
						'name' => 'GPT-OSS 120B (Fireworks)',
						'provider' => 'openai',
						'model_id' => 'openai/gpt-oss-120b:fireworks-ai',
						'base_url' => null, // rely on configured base or user setting
						'api_key' => null,
						'stream' => true,
						'timeout' => 120,
						'system_prompt' => 'You are a powerful general assistant.',
					]);
				}
			}
		} catch (\Throwable $e) {
			// Silently ignore seeding issues (e.g., during initial migration run before table exists fully)
		}
	}
}



