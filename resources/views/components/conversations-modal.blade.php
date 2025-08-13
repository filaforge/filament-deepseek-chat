<div class="space-y-4" x-data="{ 
    q: '', 
    editingId: null, 
    newTitle: '',
    chats: @js($this->conversationList),
    activeId: @js($this->conversationId),
    isAdmin: @js($this->canViewAllChats),
    adminMode: true,
    allChats: @js($this->allConversationList),
    isVisible(chat) {
        const t = (chat?.title ?? '').toString().toLowerCase();
        const q = (this.q ?? '').toString().toLowerCase();
        return !q || t.includes(q);
    }
}">
    <!-- Header: title, search, New Chat -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Your Chats</h3>
        <div class="flex items-center gap-3 w-full sm:w-auto">
            <div class="relative flex-1 sm:w-64">
                <x-filament::input
                    x-model="q"
                    placeholder="Search chats..."
                    prefix-icon="heroicon-o-magnifying-glass"
                />
            </div>
            <div class="flex items-center gap-2">
                <x-filament::button
                    size="sm"
                    :color="'gray'"
                    x-bind:class="adminMode ? 'fi-color-primary' : ''"
                    @click="adminMode = !adminMode"
                    icon="heroicon-o-table-cells"
                >
                    <span x-text="adminMode ? 'My Chats' : (isAdmin ? 'All Chats' : 'Table View')"></span>
                </x-filament::button>
            </div>
            <x-filament::button color="primary" icon="heroicon-o-plus" wire:click="newConversation">New Chat</x-filament::button>
        </div>
    </div>

    <div x-show="!adminMode" class="deepseek-conv-list divide-y divide-gray-100 dark:divide-gray-800">
        @forelse($this->conversationList as $c)
            <div
                class="group conv-row cursor-pointer focus:outline-none"
                :class="{ 'active': activeId === {{ $c['id'] }} }"
                x-show="isVisible(chats[{{ $loop->index }}])"
                @click="editingId === {{ $c['id'] }} ? null : $wire.openConversation({{ $c['id'] }})"
                role="button"
                wire:key="conv-row-{{ $c['id'] }}"
            >
                <div class="flex items-center gap-3">
                    <!-- Title or inline editor -->
                    <div class="flex-1 min-w-0">
                        <div x-show="editingId !== {{ $c['id'] }}">
                            <div class="truncate text-sm font-medium text-gray-950 dark:text-gray-100">{{ $c['title'] }}</div>
                            <div class="mt-0.5 text-xs text-gray-600 dark:text-gray-400">Updated {{ \Carbon\Carbon::parse($c['updated_at'])->diffForHumans() }}</div>
                        </div>
                        <div x-show="editingId === {{ $c['id'] }}" wire:key="conv-edit-{{ $c['id'] }}" class="flex items-center gap-2" x-effect="if (editingId === {{ $c['id'] }}) { $refs.renameInput && $refs.renameInput.focus() }">
                            <x-filament::input x-model="newTitle" x-ref="renameInput" class="flex-1" placeholder="Rename chat" />
                            <x-filament::button size="sm" color="primary" @click.stop="$wire.renameConversation({{ $c['id'] }}, newTitle); editingId = null">Save</x-filament::button>
                            <x-filament::button size="sm" color="gray" @click.stop="editingId = null">Cancel</x-filament::button>
                        </div>
                    </div>

                    <!-- Row actions -->
                    <div class="hidden sm:flex items-center gap-2 conv-actions">
                        <x-filament::button size="xs" color="gray" icon="heroicon-o-pencil" @click.stop="editingId = {{ $c['id'] }}; newTitle = chats[{{ $loop->index }}].title">Rename</x-filament::button>
                        <x-filament::link color="danger" wire:click="deleteConversation({{ $c['id'] }})" tag="button" size="xs" @click.stop.prevent="null">Delete</x-filament::link>
                    </div>
                </div>
            </div>
        @empty
            <div class="px-4 py-6 text-sm text-gray-500">No chats yet.</div>
        @endforelse
    </div>

    <!-- Admin table: All chats -->
    <div x-show="adminMode" x-cloak class="rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden bg-white dark:bg-gray-900">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-800/40">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Title</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">User</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Updated</th>
                        <th class="px-4 py-2 text-right text-xs font-semibold text-gray-600 dark:text-gray-300">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @if (!empty($this->allConversationList))
                        @foreach($this->allConversationList as $ac)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40" wire:key="admin-row-{{ $ac['id'] }}">
                                <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100 truncate max-w-[22rem]">{{ $ac['title'] }}</td>
                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">{{ $ac['user_name'] }}</td>
                                <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400">{{ \Carbon\Carbon::parse($ac['updated_at'])->diffForHumans() }}</td>
                                <td class="px-4 py-2">
                                    <div class="flex justify-end gap-2">
                                        <x-filament::button size="xs" color="gray" icon="heroicon-o-eye" wire:click="openConversation({{ $ac['id'] }})">Open</x-filament::button>
                                        <x-filament::button size="xs" color="danger" icon="heroicon-o-trash" wire:click="adminDeleteConversation({{ $ac['id'] }})" x-show="isAdmin">Delete</x-filament::button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        @foreach($this->conversationList as $mc)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40" wire:key="mine-row-{{ $mc['id'] }}">
                                <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100 truncate max-w-[22rem]">{{ $mc['title'] }}</td>
                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">{{ auth()->user()->name ?? 'You' }}</td>
                                <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400">{{ \Carbon\Carbon::parse($mc['updated_at'])->diffForHumans() }}</td>
                                <td class="px-4 py-2">
                                    <div class="flex justify-end gap-2">
                                        <x-filament::button size="xs" color="gray" icon="heroicon-o-eye" wire:click="openConversation({{ $mc['id'] }})">Open</x-filament::button>
                                        <x-filament::button size="xs" color="danger" icon="heroicon-o-trash" wire:click="deleteConversation({{ $mc['id'] }})">Delete</x-filament::button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        @if (empty($this->conversationList))
                            <tr><td colspan="4" class="px-4 py-6 text-sm text-gray-500">No chats found.</td></tr>
                        @endif
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
