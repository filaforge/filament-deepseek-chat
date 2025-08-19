<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Quick Bookmarks</x-slot>

        <div class="space-y-4">
            <div class="flex gap-2">
                <x-filament::input.wrapper class="grow">
                    <x-filament::input
                        type="text"
                        wire:model.defer="data.label"
                        placeholder="Label (e.g., Orders)"
                    />
                </x-filament::input.wrapper>
                <x-filament::input.wrapper class="grow">
                    <x-filament::input
                        type="text"
                        wire:model.defer="data.url"
                        placeholder="URL (absolute or relative)"
                    />
                </x-filament::input.wrapper>
                <x-filament::button wire:click="add">+ Add</x-filament::button>
            </div>

            <div class="divide-y divide-gray-200 dark:divide-gray-800 rounded-lg border border-gray-200 dark:border-gray-800">
                @forelse($this->bookmarks as $bookmark)
                    <div class="flex items-center justify-between p-3">
                        <a
                            href="{{ $bookmark->url }}"
                            class="text-primary-600 hover:underline"
                        >
                            {{ $bookmark->label }}
                        </a>
                        <x-filament::button color="gray" size="xs" wire:click="delete({{ $bookmark->id }})">Delete</x-filament::button>
                    </div>
                @empty
                    <div class="p-3 text-sm text-gray-500">No bookmarks yet.</div>
                @endforelse
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>



