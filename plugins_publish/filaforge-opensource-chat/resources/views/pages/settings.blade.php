<x-filament::page>
    <form wire:submit.prevent="save" class="space-y-4 max-w-lg">
        <x-filament::section heading="API Key (optional)">
            <x-filament::input.wrapper>
                <x-filament::input.label>API Key</x-filament::input.label>
                <x-filament::input type="text" wire:model.defer="apiKey" />
            </x-filament::input.wrapper>
        </x-filament::section>
        <x-filament::button type="submit">Save</x-filament::button>
    </form>
</x-filament::page>
