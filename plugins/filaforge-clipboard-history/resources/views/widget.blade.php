<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Clipboard History
        </x-slot>

        <div
            x-load
            x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('clipboard-history', 'filaforge/clipboard-history') }}"
            x-data="clipboardHistory({ max: {{ (int) config('clipboard-history.max_items', 10) }} })"
            class="space-y-3"
        >
            <div class="flex gap-2">
                <x-filament::input.wrapper class="grow">
                    <x-filament::input type="text" x-model="input" placeholder="Paste a snippet or ID and click Add" />
                </x-filament::input.wrapper>
                <x-filament::button x-on:click="add()">Add</x-filament::button>
            </div>

            <div>
                <x-filament::input.wrapper>
                    <select x-model="selected" class="fi-input block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900">
                        <template x-for="(item, idx) in history" :key="idx">
                            <option :value="item" x-text="item"></option>
                        </template>
                    </select>
                </x-filament::input.wrapper>
            </div>

            <div class="flex gap-2">
                <x-filament::button color="primary" x-on:click="copySelected()">Copy Selected</x-filament::button>
                <x-filament::button color="gray" x-on:click="clear()">Clear</x-filament::button>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>



