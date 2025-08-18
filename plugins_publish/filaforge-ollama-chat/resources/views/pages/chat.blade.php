<x-filament::page>
    @php(
        $ollamaChatCssPath = public_path('vendor/ollama-chat/ollama-chat.css')
    )
    @php(
        $ollamaChatJsPath = public_path('vendor/ollama-chat/ollama-chat.js')
    )
    @push('styles')
        <link rel="stylesheet" href="{{ asset('vendor/ollama-chat/ollama-chat.css') }}?v={{ file_exists($ollamaChatCssPath) ? filemtime($ollamaChatCssPath) : time() }}">
    @endpush
    @push('scripts')
        <script src="{{ asset('vendor/ollama-chat/ollama-chat.js') }}?v={{ file_exists($ollamaChatJsPath) ? filemtime($ollamaChatJsPath) : time() }}" defer></script>
    @endpush
    @once
        @push('head')
            @if(!app('request')->hasHeader('X-CSRF-TOKEN'))
                <meta name="csrf-token" content="{{ csrf_token() }}" />
            @endif
        @endpush
    @endonce
    <div id="ollama-single" wire:ignore data-default-model="{{ config('ollama-chat.default_model') }}" data-page-wrapper>
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <div class="model-pill active" id="ollama-active-model-pill" data-model="{{ config('ollama-chat.default_model') }}">{{ config('ollama-chat.default_model') }}</div>
                </div>
            </x-slot>
            <div id="ollama-messages-wrap" class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700">
                <div id="ollama-messages">
                    <div id="ollama-empty" class="text-sm">Start a conversation with Ollama...</div>
                    <div id="ollama-center-loader"><div class="ollama-spinner"></div></div>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Compose</x-slot>
            <div class="space-y-3">
                <form id="ollama-form" class="flex flex-col gap-2">
                    <textarea id="ollama-input" placeholder="Ask anything..." class="fi-input block w-full bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-700 focus:border-primary-500 focus:ring-primary-500 rounded-lg text-sm resize-none" rows="3"></textarea>
                    <div class="flex items-center justify-end gap-2">
                        <x-filament::button type="submit" id="ollama-send" size="sm" icon="heroicon-m-paper-airplane">
                            <span class="label-send">Send</span>
                            <span class="label-sending hidden">Sending...</span>
                        </x-filament::button>
                    </div>
                    <div class="text-[11px] text-gray-400 dark:text-gray-500">Enter sends • Shift+Enter = newline</div>
                </form>
            </div>
        </x-filament::section>
    </div>

    {{-- JS logic now loaded from published asset --}}
</x-filament::page>