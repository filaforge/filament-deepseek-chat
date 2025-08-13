<x-filament-widgets::widget>
    <x-filament::section>

    <div class="p-2">

            <div class="mt-8">
                <div
                    class="hwc-container"
                    x-data="{
                        autoScroll: true,
                        observer: null,
                        scrollToBottom() {
                            this.$nextTick(() => {
                                const el = this.$refs.messages;
                                if (!el) return;
                                if (this.autoScroll) el.scrollTop = el.scrollHeight;
                            });
                        },
                        init() {
                            // Auto-scroll on content changes
                            const el = this.$refs.messages;
                            if (el) {
                                this.observer = new MutationObserver(() => this.scrollToBottom());
                                this.observer.observe(el, { childList: true, subtree: true });
                            }
                            this.scrollToBottom();
                            // Listen for Livewire events
                            $wire.on('messageSent', () => this.scrollToBottom());
                            this.$root.addEventListener('publicChatMessageSent', () => this.scrollToBottom());
                        }
                    }"
                    x-load-css="[@js(\Filament\Support\Facades\FilamentAsset::getStyleHref('hello-widget', package: 'filaforge/hello-widget'))]"
                >
                    <div class="hwc-header px-4 py-3 text-sm">
                        💬 Public Chat
                    </div>

                    <div
                        class="hwc-messages px-2 py-2 space-y-0 max-h-96 overflow-y-auto"
                        wire:poll.2s
                        x-ref="messages"
                        @scroll="autoScroll = ($event.target.scrollTop + $event.target.clientHeight >= $event.target.scrollHeight - 12)"
                    >
                        @php $msgs = $this->getMessages(); @endphp
                        @if (empty($msgs))
                            <div class="hwc-empty">
                                <x-filament::icon icon="heroicon-o-chat-bubble-left-right" class="h-12 w-12 mx-auto mb-3 text-indigo-400" />
                                <h3 class="text-lg font-semibold mb-2 text-gray-700 dark:text-gray-300">Start the conversation</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Be the first to share a message with everyone!</p>
                            </div>
                        @else
                            @foreach ($msgs as $index => $m)
                                @php $isUser = $m['is_mine']; @endphp
                                <div class="hwc-message {{ $isUser ? 'user' : 'other' }}"
                                     x-data="{ entered: false }"
                                     x-init="setTimeout(() => entered = true, {{ $index * 80 }})"
                                     :class="{ 'entering': !entered, 'entered': entered }"
                                >
                                    <div class="hwc-msg-row">
                                        @if($isUser)
                                            <div class="hwc-bubble user" title="{{ $m['user'] }} • {{ $m['created_at'] }}" aria-label="{{ $m['user'] }} • {{ $m['created_at'] }}">{{ $m['message'] }}</div>
                                            <div class="hwc-avatar {{ $m['color'] }}" title="{{ $m['user'] }} • {{ $m['created_at'] }}" aria-label="{{ $m['user'] }} • {{ $m['created_at'] }}">{{ $m['initials'] }}</div>
                                        @else
                                            <div class="hwc-avatar {{ $m['color'] }}" title="{{ $m['user'] }} • {{ $m['created_at'] }}" aria-label="{{ $m['user'] }} • {{ $m['created_at'] }}">{{ $m['initials'] }}</div>
                                            <div class="hwc-bubble other" title="{{ $m['user'] }} • {{ $m['created_at'] }}" aria-label="{{ $m['user'] }} • {{ $m['created_at'] }}">{{ $m['message'] }}</div>
                                        @endif
                                    </div>
                                    <div class="hwc-meta-below" aria-hidden="true">
                                        <span class="hwc-meta-user">{{ $m['user'] }}</span>
                                        <span class="hwc-meta-dot">•</span>
                                        <span class="hwc-meta-time">{{ $m['created_at'] }}</span>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>

                    <div class="hwc-input-area p-2">
                        <form wire:submit.prevent="sendMessage" class="w-full"
                              x-data="{ userInput: @entangle('newMessage'), handleKeydown(e){ if(e.ctrlKey && e.key==='Enter'){ e.preventDefault(); if(this.userInput?.trim()){ $wire.sendMessage(); } } } }">
                            <div class="w-full">
                                <x-filament::input.wrapper class="w-full hwc-input-wrapper" style="overflow: hidden;">
                                    <textarea
                                        class="fi-input hwc-textarea block w-full resize-none border-none bg-transparent text-base text-gray-950 placeholder:text-gray-500 focus:ring-0 focus:outline-none disabled:text-gray-500 disabled:cursor-not-allowed dark:text-white dark:placeholder:text-gray-400 sm:text-sm leading-relaxed"
                                        x-model="userInput"
                                        @keydown="handleKeydown($event)"
                                        placeholder="Type your message here... (Ctrl+Enter to send)"
                                        rows="3"
                                        x-data="{ resize(){ const min=80,max=140; $el.style.height='auto'; const h=$el.scrollHeight; const nh=Math.max(min, Math.min(h, max)); $el.style.height=nh+'px'; const wrapper=$el.closest('.fi-input-wrp-content-ctn'); if(wrapper){ wrapper.style.height=nh+'px'; wrapper.style.minHeight=nh+'px'; wrapper.style.overflow='hidden'; } } }"
                                        x-init="resize()"
                                        @input="resize()"
                                        style="height: 80px; min-height: 80px; min-width: 100%; box-shadow: none !important; outline: none !important;"
                                    ></textarea>
                                </x-filament::input.wrapper>
                            </div>
                            <div class="hwc-send-row">
                                <button type="submit" class="hwc-send-btn" x-bind:disabled="!userInput?.trim()">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10l9-6 9 6-9 6-9-6zm0 0v6a9 9 0 0018 0v-6" /></svg>
                                    <span>Send</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
