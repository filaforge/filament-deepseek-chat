<?php

use Illuminate\Support\Facades\Route;
use Filaforge\SystemMonitor\Http\Controllers\MetricsController;

Route::middleware(['web', 'auth'])->get('/filament-system-monitor/metrics', [MetricsController::class, 'index'])
    ->name('filament-system-monitor.metrics');
