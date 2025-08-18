@php($errors ??= new \Illuminate\Support\ViewErrorBag)
<x-filament::page class="ff-shell-terminal">
    <!-- Explicitly include published assets to ensure availability -->
    <link rel="stylesheet" href="{{ asset('css/filaforge/shell-terminal/xterm.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/filaforge/shell-terminal/shell-terminal.css') }}" />
    <script src="{{ asset('js/filaforge/shell-terminal/xterm.js') }}"></script>
    <script src="{{ asset('js/filaforge/shell-terminal/xterm-addon-fit.js') }}"></script>
    <script src="{{ asset('js/filaforge/shell-terminal/xterm-addon-web-links.js') }}"></script>
    <script src="{{ asset('js/filaforge/shell-terminal/shell-terminal.js') }}"></script>

    <!-- Plugin CSS -->
    

    <style>
        @import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&display=swap');
        .fi-terminal-container { border-radius: 12px; overflow: hidden; min-height: 60vh; }
        .xterm { font-family: 'JetBrains Mono','Fira Code',monospace !important; font-size: 14px !important; line-height: 1.4 !important; padding: 16px !important; }
        .xterm .xterm-viewport { background: var(--terminal-bg) !important; }
        .xterm .xterm-screen   { background: var(--terminal-bg) !important; }
        /* Preset buttons spacing without relying on Tailwind utilities */
        .fi-preset-btn { margin-right: 10px; }
    </style>

    @php($presets = config('shell-terminal.presets', []))

    <x-filament::section>
        <x-slot name="heading">Shell Terminal</x-slot>
        <x-slot name="description">🟢 Connected to ({{ gethostname() }})</x-slot>

        <div class="fi-terminal-container">
            <div id="terminal" wire:ignore x-data="{}"
                 x-init="(() => { const lw = @this; let t=0; const boot=()=>{ if(window.FilaShellTerminal&&window.FilaShellTerminal.init){ window.FilaShellTerminal.init($el,lw);} else if(t++<60){ setTimeout(boot,50);} }; boot(); })()"
                 style="height:60vh;"></div>
        </div>
    </x-filament::section>

    @if(!empty($presets))
    <div x-data="{ active: 'all', view: 'categories' }">
        <x-filament::section>
            @php($__iconMap = [
                // Category-specific icons
                'optimize' => 'heroicon-o-bolt',
                'filament' => 'heroicon-o-sparkles',
                'status' => 'heroicon-o-cpu-chip',
                'files' => 'heroicon-o-folder',
                'github' => 'heroicon-o-code-bracket',
                'php shell' => 'heroicon-o-command-line',
                'nodejs' => 'heroicon-o-globe-alt',
                'docker' => 'heroicon-o-cube',
                'database' => 'heroicon-o-circle-stack',
                // Legacy mappings
                'laravel' => 'heroicon-o-rocket-launch',
                'git' => 'heroicon-o-code-bracket',
                'system' => 'heroicon-o-cpu-chip',
                'file' => 'heroicon-o-folder',
                'folders' => 'heroicon-o-folder',
                'net' => 'heroicon-o-globe-alt',
                'composer' => 'heroicon-o-cube',
                'node' => 'heroicon-o-globe-alt',
                'artisan' => 'heroicon-o-sparkles',
                'data' => 'heroicon-o-circle-stack',
                'php' => 'heroicon-o-command-line',
                'db' => 'heroicon-o-circle-stack',
            ])
            @php($__keys = array_keys($presets))
            @php($__keySlugs = array_map(fn($k) => \Illuminate\Support\Str::slug($k), $__keys))

            <div class="px-2 md:px-4">
                <!-- Categories view -->
                <div class="flex flex-wrap items-center gap-2 mt-2 fi-preset-rows" x-show="view === 'categories'" x-cloak>
                    <!-- Refresh (clear terminal) -->
                    <button type="button"
                        class="fi-btn fi-preset-btn fi-preset-cat fi-terminal-action-btn shadow-sm text-white bg-gray-700 hover:bg-gray-600 dark:bg-gray-800 dark:hover:bg-gray-700 ring-1 ring-gray-600 dark:ring-gray-700"
                        @click="window.FilaShellTerminal && window.FilaShellTerminal.refresh && window.FilaShellTerminal.refresh()"
                        title="Clear terminal"
                    >
                        <x-filament::icon icon="heroicon-o-arrow-path" class="h-4 w-4" aria-hidden="true" />
                    </button>
                    <!-- Toggle to Commands (placed in same row) -->
                    <button type="button"
                        class="fi-btn fi-preset-btn fi-preset-cat fi-terminal-action-btn shadow-sm text-white bg-gray-700 hover:bg-gray-600 dark:bg-gray-800 dark:hover:bg-gray-700 ring-1 ring-gray-600 dark:ring-gray-700"
                        @click="view = 'commands'"
                        title="Show commands"
                    >
                        <x-filament::icon icon="heroicon-o-squares-2x2" class="h-4 w-4" aria-hidden="true" />
                    </button>
                    @foreach(array_keys($presets) as $__grp)
                        @php($__slug = \Illuminate\Support\Str::slug($__grp))
                        @php($__lower = \Illuminate\Support\Str::lower($__grp))
                        @php($__display = $__lower === 'git' ? 'Github' : $__grp)
                        @php($__icon = 'heroicon-o-tag')
                        @foreach($__iconMap as $__k => $__v)
                            @if(str_contains($__lower, $__k))
                                @php($__icon = $__v)
                                @break
                            @endif
                        @endforeach
                        <button type="button"
                            class="fi-btn fi-preset-btn fi-preset-cat fi-terminal-action-btn shadow-sm text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600 ring-1 ring-blue-500 dark:ring-blue-600"
                            @click="active = '{{ $__slug }}'"
                            :class="{ 'ring-2 ring-offset-2': active === '{{ $__slug }}' }"
                            title="{{ $__grp }} commands"
                        >
                            <x-filament::icon :icon="$__icon" class="h-4 w-4" aria-hidden="true" />
                            <span class="ml-2">{{ $__display }}</span>
                        </button>
                    @endforeach
                </div>

                <!-- Commands view -->
                <div x-show="view === 'commands'" x-cloak>
                    <div class="mt-4">
                        @foreach($presets as $__grp => $__commands)
                            @php($__slug = \Illuminate\Support\Str::slug($__grp))
                            <div x-show="active === '{{ $__slug }}' || active === 'all'" class="mb-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">{{ $__grp }}</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                    @foreach($__commands as $__cmd => $__desc)
                                        <button type="button"
                                            class="fi-btn fi-preset-btn fi-terminal-action-btn shadow-sm text-left p-3 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600"
                                            @click="window.FilaShellTerminal && window.FilaShellTerminal.runCommand && window.FilaShellTerminal.runCommand('{{ $__cmd }}')"
                                            title="{{ $__desc }}"
                                        >
                                            <div class="font-mono text-sm text-blue-600 dark:text-blue-400">{{ $__cmd }}</div>
                                            <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">{{ $__desc }}</div>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </x-filament::section>
    @endif

    <script>
        (function() {
            let current = null;
            let isRefreshing = false;

            function attachGlobalListeners() {
                if (window.FilaShellTerminal) return;
                
                window.FilaShellTerminal = (function() {
                    function init(termEl, livewireComponent) {
                        if (termEl.dataset.initialized === '1') return;
                        
                        let terminal, fitAddon, webLinksAddon;
                        let lastPromptAt = 0;

                        try {
                            terminal = new Terminal({
                                cursorBlink: true,
                                theme: {
                                    background: '#0a0a0c',
                                    foreground: '#ffffff',
                                    cursor: '#ffffff',
                                    cursorAccent: '#ffffff'
                                }
                            });

                            fitAddon = new FitAddon.FitAddon();
                            webLinksAddon = new WebLinksAddon.WebLinksAddon();

                            if (fitAddon) terminal.loadAddon(fitAddon);
                            if (webLinksAddon) terminal.loadAddon(webLinksAddon);
                            
                            terminal.open(termEl);
                            if (fitAddon) fitAddon.fit();
                            
                            termEl._terminal = terminal;
                            termEl.dataset.initialized = '1';

                            let state = {
                                currentCommand: '',
                                commandHistory: [],
                                historyIndex: -1,
                                currentPath: '{{ $this->getCurrentPath() }}',
                                showPrompt: () => {
                                    const now = Date.now();
                                    if (now - lastPromptAt < 50) return;
                                    lastPromptAt = now;
                                    terminal.write(`\x1b[34madmin@filament\x1b[0m:\x1b[36m~\x1b[0m$ `);
                                },
                                clearCurrentLine: () => { terminal.write('\x1b[2K\x1b[0G'); },
                                refreshPrompt: () => { state.clearCurrentLine(); state.showPrompt(); terminal.write(state.currentCommand); },
                            };

                            function applyTheme() {
                                try {
                                    const root = document.querySelector('.ff-shell-terminal');
                                    if (!root) return;
                                    const styles = getComputedStyle(root);
                                    const isDark = document.documentElement.classList.contains('dark') || document.documentElement.dataset.theme === 'dark';
                                    const bg = (styles.getPropertyValue('--terminal-bg') || '').trim() || (isDark ? '#0a0a0c' : '#ffffff');
                                    const fg = (styles.getPropertyValue('--terminal-fg') || '').trim() || (isDark ? '#ffffff' : '#1e293b');
                                    const cursor = (styles.getPropertyValue('--terminal-cursor') || '').trim() || fg;
                                    terminal.options.theme = { background: bg, foreground: fg, cursor, cursorAccent: cursor };
                                    if (current && current.termEl) { current.termEl.style.background = bg; }
                                } catch (e) { /* no-op */ }
                            }

                            const writeWelcome = () => {
                                terminal.writeln('\x1b[36mWelcome to Filament Shell Terminal\x1b[0m');
                                terminal.writeln('Tab = completion, ↑/↓ = history, Ctrl+L = clear, Ctrl+C = cancel');
                                terminal.writeln(''); state.showPrompt();
                            };
                            
                            const needsWelcomeMessage = () => {
                                try {
                                    if (!terminal || !terminal.buffer || !terminal.buffer.active) return true;
                                    const lineCount = terminal.buffer.active.length;
                                    if (lineCount === 0) return true;
                                    for (let i = 0; i < Math.min(lineCount, 5); i++) {
                                        const line = terminal.buffer.active.getLine(i);
                                        if (line && line.translateToString().trim()) return false;
                                    }
                                    return true;
                                } catch (e) { return true; }
                            };
                            
                            if (fitAddon) {
                                requestAnimationFrame(() => setTimeout(() => {
                                    if (needsWelcomeMessage()) writeWelcome();
                                }, 0));
                            } else {
                                if (needsWelcomeMessage()) writeWelcome();
                            }
                            
                            setTimeout(() => terminal.focus(), 150);

                            terminal.onKey(({ key, domEvent }) => {
                                // Ensure terminal keeps focus and prevent Filament shortcuts
                                if (domEvent && domEvent.stopPropagation) domEvent.stopPropagation();
                                const printable = !domEvent.altKey && !domEvent.ctrlKey && !domEvent.metaKey;
                                if (domEvent.keyCode === 13) {
                                    (async () => {
                                        const command = state.currentCommand;
                                        if (!command.trim()) return;
                                        if (state.commandHistory[state.commandHistory.length - 1] !== command) state.commandHistory.push(command);
                                        state.historyIndex = -1;
                                        terminal.writeln('');
                                        try {
                                            if (!livewireComponent) {
                                                terminal.writeln('\x1b[31mError: Livewire component not available\x1b[0m');
                                                state.showPrompt();
                                                return;
                                            }
                                            await livewireComponent.call('$set', 'data.command', command);
                                            await livewireComponent.call('run');
                                            // Fallback: fetch final output snapshot
                                            try {
                                                const out = await livewireComponent.get('data.output');
                                                if (out) {
                                                    const normalized = String(out).replace(/\r\n/g,'\n').replace(/\r/g,'\n').replace(/\n/g,'\r\n');
                                                    terminal.write(normalized);
                                                }
                                            } catch (e) {}
                                            state.showPrompt();
                                        } catch (error) {
                                            console.error('Terminal command error:', error);
                                            terminal.writeln(`\x1b[31mError: ${error.message}\x1b[0m`);
                                            state.showPrompt();
                                        }
                                        state.currentCommand = '';
                                    })();
                                } else if (domEvent.keyCode === 8) {
                                    if (state.currentCommand.length > 0) {
                                        state.currentCommand = state.currentCommand.slice(0, -1);
                                        terminal.write('\b \b');
                                    }
                                } else if (domEvent.keyCode === 9) {
                                    domEvent.preventDefault(); // Tab
                                    (async () => {
                                        if (!state.currentCommand.trim()) return;
                                        try {
                                            if (!livewireComponent) return;
                                            const suggestions = await livewireComponent.call('getTabCompletion', state.currentCommand);
                                            if (suggestions.length === 1) {
                                                const parts = state.currentCommand.split(' ');
                                                parts[parts.length - 1] = suggestions[0];
                                                state.currentCommand = parts.join(' ');
                                                state.refreshPrompt();
                                            } else if (suggestions.length > 1) {
                                                terminal.writeln('');
                                                terminal.writeln(suggestions.join('    '));
                                                state.showPrompt();
                                                terminal.write(state.currentCommand);
                                            }
                                        } catch (e) {
                                            console.error('Tab completion error:', e);
                                        }
                                    })();
                                } else if (domEvent.keyCode === 38) { // Up
                                    if (state.commandHistory.length > 0) {
                                        if (state.historyIndex === -1) state.historyIndex = state.commandHistory.length - 1;
                                        else if (state.historyIndex > 0) state.historyIndex--;
                                        state.currentCommand = state.commandHistory[state.historyIndex] || '';
                                        state.refreshPrompt();
                                    }
                                } else if (domEvent.keyCode === 40) { // Down
                                    if (state.historyIndex >= 0) {
                                        if (state.historyIndex < state.commandHistory.length - 1) {
                                            state.historyIndex++;
                                            state.currentCommand = state.commandHistory[state.historyIndex];
                                        } else {
                                            state.historyIndex = -1;
                                            state.currentCommand = '';
                                        }
                                        state.refreshPrompt();
                                    }
                                } else if (domEvent.keyCode === 67 && domEvent.ctrlKey) { // Ctrl+C
                                    domEvent.preventDefault();
                                    terminal.writeln('^C');
                                    state.currentCommand = '';
                                    state.showPrompt();
                                } else if (domEvent.keyCode === 76 && domEvent.ctrlKey) { // Ctrl+L
                                    domEvent.preventDefault();
                                    terminal.clear();
                                    state.showPrompt();
                                } else if (printable) {
                                    state.currentCommand += key;
                                    terminal.write(key);
                                }
                            });

                            window.addEventListener('resize', () => { if (fitAddon) fitAddon.fit(); });
                            const resizeObserver = new ResizeObserver(() => { if (fitAddon) fitAddon.fit(); });
                            resizeObserver.observe(termEl);
                            const containerEl = document.querySelector('.fi-terminal-container');
                            if (containerEl) resizeObserver.observe(containerEl);
                            
                            const refit = () => { if (fitAddon) { try { fitAddon.fit(); } catch (e) {} } };
                            if (document.fonts && document.fonts.ready) document.fonts.ready.then(refit);
                            
                            termEl.addEventListener('click', () => terminal.focus());
                            current = { terminal, termEl, fitAddon, state };
                            applyTheme();
                            
                            // Watch for dark mode toggles
                            try {
                                const obs = new MutationObserver(() => { applyTheme(); });
                                obs.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
                                const mq = window.matchMedia('(prefers-color-scheme: dark)');
                                if (mq && mq.addEventListener) { mq.addEventListener('change', applyTheme); }
                                setTimeout(applyTheme, 50);
                                setTimeout(applyTheme, 250);
                            } catch (e) {}
                            
                            setTimeout(() => { if (needsWelcomeMessage()) writeWelcome(); }, 100);
                        } catch (e) {
                            console.error('Failed to initialize terminal:', e);
                        }
                    }

                    function refresh() {
                        try {
                            if (!current || !current.terminal || !current.state) return;
                            if (isRefreshing) return;
                            isRefreshing = true;
                            const { terminal, state } = current;
                            state.currentCommand = '';
                            terminal.write('\x1b[2J\x1b[3J\x1b[H');
                            terminal.write('\x1b[2K\r');
                            state.showPrompt();
                            terminal.focus();
                            setTimeout(() => { isRefreshing = false; }, 50);
                        } catch (e) { isRefreshing = false; }
                    }

                    async function clearAndRun(command) {
                        try {
                            if (!current || !current.terminal || !current.state) return;
                            await runCommand('clear');
                            await runCommand(command);
                        } catch (e) {}
                    }

                    function insertCommand(command) {
                        try {
                            if (!current || !current.terminal || !current.state) return;
                            current.state.currentCommand = String(command ?? '');
                            current.state.refreshPrompt();
                            current.terminal.focus();
                        } catch (e) {}
                    }

                    async function runCommand(command) {
                        try {
                            if (!current || !current.terminal || !current.state) return;
                            const cmd = String(command ?? '').trim();
                            if (!cmd) return;
                            current.state.currentCommand = cmd;
                            current.state.refreshPrompt();
                            const lv = @this;
                            await lv.call('$set', 'data.command', cmd);
                            await lv.call('run');
                            // Fallback: fetch final output snapshot
                            try {
                                const out = await lv.get('data.output');
                                if (out) {
                                    const normalized = String(out).replace(/\r\n/g,'\n').replace(/\r/g,'\n').replace(/\n/g,'\r\n');
                                    current.terminal.write(normalized);
                                }
                            } catch (e) {}
                            current.state.showPrompt && current.state.showPrompt();
                        } catch (e) {}
                    }

                    return { init, insertCommand, runCommand, refresh, clearAndRun };
                })();
            }

            attachGlobalListeners();

            // Stream output via Livewire events (align with original console plugin)
            const attach = () => {
                if (!window.Livewire) return;
                window.Livewire.on('shell-terminal-output', (payload) => {
                    try {
                        const text = String((payload && payload.output) ?? '');
                        if (!text) return;
                        if (current && current.terminal) {
                            const normalized = text.replace(/\r\n/g,'\n').replace(/\r/g,'\n').replace(/\n/g,'\r\n');
                            current.terminal.write(normalized);
                        }
                    } catch (e) { console.error('Output event error:', e); }
                });
                window.Livewire.on('shell-terminal-exit', () => {
                    try { if (current && current.state && current.state.showPrompt) { current.state.showPrompt(); } } catch (e) {}
                });
            };
            if (window.Livewire) attach();
            document.addEventListener('livewire:init', attach, { once: true });
        })();
    </script>
</x-filament::page>
