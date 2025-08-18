<x-filament-panels::page class="hf-chat-page">
    <x-filament::section>

        <div class="hf-chat-container">
        @if($viewMode === 'chat')
        <!-- Messages Area -->
        <div class="hf-chat-messages hf-chat-scroll">
            <!-- Model Profile Selector -->
            @if(!empty($availableProfiles))
                <div class="mb-3 flex items-center gap-2 text-sm">
                    <label class="font-medium">Model:</label>
                    <select wire:model.live="selectedProfileId" class="fi-input rounded-md border-gray-300 dark:bg-gray-800 dark:border-gray-700 dark:text-white text-sm">
                        @foreach($availableProfiles as $p)
                            <option value="{{ $p['id'] }}">{{ $p['name'] }} ({{ $p['model_id'] }})</option>
                        @endforeach
                    </select>
                    <a href="{{ route('filament.admin.resources.model-profiles.index') }}" class="text-primary-600 dark:text-primary-400 hover:underline" target="_blank">manage</a>
                </div>
            @endif
            @if(empty($messages))
                <div wire:loading.remove wire:target="send" class="hf-empty-state">
                    <x-filament::icon icon="heroicon-o-chat-bubble-left-right" class="hf-empty-icon" />
                    <h3 class="text-lg font-medium mb-2 mt-2">Start a conversation</h3>
                    <p class="text-sm">Ask me anything...</p>
                </div>
            @else
                @foreach($messages as $index => $message)
                    @php $isUser = $message['role'] === 'user'; @endphp
                    <div class="hf-message {{ $isUser ? 'user' : 'ai' }}" x-data="{ entered: false }" x-init="setTimeout(() => entered = true, {{ $index * 100 }})" :class="{ 'entering': !entered, 'entered': entered }" wire:key="message-{{ $index }}">
                        @if($isUser)
                            <div class="hf-message-bubble user"><div class="hf-message-content user">{{ $message['content'] }}</div></div>
                            <div class="hf-message-avatar user">{{ substr(auth()->user()->name ?? 'U', 0, 1) }}</div>
                        @else
                            <div class="hf-message-avatar ai">
                                <x-filament::icon icon="heroicon-s-cpu-chip" class="hf-ai-avatar-icon" />
                            </div>
                            <div class="hf-message-bubble ai"><div class="hf-message-content ai">{!! nl2br(e($message['content'])) !!}</div></div>
                        @endif
                    </div>
                @endforeach
            @endif

            <!-- Loading indicator -->
            <div wire:target="send" wire:loading.class="is-loading" id="spinner-container" class="hf-center-loader" aria-hidden="true">
                <div class="hf-yellow-spinner"></div>
                </div>
            </div>
            @elseif($viewMode === 'settings')
            <div class="p-4">
                <x-filament::section>
                    <x-slot name="heading">Settings</x-slot>
                    <form wire:submit.prevent="saveSettingsForm" class="space-y-4" wire:key="hf-settings-form">
                        {{ $this->form }}
                        <div class="flex items-center gap-3 pt-2">
                            <x-filament::button type="submit" icon="heroicon-o-check-circle">Save Settings</x-filament::button>
                            <x-filament::button color="gray" type="button" wire:click="showChat" icon="heroicon-o-arrow-uturn-left">Back to Chat</x-filament::button>
                        </div>
                    </form>
                </x-filament::section>
            </div>
            @elseif($viewMode === 'profiles')
            <div class="p-4 space-y-6">
                <x-filament::section>
                    <x-slot name="heading">Model Profiles</x-slot>
                    @if(empty($availableProfiles))
                        <div class="text-xs text-gray-500">Debug: viewMode=profiles, availableProfiles is empty (count=0). If you expected items, ensure loadProfiles() runs in showProfiles() and Livewire component refreshed.</div>
                    @endif
                    <div class="space-y-4">
                        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr class="text-left">
                                    <th class="px-3 py-2 font-medium">Name</th>
                                    <th class="px-3 py-2 font-medium">Provider</th>
                                    <th class="px-3 py-2 font-medium">Model ID</th>
                                    <th class="px-3 py-2 font-medium">Stream</th>
                                    <th class="px-3 py-2 font-medium">Actions</th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($availableProfiles as $p)
                                <tr wire:key="profile-row-{{ $p['id'] }}" class="hover:bg-gray-50/60 dark:hover:bg-gray-800/60">
                                    <td class="px-3 py-1.5">{{ $p['name'] }}</td>
                                    <td class="px-3 py-1.5">{{ $p['provider'] }}</td>
                                    <td class="px-3 py-1.5 font-mono text-xs break-all">{{ $p['model_id'] }}</td>
                                    <td class="px-3 py-1.5">{{ $p['stream'] ?? true ? 'Yes' : 'No' }}</td>
                                    <td class="px-3 py-1.5 flex gap-1">
                                        <button type="button" wire:click="$set('selectedProfileId', {{ $p['id'] }})" class="fi-btn fi-size-xs fi-color-primary">Use</button>
                                        <button type="button" wire:click="editProfile({{ $p['id'] }})" class="fi-btn fi-size-xs fi-color-gray">Edit</button>
                                        <button type="button" wire:click="deleteProfile({{ $p['id'] }})" class="fi-btn fi-size-xs fi-color-danger" onclick="return confirm('Delete this profile?')">Del</button>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="px-3 py-2 text-gray-500 dark:text-gray-400">No profiles yet.</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="pt-4">
                            <h4 class="text-sm font-semibold mb-2">{{ $editingProfileId ? 'Edit Profile' : 'Add New Profile' }}</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <x-filament::input label="Name" wire:model.defer="profileForm.name" />
                                <x-filament::input label="Provider" wire:model.defer="profileForm.provider" placeholder="huggingface" />
                                <x-filament::input label="Model ID" wire:model.defer="profileForm.model_id" placeholder="openai/gpt-oss-120b:fireworks-ai" class="md:col-span-2" />
                                <x-filament::input label="Base URL" wire:model.defer="profileForm.base_url" placeholder="https://api.inference.huggingface.co" class="md:col-span-2" />
                                <x-filament::input label="API Key" wire:model.defer="profileForm.api_key" type="password" />
                                <div class="flex items-center gap-2">
                                    <label class="text-sm font-medium">Stream</label>
                                    <input type="checkbox" wire:model.defer="profileForm.stream" class="fi-checkbox" />
                                </div>
                                <x-filament::input label="Timeout" wire:model.defer="profileForm.timeout" type="number" min="5" max="600" />
                                <div class="md:col-span-2 space-y-1">
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">System Prompt</label>
                                    <textarea rows="2" wire:model.defer="profileForm.system_prompt" class="fi-input block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white text-sm"></textarea>
                                </div>
                            </div>
                            <div class="mt-3 flex items-center gap-3">
                                <x-filament::button wire:click="saveProfile" icon="heroicon-o-plus-circle">{{ $editingProfileId ? 'Update Profile' : 'Save Profile' }}</x-filament::button>
                                @if($editingProfileId)
                                <x-filament::button color="gray" wire:click="$set('editingProfileId', null); $set('profileForm', { name: '', provider: 'huggingface', model_id: '', base_url: '', api_key: '', stream: true, timeout: 60, system_prompt: '' });" icon="heroicon-o-x-mark">Cancel</x-filament::button>
                                @endif
                                <x-filament::button color="gray" wire:click="showChat" icon="heroicon-o-arrow-uturn-left">Back</x-filament::button>
                            </div>
                        </div>
                    </div>
                </x-filament::section>
            </div>
            @elseif($viewMode === 'conversations')
        <div class="p-4">
            <x-filament::section>
                <x-slot name="heading">Conversations</x-slot>
                <div wire:key="hf-table-conversations">
                    {{ $this->table }}
                </div>
            </x-filament::section>
        </div>
        @endif
            </div>
    </x-filament::section>

    <!-- Input and Send Button Section -->
    <x-filament::section>
    <div class="space-y-6"
                x-data="{ draft: '', handleKeydown(e) { if (e.key === 'Enter') { if (e.ctrlKey) { return; } e.preventDefault(); if (this.draft?.trim()) { this.submit(); } } }, submit() { const textToSend = this.draft; this.draft = ''; this.$wire.set('userInput', textToSend); this.$wire.send(); this.$refs.input && this.$refs.input.blur(); } }">
            <form x-on:submit.prevent="submit()" class="w-full">
                <div class="w-full">
                    <x-filament::input.wrapper class="w-full hf-textarea-outer" style="overflow: hidden;">
                        <textarea class="fi-input hf-textarea block w-full resize-none border-none bg-transparent text-base text-gray-950 placeholder:text-gray-500 focus:ring-0 focus:outline-none disabled:text-gray-500 disabled:cursor-not-allowed dark:text-white dark:placeholder:text-gray-400 sm:text-sm leading-relaxed" x-ref="input" x-model="draft" @keydown="handleKeydown($event)" placeholder="Type your message here... Enter to send, Ctrl + Enter for new line" rows="3" style="overflow: hidden; height: 3rem; min-height: 3rem; min-width: 100%; box-shadow: none !important; outline: none !important;"></textarea>
                    </x-filament::input.wrapper>
                </div>
                <div class="hf-send-row" style="padding: 0; background: transparent; border-radius: 12px; justify-content: flex-end;">
                    <div class="flex items-center gap-3">
                        <x-filament::button color="gray" icon="heroicon-o-plus" wire:click="newChatFromInput" id="hf-new-chat-bottom" wire:key="hf-new-chat-bottom">New Chat</x-filament::button>
                        <x-filament::button type="submit" wire:loading.attr="disabled" wire:target="send" class="transition-all duration-300 shadow-lg hover:shadow-xl" x-bind:disabled="!draft?.trim()" icon="heroicon-m-paper-airplane" icon-class="text-white">
                            <span wire:loading.remove wire:target="send" class="font-semibold">Send Message</span>
                            <span wire:loading wire:target="send" class="font-semibold">Sending...</span>
                        </x-filament::button>
                    </div>
                </div>
            </form>
        </div>
    </x-filament::section>


</x-filament-panels::page>
