<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Terminal Console Configuration
    |--------------------------------------------------------------------------
    */

    // Enable/disable the terminal console
    'enabled' => env('TERMINAL_CONSOLE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    */

    // If true, any command can run EXCEPT those explicitly blocked below.
    // Keep this disabled in production and rely on allowlist when possible.
    'allow_any' => env('TERMINAL_ALLOW_ANY', false),

    // Allowed binaries when 'allow_any' is false. Keep this restrictive in production.
    'allowed_binaries' => [
        // Basic commands
        'ls', 'pwd', 'cd', 'whoami', 'echo', 'cat', 'head', 'tail', 'grep', 'find',
        
        // File operations (be careful with these in production)
        'mkdir', 'rmdir', 'cp', 'mv', 'rm', 'touch',
        
        // System info
        'df', 'du', 'ps', 'uname', 'tree', 'which', 'whereis',
        
    // Development tools
    'php', 'composer', 'npm', 'yarn', 'git', 'node', 'filament',
    // Containers & databases
    'docker', 'mysql', 'psql', 'redis-cli', 'pnpm',
        
        // Laravel Artisan
        'artisan',
    ],

    // Blocked binaries (always denied). Takes precedence over allow_any & allowed_binaries.
    'blocked_binaries' => [
        // Dangerous/destructive or privilege-escalation prone commands
        'rm', 'mkfs', 'dd', 'shutdown', 'reboot', 'poweroff', 'halt', 'killall',
        'userdel', 'iptables', 'ufw', 'pkexec', 'sudo', 'passwd', 'chpasswd',
    ],

    // Rate limiting (commands per minute per user)
    'rate_limit' => env('TERMINAL_RATE_LIMIT', 60),

    /*
    |--------------------------------------------------------------------------
    | Command Presets
    |--------------------------------------------------------------------------
    */

    'presets' => [
    'Artisan' => [
            [
                'label' => 'Clear Cache',
                'command' => 'php artisan cache:clear',
                'description' => 'Clear application cache',
                'icon' => 'heroicon-o-trash',
                'color' => 'warning',
                'auto_run' => true,
            ],
            [
                'label' => 'Queue Work',
                'command' => 'php artisan queue:work --stop-when-empty',
                'description' => 'Process queued jobs',
                'icon' => 'heroicon-o-play',
                'color' => 'success',
            ],
            [
                'label' => 'Run Migrations',
                'command' => 'php artisan migrate',
                'description' => 'Run database migrations',
                'icon' => 'heroicon-o-database',
                'color' => 'info',
            ],
            [
                'label' => 'Fresh Migrate',
                'command' => 'php artisan migrate:fresh --seed',
                'description' => 'Fresh migration with seeding',
                'icon' => 'heroicon-o-arrow-path',
                'color' => 'danger',
                'confirm' => true,
            ],
        ],
        'System' => [
            [
                'label' => 'Disk Usage',
                'command' => 'df -h',
                'description' => 'Show disk space usage',
                'icon' => 'heroicon-o-chart-pie',
                'color' => 'info',
            ],
            [
                'label' => 'Memory Usage',
                'command' => 'ps aux --sort=-%mem | head -10',
                'description' => 'Show top memory consumers',
                'icon' => 'heroicon-o-cpu-chip',
                'color' => 'warning',
            ],
            [
                'label' => 'List Processes',
                'command' => 'ps aux',
                'description' => 'List all running processes',
                'icon' => 'heroicon-o-list-bullet',
                'color' => 'gray',
            ],
            // Moved from 'Files' group
            [
                'label' => 'List Files',
                'command' => 'ls -la',
                'description' => 'List all files with details',
                'icon' => 'heroicon-o-folder',
                'color' => 'primary',
            ],
            [
                'label' => 'Tree View',
                'command' => 'tree -L 2',
                'description' => 'Show directory tree (2 levels)',
                'icon' => 'heroicon-o-folder-open',
                'color' => 'success',
            ],
            [
                'label' => 'Find Large Files',
                'command' => 'find . -type f -size +10M -exec ls -lh {} \;',
                'description' => 'Find files larger than 10MB',
                'icon' => 'heroicon-o-magnifying-glass',
                'color' => 'warning',
            ],
        ],
        'Github' => [
            [
                'label' => 'Git Status',
                'command' => 'git status',
                'description' => 'Show git repository status',
                'icon' => 'heroicon-o-information-circle',
                'color' => 'primary',
                'auto_run' => true,
            ],
            [
                'label' => 'Git Log',
                'command' => 'git log --oneline -10',
                'description' => 'Show recent commits',
                'icon' => 'heroicon-o-clock',
                'color' => 'gray',
            ],
            [
                'label' => 'Git Pull',
                'command' => 'git pull origin main',
                'description' => 'Pull latest changes',
                'icon' => 'heroicon-o-arrow-down-tray',
                'color' => 'success',
            ],
        ],
        // 'Files' group removed; its items were merged into 'System'.
    ],

    /*
    |--------------------------------------------------------------------------
    | Environment Settings
    |--------------------------------------------------------------------------
    */

    // Working directory for command execution
    'working_directory' => base_path(),

    // Command timeout in seconds
    'timeout' => env('TERMINAL_TIMEOUT', 60),

    // Keep only the last N history entries in memory
    'max_history' => env('TERMINAL_MAX_HISTORY', 100),

    /*
    |--------------------------------------------------------------------------
    | UI Settings
    |--------------------------------------------------------------------------
    */

    // Theme settings
    'theme' => [
        'font_family' => env('TERMINAL_FONT_FAMILY', 'JetBrains Mono'),
        'font_size' => env('TERMINAL_FONT_SIZE', 14),
        'background' => env('TERMINAL_BACKGROUND', 'transparent'),
        'foreground' => env('TERMINAL_FOREGROUND', '#f8f8f2'),
        'cursor' => env('TERMINAL_CURSOR', '#58a6ff'),
    ],

    // Show welcome message on terminal load
    'show_welcome' => env('TERMINAL_SHOW_WELCOME', true),

    // Custom welcome message
    'welcome_message' => env('TERMINAL_WELCOME_MESSAGE', null),

    /*
    |--------------------------------------------------------------------------
    | Logging & Monitoring
    |--------------------------------------------------------------------------
    */

    'logging' => [
        // Enable command execution logging
        'enabled' => env('TERMINAL_LOGGING_ENABLED', true),

        // Log channel (null = default)
        'channel' => env('TERMINAL_LOG_CHANNEL'),

        // Log successful commands
        'log_successful' => env('TERMINAL_LOG_SUCCESSFUL', true),

        // Log failed commands
        'log_failed' => env('TERMINAL_LOG_FAILED', true),

        // Include command output in logs
        'include_output' => env('TERMINAL_LOG_INCLUDE_OUTPUT', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Advanced Settings
    |--------------------------------------------------------------------------
    */

    // Environment variables to set for commands
    'environment_variables' => [
        // You can hard-override PATH here, but prefer TERMINAL_EXTRA_PATH to append instead.
        // 'PATH' => '/usr/local/bin:/usr/bin:/bin',
        // 'NODE_ENV' => 'production',
        // Tip: Set TERMINAL_EXTRA_PATH in .env to append to the current PATH for commands like docker/node/mysql
        // Example: TERMINAL_EXTRA_PATH="/usr/local/sbin:/usr/local/bin:/usr/bin:/bin:/snap/bin"
    ],

    // Enable tab completion
    'tab_completion' => env('TERMINAL_TAB_COMPLETION', true),

    // Enable command history
    'command_history' => env('TERMINAL_COMMAND_HISTORY', true),

    // Enable keyboard shortcuts
    'keyboard_shortcuts' => env('TERMINAL_KEYBOARD_SHORTCUTS', true),
];


