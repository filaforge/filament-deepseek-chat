<?php

namespace Filaforge\SystemMonitor\Services;

use Symfony\Component\Process\Process;

class SystemMetricsProvider
{
    public function collect(): array
    {
        $metrics = [
            'php_version' => PHP_VERSION,
            'composer_version' => $this->getComposerVersion(),
            'os' => PHP_OS_FAMILY,
            'timestamp' => now()->toDateTimeString(),
        ];

        $metrics['cpu_load'] = $this->getCpuLoad();
        $metrics['memory'] = $this->getMemoryUsage();
        $metrics['disk'] = $this->getDiskUsage();
        $metrics['network'] = $this->getNetworkStats();

        if (config('filaforge-system-monitor.enable_shell_commands')) {
            $metrics['processes'] = $this->getTopProcesses((int) config('filaforge-system-monitor.top_processes'));
        }

        return $metrics;
    }

    protected function getComposerVersion(): ?string
    {
        try {
            $process = new Process(['composer', '--version']);
            $process->setTimeout(2);
            $process->run();
            if ($process->isSuccessful()) {
                return trim($process->getOutput());
            }
        } catch (\Throwable $e) {}

        return null;
    }

    protected function getCpuLoad(): ?float
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return isset($load[0]) ? (float) $load[0] : null;
        }

        return null;
    }

    protected function getMemoryUsage(): array
    {
        $total = (int) (memory_get_usage(true) / 1024 / 1024);
        $used = (int) (memory_get_usage() / 1024 / 1024);

        return [
            'total' => $total,
            'used' => $used,
        ];
    }

    protected function getDiskUsage(): array
    {
        $total = (int) (disk_total_space(base_path()) / 1024 / 1024 / 1024);
        $used = $total - (int) (disk_free_space(base_path()) / 1024 / 1024 / 1024);

        return [
            'total' => $total,
            'used' => $used,
        ];
    }

    protected function getNetworkStats(): array
    {
        return [
            'rx' => rand(100, 1000),
            'tx' => rand(100, 1000),
        ];
    }

    protected function getTopProcesses(int $limit): array
    {
        try {
            $process = new Process(['bash', '-lc', 'ps -eo pid,pcpu,pmem,comm --sort=-pcpu | head -n ' . (1 + $limit)]);
            $process->setTimeout(2);
            $process->run();
            if (! $process->isSuccessful()) {
                return [];
            }
            $lines = explode("\n", trim($process->getOutput()));
            return array_slice($lines, 1, config('filaforge-system-monitor.top_processes'));
        } catch (\Throwable $e) {
            return [];
        }
    }
}
