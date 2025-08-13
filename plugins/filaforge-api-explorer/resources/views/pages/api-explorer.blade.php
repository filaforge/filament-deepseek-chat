<x-filament-panels::page>
    <div class="api-explorer fi-page-content grid grid-cols-1 xl:grid-cols-3 gap-6">
        <!-- Request Panel -->
        <div class="xl:col-span-2">
            <div class="space-y-6">
            <x-filament::section>
                <x-slot name="heading">
                    API Request
                </x-slot>

                <div class="mb-4">
                    <div class="flex gap-2 flex-wrap">
                        <x-filament::button
                            size="sm"
                            color="gray"
                            outlined
                            wire:click="loadSampleRequest('get_users')"
                        >
                            Sample: Get Users
                        </x-filament::button>

                        <x-filament::button
                            size="sm"
                            color="gray"
                            outlined
                            wire:click="loadSampleRequest('create_post')"
                        >
                            Sample: Create Post
                        </x-filament::button>

                        <x-filament::button
                            size="sm"
                            color="gray"
                            outlined
                            wire:click="loadSampleRequest('get_post')"
                        >
                            Sample: Get Post
                        </x-filament::button>
                    </div>
                </div>

                <form wire:submit="sendRequest">
                    {{ $this->form }}

                    <div class="mt-6 flex gap-3">
                        <x-filament::button
                            type="submit"
                            icon="heroicon-o-paper-airplane"
                        >
                            Send Request
                        </x-filament::button>

                        <x-filament::button
                            color="gray"
                            outlined
                            wire:click="clearResponse"
                            icon="heroicon-o-trash"
                        >
                            Clear Response
                        </x-filament::button>
                    </div>
                </form>
            </x-filament::section>

            <!-- Response Panel -->
            <x-filament::section class="space-y-6 mt-2" style="margin-top: 2rem;">
                <x-slot name="heading">
                    API Response
                </x-slot>

                <x-slot name="headerEnd">
                    <div class="flex items-center gap-2">
                        @if($statusCode > 0)
                            <span class="text-xs px-2 py-0.5 rounded border {{ $statusCode >= 200 && $statusCode < 300 ? 'text-success-600 border-success-200' : 'text-danger-600 border-danger-200' }}">
                                {{ $statusCode }}
                            </span>
                        @endif
                        @if($responseTime)
                            <span class="text-xs text-gray-500">{{ $responseTime }} ms</span>
                        @endif
                    </div>
                </x-slot>

                <div
                    x-load-css="[@js(\Filament\Support\Facades\FilamentAsset::getStyleHref('api-explorer', package: 'filaforge/api-explorer'))]"
                    class="api-explorer-response"
                    >
                    @if($response)
                        <pre class="bg-gray-50 dark:bg-gray-900 p-4 text-sm max-h-[32rem] font-mono border">{{ $response }}</pre>
                    @else
                        <div class="text-gray-500 text-center py-8">

                            <p class="text-sm">Send a request to see the response here</p>
                        </div>
                    @endif
                </div>
            </x-filament::section>
            </div>
        </div>

        <!-- Request History Panel -->
        <div>
            <div class="space-y-6">
            <x-filament::section>
                <x-slot name="heading">
                    Request History
                    @if(count($requestHistory) > 0)
                        <x-filament::button
                            size="sm"
                            color="gray"
                            outlined
                            wire:click="clearHistory"
                            class="ml-auto"
                        >
                            Clear
                        </x-filament::button>
                    @endif
                </x-slot>

                @if(count($requestHistory) > 0)
                    <div class="space-y-2">
                        @foreach(array_reverse($requestHistory) as $request)
                            <div class="p-3 bg-gray-50 dark:bg-gray-900 rounded-lg text-sm">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="font-medium text-xs {{ $request['status'] >= 200 && $request['status'] < 300 ? 'text-success-600' : 'text-danger-600' }}">
                                        {{ $request['method'] }} {{ $request['status'] }}
                                    </span>
                                    <span class="text-xs text-gray-500">{{ $request['timestamp'] }}</span>
                                </div>
                                <div class="text-xs text-gray-600 dark:text-gray-400 truncate">
                                    {{ $request['url'] }}
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ $request['time'] }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-gray-500 text-center py-4">
                        <p class="text-sm">No requests yet</p>
                    </div>
                @endif
            </x-filament::section>
            </div>
        </div>
    </div>
</x-filament-panels::page>
