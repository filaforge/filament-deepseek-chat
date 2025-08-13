<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            {{ __('DeepSeek Chat') }}
        </x-slot>

        <div 
            class="deepseek-chat-container"
            x-data="{
                loading: false,
                typing: false,
                autoScroll: true,
                init() {
                    this.scrollToBottom();
                    
                    // Listen for Livewire events
                    $wire.on('messageSent', () => {
                        this.loading = true;
                        this.typing = false;
                        this.scrollToBottom();
                    });
                    
                    $wire.on('messageReceived', () => {
                        this.loading = false;
                        this.typing = false;
                        this.scrollToBottom();
                    });
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
                        if ($wire.userInput?.trim()) {
                            $wire.send();
                        }
                    }
                }
            }"
            x-load-css="[@js(\Filament\Support\Facades\FilamentAsset::getStyleHref('deepseek-chat', package: 'filaforge/deepseek-chat'))]"
        >
        <!-- Messages Area -->
        <div 
            class="deepseek-chat-messages"
            x-ref="messages"
            @scroll="autoScroll = ($event.target.scrollTop + $event.target.clientHeight >= $event.target.scrollHeight - 10)"
        >
            @if(empty($messages))
                <div class="deepseek-empty-state">
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
            
            <!-- Loading indicator -->
            <div 
                x-show="loading" 
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                class="deepseek-message ai"
            >
                <div class="deepseek-message-avatar ai">
                    DS
                </div>
                <div class="deepseek-message-bubble ai">
                    <div class="deepseek-message-content ai">
                        <div class="deepseek-loading-dots">
                            <div class="deepseek-loading-dot"></div>
                            <div class="deepseek-loading-dot"></div>
                            <div class="deepseek-loading-dot"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </x-filament::section>

    <!-- Input and Send Button Section -->
    <x-filament::section>
        <div class="space-y-6"
             x-data="{
                // Two-way bind with Livewire for immediate updates
                userInput: @entangle('userInput'),
                // Support Ctrl+Enter to send
                handleKeydown(e) {
                    if (e.ctrlKey && e.key === 'Enter') {
                        e.preventDefault();
                        if (this.userInput?.trim()) {
                            $wire.send();
                        }
                    }
                }
             }">
            <form wire:submit.prevent="send" class="w-full">
                <!-- Text Input -->
                <div class="w-full">
                    <x-filament::input.wrapper class="w-full" style="overflow: hidden;">
                        <textarea
                            class="fi-input deepseek-textarea block w-full resize-none border-none bg-transparent text-base text-gray-950 placeholder:text-gray-500 focus:ring-0 focus:outline-none disabled:text-gray-500 disabled:cursor-not-allowed dark:text-white dark:placeholder:text-gray-400 sm:text-sm leading-relaxed"
                            x-model="userInput"
                            @keydown="handleKeydown($event)"
                            placeholder="Type your message here..."
                            rows="4"
                            x-data="{
                                resize() {
                                    const minHeight = 100;
                                    const maxHeight = 160;
                                    $el.style.height = 'auto';
                                    const scrollHeight = $el.scrollHeight;
                                    const newHeight = Math.max(minHeight, Math.min(scrollHeight, maxHeight));
                                    $el.style.height = newHeight + 'px';
                                    
                                    // Find and update the Filament wrapper content container
                                    const wrapper = $el.closest('.fi-input-wrp-content-ctn');
                                    if (wrapper) {
                                        wrapper.style.height = newHeight + 'px';
                                        wrapper.style.minHeight = newHeight + 'px';
                                        wrapper.style.overflow = 'hidden';
                                    }
                                }
                            }"
                            x-init="resize()"
                            @input="resize()"
                            style="height: 100px; min-height: 100px; min-width: 100%; box-shadow: none !important; outline: none !important;"
                        ></textarea>
                    </x-filament::input.wrapper>
                </div>
                
                <!-- Send Button -->
            <div class="deepseek-send-row" style="padding: 0; background: transparent; border-radius: 12px;">
                    <x-filament::button
                        type="submit"
                        color="primary"
                        size="lg"
                        wire:loading.attr="disabled"
                        wire:target="send"
                class="deepseek-send-button transition-all duration-300 shadow-lg hover:shadow-xl ml-auto"
                style="font-weight: 600;"
                                x-bind:disabled="!userInput?.trim()"
                    >
                        <x-slot name="icon">
                            <span wire:loading.remove wire:target="send">
                                <x-filament::icon icon="heroicon-m-paper-airplane" class="h-5 w-5 text-white" />
                            </span>
                            <span wire:loading wire:target="send">
                                <x-filament::icon icon="heroicon-m-arrow-path" class="h-5 w-5 animate-spin text-white" />
                            </span>
                        </x-slot>
                        
                        <span wire:loading.remove wire:target="send" class="font-semibold">Send Message</span>
                        <span wire:loading wire:target="send" class="font-semibold">Sending...</span>
                    </x-filament::button>
                </div>
            </form>
        </div>
    </x-filament::section>
</x-filament-panels::page>
