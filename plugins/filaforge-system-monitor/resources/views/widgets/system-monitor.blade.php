<x-filament::card>
    <div wire:poll.{{ config('filament-system-monitor.refresh_interval_seconds') }}s="updateMetrics">
        <!-- System Information Stats -->
        <div class="mb-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="flex items-center gap-3 p-4 rounded-xl bg-white dark:bg-gray-900 shadow border border-gray-100 dark:border-gray-800">
                <svg class="w-7 h-7 text-primary-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.32 6.32a2.25 2.25 0 0 1 3.18 0l8.18 8.18a2.25 2.25 0 0 1 0 3.18l-2.12 2.12a2.25 2.25 0 0 1-3.18 0L4 11.62a2.25 2.25 0 0 1 0-3.18l2.12-2.12z"/></svg>
                <div>
                    <div class="text-xs text-gray-500 font-medium">PHP Version</div>
                    <div class="font-bold text-xl text-gray-900 dark:text-white tracking-tight">{{ $this->metrics['php_version'] ?? '-' }}</div>
                </div>
            </div>
            <div class="flex items-center gap-3 p-4 rounded-xl bg-white dark:bg-gray-900 shadow border border-gray-100 dark:border-gray-800">
                <svg class="w-7 h-7 text-primary-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 4.5v15m-9-15v15m-2.25-12h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5a2.25 2.25 0 0 1-2.25 2.25h-13.5A2.25 2.25 0 0 1 3 14.25v-7.5A2.25 2.25 0 0 1 5.25 4.5z"/></svg>
                <div>
                    <div class="text-xs text-gray-500 font-medium">Composer Version</div>
                    <div class="font-bold text-xl text-gray-900 dark:text-white tracking-tight">{{ $this->metrics['composer_version'] ?? '-' }}</div>
                </div>
            </div>
            <div class="flex items-center gap-3 p-4 rounded-xl bg-white dark:bg-gray-900 shadow border border-gray-100 dark:border-gray-800">
                <svg class="w-7 h-7 text-primary-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.75h4.5a2.25 2.25 0 0 1 2.25 2.25v12a2.25 2.25 0 0 1-2.25 2.25h-4.5A2.25 2.25 0 0 1 7.5 18V6a2.25 2.25 0 0 1 2.25-2.25z"/></svg>
                <div>
                    <div class="text-xs text-gray-500 font-medium">OS</div>
                    <div class="font-bold text-xl text-gray-900 dark:text-white tracking-tight">{{ $this->metrics['os'] ?? '-' }}</div>
                </div>
            </div>
        </div>

        <!-- System Metrics Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- CPU Load -->
            <div class="rounded-xl bg-white dark:bg-gray-900 shadow border border-gray-100 dark:border-gray-800 p-4 flex flex-col gap-2 h-full">
                <div class="flex items-center gap-2 mb-1">
                    <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 3v1.5M4.5 8.25H3m18 0h-1.5M4.5 12H3m18 0h-1.5m-16.5 3.75H3m18 0h-1.5M8.25 19.5V21M12 3v1.5m0 15V21m3.75-18v1.5m0 15V21"/></svg>
                    <span class="font-semibold text-primary-600">CPU Load</span>
                </div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $this->metrics['cpu_load'] ?? '-' }}</div>
            </div>

            <!-- Memory Usage -->
            <div class="rounded-xl bg-white dark:bg-gray-900 shadow border border-gray-100 dark:border-gray-800 p-4 flex flex-col gap-2 h-full">
                <div class="flex items-center gap-2 mb-1">
                    <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
                    <span class="font-semibold text-primary-600">Memory Usage</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="font-mono">{{ $this->metrics['memory']['used'] ?? '-' }} MB</span>
                    <span class="text-gray-400">/</span>
                    <span class="font-mono">{{ $this->metrics['memory']['total'] ?? '-' }} MB</span>
                </div>
                @php
                    $memPercent = (isset($this->metrics['memory']['used'], $this->metrics['memory']['total']) && $this->metrics['memory']['total'] > 0)
                        ? ($this->metrics['memory']['used'] / $this->metrics['memory']['total']) * 100
                        : 0;
                @endphp
                <div class="w-full bg-gray-200 dark:bg-gray-800 rounded h-2 mt-1">
                    <div class="bg-primary-500 h-2 rounded transition-all duration-300" style="width: {{ $memPercent }}%"></div>
                </div>
            </div>

            <!-- Disk Usage -->
            <div class="rounded-xl bg-white dark:bg-gray-900 shadow border border-gray-100 dark:border-gray-800 p-4 flex flex-col gap-2 h-full">
                <div class="flex items-center gap-2 mb-1">
                    <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125"/></svg>
                    <span class="font-semibold text-primary-600">Disk Usage</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="font-mono">{{ $this->metrics['disk']['used'] ?? '-' }} GB</span>
                    <span class="text-gray-400">/</span>
                    <span class="font-mono">{{ $this->metrics['disk']['total'] ?? '-' }} GB</span>
                </div>
                @php
                    $diskPercent = (isset($this->metrics['disk']['used'], $this->metrics['disk']['total']) && $this->metrics['disk']['total'] > 0)
                        ? ($this->metrics['disk']['used'] / $this->metrics['disk']['total']) * 100
                        : 0;
                @endphp
                <div class="w-full bg-gray-200 dark:bg-gray-800 rounded h-2 mt-1">
                    <div class="bg-yellow-500 h-2 rounded transition-all duration-300" style="width: {{ $diskPercent }}%"></div>
                </div>
            </div>

            <!-- Network -->
            <div class="rounded-xl bg-white dark:bg-gray-900 shadow border border-gray-100 dark:border-gray-800 p-4 flex flex-col gap-2 h-full">
                <div class="flex items-center gap-2 mb-1">
                    <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                    <span class="font-semibold text-primary-600">Network</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="font-mono">RX: {{ $this->metrics['network']['rx'] ?? '-' }} KB</span>
                    <span class="font-mono">TX: {{ $this->metrics['network']['tx'] ?? '-' }} KB</span>
                </div>
            </div>
        </div>

        <!-- Top Processes Section -->
        <div class="mt-8">
            <div class="font-semibold text-primary-600 mb-2 flex items-center gap-2 text-lg">
                <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                Top Processes
            </div>
            <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 shadow">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800">
                            <th class="px-2 py-1 text-left">PID</th>
                            <th class="px-2 py-1 text-left">CPU %</th>
                            <th class="px-2 py-1 text-left">MEM %</th>
                            <th class="px-2 py-1 text-left">Command</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(($this->metrics['processes'] ?? []) as $proc)
                            @php $cols = preg_split('/\s+/', trim($proc), 4); @endphp
                            @if(count($cols) === 4)
                                <tr class="odd:bg-gray-50 even:bg-white dark:odd:bg-gray-800 dark:even:bg-gray-900">
                                    <td class="px-2 py-1 font-mono">{{ $cols[0] }}</td>
                                    <td class="px-2 py-1 font-mono">{{ $cols[1] }}</td>
                                    <td class="px-2 py-1 font-mono">{{ $cols[2] }}</td>
                                    <td class="px-2 py-1 font-mono">{{ $cols[3] }}</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Last Updated -->
        <div class="mt-4 text-xs text-gray-500 text-right">Last updated: {{ $this->metrics['timestamp'] ?? '-' }}</div>
    </div>
</x-filament::card>
