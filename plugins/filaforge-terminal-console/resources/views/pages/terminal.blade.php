@php($errors ??= new \Illuminate\Support\ViewErrorBag)
<x-filament::page>
    <!-- Xterm.js CSS and JS (UMD builds) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/xterm@5.3.0/css/xterm.css" />
    <script src="https://cdn.jsdelivr.net/npm/xterm@5.3.0/lib/xterm.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xterm-addon-fit@0.8.0/lib/xterm-addon-fit.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xterm-addon-web-links@0.9.0/lib/xterm-addon-web-links.js"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&display=swap');
        .fi-terminal-container { border: 1px solid rgba(48,54,61,.8); border-radius: 12px; overflow: hidden; min-height: 60vh; }
        .xterm { font-family: 'JetBrains Mono','Fira Code',monospace !important; font-size: 14px !important; line-height: 1.4 !important; padding: 16px !important; }
        .xterm .xterm-viewport { background: transparent !important; }
        .xterm .xterm-screen   { background: transparent !important; }
    /* Preset buttons spacing without relying on Tailwind utilities */
    .fi-preset-btn { margin-right: 10px; }
    </style>

    @php($presets = config('terminal.presets', []))

    @if(!empty($presets))
    <div x-data="{ active: 'all' }">
        <x-filament::section>
            <x-slot name="heading">
                @php($__iconMap = [
                    'laravel' => 'heroicon-o-rocket-launch',
                    'git' => 'heroicon-o-command-line',
                    'system' => 'heroicon-o-cpu-chip',
                    'files' => 'heroicon-o-folder',
                    'file' => 'heroicon-o-folder',
                    'folders' => 'heroicon-o-folder',
                    'composer' => 'heroicon-o-cube',
                    'node' => 'heroicon-o-bolt',
                    'artisan' => 'heroicon-o-sparkles',
                    'database' => 'heroicon-o-circle-stack',
                    'data' => 'heroicon-o-circle-stack',
                ])
                @php($__keys = array_keys($presets))
                @php($__keySlugs = array_map(fn($k) => \Illuminate\Support\Str::slug($k), $__keys))
        <div class="flex flex-wrap items-center gap-2 px-2 md:px-4">
                    <button type="button"
                        class="fi-btn fi-preset-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-gray fi-btn-color-gray fi-size-sm fi-btn-size-sm gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-white text-gray-950 hover:bg-gray-50 focus-visible:ring-gray-500/50 dark:bg-white/5 dark:text-white dark:hover:bg-white/10 dark:focus-visible:ring-gray-400/50 ring-1 ring-gray-950/10 dark:ring-white/20"
                        @click="active = (active === 'all' ? 'none' : 'all')"
                        :class="active === 'all' ? 'bg-danger-600 text-white hover:bg-danger-700 dark:hover:bg-danger-500' : ''"
                    >
                        <x-filament::icon x-show="active !== 'all'" icon="heroicon-o-squares-2x2" class="h-4 w-4" aria-hidden="true" />
                        <x-filament::icon x-show="active === 'all'" icon="heroicon-o-x-mark" class="h-4 w-4" aria-hidden="true" />
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
                            class="fi-btn fi-preset-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-gray fi-btn-color-gray fi-size-sm fi-btn-size-sm gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-white text-gray-950 hover:bg-gray-50 focus-visible:ring-gray-500/50 dark:bg-white/5 dark:text-white dark:hover:bg-white/10 dark:focus-visible:ring-gray-400/50 ring-1 ring-gray-950/10 dark:ring-white/20"
                            @click="active = '{{ $__slug }}'"
                            :class="active === '{{ $__slug }}' ? 'bg-primary-600 text-white hover:bg-primary-700 dark:hover:bg-primary-500' : ''"
                        >
                            <x-filament::icon :icon="$__icon" class="h-4 w-4" aria-hidden="true" />
                            <span>{{ $__display }}</span>
                        </button>
                    @endforeach
                    @php($__extras = [
                        'Composer' => 'composer',
                        'Folders' => 'folders',
                        'Node NPM' => 'node',
                        'Docker' => 'docker',
                        'Database' => 'database',
                    ])
                    @foreach($__extras as $__label => $__key)
                        @if(!in_array($__key, $__keySlugs))
                            @php($__slug = \Illuminate\Support\Str::slug($__label))
                            @php($__icon = $__iconMap[$__key] ?? 'heroicon-o-cog-6-tooth')
                            <button type="button"
                                class="fi-btn fi-preset-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-gray fi-btn-color-gray fi-size-sm fi-btn-size-sm gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-white text-gray-950 hover:bg-gray-50 focus-visible:ring-gray-500/50 dark:bg-white/5 dark:text-white dark:hover:bg-white/10 dark:focus-visible:ring-gray-400/50 ring-1 ring-gray-950/10 dark:ring-white/20"
                                @click="active = '{{ $__key }}'"
                                :class="active === '{{ $__key }}' ? 'bg-primary-600 text-white hover:bg-primary-700 dark:hover:bg-primary-500' : ''"
                            >
                                <x-filament::icon :icon="$__icon" class="h-4 w-4" aria-hidden="true" />
                                <span>{{ $__label }}</span>
                            </button>
                        @endif
                    @endforeach
                </div>
            </x-slot>

            <div class="space-y-4">
                @foreach($presets as $group => $items)
                    @php($groupSlug = \Illuminate\Support\Str::slug($group))
                        <div x-show="active !== 'none' && (active === 'all' || active === '{{ $groupSlug }}' || active === '{{ $group }}' @if($groupSlug==='system') || active === 'files' || active === 'file' || active === 'folders' @endif )" x-cloak
                         class="fi-section rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
                        <div class="fi-section-content p-4">
                            <div class="flex flex-wrap gap-2">
                                @foreach($items as $item)
                                    <button
                                        type="button"
                                        title="{{ $item['description'] ?? ($item['command'] ?? '') }}"
                                        class="fi-btn fi-preset-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-gray fi-btn-color-gray fi-size-sm fi-btn-size-sm gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-white text-gray-950 hover:bg-gray-50 focus-visible:ring-gray-500/50 dark:bg-white/5 dark:text-white dark:hover:bg-white/10 dark:focus-visible:ring-gray-400/50 ring-1 ring-gray-950/10 dark:ring-white/20"
                                        data-command="{{ $item['command'] ?? '' }}"
                                        onclick="if(window.FilaTerminal && window.FilaTerminal.insertCommand){window.FilaTerminal.insertCommand(this.dataset.command)}"
                                    >
                                        <span class="fi-btn-label">{{ $item['label'] ?? 'Command' }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-filament::section>
    </div>
    @endif

    <x-filament::section>
        <x-slot name="heading">Terminal Console</x-slot>
        <x-slot name="description">🟢 Connected to ({{ gethostname() }})</x-slot>

        <div class="fi-terminal-container">
            <div id="terminal" wire:ignore x-data="{}"
                 x-init="(() => { const lw = @this; let t=0; const boot=()=>{ if(window.FilaTerminal&&window.FilaTerminal.init){ window.FilaTerminal.init($el,lw);} else if(t++<60){ setTimeout(boot,50);} }; boot(); })()"
                 style="height:60vh;"></div>
        </div>
    </x-filament::section>

    <script>
    window.FilaTerminal = window.FilaTerminal || (function () {
        let listenersAttached = false;
        let current = null; // { terminal, termEl, fitAddon, state }

        function attachGlobalListeners() {
            if (listenersAttached) return; listenersAttached = true;
            const attach = () => {
                if (!window.Livewire) return;
                window.Livewire.on('terminal.output', (payload) => {
                    if (!current) return; const { terminal, state } = current; const { command, output, path } = payload || {};
                    if (command) terminal.writeln(`$ ${command}`);
                    if (typeof output === 'string' && output.length) {
                        const text = (output ?? '').toString();
                        const normalized = text.replace(/\r\n/g,'\n').replace(/\r/g,'\n').replace(/\n/g,'\r\n');
                        terminal.write(normalized);
                        if (!output.endsWith('\n') && !output.endsWith('\r')) terminal.write('\r\n');
                    }
                    if (path) state.currentPath = path; state.showPrompt();
                });
                window.Livewire.on('terminal.clear', (payload) => {
                    if (!current) return; const { terminal, state } = current; const { path } = payload || {};
                    terminal.clear(); if (path) state.currentPath = path; state.showPrompt();
                });
            };
            if (window.Livewire) attach();
            document.addEventListener('livewire:init', attach, { once: true });
        }

        function init(el, livewireComponent) {
            const termEl = el; if (!termEl || termEl._terminal) return;
            const Terminal = window.Terminal; const FitAddon = window.FitAddon && window.FitAddon.FitAddon; const WebLinksAddon = window.WebLinksAddon && window.WebLinksAddon.WebLinksAddon;
            if (!Terminal) { setTimeout(() => init(termEl, livewireComponent), 50); return; }

            const terminal = new Terminal({
                theme: { background: 'transparent', foreground: '#f8f8f2', cursor: '#58a6ff', cursorAccent: '#58a6ff' },
                fontFamily: '\"JetBrains Mono\", \"Fira Code\", monospace', fontSize: 14, lineHeight: 1.4, cursorBlink: true, cursorStyle: 'block', scrollback: 1000, tabStopWidth: 4
            });
            const fitAddon = FitAddon ? new FitAddon() : null; const webLinksAddon = WebLinksAddon ? new WebLinksAddon() : null;
            if (fitAddon) terminal.loadAddon(fitAddon); if (webLinksAddon) terminal.loadAddon(webLinksAddon);
            terminal.open(termEl); if (fitAddon) fitAddon.fit(); termEl._terminal = terminal; termEl.dataset.initialized = '1';

            let state = {
                currentCommand: '', commandHistory: [], historyIndex: -1,
                currentPath: '{{ $this->getCurrentPath() }}',
                showPrompt: () => { terminal.write(`\x1b[34mfilaforge@terminal\x1b[0m:\x1b[36m${state.currentPath}\x1b[0m$ `); },
                clearCurrentLine: () => { terminal.write('\x1b[2K\x1b[0G'); },
                refreshPrompt: () => { state.clearCurrentLine(); state.showPrompt(); terminal.write(state.currentCommand); },
            };

            const writeWelcome = () => {
                terminal.writeln('\x1b[36mWelcome to Filament Terminal\x1b[0m');
                terminal.writeln('Type commands here. Tab = completion, ↑/↓ = history, Ctrl+L = clear, Ctrl+C = cancel');
                terminal.writeln(''); state.showPrompt();
            };
            const needsWelcomeMessage = () => { try { if (!terminal || !terminal.buffer || !terminal.buffer.active) return true; const lineCount = terminal.buffer.active.length; if (lineCount === 0) return true; for (let i=0; i<Math.min(lineCount,5); i++){ const line = terminal.buffer.active.getLine(i); if (line && line.translateToString().trim()) return false; } return true; } catch(e){ return true; } };
            if (fitAddon) { requestAnimationFrame(() => setTimeout(() => { if (needsWelcomeMessage()) writeWelcome(); }, 0)); } else { if (needsWelcomeMessage()) writeWelcome(); }
            setTimeout(() => terminal.focus(), 150);

            terminal.onKey(({ key, domEvent }) => {
                const printable = !domEvent.altKey && !domEvent.ctrlKey && !domEvent.metaKey;
                if (domEvent.keyCode === 13) {
                    (async () => { const command = state.currentCommand; if (!command.trim()) return; if (state.commandHistory[state.commandHistory.length - 1] !== command) state.commandHistory.push(command); state.historyIndex = -1; terminal.writeln(''); try { if (!livewireComponent) { terminal.writeln('\x1b[31mError: Livewire component not available\x1b[0m'); state.showPrompt(); return; } await livewireComponent.call('$set','data.command',command); await livewireComponent.call('run'); } catch (error) { console.error('Terminal command error:', error); terminal.writeln(`\x1b[31mError: ${error.message}\x1b[0m`); state.showPrompt(); } state.currentCommand = ''; })();
                } else if (domEvent.keyCode === 8) {
                    if (state.currentCommand.length > 0) { state.currentCommand = state.currentCommand.slice(0,-1); terminal.write('\b \b'); }
                } else if (domEvent.keyCode === 9) {
                    domEvent.preventDefault(); (async () => { if (!state.currentCommand.trim()) return; try { if (!livewireComponent) return; const suggestions = await livewireComponent.call('getTabCompletion', state.currentCommand); if (suggestions.length === 1) { const parts = state.currentCommand.split(' '); parts[parts.length - 1] = suggestions[0]; state.currentCommand = parts.join(' '); state.refreshPrompt(); } else if (suggestions.length > 1) { terminal.writeln(''); terminal.writeln(suggestions.join('    ')); state.showPrompt(); terminal.write(state.currentCommand); } } catch (e) { console.error('Tab completion error:', e); } })();
                } else if (domEvent.keyCode === 38) {
                    if (state.commandHistory.length > 0) { if (state.historyIndex === -1) state.historyIndex = state.commandHistory.length - 1; else if (state.historyIndex > 0) state.historyIndex--; state.currentCommand = state.commandHistory[state.historyIndex] || ''; state.refreshPrompt(); }
                } else if (domEvent.keyCode === 40) {
                    if (state.historyIndex >= 0) { if (state.historyIndex < state.commandHistory.length - 1) { state.historyIndex++; state.currentCommand = state.commandHistory[state.historyIndex]; } else { state.historyIndex = -1; state.currentCommand = ''; } state.refreshPrompt(); }
                } else if (domEvent.keyCode === 67 && domEvent.ctrlKey) {
                    terminal.writeln('^C'); state.currentCommand = ''; state.showPrompt();
                } else if (domEvent.keyCode === 76 && domEvent.ctrlKey) {
                    terminal.clear(); state.showPrompt();
                } else if (printable) {
                    state.currentCommand += key; terminal.write(key);
                }
            });

            window.addEventListener('resize', () => { if (fitAddon) fitAddon.fit(); });
            const resizeObserver = new ResizeObserver(() => { if (fitAddon) fitAddon.fit(); }); resizeObserver.observe(termEl);
            const containerEl = document.querySelector('.fi-terminal-container'); if (containerEl) resizeObserver.observe(containerEl);
            const refit = () => { if (fitAddon) { try { fitAddon.fit(); } catch (e) {} } }; if (document.fonts && document.fonts.ready) document.fonts.ready.then(refit);
            termEl.addEventListener('click', () => terminal.focus());
            current = { terminal, termEl, fitAddon, state };
            setTimeout(() => { if (needsWelcomeMessage()) writeWelcome(); }, 100);
        }

        attachGlobalListeners();

        function insertCommand(command){ try { if (!current||!current.terminal||!current.state) return; current.state.currentCommand = String(command ?? ''); current.state.refreshPrompt(); current.terminal.focus(); } catch(e){} }
        async function runCommand(command){ try { if (!current||!current.terminal||!current.state) return; const cmd = String(command ?? '').trim(); if(!cmd) return; current.state.currentCommand = cmd; current.state.refreshPrompt(); const lv = @this; await lv.call('$set','data.command',cmd); await lv.call('run'); } catch(e){} }
        return { init, insertCommand, runCommand };
    })();
    </script>
</x-filament::page>
