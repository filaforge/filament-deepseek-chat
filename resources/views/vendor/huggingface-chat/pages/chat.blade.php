<x-filament-panels::page class="hf-chat-page">
	<x-filament::section>
		<div class="hf-chat-container">
			@if($viewMode === 'chat')
				<div
					class="hf-chat-messages hf-chat-scroll"
					style="min-height:55vh;"
					x-data="{ typing: false, autoScroll: true, messageSent: false, optimisticMessage: '', showOptimisticUserMessage: false,
						init() { this.scrollToBottom();
							$wire.on('messageSent', () => { this.messageSent = true; this.typing = false; this.showOptimisticUserMessage = false; this.optimisticMessage=''; setTimeout(() => { this.scrollDownExtra(); }, 100); });
							$wire.on('messageReceived', () => { this.messageSent = false; this.typing = false; this.showOptimisticUserMessage = false; this.optimisticMessage=''; this.scrollToBottom(); });
							this.$nextTick(() => { const el = this.$refs.messages; if(!el||typeof ResizeObserver==='undefined') return; const ro=new ResizeObserver(()=>{ el.scrollTop = el.scrollHeight; }); ro.observe(el); });
						},
						sendMessage() { if ($wire.userInput?.trim()) { this.messageSent = true; $wire.send(); } },
						scrollToBottom() { this.$nextTick(()=>{ const el=this.$refs.messages; if(el && this.autoScroll){ el.scrollTop = el.scrollHeight; } }); },
						scrollDownExtra() { this.$nextTick(()=>{ const el=this.$refs.messages; if(el){ el.scrollTop = el.scrollHeight + 400; } }); },
						handleKeydown(e){ if(e.key==='Enter'){ if(e.ctrlKey) return; e.preventDefault(); this.sendMessage(); this.scrollToBottom(); } }
					}"
					x-ref="messages"
					@scroll="autoScroll = ($event.target.scrollTop + $event.target.clientHeight >= $event.target.scrollHeight - 10)"
				>
				@if(empty($messages))
					<div wire:loading.remove wire:target="send" class="hf-empty-state">
						<x-filament::icon icon="heroicon-o-chat-bubble-left-right" class="hf-empty-icon" />
						<h3 class="text-lg font-medium mb-2 mt-2">Start a conversation</h3>
						<p class="text-sm">Ask me anything...</p>
					</div>
				@else
					@foreach($messages as $index => $message)
						@php $isUser = $message['role'] === 'user'; @endphp
						<div class="hf-message {{ $isUser ? 'user' : 'ai' }}" x-data="{ entered: false }" x-init="setTimeout(()=>entered=true, {{ $index * 80 }})" :class="{ 'entering': !entered, 'entered': entered }" wire:key="message-{{ $index }}">
							@if($isUser)
								<div class="hf-message-bubble user"><div class="hf-message-content user">{{ $message['content'] }}</div></div>
								<div class="hf-message-avatar user">{{ substr(auth()->user()->name ?? 'U', 0, 1) }}</div>
							@else
								<div class="hf-message-avatar ai"><x-filament::icon icon="heroicon-s-cpu-chip" class="hf-ai-avatar-icon" /></div>
								<div class="hf-message-bubble ai"><div class="hf-message-content ai">{!! nl2br(e($message['content'])) !!}</div></div>
							@endif
						</div>
					@endforeach
				@endif
				<div wire:target="send" wire:loading.delay.class="is-loading" id="spinner-container" x-ref="spinner" class="hf-center-loader" aria-hidden="true">
					<div class="hf-yellow-spinner"></div>
				</div>
				</div>
			@elseif($viewMode === 'settings')
				<div class="w-full space-y-4" wire:key="hf-settings-wrapper" style="min-height:55vh;">
					<h3 class="text-base font-semibold tracking-tight">Settings</h3>
					<form wire:submit.prevent="saveSettingsForm" wire:key="hf-settings-form" class="space-y-6">
						{{ $this->form }}
						<div class="flex items-center justify-end gap-3 pt-2">
							<x-filament::button type="button" color="gray" wire:click="showChat">Cancel</x-filament::button>
							<x-filament::button type="submit" icon="heroicon-o-check" wire:loading.attr="disabled" wire:target="saveSettingsForm">
								<span wire:loading.remove wire:target="saveSettingsForm">Save Settings</span>
								<span wire:loading wire:target="saveSettingsForm">Saving...</span>
							</x-filament::button>
						</div>
					</form>
				</div>
			@elseif($viewMode === 'profiles')
				<div class="w-full space-y-4" wire:key="hf-profiles-wrapper" style="min-height:55vh;">
					<div class="flex items-center justify-between">
						<h3 class="text-base font-semibold tracking-tight flex items-center gap-2">Model Profiles</h3>
						<div class="flex items-center gap-2">
							@if(!$showProfileForm)
								<x-filament::button size="sm" icon="heroicon-o-plus" wire:click="newProfile">Add Profile</x-filament::button>
							@else
								<x-filament::button size="sm" color="gray" icon="heroicon-o-x-mark" wire:click="cancelProfileForm">Close Form</x-filament::button>
							@endif
							<x-filament::button size="sm" color="gray" icon="heroicon-o-arrow-path" wire:click="showProfiles">Refresh</x-filament::button>
						</div>
					</div>
					<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-2" wire:key="hf-table-profiles">
						{{ $this->table }}
					</div>
					@if($showProfileForm)
					<div class="rounded-xl border border-dashed border-gray-300 dark:border-gray-700 p-4 space-y-4" wire:key="hf-profile-form">
						<h4 class="text-sm font-semibold">{{ $editingProfileId ? 'Edit Profile' : 'Add New Profile' }}</h4>
						<div class="grid grid-cols-1 md:grid-cols-2 gap-3">
							<x-filament::input label="Name" wire:model.defer="profileForm.name" />
							<x-filament::input label="Provider" wire:model.defer="profileForm.provider" placeholder="huggingface" />
							<x-filament::input label="Model ID" wire:model.defer="profileForm.model_id" class="md:col-span-2" placeholder="meta-llama/Meta-Llama-3-8B-Instruct" />
							<x-filament::input label="Base URL" wire:model.defer="profileForm.base_url" class="md:col-span-2" placeholder="https://api-inference.huggingface.co" />
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
						<div class="flex items-center gap-2 pt-1">
							<x-filament::button wire:click="saveProfile" icon="heroicon-o-plus-circle">{{ $editingProfileId ? 'Update' : 'Save' }}</x-filament::button>
							@if($editingProfileId)
							<x-filament::button color="gray" wire:click="$set('editingProfileId', null); $set('profileForm', { name: '', provider: 'huggingface', model_id: '', base_url: '', api_key: '', stream: true, timeout: 60, system_prompt: '' });" icon="heroicon-o-x-mark">Cancel</x-filament::button>
							@endif
							<x-filament::button color="gray" wire:click="showChat" icon="heroicon-o-arrow-uturn-left">Back</x-filament::button>
						</div>
					</div>
					@endif
				</div>
			@elseif($viewMode === 'conversations')
				<div class="w-full space-y-4" wire:key="hf-conversations-wrapper" style="min-height:55vh;">
					<h3 class="text-base font-semibold tracking-tight">Conversations</h3>
					<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-2" wire:key="hf-table-conversations">
						{{ $this->table }}
					</div>
				</div>
			@endif
		</div>
	</x-filament::section>

	@if($viewMode === 'chat')
	<x-filament::section>
		<div class="space-y-6"
			x-data="{ draft: '', handleKeydown(e){ if(e.key==='Enter'){ if(e.ctrlKey){ return;} e.preventDefault(); if(this.draft?.trim()){ this.submit(); } } }, submit(){ const text=this.draft; this.draft=''; $wire.set('userInput', text); $wire.send(); this.$refs.input && this.$refs.input.blur(); } }"
		>
			<form x-on:submit.prevent="submit()" class="w-full">
				@if(!empty($availableProfiles))
				<div class="flex items-center gap-3 mb-2" wire:key="hf-profile-select">
					<label class="text-sm font-medium text-gray-600 dark:text-gray-300">Model</label>
					<select wire:model="selectedProfileId" class="fi-input text-sm rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
						@foreach($availableProfiles as $p)
							<option value="{{ $p['id'] }}">{{ $p['name'] }} ({{ $p['model_id'] }})</option>
						@endforeach
					</select>
				</div>
				@endif
				<div class="w-full">
					<x-filament::input.wrapper class="w-full hf-textarea-outer" style="overflow: hidden;">
						<textarea class="fi-input hf-textarea block w-full resize-none border-none bg-transparent text-base text-gray-950 placeholder:text-gray-500 focus:ring-0 focus:outline-none disabled:text-gray-500 disabled:cursor-not-allowed dark:text-white dark:placeholder:text-gray-400 sm:text-sm leading-relaxed" x-ref="input" x-model="draft" @keydown="handleKeydown($event)" placeholder="Type your message here... Enter to send, Ctrl + Enter for new line" rows="3" style="overflow:hidden; height:3rem; min-height:3rem; min-width:100%; box-shadow:none !important; outline:none !important; padding:10px 12px;"></textarea>
					</x-filament::input.wrapper>
				</div>
				<div class="hf-send-row" style="padding:4px 0 0; background:transparent; border-radius:12px; justify-content:flex-end; align-items:center;">
					<div class="flex items-center gap-3">
						<x-filament::button type="submit" wire:loading.attr="disabled" wire:target="send" class="transition-all duration-300" x-bind:disabled="!draft?.trim()" icon="heroicon-m-paper-airplane" icon-class="text-white">
							<span wire:loading.remove wire:target="send" class="font-semibold">Send Chat</span>
							<span wire:loading wire:target="send" class="font-semibold">Sending...</span>
						</x-filament::button>
					</div>
				</div>
			</form>
		</div>
	</x-filament::section>
	@endif
</x-filament-panels::page>



