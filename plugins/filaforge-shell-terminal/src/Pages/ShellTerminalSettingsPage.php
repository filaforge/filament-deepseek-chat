<?php

namespace Filaforge\ShellTerminal\Pages;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;

class ShellTerminalSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Shell Settings';
    
    protected static ?string $title = 'Shell Settings';

    protected static \UnitEnum|string|null $navigationGroup = 'System';

    protected string $view = 'shell-terminal::pages.settings';

    public array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->getSettings());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General Settings')
                    ->description('Configure basic terminal behavior')
                    ->schema([
                        Toggle::make('enabled')
                            ->label('Enable Terminal')
                            ->helperText('Enable or disable the shell terminal functionality')
                            ->default(true),

                        TextInput::make('rate_limit')
                            ->label('Rate Limit')
                            ->helperText('Maximum number of commands per minute per user')
                            ->numeric()
                            ->default(60)
                            ->minValue(1)
                            ->maxValue(1000),

                        TextInput::make('command_timeout')
                            ->label('Command Timeout')
                            ->helperText('Maximum execution time for commands in seconds')
                            ->numeric()
                            ->default(300)
                            ->minValue(30)
                            ->maxValue(3600),

                        TextInput::make('max_history')
                            ->label('Maximum History')
                            ->helperText('Maximum number of commands to keep in history')
                            ->numeric()
                            ->default(100)
                            ->minValue(10)
                            ->maxValue(1000),
                    ]),

                Section::make('Security Settings')
                    ->description('Configure security restrictions')
                    ->schema([
                        Toggle::make('log_commands')
                            ->label('Log Commands')
                            ->helperText('Log all executed commands for audit purposes')
                            ->default(false),

                        Toggle::make('require_confirmation')
                            ->label('Require Confirmation')
                            ->helperText('Require user confirmation for potentially dangerous commands')
                            ->default(true),

                        Textarea::make('disallowed_commands')
                            ->label('Disallowed Commands')
                            ->helperText('One command per line. These commands will be blocked.')
                            ->rows(10)
                            ->default("rm -rf /\ndd if=/dev/zero of=/dev/sda\nmkfs.ext4 /dev/sda\nfdisk /dev/sda\nmount\numount\nchmod 777\nchown root\npasswd\nuseradd\nuserdel\ngroupadd\ngroupdel\nvisudo\ncrontab\nsystemctl\nservice\ninit\ntelinit\nshutdown\nreboot\nhalt\npoweroff"),

                        Textarea::make('allowed_directories')
                            ->label('Allowed Directories')
                            ->helperText('One directory per line. Commands can only be executed in these directories.')
                            ->rows(5)
                            ->default(base_path()),
                    ]),

                Section::make('Display Settings')
                    ->description('Configure terminal appearance and behavior')
                    ->schema([
                        Toggle::make('show_welcome_message')
                            ->label('Show Welcome Message')
                            ->helperText('Display welcome message when terminal starts')
                            ->default(true),

                        Toggle::make('enable_tab_completion')
                            ->label('Enable Tab Completion')
                            ->helperText('Enable command and file name completion with Tab key')
                            ->default(true),

                        Toggle::make('enable_command_history')
                            ->label('Enable Command History')
                            ->helperText('Remember and allow navigation through command history')
                            ->default(true),

                        TextInput::make('terminal_height')
                            ->label('Terminal Height')
                            ->helperText('Height of the terminal in viewport height units (vh)')
                            ->numeric()
                            ->default(60)
                            ->minValue(30)
                            ->maxValue(90),

                        Toggle::make('dark_mode')
                            ->label('Dark Mode')
                            ->helperText('Use dark theme for the terminal')
                            ->default(true),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        
        // Validate and save settings
        $this->saveSettings($data);
        
        // Clear caches
        Cache::forget('shell_terminal_settings');
        
        Notification::make()
            ->title('Settings saved successfully')
            ->success()
            ->send();
    }

    protected function getSettings(): array
    {
        return [
            'enabled' => config('shell-terminal.enabled', true),
            'rate_limit' => config('shell-terminal.rate_limit', 60),
            'command_timeout' => config('shell-terminal.command_timeout', 300),
            'max_history' => config('shell-terminal.max_history', 100),
            'log_commands' => config('shell-terminal.log_commands', false),
            'require_confirmation' => config('shell-terminal.require_confirmation', true),
            'disallowed_commands' => implode("\n", config('shell-terminal.disallowed_commands', [])),
            'allowed_directories' => implode("\n", config('shell-terminal.allowed_directories', [base_path()])),
            'show_welcome_message' => config('shell-terminal.show_welcome_message', true),
            'enable_tab_completion' => config('shell-terminal.enable_tab_completion', true),
            'enable_command_history' => config('shell-terminal.enable_command_history', true),
            'terminal_height' => config('shell-terminal.terminal_height', 60),
            'dark_mode' => config('shell-terminal.dark_mode', true),
        ];
    }

    protected function saveSettings(array $data): void
    {
        // Convert textarea values back to arrays
        $disallowedCommands = array_filter(explode("\n", $data['disallowed_commands']));
        $allowedDirectories = array_filter(explode("\n", $data['allowed_directories']));
        
        // Update config values (namespaced to avoid conflicts with the original console plugin)
        config([
            'shell-terminal.enabled' => $data['enabled'],
            'shell-terminal.rate_limit' => (int) $data['rate_limit'],
            'shell-terminal.command_timeout' => (int) $data['command_timeout'],
            'shell-terminal.max_history' => (int) $data['max_history'],
            'shell-terminal.log_commands' => $data['log_commands'],
            'shell-terminal.require_confirmation' => $data['require_confirmation'],
            'shell-terminal.disallowed_commands' => $disallowedCommands,
            'shell-terminal.allowed_directories' => $allowedDirectories,
            'shell-terminal.show_welcome_message' => $data['show_welcome_message'],
            'shell-terminal.enable_tab_completion' => $data['enable_tab_completion'],
            'shell-terminal.enable_command_history' => $data['enable_command_history'],
            'shell-terminal.terminal_height' => (int) $data['terminal_height'],
            'shell-terminal.dark_mode' => $data['dark_mode'],
        ]);
        
        // Save to cache for immediate use
        Cache::put('shell_terminal_settings', $data, 3600);
    }
}
