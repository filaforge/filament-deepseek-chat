<?php

namespace Filaforge\ShellTerminal\Providers;

use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilaforgeShellTerminalServiceProvider extends PackageServiceProvider
{
    public static string $name = 'shell-terminal';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile('shell-terminal')
            ->hasMigrations()
            ->hasViews()
            ->hasAssets()
            ->hasTranslations();
    }

    public function packageBooted(): void
    {
        // Register CSS and JS assets (including xterm and addons)
        FilamentAsset::register([
            Css::make('shell-terminal', __DIR__ . '/../../resources/dist/shell-terminal.css'),
            // Xterm core CSS
            Css::make('xterm', __DIR__ . '/../../resources/dist/css/xterm.css'),

            // Our bundle
            Js::make('shell-terminal', __DIR__ . '/../../resources/dist/shell-terminal.js'),
            // Xterm core + addons (global builds copied in dist by build script)
            Js::make('xterm', __DIR__ . '/../../resources/dist/js/xterm.js'),
            Js::make('xterm-addon-fit', __DIR__ . '/../../resources/dist/js/xterm-addon-fit.js'),
            Js::make('xterm-addon-web-links', __DIR__ . '/../../resources/dist/js/xterm-addon-web-links.js'),
        ], 'filaforge/shell-terminal');
    }
}
