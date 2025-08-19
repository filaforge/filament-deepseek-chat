<?php

return [
    /*
    |--------------------------------------------------------------------------
    | System Monitor Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for system monitoring thresholds and alerts
    |
    */
    
    'cpu' => [
        'warning_threshold' => env('SYSTEM_MONITOR_CPU_WARNING', 70),
        'critical_threshold' => env('SYSTEM_MONITOR_CPU_CRITICAL', 90),
        'check_interval' => env('SYSTEM_MONITOR_CPU_INTERVAL', 30), // seconds
    ],
    
    'memory' => [
        'warning_threshold' => env('SYSTEM_MONITOR_MEMORY_WARNING', 80),
        'critical_threshold' => env('SYSTEM_MONITOR_MEMORY_CRITICAL', 95),
        'check_interval' => env('SYSTEM_MONITOR_MEMORY_INTERVAL', 30),
    ],
    
    'disk' => [
        'warning_threshold' => env('SYSTEM_MONITOR_DISK_WARNING', 85),
        'critical_threshold' => env('SYSTEM_MONITOR_DISK_CRITICAL', 95),
        'check_interval' => env('SYSTEM_MONITOR_DISK_INTERVAL', 60),
    ],
    
    'notifications' => [
        'enabled' => env('SYSTEM_MONITOR_NOTIFICATIONS', true),
        'channels' => ['mail', 'database'],
        'cooldown' => env('SYSTEM_MONITOR_COOLDOWN', 300), // 5 minutes
    ],
];
