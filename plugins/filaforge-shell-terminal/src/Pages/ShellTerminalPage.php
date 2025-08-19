<?php

namespace Filaforge\ShellTerminal\Pages;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\Process\Process;

class ShellTerminalPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-command-line';

    protected static ?string $navigationLabel = 'Shell Terminal';

    protected static ?string $title = 'Shell Terminal';

    protected static \UnitEnum|string|null $navigationGroup = 'System';

    protected string $view = 'shell-terminal::pages.terminal';

    public array $data = [];

    /**
     * @var list<array{command:string, exit:int|null, output:string, timestamp:string}>
     */
    public array $history = [];

    public ?int $exitCode = null;

    public string $currentWorkingDirectory = '';

    public array $commandHistory = [];

    public int $historyIndex = -1;

    public function mount(): void
    {
        if (!config('shell-terminal.enabled', true)) {
            abort(403, 'Shell terminal is disabled.');
        }
        $this->updateCurrentWorkingDirectory();
        $this->loadCommandHistory();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('command')
                    ->label('Command')
                    ->placeholder('Enter command (e.g., php artisan migrate)')
                    ->required()
                    ->autocomplete(false)
                    ->extraAlpineAttributes([
                        'x-on:keydown.ctrl.enter.prevent' => '$wire.run()',
                        'x-on:keydown.enter.prevent' => '$wire.run()',
                    ]),
                
                Textarea::make('output')
                    ->rows(1)
                    ->disabled()
                    ->dehydrated(false)
                    ->hidden(),
            ])
            ->statePath('data');
    }

    public function run(): void
    {
        // Rate limiting
        $rateLimitKey = 'shell-terminal-commands:' . auth()->id();
        $maxAttempts = config('shell-terminal.rate_limit', 60);
        
        if (RateLimiter::tooManyAttempts($rateLimitKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            Notification::make()
                ->title('Rate limit exceeded')
                ->body("Too many commands. Try again in {$seconds} seconds.")
                ->danger()
                ->send();
            return;
        }

        RateLimiter::hit($rateLimitKey, 60); // 1 minute window

        $state = $this->form->getState();
        $full = trim((string) ($state['command'] ?? ''));

        if (empty($full)) {
            Notification::make()
                ->title('Command required')
                ->body('Please enter a command to execute.')
                ->warning()
                ->send();
            return;
        }

        // Security checks
        if (!$this->isCommandAllowed($full)) {
            Notification::make()
                ->title('Command not allowed')
                ->body('This command is not allowed for security reasons.')
                ->danger()
                ->send();
            return;
        }

        try {
            $this->executeCommand($full);
        } catch (\Exception $e) {
            Log::error('Shell terminal command execution failed', [
                'command' => $full,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            Notification::make()
                ->title('Command execution failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function executeCommand(string $command): void
    {
        // Clear previous output buffer
        $this->data['output'] = '';

        $timeout = (float) (config('shell-terminal.command_timeout', 300) ?? 300);
        $process = Process::fromShellCommandline($command, $this->currentWorkingDirectory, null, null, $timeout);

        $buffered = '';
        $process->run(function ($type, $buffer) use (&$buffered) {
            // Accumulate output and stream to browser via Livewire event
            $this->data['output'] = ($this->data['output'] ?? '') . $buffer;
            $buffered .= $buffer;
            // Flush in chunks to reduce event spam
            if (strlen($buffered) >= 1024) {
                try { $this->dispatch('shell-terminal-output', output: $buffered); } catch (\Throwable $e) {}
                $buffered = '';
            }
        });
        if ($buffered !== '') {
            try { $this->dispatch('shell-terminal-output', output: $buffered); } catch (\Throwable $e) {}
        }

        $this->exitCode = $process->getExitCode();

        // Add to history
        $this->addToHistory($command, $this->exitCode, $this->data['output'] ?? '');
        
        // Save to command history
        $this->addToCommandHistory($command);

        // Final output snapshot dispatch (for clients not handling stream chunks)
        $finalOutput = (string) ($this->data['output'] ?? '');
        try {
            // Livewire v3 browser event
            $this->dispatch('shell-terminal-output', output: $finalOutput);
        } catch (\Throwable $e) {}

        try {
            // Compatibility with original plugin listeners
            $this->dispatch('terminal.output', command: $command, output: $finalOutput, exit: $this->exitCode, path: $this->getCurrentPath());
        } catch (\Throwable $e) {}

        // Notify completion (optional)
        try { $this->dispatch('shell-terminal-exit', code: $this->exitCode ?? 0); } catch (\Throwable $e) {}

        if ($this->exitCode === 0) {
            Notification::make()
                ->title('Command executed successfully')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Command failed')
                ->body("Exit code: {$this->exitCode}")
                ->warning()
                ->send();
        }
    }

    protected function isCommandAllowed(string $command): bool
    {
        $disallowedCommands = config('shell-terminal.disallowed_commands', [
            'rm -rf /',
            'dd if=/dev/zero of=/dev/sda',
            'mkfs.ext4 /dev/sda',
            'fdisk /dev/sda',
            'mount',
            'umount',
            'chmod 777',
            'chown root',
            'passwd',
            'useradd',
            'userdel',
            'groupadd',
            'groupdel',
            'visudo',
            'crontab',
            'systemctl',
            'service',
            'init',
            'telinit',
            'shutdown',
            'reboot',
            'halt',
            'poweroff',
        ]);

        $commandLower = strtolower($command);
        
        foreach ($disallowedCommands as $disallowed) {
            if (str_contains($commandLower, strtolower($disallowed))) {
                return false;
            }
        }

        // Check for dangerous patterns (shell piping)
        $dangerousPatterns = [
            '/\|\s*sh\s*$/',
            '/\|\s*bash\s*$/',
            '/\|\s*zsh\s*$/',
            '/\|\s*fish\s*$/',
            '/\|\s*ksh\s*$/',
            '/\|\s*tcsh\s*$/',
            '/\|\s*dash\s*$/',
            '/\|\s*ash\s*$/',
        ];

        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $command)) {
                return false;
            }
        }

        return true;
    }

    protected function addToHistory(string $command, ?int $exitCode, string $output): void
    {
        $this->history[] = [
            'command' => $command,
            'exit' => $exitCode,
            'output' => $output,
            'timestamp' => now()->toISOString(),
        ];

        // Keep only last 100 commands
        if (count($this->history) > 100) {
            $this->history = array_slice($this->history, -100);
        }
    }

    protected function addToCommandHistory(string $command): void
    {
        if (!in_array($command, $this->commandHistory)) {
            $this->commandHistory[] = $command;
            
            // Keep only last 50 unique commands
            if (count($this->commandHistory) > 50) {
                $this->commandHistory = array_slice($this->commandHistory, -50);
            }
        }
    }

    protected function updateCurrentWorkingDirectory(): void
    {
        $this->currentWorkingDirectory = getcwd() ?: base_path();
    }

    protected function loadCommandHistory(): void
    {
        // Load from session or database if needed
        $this->commandHistory = session('shell_terminal_history', []);
    }

    public function getTabCompletion(string $partial): array
    {
        $partial = trim($partial);
        if (empty($partial)) {
            return [];
        }

        $suggestions = [];
        
        // File/directory completion
        if (str_ends_with($partial, '/') || !str_contains($partial, ' ')) {
            $path = $partial;
            if (!str_starts_with($path, '/')) {
                $path = $this->currentWorkingDirectory . '/' . $path;
            }
            
            $dir = dirname($path);
            $file = basename($path);
            
            if (is_dir($dir)) {
                $files = scandir($dir);
                foreach ($files as $f) {
                    if ($f !== '.' && $f !== '..' && str_starts_with($f, $file)) {
                        $suggestions[] = $f;
                    }
                }
            }
        }

        // Command completion
        if (!str_contains($partial, '/') && !str_contains($partial, ' ')) {
            $commands = ['php', 'artisan', 'composer', 'npm', 'git', 'ls', 'cd', 'pwd', 'cat', 'grep', 'find'];
            foreach ($commands as $cmd) {
                if (str_starts_with($cmd, $partial)) {
                    $suggestions[] = $cmd;
                }
            }
        }

        return array_unique($suggestions);
    }

    public function getCurrentPath(): string
    {
        return $this->currentWorkingDirectory;
    }

    public function clearOutput(): void
    {
        $this->data['output'] = '';
        $this->exitCode = null;
    }

    public function getHistory(): array
    {
        return $this->history;
    }

    public function getCommandHistory(): array
    {
        return $this->commandHistory;
    }
}
