<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">DeepSeek API Settings</x-slot>

        <form wire:submit.prevent="save" class="space-y-6">
            {{ $this->form }}

            <div class="flex justify-end">
                <x-filament::button type="submit" color="primary">
                    Save Settings
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>
</x-filament-panels::page>
