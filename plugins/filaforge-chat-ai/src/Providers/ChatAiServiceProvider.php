<?php

namespace Filaforge\ChatAi\Providers;

use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Illuminate\Support\Facades\Schema;
use Filaforge\ChatAi\Models\ModelProfile;

class ChatAiServiceProvider extends PackageServiceProvider
{
	public static string $name = 'chat-ai';

	public function configurePackage(Package $package): void
	{
		$package
			->name(static::$name)
			->hasConfigFile('chat-ai')
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
			Css::make('chat-ai', __DIR__ . '/../../resources/css/chat-ai.css'),
			Js::make('chat-ai', __DIR__ . '/../../resources/js/chat-ai.js'),
		], package: 'filaforge/chat-ai');

		// Seed default profiles if they don't exist (idempotent)
		try {
			if (Schema::hasTable('chat_ai_model_profiles')) {
				// Load default profiles from config
				$defaultProfiles = config('chat-ai.default_profiles', []);

				foreach ($defaultProfiles as $profileConfig) {
					$apiKey = trim((string) ($profileConfig['api_key'] ?? ''));
					if ($apiKey === '') {
						$profileConfig['is_active'] = false;
					}
					if (!ModelProfile::query()->where('model_id', $profileConfig['model_id'])->exists()) {
						ModelProfile::create($profileConfig);
					}
				}
			}
		} catch (\Throwable $e) {
			// Silently ignore seeding issues (e.g., during initial migration run before table exists fully)
		}
	}
}



