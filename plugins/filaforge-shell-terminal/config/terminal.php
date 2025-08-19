<?php
// This file is published as 'config/shell-terminal.php'

return [
    /*
    |--------------------------------------------------------------------------
    | Terminal Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration options for the Shell Terminal plugin.
    | You can customize these settings to match your security and usage requirements.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | General Settings
    |--------------------------------------------------------------------------
    */

    // Enable or disable the terminal
    'enabled' => env('SHELL_TERMINAL_ENABLED', true),

    // Rate limiting for commands (commands per minute per user)
    'rate_limit' => env('SHELL_TERMINAL_RATE_LIMIT', 60),

    // Command execution timeout in seconds
    'command_timeout' => env('SHELL_TERMINAL_TIMEOUT', 300),

    // Maximum number of commands to keep in history
    'max_history' => env('SHELL_TERMINAL_MAX_HISTORY', 100),

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    */

    // Log all executed commands for audit purposes
    'log_commands' => env('SHELL_TERMINAL_LOG_COMMANDS', false),

    // Require confirmation for potentially dangerous commands
    'require_confirmation' => env('SHELL_TERMINAL_REQUIRE_CONFIRMATION', true),

    // Commands that are explicitly disallowed
    'disallowed_commands' => [
        'rm -rf /',
        'dd if=/dev/zero of=/dev/sda',
        'mkfs.ext4 /dev/sda',
        'fdisk /dev/sda',
        'mount',
        'umount',
        'chmod 777',
        'chown root',
        'passwd',
        'useradd',
        'userdel',
        'groupadd',
        'groupdel',
        'visudo',
        'crontab',
        'systemctl',
        'service',
        'init',
        'telinit',
        'shutdown',
        'reboot',
        'halt',
        'poweroff',
        'mkfs',
        'fdisk',
        'parted',
        'cfdisk',
        'sfdisk',
        'gdisk',
        'sgdisk',
        'wipefs',
        'blkdiscard',
        'hdparm',
        'smartctl',
        'badblocks',
        'e2fsck',
        'fsck',
        'tune2fs',
        'resize2fs',
        'debugfs',
        'dumpe2fs',
        'tune2fs',
        'e2label',
        'e2image',
        'e2undo',
        'e2freefrag',
        'filefrag',
        'hdparm',
        'smartctl',
        'badblocks',
        'e2fsck',
        'fsck',
        'tune2fs',
        'resize2fs',
        'debugfs',
        'dumpe2fs',
        'tune2fs',
        'e2label',
        'e2image',
        'e2undo',
        'e2freefrag',
        'filefrag',
    ],

    // Directories where commands are allowed to be executed
    'allowed_directories' => [
        base_path(),
        storage_path(),
        public_path(),
    ],

    /*
    |--------------------------------------------------------------------------
    | Display Settings
    |--------------------------------------------------------------------------
    */

    // Show welcome message when terminal starts
    'show_welcome_message' => env('SHELL_TERMINAL_SHOW_WELCOME', true),

    // Enable tab completion for commands and files
    'enable_tab_completion' => env('SHELL_TERMINAL_TAB_COMPLETION', true),

    // Enable command history navigation
    'enable_command_history' => env('SHELL_TERMINAL_HISTORY', true),

    // Terminal height in viewport height units (vh)
    'terminal_height' => env('SHELL_TERMINAL_HEIGHT', 60),

    // Use dark theme for terminal
    'dark_mode' => env('SHELL_TERMINAL_DARK_MODE', true),

    /*
    |--------------------------------------------------------------------------
    | Preset Commands
    |--------------------------------------------------------------------------
    |
    | Predefined commands that users can quickly access.
    | These are organized by category for easy navigation.
    |
    */

    'presets' => [
        'Laravel' => [
            'php artisan migrate' => 'Run database migrations',
            'php artisan migrate:status' => 'Check migration status',
            'php artisan migrate:rollback' => 'Rollback last migration',
            'php artisan migrate:refresh' => 'Refresh all migrations',
            'php artisan migrate:fresh' => 'Fresh migration with seed',
            'php artisan migrate:reset' => 'Reset all migrations',
            'php artisan db:seed' => 'Seed the database',
            'php artisan route:list' => 'List all routes',
            'php artisan config:clear' => 'Clear config cache',
            'php artisan cache:clear' => 'Clear application cache',
            'php artisan view:clear' => 'Clear view cache',
            'php artisan route:clear' => 'Clear route cache',
            'php artisan optimize:clear' => 'Clear all caches',
            'php artisan queue:work' => 'Start queue worker',
            'php artisan queue:restart' => 'Restart queue workers',
            'php artisan schedule:run' => 'Run scheduled commands',
            'php artisan tinker' => 'Start Tinker REPL',
            'php artisan serve' => 'Start development server',
            'php artisan test' => 'Run tests',
            'php artisan test --coverage' => 'Run tests with coverage',
        ],

        'Composer' => [
            'composer install' => 'Install dependencies',
            'composer update' => 'Update dependencies',
            'composer dump-autoload' => 'Regenerate autoload files',
            'composer clear-cache' => 'Clear Composer cache',
            'composer outdated' => 'Show outdated packages',
            'composer audit' => 'Security audit',
            'composer validate' => 'Validate composer.json',
            'composer diagnose' => 'Diagnose issues',
            'composer show' => 'Show package information',
            'composer list' => 'List available commands',
        ],

        'Git' => [
            'git status' => 'Check repository status',
            'git add .' => 'Stage all changes',
            'git commit -m "Update"' => 'Commit changes',
            'git push' => 'Push to remote',
            'git pull' => 'Pull from remote',
            'git branch' => 'List branches',
            'git checkout -b feature/new-feature' => 'Create new branch',
            'git merge main' => 'Merge main branch',
            'git log --oneline' => 'Show commit history',
            'git diff' => 'Show unstaged changes',
            'git stash' => 'Stash changes',
            'git stash pop' => 'Apply stashed changes',
        ],

        'System' => [
            'ls -la' => 'List files with details',
            'pwd' => 'Show current directory',
            'cd ..' => 'Go to parent directory',
            'cat .env' => 'View environment file',
            'tail -f storage/logs/laravel.log' => 'Monitor Laravel logs',
            'du -sh *' => 'Show directory sizes',
            'df -h' => 'Show disk usage',
            'free -h' => 'Show memory usage',
            'ps aux | grep php' => 'Show PHP processes',
            'top' => 'Show system processes',
        ],

        'Database' => [
            'php artisan db:show' => 'Show database configuration',
            'php artisan db:monitor' => 'Monitor database',
            'php artisan db:table users' => 'Show users table structure',
            'php artisan db:seed --class=UserSeeder' => 'Seed specific seeder',
            'php artisan migrate:make create_users_table' => 'Create migration',
            'php artisan make:model User' => 'Create model',
            'php artisan make:controller UserController' => 'Create controller',
            'php artisan make:resource UserResource' => 'Create API resource',
        ],

        'Optimization' => [
            'php artisan optimize' => 'Optimize for production',
            'php artisan config:cache' => 'Cache configuration',
            'php artisan route:cache' => 'Cache routes',
            'php artisan view:cache' => 'Cache views',
            'php artisan event:cache' => 'Cache events',
            'composer install --optimize-autoloader --no-dev' => 'Optimize autoloader',
            'npm run build' => 'Build frontend assets',
            'npm run dev' => 'Build frontend assets (dev)',
        ],

        'Maintenance' => [
            'php artisan down' => 'Put application in maintenance mode',
            'php artisan up' => 'Bring application out of maintenance mode',
            'php artisan down --secret="1630542a-246b-4b66-afa1-dd72a4c43515"' => 'Maintenance mode with secret',
            'php artisan queue:failed' => 'Show failed queue jobs',
            'php artisan queue:retry all' => 'Retry all failed jobs',
            'php artisan queue:flush' => 'Flush all failed jobs',
            'php artisan schedule:list' => 'List scheduled commands',
            'php artisan schedule:test' => 'Test scheduled commands',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    */

    // Log channel for terminal commands
    'log_channel' => env('SHELL_TERMINAL_LOG_CHANNEL', 'daily'),

    // Log level for terminal commands
    'log_level' => env('SHELL_TERMINAL_LOG_LEVEL', 'info'),

    // Include command output in logs
    'log_output' => env('SHELL_TERMINAL_LOG_OUTPUT', false),

    // Maximum output length to log
    'max_log_output_length' => env('SHELL_TERMINAL_MAX_LOG_OUTPUT', 1000),

    /*
    |--------------------------------------------------------------------------
    | Performance Settings
    |--------------------------------------------------------------------------
    */

    // Maximum output buffer size
    'max_output_buffer' => env('SHELL_TERMINAL_MAX_BUFFER', 10000),

    // Command execution memory limit
    'memory_limit' => env('SHELL_TERMINAL_MEMORY_LIMIT', '256M'),

    // Enable output streaming for long-running commands
    'stream_output' => env('SHELL_TERMINAL_STREAM_OUTPUT', true),

    /*
    |--------------------------------------------------------------------------
    | User Interface Settings
    |--------------------------------------------------------------------------
    */

    // Show command suggestions
    'show_suggestions' => env('SHELL_TERMINAL_SUGGESTIONS', true),

    // Enable command auto-completion
    'auto_completion' => env('SHELL_TERMINAL_AUTO_COMPLETION', true),

    // Show command history in sidebar
    'show_history_sidebar' => env('SHELL_TERMINAL_HISTORY_SIDEBAR', true),

    // Maximum number of suggestions to show
    'max_suggestions' => env('SHELL_TERMINAL_MAX_SUGGESTIONS', 10),

    /*
    |--------------------------------------------------------------------------
    | Advanced Settings
    |--------------------------------------------------------------------------
    */

    // Custom environment variables for commands
    'environment' => [
        'PATH' => '/usr/local/bin:/usr/bin:/bin',
        'HOME' => env('HOME', '/home/forge'),
        'TERM' => 'xterm-256color',
    ],

    // Custom working directory for commands
    'working_directory' => env('SHELL_TERMINAL_WORKING_DIR', base_path()),

    // Enable command aliases
    'enable_aliases' => env('SHELL_TERMINAL_ALIASES', true),

    // Custom command aliases
    'aliases' => [
        'll' => 'ls -la',
        'la' => 'ls -A',
        'l' => 'ls -CF',
        '..' => 'cd ..',
        '...' => 'cd ../..',
        '....' => 'cd ../../..',
        'art' => 'php artisan',
        'tinker' => 'php artisan tinker',
        'migrate' => 'php artisan migrate',
        'seed' => 'php artisan db:seed',
    ],
];
