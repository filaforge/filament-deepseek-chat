<?php

namespace Filaforge\SystemMonitor\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filaforge\SystemMonitor\Services\SystemMetricsProvider;

class SystemMonitorWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        $provider = app(SystemMetricsProvider::class);
        $metrics = $provider->collect();

        $stats = [
            Stat::make('CPU Load', number_format($metrics['cpu_load'] ?? 0, 2))
                ->description('Current system load average')
                ->descriptionIcon('heroicon-m-cpu-chip')
                ->color($this->getCpuColor($metrics['cpu_load'] ?? 0))
                ->chart([
                    min(($metrics['cpu_load'] ?? 0) * 50, 100), // Scale for visualization
                    max(100 - (($metrics['cpu_load'] ?? 0) * 50), 0)
                ]),
        ];

        // Memory Usage
        if (isset($metrics['memory']['used'], $metrics['memory']['total']) && $metrics['memory']['total'] > 0) {
            $memPercent = ($metrics['memory']['used'] / $metrics['memory']['total']) * 100;
            $stats[] = Stat::make('Memory Usage', number_format($memPercent, 1) . '%')
                ->description("{$metrics['memory']['used']} MB / {$metrics['memory']['total']} MB")
                ->descriptionIcon('heroicon-m-bolt')
                ->color($this->getMemoryColor($memPercent))
                ->chart([
                    $memPercent,
                    100 - $memPercent
                ]);
        }

        // Disk Usage
        if (isset($metrics['disk']['used'], $metrics['disk']['total']) && $metrics['disk']['total'] > 0) {
            $diskPercent = ($metrics['disk']['used'] / $metrics['disk']['total']) * 100;
            $stats[] = Stat::make('Disk Usage', number_format($diskPercent, 1) . '%')
                ->description("{$metrics['disk']['used']} GB / {$metrics['disk']['total']} GB")
                ->descriptionIcon('heroicon-m-circle-stack')
                ->color($this->getDiskColor($diskPercent))
                ->chart([
                    $diskPercent,
                    100 - $diskPercent
                ]);
        }

        return $stats;
    }

    protected function getCpuColor(float $load): string
    {
        if ($load > 2.0) return 'danger';
        if ($load > 1.0) return 'warning';
        return 'success';
    }

    protected function getMemoryColor(float $percent): string
    {
        if ($percent > 80) return 'danger';
        if ($percent > 60) return 'warning';
        return 'success';
    }

    protected function getDiskColor(float $percent): string
    {
        if ($percent > 90) return 'danger';
        if ($percent > 75) return 'warning';
        return 'success';
    }

    public static function canView(): bool
    {
        return auth()->check();
    }
}
