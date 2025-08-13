<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-filament::icon icon="heroicon-o-shield-check" class="h-5 w-5 text-gray-500 dark:text-gray-400" />
                System Health Monitor
            </div>
        </x-slot>

        <x-slot name="description">
            Comprehensive system status and health checks
        </x-slot>

        <div wire:poll.30s class="space-y-4">
            @php
                $healthyCount = collect($this->getViewData()['systemChecks'])->where('status.status', 'healthy')->count();
                $totalCount = count($this->getViewData()['systemChecks']);
                $overallHealth = $healthyCount === $totalCount ? 'healthy' : ($healthyCount > $totalCount / 2 ? 'warning' : 'error');
            @endphp

            <!-- Overall Status Header -->
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        @if ($overallHealth === 'healthy')
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/20">
                                <x-filament::icon icon="heroicon-m-check-circle" class="h-5 w-5 text-green-600 dark:text-green-400" />
                            </div>
                            <div>
                                <h3 class="font-semibold text-green-800 dark:text-green-200">System Healthy</h3>
                                <p class="text-sm text-green-600 dark:text-green-400">All systems operational</p>
                            </div>
                        @elseif ($overallHealth === 'warning')
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-yellow-100 dark:bg-yellow-900/20">
                                <x-filament::icon icon="heroicon-m-exclamation-triangle" class="h-5 w-5 text-yellow-600 dark:text-yellow-400" />
                            </div>
                            <div>
                                <h3 class="font-semibold text-yellow-800 dark:text-yellow-200">Minor Issues</h3>
                                <p class="text-sm text-yellow-600 dark:text-yellow-400">Some systems need attention</p>
                            </div>
                        @else
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/20">
                                <x-filament::icon icon="heroicon-m-x-circle" class="h-5 w-5 text-red-600 dark:text-red-400" />
                            </div>
                            <div>
                                <h3 class="font-semibold text-red-800 dark:text-red-200">Critical Issues</h3>
                                <p class="text-sm text-red-600 dark:text-red-400">Immediate attention required</p>
                            </div>
                        @endif
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $healthyCount }}/{{ $totalCount }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Systems Healthy</div>
                    </div>
                </div>
            </div>

            <!-- System Checks Grid -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($this->getViewData()['systemChecks'] as $check)
                    @php
                        $status = $check['status'];
                        $isHealthy = $status['status'] === 'healthy';
                        $isWarning = $status['status'] === 'warning';
                        $isError = $status['status'] === 'error';
                    @endphp

                    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm transition-all hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                @if ($isHealthy)
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/20">
                                        <x-dynamic-component :component="$check['icon']" class="h-4 w-4 text-green-600 dark:text-green-400" />
                                    </div>
                                @elseif ($isWarning)
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-yellow-100 dark:bg-yellow-900/20">
                                        <x-dynamic-component :component="$check['icon']" class="h-4 w-4 text-yellow-600 dark:text-yellow-400" />
                                    </div>
                                @else
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/20">
                                        <x-dynamic-component :component="$check['icon']" class="h-4 w-4 text-red-600 dark:text-red-400" />
                                    </div>
                                @endif

                                <div class="min-w-0 flex-1">
                                    <h4 class="font-medium text-gray-900 dark:text-white">{{ $check['name'] }}</h4>
                                    <p class="truncate text-sm text-gray-500 dark:text-gray-400">{{ $status['message'] }}</p>
                                    @if (isset($status['details']))
                                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ $status['details'] }}</p>
                                    @endif
                                </div>
                            </div>

                            <div class="ml-2 flex-shrink-0">
                                @if ($isHealthy)
                                    <x-filament::icon icon="heroicon-m-check-circle" class="h-5 w-5 text-green-500" />
                                @elseif ($isWarning)
                                    <x-filament::icon icon="heroicon-m-exclamation-triangle" class="h-5 w-5 text-yellow-500" />
                                @else
                                    <x-filament::icon icon="heroicon-m-x-circle" class="h-5 w-5 text-red-500" />
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Last Updated -->
            <div class="text-center">
                <p class="text-xs text-gray-400 dark:text-gray-500">
                    Last updated: {{ now()->format('M j, Y g:i A') }}
                </p>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
                            $healthClasses = $healthStatusClasses[$overallHealth];
                        @endphp
                        <div class="h-2 w-2 rounded-full animate-pulse {{ $healthClasses['dot'] }}"></div>
                        <span class="text-sm font-medium {{ $healthClasses['text'] }}">
                            {{ $healthyCount }}/{{ $totalCount }} Systems Healthy
                        </span>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($this->getViewData()['systemStatus'] as $check)
                        @php
                            $statusColors = [
                                'healthy' => [
                                    'bg' => 'bg-green-50 dark:bg-green-900/20',
                                    'border' => 'border-green-200 dark:border-green-800',
                                    'icon' => 'text-green-500',
                                    'text' => 'text-green-700 dark:text-green-300',
                                    'badge' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200'
                                ],
                                'warning' => [
                                    'bg' => 'bg-yellow-50 dark:bg-yellow-900/20',
                                    'border' => 'border-yellow-200 dark:border-yellow-800',
                                    'icon' => 'text-yellow-500',
                                    'text' => 'text-yellow-700 dark:text-yellow-300',
                                    'badge' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-200'
                                ],
                                'error' => [
                                    'bg' => 'bg-red-50 dark:bg-red-900/20',
                                    'border' => 'border-red-200 dark:border-red-800',
                                    'icon' => 'text-red-500',
                                    'text' => 'text-red-700 dark:text-red-300',
                                    'badge' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-200'
                                ]
                            ];
                            $colors = $statusColors[$check['status']['status']] ?? $statusColors['error'];
                        @endphp

                        <div class="relative overflow-hidden rounded-lg border {{ $colors['border'] }} {{ $colors['bg'] }} p-4 transition-all duration-200 hover:shadow-md">
                            <!-- Status indicator -->
                            <div class="absolute right-3 top-3">
                                @if($check['status']['status'] === 'healthy')
                                    <svg class="h-5 w-5 {{ $colors['icon'] }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                @elseif($check['status']['status'] === 'warning')
                                    <svg class="h-5 w-5 {{ $colors['icon'] }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                @else
                                    <svg class="h-5 w-5 {{ $colors['icon'] }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                            </div>

                            <!-- Content -->
                            <div class="space-y-2">
                                <div class="flex items-center gap-2">
                                    <x-filament::icon
                                        :icon="$check['icon']"
                                        class="h-5 w-5 {{ $colors['icon'] }}"
                                    />
                                    <h4 class="font-medium {{ $colors['text'] }}">{{ $check['name'] }}</h4>
                                </div>

                                <div class="space-y-1">
                                    <p class="text-sm font-medium {{ $colors['text'] }}">
                                        {{ $check['status']['message'] }}
                                    </p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">
                                        {{ $check['status']['details'] }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-500">
                                        {{ $check['description'] }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Quick System Info -->
            <div class="grid gap-4 sm:grid-cols-3">
                @foreach($this->getViewData()['systemInfo'] as $info)
                    <div class="rounded-lg border border-gray-950/5 bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:border-white/10 dark:bg-gray-900 dark:ring-white/10">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ $info['property'] }}</p>
                                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $info['value'] }}</p>
                            </div>
                            <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900/20 dark:text-blue-200">
                                {{ $info['badge'] }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- System Statistics -->
            <div class="rounded-xl border border-gray-950/5 bg-gradient-to-r from-blue-50 to-indigo-50 p-6 shadow-sm ring-1 ring-gray-950/5 dark:border-white/10 dark:from-blue-900/20 dark:to-indigo-900/20 dark:ring-white/10">
                <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Runtime Statistics</h3>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                            {{ round(memory_get_peak_usage(true) / 1024 / 1024, 1) }} MB
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Peak Memory</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                            {{ count(get_loaded_extensions()) }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">PHP Extensions</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">
                            {{ ini_get('max_execution_time') ?: '∞' }}s
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Max Execution</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                            {{ ini_get('memory_limit') }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Memory Limit</div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-between border-t border-gray-200 pt-4 text-xs text-gray-500 dark:border-gray-700 dark:text-gray-400">
                <span>Last checked: {{ now()->format('M j, Y H:i:s') }}</span>
                <div class="flex items-center gap-1">
                    <div class="h-2 w-2 rounded-full bg-green-500 animate-pulse"></div>
                    <span>Auto-refresh every 30s</span>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
