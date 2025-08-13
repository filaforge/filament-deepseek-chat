<?php

namespace Filaforge\SystemMonitor\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SystemInfoWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $dbStatus = $this->checkDatabaseConnection();
        $cacheStatus = $this->checkCacheSystem();
        $storageStatus = $this->checkStoragePermissions();
        $envStatus = $this->checkEnvironmentConfig();
        $queueStatus = $this->checkQueueSystem();
        $logStatus = $this->checkLogFiles();

        return [
            Stat::make('Database', $dbStatus['status'] === 'healthy' ? '✓ Connected' : '✗ Error')
                ->description($dbStatus['message'])
                ->descriptionIcon($dbStatus['status'] === 'healthy' ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle')
                ->color($dbStatus['status'] === 'healthy' ? 'success' : 'danger'),

            Stat::make('Cache System', $cacheStatus['status'] === 'healthy' ? '✓ Working' : '✗ Error')
                ->description($cacheStatus['message'])
                ->descriptionIcon($cacheStatus['status'] === 'healthy' ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle')
                ->color($cacheStatus['status'] === 'healthy' ? 'success' : 'danger'),

            Stat::make('Storage', $storageStatus['status'] === 'healthy' ? '✓ Writable' : '✗ Error')
                ->description($storageStatus['message'])
                ->descriptionIcon($storageStatus['status'] === 'healthy' ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle')
                ->color($storageStatus['status'] === 'healthy' ? 'success' : 'danger'),

            Stat::make('Environment', $envStatus['status'] === 'healthy' ? '✓ Configured' : '⚠ Warning')
                ->description($envStatus['message'])
                ->descriptionIcon($envStatus['status'] === 'healthy' ? 'heroicon-m-check-circle' : 'heroicon-m-exclamation-triangle')
                ->color($envStatus['status'] === 'healthy' ? 'success' : 'warning'),

            Stat::make('Queue System', $queueStatus['status'] === 'healthy' ? '✓ Running' : ($queueStatus['status'] === 'warning' ? '⚠ Sync Mode' : '✗ Error'))
                ->description($queueStatus['message'])
                ->descriptionIcon($queueStatus['status'] === 'healthy' ? 'heroicon-m-check-circle' : ($queueStatus['status'] === 'warning' ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-x-circle'))
                ->color($queueStatus['status'] === 'healthy' ? 'success' : ($queueStatus['status'] === 'warning' ? 'warning' : 'danger')),

            Stat::make('Logs', $logStatus['status'] === 'healthy' ? '✓ Active' : '⚠ Warning')
                ->description($logStatus['message'])
                ->descriptionIcon($logStatus['status'] === 'healthy' ? 'heroicon-m-check-circle' : 'heroicon-m-exclamation-triangle')
                ->color($logStatus['status'] === 'healthy' ? 'success' : 'warning'),
        ];
    }

    protected function checkDatabaseConnection(): array
    {
        try {
            \DB::connection()->getPdo();
            return [
                'status' => 'healthy',
                'message' => 'Connected successfully',
                'details' => 'Driver: ' . config('database.default'),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Connection failed',
                'details' => 'Check database configuration',
            ];
        }
    }

    protected function checkCacheSystem(): array
    {
        try {
            $testKey = 'system_monitor_test_' . time();
            cache()->put($testKey, 'test', 60);
            $retrieved = cache()->get($testKey);
            cache()->forget($testKey);

            if ($retrieved === 'test') {
                return [
                    'status' => 'healthy',
                    'message' => 'Cache working',
                    'details' => 'Driver: ' . config('cache.default'),
                ];
            }

            return [
                'status' => 'warning',
                'message' => 'Cache not working',
                'details' => 'Test failed',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Cache error',
                'details' => 'Check cache configuration',
            ];
        }
    }

    protected function checkStoragePermissions(): array
    {
        $paths = [
            storage_path('app'),
            storage_path('logs'),
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
        ];

        $issues = [];
        foreach ($paths as $path) {
            if (!is_writable($path)) {
                $issues[] = basename($path);
            }
        }

        if (empty($issues)) {
            return [
                'status' => 'healthy',
                'message' => 'All permissions OK',
                'details' => 'Storage folders writable',
            ];
        }

        return [
            'status' => 'error',
            'message' => 'Permission issues',
            'details' => 'Issues: ' . implode(', ', $issues),
        ];
    }

    protected function checkEnvironmentConfig(): array
    {
        $requiredEnvVars = ['APP_KEY', 'APP_ENV'];

        // Only require DB_DATABASE if not using SQLite
        if (config('database.default') !== 'sqlite') {
            $requiredEnvVars[] = 'DB_DATABASE';
        }

        $missing = [];
        foreach ($requiredEnvVars as $var) {
            if (!env($var)) {
                $missing[] = $var;
            }
        }

        if (empty($missing)) {
            $env = app()->environment();
            return [
                'status' => $env === 'production' ? 'healthy' : ($env === 'local' ? 'warning' : 'healthy'),
                'message' => "Environment: {$env}",
                'details' => 'Configuration complete',
            ];
        }

        return [
            'status' => 'error',
            'message' => 'Missing config',
            'details' => 'Missing: ' . implode(', ', $missing),
        ];
    }

    protected function checkQueueSystem(): array
    {
        try {
            $driver = config('queue.default');

            if ($driver === 'sync') {
                return [
                    'status' => 'warning',
                    'message' => 'Sync driver',
                    'details' => 'Jobs run synchronously',
                ];
            }

            // Try to get queue size (works for database driver)
            if ($driver === 'database') {
                $jobsCount = \DB::table('jobs')->count();
                return [
                    'status' => 'healthy',
                    'message' => "Queue active",
                    'details' => "{$jobsCount} jobs pending",
                ];
            }

            return [
                'status' => 'healthy',
                'message' => 'Queue configured',
                'details' => "Driver: {$driver}",
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Queue error',
                'details' => 'Check queue configuration',
            ];
        }
    }

    protected function checkLogFiles(): array
    {
        $logPath = storage_path('logs/laravel.log');

        if (!file_exists($logPath)) {
            return [
                'status' => 'warning',
                'message' => 'No log file',
                'details' => 'Log file not found',
            ];
        }

        $size = filesize($logPath);
        $sizeFormatted = $this->formatBytes($size);

        if ($size > 100 * 1024 * 1024) { // 100MB
            return [
                'status' => 'warning',
                'message' => 'Large log file',
                'details' => "Size: {$sizeFormatted}",
            ];
        }

        return [
            'status' => 'healthy',
            'message' => 'Logs accessible',
            'details' => "Size: {$sizeFormatted}",
        ];
    }

    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    public static function canView(): bool
    {
        return auth()->check();
    }
}
