<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">Available Models</x-slot>
            <x-slot name="description">Browse models from configured providers and create model profiles.</x-slot>

            {{ $this->table }}

        </x-filament::section>
    </div>
</x-filament-panels::page>
