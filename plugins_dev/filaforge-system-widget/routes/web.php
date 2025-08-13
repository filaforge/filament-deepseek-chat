<?php

use Illuminate\Support\Facades\Route;
use Filaforge\SystemMonitor\Http\Controllers\MetricsController;

Route::middleware(['web', 'auth'])
    ->get('/filaforge-system-monitor/metrics', [MetricsController::class, 'index'])
    ->name('filaforge-system-monitor.metrics');
