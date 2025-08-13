@php($errors ??= new \Illuminate\Support\ViewErrorBag)
<x-filament::page>
    <style>
        .fi-db-query-result { @apply rounded-lg p-6 text-sm; background: #0b1220; color: #d1fae5; overflow: auto; max-height: 70vh; }
        .fi-db-query-result pre { @apply whitespace-pre font-mono; line-height: 1.4; }
    </style>

    <div class="fi-page-content space-y-6">
        @if($queryResult)
            <x-filament::section>
                <x-slot name="heading">Query Results</x-slot>
                <div class="fi-db-query-result">
                    <pre>{{ $queryResult }}</pre>
                </div>
            </x-filament::section>
        @endif

        <x-filament::section>
            <x-slot name="heading">SQL Query</x-slot>
            <x-slot name="description">Run read-only queries against the selected connection.</x-slot>

            <div class="space-y-4">
                {{ $this->form }}
                <div class="flex items-center justify-end">
                    <x-filament::button wire:click="executeQuery" icon="heroicon-m-play" color="success">
                        Execute Query
                    </x-filament::button>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament::page>


