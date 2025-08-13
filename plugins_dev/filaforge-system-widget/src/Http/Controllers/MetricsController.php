<?php

namespace Filaforge\FilamentSystemMonitor\Http\Controllers;

use Illuminate\Routing\Controller;
use Filaforge\FilamentSystemMonitor\Services\SystemMetricsProvider;

class MetricsController extends Controller
{
    public function index(SystemMetricsProvider $provider)
    {
        return response()->json($provider->collect());
    }
}
