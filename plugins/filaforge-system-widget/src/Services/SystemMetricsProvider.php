<?php

namespace Filaforge\SystemMonitor\Services;

use Symfony\Component\Process\Process;

class SystemMetricsProvider
{
    public function collect(): array
    {
        return [
            'cpu_load' => $this->cpuLoad(),
            'memory'   => $this->memoryUsage(),
            'disk'     => $this->diskUsage(),
            'network'  => $this->networkStats(),
            'processes'=> $this->topProcesses(),
            'php_version' => PHP_VERSION,
            'os' => PHP_OS_FAMILY . ' (' . php_uname('s') . ' ' . php_uname('r') . ')',
            'composer_version' => $this->composerVersion(),
            'timestamp'=> now()->toISOString(),
        ];

    }

    protected function composerVersion(): string
    {
        try {
            $process = new Process(['composer', '--version']);
            $process->run();
            if ($process->isSuccessful()) {
                return trim($process->getOutput());
            }
        } catch (\Throwable $e) {}
        if (function_exists('exec')) {
            @exec('composer --version 2>&1', $output);
            if (!empty($output[0])) {
                return trim($output[0]);
            }
        }
        return 'Unknown';
    }

    protected function cpuLoad(): float
    {
        $load = sys_getloadavg();
        return $load[0] ?? 0;
    }

    protected function memoryUsage(): array
    {
        if (PHP_OS_FAMILY === 'Linux' && file_exists('/proc/meminfo')) {
            $data = file_get_contents('/proc/meminfo');
            preg_match('/MemTotal:\s+(\d+)/', $data, $total);
            preg_match('/MemAvailable:\s+(\d+)/', $data, $free);

            $totalMb = (int) $total[1] / 1024;
            $freeMb  = (int) $free[1] / 1024;

            return [
                'total' => round($totalMb, 2),
                'used'  => round($totalMb - $freeMb, 2),
            ];
        }
        return [];
    }

    protected function diskUsage(): array
    {
        $total = disk_total_space('/');
        $free  = disk_free_space('/');

        return [
            'total' => round($total / 1024 / 1024 / 1024, 2),
            'used'  => round(($total - $free) / 1024 / 1024 / 1024, 2),
        ];
    }

    protected function networkStats(): array
    {
        return ['rx' => 0, 'tx' => 0];
    }

    protected function topProcesses(): array
    {
        if (!config('filaforge-system-monitor.enable_shell_commands')) {
            return [];
        }

        $process = new Process(['ps', '-eo', 'pid,pcpu,pmem,comm', '--sort=-pcpu']);
        $process->run();
        $lines = explode("\n", trim($process->getOutput()));

        return array_slice($lines, 1, config('filaforge-system-monitor.top_processes'));
    }
}
