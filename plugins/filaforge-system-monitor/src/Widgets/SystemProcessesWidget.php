<?php

namespace Filaforge\SystemMonitor\Widgets;

use Filament\Widgets\Widget;
use Filaforge\SystemMonitor\Services\SystemMetricsProvider;

class SystemProcessesWidget extends Widget
{
    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

    protected string $view = 'system-monitor::widgets.system-processes';

    public function getViewData(): array
    {
        $provider = app(SystemMetricsProvider::class);
        $metrics = $provider->collect();

        // Transform processes into proper format
        $processes = collect($metrics['processes'] ?? [])->map(function ($process) {
            $cols = preg_split('/\s+/', trim($process), 4);

            if (count($cols) === 4) {
                return [
                    'pid' => $cols[0],
                    'cpu' => (float) $cols[1],
                    'memory' => (float) $cols[2],
                    'command' => $cols[3],
                    'cpu_color' => $this->getCpuColor((float) $cols[1]),
                    'memory_color' => $this->getMemoryColor((float) $cols[2]),
                ];
            }

            return null;
        })->filter()->values()->take(10);

        return [
            'processes' => $processes,
        ];
    }

    protected function getCpuColor(float $cpu): string
    {
        if ($cpu > 50) return 'danger';
        if ($cpu > 20) return 'warning';
        return 'success';
    }

    protected function getMemoryColor(float $memory): string
    {
        if ($memory > 10) return 'danger';
        if ($memory > 5) return 'warning';
        return 'success';
    }

    public static function canView(): bool
    {
        return auth()->check();
    }
}
