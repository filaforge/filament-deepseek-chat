<?php

namespace Filaforge\Wirechat\Providers;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class WirechatServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filaforge-wirechat';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile('wirechat')
            ->hasViews('filaforge-wirechat')
            ->hasTranslations()
            ->hasMigrations([
                '2024_11_01_000001_create_wirechat_conversations_table',
                '2024_11_01_000002_create_wirechat_attachments_table',
                '2024_11_01_000003_create_wirechat_messages_table',
                '2024_11_01_000004_create_wirechat_participants_table',
                '2024_11_01_000006_create_wirechat_actions_table',
                '2024_11_01_000007_create_wirechat_groups_table',
            ]);
    }

    public function packageBooted(): void
    {
        FilamentAsset::register([
            Css::make('filaforge-wirechat', __DIR__ . '/../../resources/dist/wirechat.css')->loadedOnRequest(),
        ], 'filaforge/wirechat');

        // Load vendorized upstream structures and our overrides
        // Ensure our override takes precedence for the 'wirechat' namespace
        $this->loadViewsFrom([
            __DIR__.'/../../resources/views/override',
            __DIR__.'/../../upstream/resources/views',
        ], 'wirechat');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'filaforge-wirechat');
        $this->loadTranslationsFrom(__DIR__.'/../../upstream/lang', 'wirechat');
        if (file_exists(__DIR__.'/../../upstream/routes/channels.php')) {
            $this->loadRoutesFrom(__DIR__.'/../../upstream/routes/channels.php');
        }

        // Register upstream service provider to wire Livewire aliases, routes, directives
        if (class_exists(\Namu\WireChat\WireChatServiceProvider::class)) {
            $this->app->register(\Namu\WireChat\WireChatServiceProvider::class);
        } else {
            // Fallback: minimally register core Livewire aliases if upstream provider class cannot be autoloaded
            if (class_exists(\Namu\WireChat\Livewire\Pages\Chats::class)) {
                Livewire::component('wirechat.pages.index', \Namu\WireChat\Livewire\Pages\Chats::class);
            }
            if (class_exists(\Namu\WireChat\Livewire\Chats\Chats::class)) {
                Livewire::component('wirechat.chats', \Namu\WireChat\Livewire\Chats\Chats::class);
            }
        }

        // Ensure key aliases are present regardless of provider registration order
        if (! Livewire::isDiscoverable('wirechat.chats') && class_exists(\Namu\WireChat\Livewire\Chats\Chats::class)) {
            Livewire::component('wirechat.chats', \Namu\WireChat\Livewire\Chats\Chats::class);
        }
        if (! Livewire::isDiscoverable('wirechat.pages.index') && class_exists(\Namu\WireChat\Livewire\Pages\Chats::class)) {
            Livewire::component('wirechat.pages.index', \Namu\WireChat\Livewire\Pages\Chats::class);
        }
    }
}


