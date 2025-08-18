<?php

namespace Filaforge\WirechatDashboard;

use Illuminate\Support\ServiceProvider;
use Filaforge\WirechatDashboard\Providers\UserPanelProvider;

class WirechatDashboardServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'wirechat-dashboard');
    }
}