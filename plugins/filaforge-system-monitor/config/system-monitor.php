<?php

return [
    // Polling interval in seconds for live updates
    'refresh_interval_seconds' => env('FILAFORGE_SYSTEM_MONITOR_INTERVAL', 60),

    // Number of top CPU processes to display
    'top_processes' => 5,

    // Gate control (optional)
    'allow_roles' => ['admin'],

    // Whether to run shell commands to collect top processes
    'enable_shell_commands' => true,

    // Optional IP restrictions
    'restricted_ips' => [],
];
