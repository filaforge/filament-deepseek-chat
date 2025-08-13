<?php

return [
    'refresh_interval_seconds' => env('SYSTEM_WIDGET_INTERVAL', 5),
    'top_processes' => 5,
    'allow_roles' => ['admin'],
    'enable_shell_commands' => true,
    'restricted_ips' => [],
];
