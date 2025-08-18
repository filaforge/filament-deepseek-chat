<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">API Configuration</x-slot>
            <x-slot name="description">Configure an optional API key for the Open Source Chat plugin</x-slot>

            <form wire:submit.prevent="save" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 max-w-lg">
                    <div>
                        <x-filament::input.wrapper>
                            <x-filament::input
                                type="password"
                                wire:model.defer="apiKey"
                                placeholder="Optional API Key"
                                id="apiKey"
                            />
                        </x-filament::input.wrapper>
                    </div>
                </div>

                <div class="flex justify-end">
                    <x-filament::button type="submit" size="sm" icon="heroicon-o-key">
                        Save API Key
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>
    </div>
</x-filament-panels::page>
