<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">Wirechat</x-slot>

        <div
            x-load-css="[@js(\Filament\Support\Facades\FilamentAsset::getStyleHref('filaforge-wirechat', package: 'filaforge/wirechat'))]"
            class="ff-wirechat"
        >
            <div class="wc-container">
                <div class="wc-header">
                    <div class="flex items-center gap-2">
                        <x-filament::icon icon="heroicon-o-chat-bubble-left-right" class="h-5 w-5 text-primary-600" />
                        <span class="font-medium">Wirechat</span>
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Conversations</div>
                </div>
                <div class="wc-list">
                    @livewire('wirechat.chats')
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-panels::page>


