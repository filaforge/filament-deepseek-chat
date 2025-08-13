@php($errors ??= new \Illuminate\Support\ViewErrorBag)
<x-filament::page>
    <div class="fi-page-content space-y-6">
        <x-filament::section>
            <x-slot name="heading">Custom Command</x-slot>
            <x-slot name="description">Choose any allowed command and provide arguments.</x-slot>

            <div class="space-y-4">
                {{ $this->form }}

                <div class="flex items-center gap-3">
                    <x-filament::button wire:click="run" icon="heroicon-m-play">Run</x-filament::button>

                    @if($exitCode !== null)
                        <span class="text-sm text-gray-500">Exit code: {{ $exitCode }}</span>
                    @endif
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Output</x-slot>
            <div class="rounded-md bg-gray-950/90 p-4 text-sm text-gray-100 ring-1 ring-gray-900/20 dark:bg-gray-900 dark:text-gray-200">
                <pre class="whitespace-pre-wrap break-words">{{ data_get($data, 'output') }}</pre>
            </div>
        </x-filament::section>
    </div>
</x-filament::page>



