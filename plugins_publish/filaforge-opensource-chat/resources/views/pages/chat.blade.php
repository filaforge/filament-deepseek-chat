<x-filament-panels::page class="oschat-page">
    <x-filament::section>
        <div class="oschat-container">
            @if($viewMode === 'chat')
                <div class="oschat-messages oschat-scroll" style="min-height:55vh;"
                     x-data="{ autoScroll:true, scrollToBottom(){ if(!this.$refs.msgs) return; this.$refs.msgs.scrollTop = this.$refs.msgs.scrollHeight; } }"
                     x-ref="msgs"
                     @messageReceived.window="scrollToBottom()"
                >
                    @if(empty($messages))
                        <div class="text-sm text-gray-500 dark:text-gray-400 p-6" wire:loading.remove wire:target="send">Start a new conversation by sending a message.</div>
                    @else
                        @foreach($messages as $m)
                            <div class="mb-4">
                                <div class="font-semibold text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ $m['role'] === 'assistant' ? 'Assistant' : 'You' }}</div>
                                <div class="prose dark:prose-invert max-w-none text-sm leading-relaxed whitespace-pre-line">{{ $m['content'] }}</div>
                            </div>
                        @endforeach
                    @endif
                    <div wire:target="send" wire:loading.delay.class="opacity-100" class="opacity-0 transition-opacity" aria-hidden="true">
                        <div class="flex items-center gap-2 text-xs text-gray-400"><span class="animate-spin h-3 w-3 border-2 border-blue-500 border-t-transparent rounded-full"></span> Generating...</div>
                    </div>
                </div>
            @elseif($viewMode === 'settings')
                <div class="w-full space-y-4" wire:key="oschat-settings-wrapper" style="min-height:55vh;">
                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4 space-y-4">
                        <h3 class="text-sm font-semibold">Settings</h3>
                        <form wire:submit.prevent="saveSettingsForm" class="space-y-4">
                            {{ $this->form }}
                            <x-filament::button type="submit">Save</x-filament::button>
                            <x-filament::button color="gray" wire:click="showChat" type="button">Back</x-filament::button>
                        </form>
                    </div>
                </div>
            @elseif($viewMode === 'profiles')
                <div class="w-full space-y-4" wire:key="oschat-profiles-wrapper" style="min-height:55vh;">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-semibold tracking-tight flex items-center gap-2">Model Profiles</h3>
                        <div class="flex items-center gap-2">
                            @if(!$showProfileForm)
                                <x-filament::button size="sm" icon="heroicon-o-plus" wire:click="newProfile">Add Profile</x-filament::button>
                            @else
                                <x-filament::button size="sm" color="gray" icon="heroicon-o-x-mark" wire:click="cancelProfileForm">Close Form</x-filament::button>
                            @endif
                            <x-filament::button size="sm" color="gray" icon="heroicon-o-arrow-path" wire:click="showProfiles">Refresh</x-filament::button>
                            <x-filament::button size="sm" color="gray" icon="heroicon-o-arrow-uturn-left" wire:click="showChat">Back</x-filament::button>
                        </div>
                    </div>
                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-2" wire:key="oschat-table-profiles">
                        {{ $this->table }}
                    </div>
                    @if($showProfileForm)
                        <div class="rounded-xl border border-dashed border-gray-300 dark:border-gray-700 p-4 space-y-4" wire:key="oschat-profile-form">
                            <h4 class="text-sm font-semibold">{{ $editingProfileId ? 'Edit Profile' : 'Add New Profile' }}</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <x-filament::input label="Name" wire:model.defer="profileForm.name" />
                                <x-filament::input label="Provider" wire:model.defer="profileForm.provider" />
                                <x-filament::input label="Model ID" wire:model.defer="profileForm.model_id" class="md:col-span-2" />
                                <x-filament::input label="Base URL" wire:model.defer="profileForm.base_url" class="md:col-span-2" />
                                <x-filament::input label="API Key" type="password" wire:model.defer="profileForm.api_key" />
                                <div class="flex items-center gap-2">
                                    <label class="text-sm font-medium">Stream</label>
                                    <input type="checkbox" wire:model.defer="profileForm.stream" class="fi-checkbox" />
                                </div>
                                <x-filament::input type="number" min="5" max="600" label="Timeout" wire:model.defer="profileForm.timeout" />
                                <div class="md:col-span-2 space-y-1">
                                    <label class="text-sm font-medium">System Prompt</label>
                                    <textarea rows="2" wire:model.defer="profileForm.system_prompt" class="fi-input block w-full rounded-md text-sm"></textarea>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 pt-1">
                                <x-filament::button wire:click="saveProfile" icon="heroicon-o-plus-circle">{{ $editingProfileId ? 'Update' : 'Save' }}</x-filament::button>
                                @if($editingProfileId)
                                    <x-filament::button color="gray" wire:click="$set('editingProfileId', null); $set('profileForm', { name: '', provider: 'opensource', model_id: '', base_url: '', api_key: '', stream: true, timeout: 60, system_prompt: '' });" icon="heroicon-o-x-mark">Cancel</x-filament::button>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            @elseif($viewMode === 'conversations')
                <div class="w-full space-y-4" wire:key="oschat-conversations-wrapper" style="min-height:55vh;">
                    <h3 class="text-base font-semibold tracking-tight">Conversations</h3>
                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-2" wire:key="oschat-table-conv">{{ $this->table }}</div>
                    <x-filament::button color="gray" size="sm" wire:click="showChat" icon="heroicon-o-arrow-uturn-left">Back</x-filament::button>
                </div>
            @endif
        </div>
    </x-filament::section>

    @if($viewMode === 'chat')
        <x-filament::section>
            <div class="space-y-6"
                 x-data="{ draft:'', submit(){ const t=this.draft; if(!t.trim()) return; this.draft=''; $wire.set('userInput', t); $wire.send(); this.$refs.input && this.$refs.input.blur(); }, handleKey(e){ if(e.key==='Enter'){ if(e.ctrlKey) return; e.preventDefault(); this.submit(); } } }">
                <form x-on:submit.prevent="submit" class="w-full">
                    @if(!empty($availableProfiles))
                        <div class="flex items-center gap-3 mb-2" wire:key="oschat-profile-select">
                            <label class="text-sm font-medium text-gray-600 dark:text-gray-300">Model</label>
                            <select wire:model="selectedProfileId" class="fi-input text-sm rounded-md">
                                @foreach($availableProfiles as $p)
                                    <option value="{{ $p['id'] }}">{{ $p['name'] }} ({{ $p['model_id'] }})</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="w-full">
                        <x-filament::input.wrapper class="w-full" style="overflow:hidden;">
                            <textarea x-ref="input" x-model="draft" @keydown="handleKey($event)" placeholder="Type your message... Enter to send, Ctrl+Enter for new line" rows="3" class="fi-input block w-full resize-none border-none bg-transparent text-sm leading-relaxed"></textarea>
                        </x-filament::input.wrapper>
                    </div>
                    <div class="flex justify-end pt-2 gap-2">
                        <x-filament::button type="button" color="gray" wire:click="showProfiles" size="sm" icon="heroicon-o-cog-8-tooth">Profiles</x-filament::button>
                        <x-filament::button type="button" color="gray" wire:click="showSettings" size="sm" icon="heroicon-o-cog-6-tooth">Settings</x-filament::button>
                        <x-filament::button type="submit" size="sm" icon="heroicon-o-paper-airplane">Send</x-filament::button>
                    </div>
                </form>
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
