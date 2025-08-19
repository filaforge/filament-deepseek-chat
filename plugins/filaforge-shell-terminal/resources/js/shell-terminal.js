/**
 * Filaforge Shell Terminal Plugin
 * Enhanced terminal functionality for FilamentPHP
 */

(function() {
    'use strict';

    // Terminal state management
    class TerminalState {
        constructor() {
            this.currentCommand = '';
            this.commandHistory = [];
            this.historyIndex = -1;
            this.currentPath = '';
            this.isInitialized = false;
        }

        addToHistory(command) {
            if (command && command.trim() && this.commandHistory[this.commandHistory.length - 1] !== command) {
                this.commandHistory.push(command.trim());
                if (this.commandHistory.length > 50) {
                    this.commandHistory = this.commandHistory.slice(-50);
                }
            }
        }

        getHistorySuggestions(partial) {
            if (!partial) return [];
            return this.commandHistory.filter(cmd => 
                cmd.toLowerCase().includes(partial.toLowerCase())
            ).slice(0, 10);
        }

        navigateHistory(direction) {
            if (direction === 'up') {
                if (this.historyIndex === -1) {
                    this.historyIndex = this.commandHistory.length - 1;
                } else if (this.historyIndex > 0) {
                    this.historyIndex--;
                }
            } else if (direction === 'down') {
                if (this.historyIndex >= 0) {
                    if (this.historyIndex < this.commandHistory.length - 1) {
                        this.historyIndex++;
                    } else {
                        this.historyIndex = -1;
                    }
                }
            }
            return this.commandHistory[this.historyIndex] || '';
        }

        resetHistoryIndex() {
            this.historyIndex = -1;
        }
    }

    // Terminal UI management
    class TerminalUI {
        constructor(terminal, state) {
            this.terminal = terminal;
            this.state = state;
            this.lastPromptAt = 0;
        }

        showPrompt() {
            const now = Date.now();
            if (now - this.lastPromptAt < 50) return;
            this.lastPromptAt = now;
            
            this.terminal.write(`\x1b[34madmin@filament\x1b[0m:\x1b[36m~\x1b[0m$ `);
        }

        clearCurrentLine() {
            this.terminal.write('\x1b[2K\x1b[0G');
        }

        refreshPrompt() {
            this.clearCurrentLine();
            this.showPrompt();
            this.terminal.write(this.state.currentCommand);
        }

        writeWelcome() {
            this.terminal.writeln('\x1b[36mWelcome to Filament Shell Terminal\x1b[0m');
            this.terminal.writeln('Tab = completion, ↑/↓ = history, Ctrl+L = clear, Ctrl+C = cancel');
            this.terminal.writeln('');
            this.showPrompt();
        }

        clearScreen() {
            this.terminal.write('\x1b[2J\x1b[3J\x1b[H');
            this.terminal.write('\x1b[2K\r');
            this.showPrompt();
        }

        showError(message) {
            this.terminal.writeln(`\x1b[31mError: ${message}\x1b[0m`);
        }

        showSuccess(message) {
            this.terminal.writeln(`\x1b[32m${message}\x1b[0m`);
        }

        showInfo(message) {
            this.terminal.writeln(`\x1b[36m${message}\x1b[0m`);
        }
    }

    // Command execution handler
    class CommandHandler {
        constructor(terminal, state, ui, livewireComponent) {
            this.terminal = terminal;
            this.state = state;
            this.ui = ui;
            this.livewireComponent = livewireComponent;
        }

        async executeCommand(command) {
            if (!command || !command.trim()) return;

            try {
                // Add to history
                this.state.addToHistory(command);
                this.state.resetHistoryIndex();

                // Execute via Livewire
                if (this.livewireComponent) {
                    await this.livewireComponent.call('$set', 'data.command', command);
                    await this.livewireComponent.call('run');
                } else {
                    this.ui.showError('Livewire component not available');
                }
            } catch (error) {
                console.error('Command execution error:', error);
                this.ui.showError(error.message || 'Unknown error occurred');
            }
        }

        handleTabCompletion(partial) {
            if (!partial || !partial.trim()) return [];

            // Get suggestions from Livewire
            if (this.livewireComponent) {
                this.livewireComponent.call('getTabCompletion', partial)
                    .then(suggestions => {
                        if (suggestions.length === 1) {
                            // Auto-complete
                            const parts = partial.split(' ');
                            parts[parts.length - 1] = suggestions[0];
                            this.state.currentCommand = parts.join(' ');
                            this.ui.refreshPrompt();
                        } else if (suggestions.length > 1) {
                            // Show multiple suggestions
                            this.terminal.writeln('');
                            this.terminal.writeln(suggestions.join('    '));
                            this.ui.showPrompt();
                            this.terminal.write(this.state.currentCommand);
                        }
                    })
                    .catch(error => {
                        console.error('Tab completion error:', error);
                    });
            }
        }
    }

    // Keyboard event handler
    class KeyboardHandler {
        constructor(terminal, state, ui, commandHandler) {
            this.terminal = terminal;
            this.state = state;
            this.ui = ui;
            this.commandHandler = commandHandler;
        }

        handleKeyEvent({ key, domEvent }) {
            const printable = !domEvent.altKey && !domEvent.ctrlKey && !domEvent.metaKey;

            switch (domEvent.keyCode) {
                case 13: // Enter
                    this.handleEnter();
                    break;
                case 8: // Backspace
                    this.handleBackspace();
                    break;
                case 9: // Tab
                    this.handleTab(domEvent);
                    break;
                case 38: // Up arrow
                    this.handleArrowUp();
                    break;
                case 40: // Down arrow
                    this.handleArrowDown();
                    break;
                case 67: // Ctrl+C
                    if (domEvent.ctrlKey) this.handleCtrlC();
                    break;
                case 76: // Ctrl+L
                    if (domEvent.ctrlKey) { domEvent.preventDefault(); this.handleCtrlL(); }
                    break;
                default:
                    if (printable) this.handlePrintable(key);
                    break;
            }
        }

        handleEnter() {
            const command = this.state.currentCommand;
            if (!command.trim()) return;

            this.terminal.writeln('');
            this.commandHandler.executeCommand(command);
            this.state.currentCommand = '';
        }

        handleBackspace() {
            if (this.state.currentCommand.length > 0) {
                this.state.currentCommand = this.state.currentCommand.slice(0, -1);
                this.terminal.write('\b \b');
            }
        }

        handleTab(domEvent) {
            domEvent.preventDefault();
            this.commandHandler.handleTabCompletion(this.state.currentCommand);
        }

        handleArrowUp() {
            const command = this.state.navigateHistory('up');
            this.state.currentCommand = command;
            this.ui.refreshPrompt();
        }

        handleArrowDown() {
            const command = this.state.navigateHistory('down');
            this.state.currentCommand = command;
            this.ui.refreshPrompt();
        }

        handleCtrlC() {
            this.terminal.writeln('^C');
            this.state.currentCommand = '';
            this.ui.showPrompt();
        }

        handleCtrlL() {
            this.ui.clearScreen();
        }

        handlePrintable(key) {
            this.state.currentCommand += key;
            this.terminal.write(key);
        }
    }

    // Theme manager
    class ThemeManager {
        constructor(terminal) {
            this.terminal = terminal;
            this.currentTheme = 'dark';
        }

        applyTheme() {
            try {
                const root = document.querySelector('.ff-shell-terminal');
                if (!root) return;

                const styles = getComputedStyle(root);
                const isDark = document.documentElement.classList.contains('dark') || 
                              document.documentElement.dataset.theme === 'dark';

                const bg = (styles.getPropertyValue('--terminal-bg') || '').trim() || 
                           (isDark ? '#0a0a0c' : '#ffffff');
                const fg = (styles.getPropertyValue('--terminal-fg') || '').trim() || 
                           (isDark ? '#ffffff' : '#1e293b');
                const cursor = (styles.getPropertyValue('--terminal-cursor') || '').trim() || fg;

                this.terminal.options.theme = {
                    background: bg,
                    foreground: fg,
                    cursor: cursor,
                    cursorAccent: cursor
                };

                this.currentTheme = isDark ? 'dark' : 'light';
            } catch (e) {
                console.warn('Theme application failed:', e);
            }
        }

        watchThemeChanges() {
            // Watch for dark mode toggles
            try {
                const obs = new MutationObserver(() => this.applyTheme());
                obs.observe(document.documentElement, { 
                    attributes: true, 
                    attributeFilter: ['class'] 
                });

                // Listen to prefers-color-scheme changes
                const mq = window.matchMedia('(prefers-color-scheme: dark)');
                if (mq && mq.addEventListener) {
                    mq.addEventListener('change', () => this.applyTheme());
                }
            } catch (e) {
                console.warn('Theme watching failed:', e);
            }
        }
    }

    // Main terminal controller
    class ShellTerminal {
        constructor() {
            this.current = null;
            this.isRefreshing = false;
        }

        init(termEl, livewireComponent) {
            if (termEl.dataset.initialized === '1') return;

            try {
                // Initialize terminal
                const terminal = new Terminal({
                    cursorBlink: true,
                    theme: {
                        background: '#0a0a0c',
                        foreground: '#ffffff',
                        cursor: '#ffffff',
                        cursorAccent: '#ffffff'
                    }
                });

                // Load addons
                const fitAddon = new FitAddon.FitAddon();
                const webLinksAddon = new WebLinksAddon.WebLinksAddon();

                terminal.loadAddon(fitAddon);
                terminal.loadAddon(webLinksAddon);

                // Open terminal
                terminal.open(termEl);
                fitAddon.fit();

                // Initialize components
                const state = new TerminalState();
                const ui = new TerminalUI(terminal, state);
                const commandHandler = new CommandHandler(terminal, state, ui, livewireComponent);
                const keyboardHandler = new KeyboardHandler(terminal, state, ui, commandHandler);
                const themeManager = new ThemeManager(terminal);

                // Set up event handlers
                terminal.onKey((event) => keyboardHandler.handleKeyEvent(event));

                // Set up resize handling
                this.setupResizeHandling(terminal, fitAddon, termEl);

                // Initialize UI
                this.initializeUI(terminal, ui, themeManager);

                // Store reference
                termEl._terminal = terminal;
                termEl.dataset.initialized = '1';
                this.current = { 
                    terminal, 
                    termEl, 
                    fitAddon, 
                    state, 
                    ui, 
                    commandHandler, 
                    keyboardHandler, 
                    themeManager 
                };

                state.isInitialized = true;

            } catch (error) {
                console.error('Failed to initialize terminal:', error);
            }
        }

        setupResizeHandling(terminal, fitAddon, termEl) {
            // Window resize
            window.addEventListener('resize', () => {
                if (fitAddon) fitAddon.fit();
            });

            // ResizeObserver for container changes
            const resizeObserver = new ResizeObserver(() => {
                if (fitAddon) fitAddon.fit();
            });

            resizeObserver.observe(termEl);
            
            const containerEl = document.querySelector('.fi-terminal-container');
            if (containerEl) resizeObserver.observe(containerEl);

            // Font loading
            if (document.fonts && document.fonts.ready) {
                document.fonts.ready.then(() => {
                    if (fitAddon) fitAddon.fit();
                });
            }
        }

        initializeUI(terminal, ui, themeManager) {
            // Apply theme
            themeManager.applyTheme();
            themeManager.watchThemeChanges();

            // Show welcome message
            setTimeout(() => {
                if (this.needsWelcomeMessage(terminal)) {
                    ui.writeWelcome();
                }
            }, 100);

            // Focus terminal
            setTimeout(() => terminal.focus(), 150);
        }

        needsWelcomeMessage(terminal) {
            try {
                if (!terminal || !terminal.buffer || !terminal.buffer.active) return true;
                
                const lineCount = terminal.buffer.active.length;
                if (lineCount === 0) return true;
                
                for (let i = 0; i < Math.min(lineCount, 5); i++) {
                    const line = terminal.buffer.active.getLine(i);
                    if (line && line.translateToString().trim()) return false;
                }
                return true;
            } catch (e) {
                return true;
            }
        }

        refresh() {
            try {
                if (!this.current || !this.current.terminal || !this.current.ui) return;
                if (this.isRefreshing) return;

                this.isRefreshing = true;
                this.current.ui.clearScreen();
                this.current.terminal.focus();
                
                setTimeout(() => { this.isRefreshing = false; }, 50);
            } catch (e) {
                this.isRefreshing = false;
            }
        }

        async clearAndRun(command) {
            try {
                if (!this.current || !this.current.ui || !this.current.commandHandler) return;
                await this.current.commandHandler.executeCommand('clear');
                await this.current.commandHandler.executeCommand(command);
            } catch (e) {
                console.error('Clear and run failed:', e);
            }
        }

        insertCommand(command) {
            try {
                if (!this.current || !this.current.state || !this.current.ui || !this.current.terminal) return;
                this.current.state.currentCommand = String(command ?? '');
                this.current.ui.refreshPrompt();
                this.current.terminal.focus();
            } catch (e) {
                console.error('Insert command failed:', e);
            }
        }

        async runCommand(command) {
            try {
                if (!this.current || !this.current.commandHandler) return;
                await this.current.commandHandler.executeCommand(command);
            } catch (e) {
                console.error('Run command failed:', e);
            }
        }
    }

    // Global initialization
    function attachGlobalListeners() {
        if (window.FilaShellTerminal) return;
        
        window.FilaShellTerminal = new ShellTerminal();
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', attachGlobalListeners);
    } else {
        attachGlobalListeners();
    }

})();
