<x-filament-panels::page class="deepseek-chat-page">
    <style>
        /* Hide the Filament page header on the DeepSeek Chat page only */
        .deepseek-chat-page .fi-header { display: none !important; }
    </style>
    <x-filament::section>

        <div 
            class="deepseek-chat-container"
            x-data="{
                loading: false,
                typing: false,
                autoScroll: true,
                messageSent: false,
                // optimistic UI for the user's just-sent message
                optimisticMessage: '',
                showOptimisticUserMessage: false,
                showChatsTable: false,
                init() {
                    this.scrollToBottom();
                    
                    // Listen for Livewire events
                    $wire.on('messageSent', () => {
                        this.loading = true;
                        this.messageSent = true;
                        this.typing = false;
                        // Remove optimistic bubble once server-rendered message arrives
                        this.showOptimisticUserMessage = false;
                        this.optimisticMessage = '';
                        this.scrollToBottom();
                    });
                    
                    $wire.on('messageReceived', () => {
                        this.loading = false;
                        this.messageSent = false;
                        this.typing = false;
                        this.showOptimisticUserMessage = false;
                        this.optimisticMessage = '';
                        this.scrollToBottom();
                    });
                },
                sendMessage() {
                    if ($wire.userInput?.trim()) {
                        this.loading = true;
                        this.messageSent = true;
                        $wire.send();
                    }
                },
                scrollToBottom() {
                    this.$nextTick(() => {
                        const messagesContainer = this.$refs.messages;
                        if (messagesContainer && this.autoScroll) {
                            messagesContainer.scrollTop = messagesContainer.scrollHeight;
                        }
                    });
                },
                handleKeydown(event) {
                    if (event.ctrlKey && event.key === 'Enter') {
                        event.preventDefault();
                        this.sendMessage();
                    }
                }
            }"
            :data-loading="loading ? 'true' : null"
            @deepseek-send.window="loading = true; messageSent = true; scrollToBottom()"
            @deepseek-optimistic.window="optimisticMessage = $event.detail.text; showOptimisticUserMessage = true; scrollToBottom()"
            @toggle-chats.window="showChatsTable = !showChatsTable"
            @hide-chats.window="showChatsTable = false"
            x-load-css="[@js(\Filament\Support\Facades\FilamentAsset::getStyleHref('deepseek-chat', package: 'filaforge/deepseek-chat'))]"
        >
    <!-- Messages Area -->
        <div 
            class="deepseek-chat-messages deepseek-chat-scroll"
            x-ref="messages"
            x-show="!showChatsTable"
            @scroll="autoScroll = ($event.target.scrollTop + $event.target.clientHeight >= $event.target.scrollHeight - 10)"
        >
            @if(empty($messages))
                <div x-show="!loading || !messageSent" x-cloak class="deepseek-empty-state">
                    <x-filament::icon 
                        icon="heroicon-o-chat-bubble-left-right" 
                        class="deepseek-empty-icon"
                    />
                    <h3 class="text-lg font-medium mb-2 mt-2">Start a conversation</h3>
                    <p class="text-sm">Ask me anything...</p>
                </div>
            @else
                @foreach($messages as $index => $message)
                    @php
                        $isUser = $message['role'] === 'user';
                    @endphp
                    <div 
                        class="deepseek-message {{ $isUser ? 'user' : 'ai' }}"
                        x-data="{ entered: false }"
                        x-init="setTimeout(() => entered = true, {{ $index * 100 }})"
                        :class="{ 'entering': !entered, 'entered': entered }"
                        wire:key="message-{{ $index }}"
                    >
                        @if($isUser)
                            <div class="deepseek-message-bubble user">
                                <div class="deepseek-message-content user">{{ $message['content'] }}</div>
                            </div>
                            <div class="deepseek-message-avatar user">
                                {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                            </div>
                        @else
                            <div class="deepseek-message-avatar ai">
                                DS
                            </div>
                            <div class="deepseek-message-bubble ai">
                                <div class="deepseek-message-content ai">
                                    {!! Str::markdown($message['content']) !!}
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            @endif
            
            <!-- Optimistic user message (shows immediately on the right, at the bottom) -->
            <template x-if="showOptimisticUserMessage && optimisticMessage">
                <div 
                    x-cloak
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform translate-y-2"
                    x-transition:enter-end="opacity-100 transform translate-y-0"
                    class="deepseek-message user"
                >
                    <div class="deepseek-message-bubble user">
                        <div class="deepseek-message-content user" x-text="optimisticMessage"></div>
                    </div>
                    <div class="deepseek-message-avatar user">
                        {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                    </div>
                </div>
            </template>

            <!-- Loading indicator: centered spinner (no AI avatar) -->
            <template x-if="loading">
                <div x-cloak class="deepseek-center-loader">
                    <div class="deepseek-yellow-spinner"></div>
                </div>
            </template>
        </div>
        
        <!-- Chats Table (swap view) -->
        <div x-show="showChatsTable" x-cloak class="p-4">
            {{ $this->table }}
        </div>
        </div>
    </x-filament::section>

    <!-- Input and Send Button Section -->
    <x-filament::section>
    <div class="space-y-6"
             x-data="{
                draft: '',
                showTableLocal: false,
                handleKeydown(e) {
                    if (e.ctrlKey && e.key === 'Enter') {
                        e.preventDefault();
                        if (this.draft?.trim()) {
                            this.submit();
                        }
                    }
                },
                submit() {
                    // If conversations table is open, hide it before sending
                    if (this.showTableLocal) {
                        this.showTableLocal = false;
                        window.dispatchEvent(new CustomEvent('hide-chats'));
                    }
                    // Show loader immediately and keep sent message visible
                    this.$dispatch('deepseek-send');
                    // Show optimistic user message immediately on the right
                    this.$dispatch('deepseek-optimistic', { text: this.draft });
                    // Pass the message to Livewire, then clear the textarea
                    $wire.set('userInput', this.draft);
                    $wire.send();
                    this.draft = '';
            // Remove focus/outline from textarea until user re-selects
            this.$refs.input && this.$refs.input.blur();
                }
             }"
             @toggle-chats.window="showTableLocal = !showTableLocal"
             @hide-chats.window="showTableLocal = false"
    >
            <form x-on:submit.prevent="submit()" class="w-full">
                <!-- Text Input -->
                <div class="w-full">
                    <x-filament::input.wrapper class="w-full deepseek-textarea-outer" style="overflow: hidden;">
                        <textarea
                            class="fi-input deepseek-textarea block w-full resize-none border-none bg-transparent text-base text-gray-950 placeholder:text-gray-500 focus:ring-0 focus:outline-none disabled:text-gray-500 disabled:cursor-not-allowed dark:text-white dark:placeholder:text-gray-400 sm:text-sm leading-relaxed"
                            x-ref="input"
                            x-model="draft"
                            @keydown="handleKeydown($event)"
                            placeholder="Type your message here... Ctrl + Enter to send message"
                            rows="3"
                            :disabled="showTableLocal"
                            style="overflow: hidden; height: 3rem; min-height: 3rem; min-width: 100%; box-shadow: none !important; outline: none !important;"
                        ></textarea>
                    </x-filament::input.wrapper>
                </div>
                
                <!-- Actions Row -->
            <div class="deepseek-send-row" style="padding: 0; background: transparent; border-radius: 12px; justify-content: space-between;">
                <!-- Left group: New Chat + Set API Key -->
                <div class="flex items-center gap-3">
                    <x-filament::button
                        color="primary"
                        icon="heroicon-o-chat-bubble-left-right"
                        class="mr-2"
                        style="margin-right: 0.5rem;"
                        @click.prevent="$wire.newConversation(); window.dispatchEvent(new CustomEvent('hide-chats'))"
                    >
                        New Chat
                    </x-filament::button>
                    <x-filament::button
                        color="gray"
                        icon="heroicon-o-key"
                        class="mr-2"
                        style="margin-right: 0.5rem;"
                        @click.prevent="$dispatch('open-modal', { id: 'set-api-key-modal' })"
                    >
                        Set API Key
                    </x-filament::button>
                </div>

                <!-- Right group: Conversations + Send -->
                <div class="flex items-center gap-3">
                    <x-filament::button
                        color="gray"
                        icon="heroicon-o-table-cells"
                        class="mr-2"
                        style="margin-right: 0.5rem;"
                        @click.prevent="window.dispatchEvent(new CustomEvent('toggle-chats'))"
                    >
                        <span x-text="showTableLocal ? 'Back to Chat' : 'Conversations'"></span>
                    </x-filament::button>
                    <x-filament::button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="send"
                        class="transition-all duration-300 shadow-lg hover:shadow-xl"
                        x-bind:disabled="!draft?.trim()"
                        icon="heroicon-m-paper-airplane"
                        icon-class="text-white"
                    >
                        <span wire:loading.remove wire:target="send" class="font-semibold">Send Message</span>
                        <span wire:loading wire:target="send" class="font-semibold">Sending...</span>
                    </x-filament::button>
                </div>
            </div>
            </form>
        </div>
    </x-filament::section>
    <x-filament::modal id="set-api-key-modal" width="md" heading="Set API Key">
    <form x-on:submit.prevent="$wire.saveApiKey($refs.apiKey.value); $dispatch('close-modal', { id: 'set-api-key-modal' })">
        <x-filament::input.wrapper class="w-full">
            <textarea x-ref="apiKey" rows="4" placeholder="Enter your DeepSeek API key..." class="fi-input block w-full resize-y border-none bg-transparent text-base text-gray-950 placeholder:text-gray-500 focus:ring-0 focus:outline-none dark:text-white dark:placeholder:text-gray-400 sm:text-sm"></textarea>
        </x-filament::input.wrapper>
        <div class="mt-4 flex justify-end gap-2">
            <x-filament::button color="gray" type="button" x-on:click="$dispatch('close-modal', { id: 'set-api-key-modal' })">Cancel</x-filament::button>
            <x-filament::button type="submit">Save</x-filament::button>
        </div>
    </form>
    </x-filament::modal>
</x-filament-panels::page>
