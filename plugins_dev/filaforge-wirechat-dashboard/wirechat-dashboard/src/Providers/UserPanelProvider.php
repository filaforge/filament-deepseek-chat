<?php

namespace Filaforge\WirechatDashboard\Providers;

use Filament\Panel;
use Filament\PanelProvider;

class UserPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('user')
            ->path('dashboard')
            ->login()
            ->registration()
            ->colors(['primary' => '#3b82f6'])
            ->discoverPages(in: __DIR__.'/../Pages', for: 'Filaforge\\WirechatDashboard\\Pages')
            ->middleware([
                'web',
                \Illuminate\Session\Middleware\StartSession::class,
                \Illuminate\View\Middleware\ShareErrorsFromSession::class,
                \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            ])
            ->authMiddleware([
                \App\Http\Middleware\Authenticate::class,
            ]);
    }
}