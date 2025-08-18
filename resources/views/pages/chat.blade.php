<x-filament-panels::page class="deepseek-chat-page">
    <x-filament::section>

        <div
            class="deepseek-chat-container"
            x-data="{
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
                        this.messageSent = true;
                        this.typing = false;
                        // Remove optimistic bubble once server-rendered message arrives
                        this.showOptimisticUserMessage = false;
                        this.optimisticMessage = '';
                        // Scroll down a bit more to create space for the loading spinner
                        setTimeout(() => {
                            this.scrollDownExtra();
                        }, 100);
                    });

                    $wire.on('messageReceived', () => {
                        this.messageSent = false;
                        this.typing = false;
                        this.showOptimisticUserMessage = false;
                        this.optimisticMessage = '';
                        this.scrollToBottom();
                    });

                    // Observe spinner visibility (is-loading class is added by wire:loading.delay)
                    this.$nextTick(() => {
                        const spinnerEl = this.$refs.spinner;
                        if (!spinnerEl) return;
                        const observer = new MutationObserver(() => {
                            if (spinnerEl.classList.contains('is-loading')) {
                                // Scroll only after spinner is rendered and layout has settled
                                const messagesEl = this.$refs.messages;
                                if (!messagesEl) return;
                                requestAnimationFrame(() => {
                                    requestAnimationFrame(() => {
                                        messagesEl.scrollTop = messagesEl.scrollHeight + 400; // extra space for centered spinner
                                    });
                                });
                            }
                        });
                        observer.observe(spinnerEl, { attributes: true, attributeFilter: ['class'] });
                    });

                    // Always scroll when messages container resizes (new content, images, etc.)
                    this.$nextTick(() => {
                        const messagesEl = this.$refs.messages;
                        if (!messagesEl || typeof ResizeObserver === 'undefined') return;
                        const resizeObserver = new ResizeObserver(() => {
                            // Always scroll to bottom when content/height changes
                            messagesEl.scrollTop = messagesEl.scrollHeight;
                        });
                        resizeObserver.observe(messagesEl);
                    });

                    // Also observe DOM mutations inside the messages container (child list changes from Livewire updates)
                    this.$nextTick(() => {
                        const messagesEl = this.$refs.messages;
                        if (!messagesEl) return;
                        const mutationObserver = new MutationObserver(() => {
                            messagesEl.scrollTop = messagesEl.scrollHeight;
                        });
                        mutationObserver.observe(messagesEl, { childList: true, subtree: true });
                    });
                },
                sendMessage() {
                    if ($wire.userInput?.trim()) {
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
                scrollDownExtra() {
                    this.$nextTick(() => {
                        const messagesContainer = this.$refs.messages;
                        if (messagesContainer) {
                            // Scroll down extra to create much more space for the centered spinner
                            messagesContainer.scrollTop = messagesContainer.scrollHeight + 400;
                        }
                    });
                },
                handleKeydown(event) {
                    if (event.key === 'Enter') {
                        if (event.ctrlKey) {
                            return; // allow newline
                        }
                        event.preventDefault();
                        this.sendMessage();
                        this.scrollToBottom();
                    }
                }
            }"
            @deepseek-send.window="messageSent = true; scrollToBottom()"
            @deepseek-optimistic.window="optimisticMessage = $event.detail.text; showOptimisticUserMessage = true; scrollToBottom()"
            @deepseek-scroll-extra.window="scrollDownExtra()"
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
                <div wire:loading.remove wire:target="send" class="deepseek-empty-state">
                    <x-filament::icon
                        icon="heroicon-o-chat-bubble-left-right"
                        class="deepseek-empty-icon"
                    />
                    <h3 class="text-lg font-medium mb-2 mt-2">
                        @if($this->hasApiKey())
                            Start a conversation
                        @else
                            Set your API key to start chatting
                        @endif
                    </h3>
                    <p class="text-sm">
                        @if($this->hasApiKey())
                            Ask me anything...
                        @else
                            You need to set your DeepSeek API key to start chatting.
                        @endif
                    </p>
                    @if(!$this->hasApiKey())
                        <div class="mt-4">
                            <x-filament::button
                                color="primary"
                                icon="heroicon-o-key"
                                @click="$dispatch('open-modal', { id: 'set-api-key-modal' })"
                            >
                                Set API Key
                            </x-filament::button>
                        </div>
                    @endif
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
                                @php($aiIcon = public_path('vendor/filaforge/deepseek-chat/icon.png'))
                                @if (file_exists($aiIcon))
                                    <img src="{{ asset('vendor/filaforge/deepseek-chat/icon.png') }}" alt="AI" class="deepseek-ai-avatar-img" />
                                @else
                                    <x-filament::icon
                                        icon="heroicon-s-cpu-chip"
                                        class="deepseek-ai-avatar-icon"
                                    />
                                @endif
                            </div>
                            <div class="deepseek-message-bubble ai">
                                <div class="deepseek-message-content ai">
                                    {!! nl2br(e($message['content'])) !!}
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

            <!-- Loading indicator: positioned in center of chat area when sending -->
            <div
                wire:target="send"
                wire:loading.delay.class="is-loading"
                id="spinner-container"
                x-ref="spinner"
                class="deepseek-center-loader"
                aria-hidden="true"
            >
                <div class="deepseek-yellow-spinner"></div>
            </div>
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
                    if (e.key === 'Enter') {
                        if (e.ctrlKey) {
                            return; // newline
                        }
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

                    // Show optimistic user message immediately on the right
                    this.$dispatch('deepseek-optimistic', { text: this.draft });

                    // Pass the message to Livewire after a tiny delay to allow scroll
                    const textToSend = this.draft;
                    this.draft = '';
                    setTimeout(() => {
                        $wire.set('userInput', textToSend);
                        $wire.send();
                    }, 40);

                    // Remove focus from textarea
                    this.$refs.input && this.$refs.input.blur();

                    setTimeout(() => {
                    // Scroll extra space before triggering loading spinner
                    window.dispatchEvent(new CustomEvent('deepseek-scroll-extra'));

                    }, 100);

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
                            placeholder="Type your message here... Enter to send, Ctrl + Enter for new line"
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
    <form x-on:submit.prevent="$wire.saveApiKey($refs.apiKey.value); $dispatch('close-modal', { id: 'set-api-key-modal' }); setTimeout(() => window.location.reload(), 500);">
        <x-filament::input.wrapper class="w-full" style="height:3rem;margin-bottom:1rem;">
            <textarea id="deepseek-api-key" x-ref="apiKey" rows="4" style="min-width: 100%; height: 3rem;padding:0.6rem;" placeholder="Enter your DeepSeek API key..." class="fi-input block w-full resize-y border-none bg-transparent text-base text-gray-950 placeholder:text-gray-500 focus:ring:0 focus:outline-none dark:text-white dark:placeholder:text-gray-400 sm:text-sm"></textarea>
        </x-filament::input.wrapper>
        <div class="mt-4 flex justify-end gap-2">
            <x-filament::button color="gray" type="button" style="margin-right:0.5rem;" x-on:click="$dispatch('close-modal', { id: 'set-api-key-modal' })">Cancel</x-filament::button>
            <x-filament::button type="submit">Save Api Key</x-filament::button>
        </div>
    </form>
    </x-filament::modal>
</x-filament-panels::page>
