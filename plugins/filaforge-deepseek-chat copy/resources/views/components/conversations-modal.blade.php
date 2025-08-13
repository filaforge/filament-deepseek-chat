<div class="space-y-2">
    <div class="divide-y divide-gray-200 dark:divide-gray-800 border rounded-lg overflow-hidden">
        @forelse($this->conversationList as $c)
            <div class="flex items-center justify-between px-3 py-2 bg-white dark:bg-gray-900">
                <button type="button" wire:click="openConversation({{ $c['id'] }})" class="truncate text-left text-sm text-gray-800 dark:text-gray-200">
                    {{ $c['title'] }}
                </button>
                <x-filament::link color="danger" wire:click="deleteConversation({{ $c['id'] }})" tag="button" size="xs">Delete</x-filament::link>
            </div>
        @empty
            <div class="px-3 py-4 text-sm text-gray-500">No conversations yet.</div>
        @endforelse
    </div>
</div>
