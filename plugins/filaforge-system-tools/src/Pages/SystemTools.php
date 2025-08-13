<?php

namespace Filaforge\SystemTools\Pages;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ViewErrorBag;

class SystemTools extends Page implements HasForms
{
    use InteractsWithForms;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected string $view = 'system-tools::pages.system-tools';

    protected static ?string $navigationLabel = 'System Tools';

    protected static ?string $title = 'System Tools';

    protected static \UnitEnum|string|null $navigationGroup = 'System';

    public array $data = [];

    public ?int $exitCode = null;

    public function mount(): void
    {
        view()->share('errors', session('errors') ?: new ViewErrorBag());
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        $allowed = collect(config('filament-system-tools.allowed_commands', []))
            ->mapWithKeys(fn ($cmd) => [$cmd => $cmd])
            ->all();

        return $schema
            ->components([
                Select::make('selectedCommand')
                    ->label('Command')
                    ->options($allowed)
                    ->required()
                    ->searchable(),
                TextInput::make('arguments')
                    ->label('Arguments')
                    ->placeholder('--force --queue=default'),
            ])
            ->statePath('data');
    }

    public function run(): void
    {
        $state = $this->form->getState();
        $command = $state['selectedCommand'] ?? null;
        $argsString = $state['arguments'] ?? '';

        if (! $command) {
            Notification::make()->title('Select a command')->danger()->send();
            return;
        }

        $allowedCommands = config('filament-system-tools.allowed_commands', []);
        if (! in_array($command, $allowedCommands, true)) {
            Notification::make()->title('Command not allowed')->danger()->send();
            return;
        }

        $arguments = $this->parseArguments($argsString);
        $this->executeCommand($command, $arguments, $argsString);
    }

    public function runQuick(string $command): void {}

    protected function executeCommand(string $command, array $arguments = [], ?string $argsString = null): void
    {
        try {
            $this->exitCode = Artisan::call($command, $arguments);
            $output = Artisan::output();
        } catch (\Throwable $e) {
            $this->exitCode = 1;
            $output = $e->getMessage();
            Notification::make()->title('Command failed')->danger()->body($e->getMessage())->send();
        }

        $this->form->fill([
            'selectedCommand' => $command,
            'arguments' => $argsString ?? $this->argumentsToString($arguments),
            'output' => $output,
        ]);

        if ($this->exitCode === 0) {
            Notification::make()->title('Command finished')->success()->send();
        }
    }

    protected function argumentsToString(array $arguments): string
    {
        $parts = [];
        foreach ($arguments as $key => $value) {
            if (is_int($key)) {
                $parts[] = (string) $value;
                continue;
            }
            if ($value === true) {
                $parts[] = "--{$key}";
            } elseif ($value !== false && $value !== null) {
                $parts[] = "--{$key}={$value}";
            }
        }
        return implode(' ', $parts);
    }

    protected function parseArguments(?string $args): array
    {
        if (! $args) {
            return [];
        }

        $parts = preg_split('/\s+/', trim($args));
        $parsed = [];
        foreach ($parts as $part) {
            if (str_starts_with($part, '--')) {
                $eqPos = strpos($part, '=');
                if ($eqPos !== false) {
                    $key = substr($part, 2, $eqPos - 2);
                    $value = substr($part, $eqPos + 1);
                    $parsed[$key] = $value;
                } else {
                    $key = ltrim($part, '-');
                    $parsed[$key] = true;
                }
            } else {
                $parsed[] = $part;
            }
        }

        return $parsed;
    }
}


