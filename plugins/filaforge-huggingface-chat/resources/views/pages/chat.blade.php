<x-filament-panels::page class="hf-chat-page">
	<x-filament::section>

		<div
			class="hf-chat-container"
			x-data="{ typing: false, autoScroll: true, messageSent: false, optimisticMessage: '', showOptimisticUserMessage: false, showChatsTable: false,
				init() { this.scrollToBottom();
					$wire.on('messageSent', () => { this.messageSent = true; this.typing = false; this.showOptimisticUserMessage = false; this.optimisticMessage = ''; setTimeout(() => { this.scrollDownExtra(); }, 100); });
					$wire.on('messageReceived', () => { this.messageSent = false; this.typing = false; this.showOptimisticUserMessage = false; this.optimisticMessage = ''; this.scrollToBottom(); });
					this.$nextTick(() => { const messagesEl = this.$refs.messages; if (!messagesEl || typeof ResizeObserver === 'undefined') return; const ro = new ResizeObserver(() => { messagesEl.scrollTop = messagesEl.scrollHeight; }); ro.observe(messagesEl); });
					this.$nextTick(() => { const messagesEl = this.$refs.messages; if (!messagesEl) return; const mo = new MutationObserver(() => { messagesEl.scrollTop = messagesEl.scrollHeight; }); mo.observe(messagesEl, { childList: true, subtree: true }); });
				},
				sendMessage() { if ($wire.userInput?.trim()) { this.messageSent = true; $wire.send(); } },
				scrollToBottom() { this.$nextTick(() => { const el = this.$refs.messages; if (el && this.autoScroll) { el.scrollTop = el.scrollHeight; } }); },
				scrollDownExtra() { this.$nextTick(() => { const el = this.$refs.messages; if (el) { el.scrollTop = el.scrollHeight + 400; } }); },
				handleKeydown(event) { if (event.key === 'Enter') { if (event.ctrlKey) { return; } event.preventDefault(); this.sendMessage(); this.scrollToBottom(); } }
			}"
			@toggle-chats.window="showChatsTable = !showChatsTable"
			@hide-chats.window="showChatsTable = false"
			x-load-css="[@js(\Filament\Support\Facades\FilamentAsset::getStyleHref('hf-chat', package: 'filaforge/huggingface-chat'))]"
		>
		<!-- Messages Area -->
		<div class="hf-chat-messages hf-chat-scroll" x-ref="messages" x-show="!showChatsTable" @scroll="autoScroll = ($event.target.scrollTop + $event.target.clientHeight >= $event.target.scrollHeight - 10)">
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

			<!-- Optimistic user message -->
			<template x-if="showOptimisticUserMessage && optimisticMessage">
				<div x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" class="hf-message user">
					<div class="hf-message-bubble user"><div class="hf-message-content user" x-text="optimisticMessage"></div></div>
					<div class="hf-message-avatar user">{{ substr(auth()->user()->name ?? 'U', 0, 1) }}</div>
				</div>
			</template>

			<!-- Loading indicator -->
			<div wire:target="send" wire:loading.delay.class="is-loading" id="spinner-container" x-ref="spinner" class="hf-center-loader" aria-hidden="true">
				<div class="hf-yellow-spinner"></div>
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
			x-data="{ draft: '', showTableLocal: false, handleKeydown(e) { if (e.key === 'Enter') { if (e.ctrlKey) { return; } e.preventDefault(); if (this.draft?.trim()) { this.submit(); } } }, submit() { if (this.showTableLocal) { this.showTableLocal = false; window.dispatchEvent(new CustomEvent('hide-chats')); } const textToSend = this.draft; this.draft = ''; setTimeout(() => { $wire.set('userInput', textToSend); $wire.send(); }, 40); this.$refs.input && this.$refs.input.blur(); setTimeout(() => { window.dispatchEvent(new CustomEvent('hf-scroll-extra')); }, 100); } }"
			@toggle-chats.window="showTableLocal = !showTableLocal" @hide-chats.window="showTableLocal = false">
			<form x-on:submit.prevent="submit()" class="w-full">
				<div class="w-full">
					<x-filament::input.wrapper class="w-full hf-textarea-outer" style="overflow: hidden;">
						<textarea class="fi-input hf-textarea block w-full resize-none border-none bg-transparent text-base text-gray-950 placeholder:text-gray-500 focus:ring-0 focus:outline-none disabled:text-gray-500 disabled:cursor-not-allowed dark:text-white dark:placeholder:text-gray-400 sm:text-sm leading-relaxed" x-ref="input" x-model="draft" @keydown="handleKeydown($event)" placeholder="Type your message here... Enter to send, Ctrl + Enter for new line" rows="3" :disabled="showTableLocal" style="overflow: hidden; height: 3rem; min-height: 3rem; min-width: 100%; box-shadow: none !important; outline: none !important;"></textarea>
					</x-filament::input.wrapper>
				</div>
				<div class="hf-send-row" style="padding: 0; background: transparent; border-radius: 12px; justify-content: space-between;">
					<div class="flex items-center gap-3">
						<x-filament::button color="primary" icon="heroicon-o-chat-bubble-left-right" class="mr-2" style="margin-right: 0.5rem;" @click.prevent="$wire.newConversation(); window.dispatchEvent(new CustomEvent('hide-chats'))">New Chat</x-filament::button>
						<x-filament::button color="gray" icon="heroicon-o-key" class="mr-2" style="margin-right: 0.5rem;" @click.prevent="$dispatch('open-modal', { id: 'set-api-key-modal' })">Set API Token</x-filament::button>
					</div>
					<div class="flex items-center gap-3">
						<x-filament::button color="gray" icon="heroicon-o-table-cells" class="mr-2" style="margin-right: 0.5rem;" @click.prevent="window.dispatchEvent(new CustomEvent('toggle-chats'))"><span x-text="showTableLocal ? 'Back to Chat' : 'Conversations'"></span></x-filament::button>
						<x-filament::button type="submit" wire:loading.attr="disabled" wire:target="send" class="transition-all duration-300 shadow-lg hover:shadow-xl" x-bind:disabled="!draft?.trim()" icon="heroicon-m-paper-airplane" icon-class="text-white">
							<span wire:loading.remove wire:target="send" class="font-semibold">Send Message</span>
							<span wire:loading wire:target="send" class="font-semibold">Sending...</span>
						</x-filament::button>
					</div>
				</div>
			</form>
		</div>
	</x-filament::section>
	<x-filament::modal id="set-api-key-modal" width="md" heading="Set HF API Token">
	<form x-on:submit.prevent="$wire.saveApiKey($refs.apiKey.value); $dispatch('close-modal', { id: 'set-api-key-modal' })">
		<x-filament::input.wrapper class="w-full" style="height:3rem;margin-bottom:1rem;">
			<textarea id="hf-api-key" x-ref="apiKey" rows="4" style="min-width: 100%; height: 3rem;padding:0.6rem;" placeholder="Enter your Hugging Face API token..." class="fi-input block w-full resize-y border-none bg-transparent text-base text-gray-950 placeholder:text-gray-500 focus:ring-0 focus:outline-none dark:text-white dark:placeholder:text-gray-400 sm:text-sm"></textarea>
		</x-filament::input.wrapper>
		<div class="mt-4 flex justify-end gap-2">
			<x-filament::button color="gray" type="button" style="margin-right:0.5rem;" x-on:click="$dispatch('close-modal', { id: 'set-api-key-modal' })">Cancel</x-filament::button>
			<x-filament::button type="submit">Save Token</x-filament::button>
		</div>
	</form>
	</x-filament::modal>
</x-filament-panels::page>


