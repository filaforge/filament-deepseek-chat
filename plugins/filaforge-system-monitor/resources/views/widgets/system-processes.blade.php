<x-filament-widgets::widget>
    <x-filament::section
        heading="Top Processes"
        description="Processes with highest CPU usage"
    >
        <div wire:poll.10s class="overflow-hidden">
            @if(count($this->getViewData()['processes']) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                    PID
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                    CPU %
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                    Memory %
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                    Command
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                            @foreach($this->getViewData()['processes'] as $process)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900 dark:text-white">
                                        {{ $process['pid'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono">
                                        @php
                                            $cpuColorClasses = [
                                                'danger' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                                'warning' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                                'success' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                            ];
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $cpuColorClasses[$process['cpu_color']] ?? $cpuColorClasses['success'] }}">
                                            {{ number_format($process['cpu'], 1) }}%
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono">
                                        @php
                                            $memoryColorClasses = [
                                                'danger' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                                'warning' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                                'success' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                            ];
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $memoryColorClasses[$process['memory_color']] ?? $memoryColorClasses['success'] }}">
                                            {{ number_format($process['memory'], 1) }}%
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-mono text-gray-900 dark:text-white">
                                        <div class="max-w-xs truncate" title="{{ $process['command'] }}">
                                            {{ $process['command'] }}
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8">
                    <x-filament::icon
                        icon="heroicon-o-cpu-chip"
                        class="mx-auto h-12 w-12 text-gray-400"
                    />
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No processes</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Process monitoring is disabled or not available.</p>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
