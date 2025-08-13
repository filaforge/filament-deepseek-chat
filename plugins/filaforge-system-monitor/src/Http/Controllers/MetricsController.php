<?php

namespace Filaforge\SystemMonitor\Http\Controllers;

use Illuminate\Routing\Controller;
use Filaforge\SystemMonitor\Services\SystemMetricsProvider;

class MetricsController extends Controller
{
    public function index(SystemMetricsProvider $provider)
    {
        return response()->json($provider->collect());
    }
}
