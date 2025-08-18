<x-filament-panels::page class="hf-chat-page">
    <x-filament::section>

        <div class="hf-chat-container">
        @if($viewMode === 'chat')
        <!-- Messages Area -->
        <div class="hf-chat-messages hf-chat-scroll">
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
                <div wire:key="hf-table-settings">
                    {{ $this->table }}
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
