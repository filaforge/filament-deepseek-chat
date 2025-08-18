<?php

namespace Filaforge\OpensourceChat;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;

class OpensourceChatServiceProvider extends PackageServiceProvider
{
    public static string $name = 'opensource-chat';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile()
            ->hasViews()
            ->hasTranslations()
            ->hasMigrations([
                'create_oschat_conversations_table',
                'create_oschat_model_profiles_table',
                'create_oschat_settings_table',
                'add_oschat_last_profile_id_to_users_table',
            ]);
    }

    public function packageBooted(): void
    {
        FilamentAsset::register([
            Css::make('opensource-chat-styles', __DIR__.'/../resources/dist/opensource-chat.css'),
            Js::make('opensource-chat-scripts', __DIR__.'/../resources/dist/opensource-chat.js'),
        ], package: 'filaforge/opensource-chat');
    }
}
