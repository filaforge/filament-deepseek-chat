<?php

namespace Filaforge\HuggingfaceChat\Providers;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

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
		FilamentAsset::register([
			Css::make('hf-chat', __DIR__ . '/../../resources/css/hf-chat.css'),
		], package: 'filaforge/huggingface-chat');
	}
}


