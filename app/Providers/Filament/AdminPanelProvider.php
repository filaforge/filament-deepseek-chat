<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Dashboard;
use App\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use LaraZeus\SpatieTranslatable\SpatieTranslatablePlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->login(Login::class)
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->unsavedChangesAlerts()
            ->brandLogo(fn () => view('filament.app.logo'))
            ->brandLogoHeight('1.25rem')
            ->navigationGroups([
                'Shop',
                'Blog',
            ])
            ->databaseNotifications()
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugin(
                SpatieTranslatablePlugin::make()
                    ->defaultLocales(['en', 'es', 'nl'])
            )
            ->plugin(\Filaforge\ApiExplorer\ApiExplorerPlugin::make())
            ->plugin(\Filaforge\DatabaseQuery\DatabaseQueryPlugin::make())
            ->plugin(\Filaforge\DatabaseViewer\DatabaseViewerPlugin::make())
            ->plugin(\Filaforge\HuggingfaceChat\Providers\HfChatPanelPlugin::make())
            ->plugin(\Filaforge\HelloWidget\HelloWidgetPlugin::make())
            ->plugin(\Filaforge\SystemMonitor\SystemMonitorPlugin::make())
            ->plugin(\Filaforge\SystemPackages\SystemPackagesPlugin::make())
            ->plugin(\Filaforge\SystemTools\SystemToolsPlugin::make())
            ->plugin(\Filaforge\TerminalConsole\TerminalConsolePlugin::make())
            ->plugin(\Filaforge\ShellTerminal\FilaforgeShellTerminalPlugin::make())
            ->plugin(\Filaforge\OpensourceChat\OpensourceChatPlugin::make())
            ->plugin(\Filaforge\Wirechat\WirechatPlugin::make())
            ->plugin(\Filaforge\UserManager\UserManagerPlugin::make())
            ->plugin(\Awcodes\QuickCreate\QuickCreatePlugin::make())
            ->plugin(\Filaforge\OllamaChat\Filament\OllamaChatPanelPlugin::make())
            ->spa();

            return $panel->colors([
                'primary' => Color::Blue,
            ]);
    }
}
