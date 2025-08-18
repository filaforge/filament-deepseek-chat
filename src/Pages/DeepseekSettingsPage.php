<?php

namespace Filaforge\DeepseekChat\Pages;

use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filaforge\DeepseekChat\Models\DeepseekSetting;

class DeepseekSettingsPage extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected string $view = 'deepseek-chat::pages.settings';
    protected static ?string $navigationLabel = 'DeepSeek Settings';
    protected static \UnitEnum|string|null $navigationGroup = null;
    protected static ?int $navigationSort = 11;
    protected static ?string $title = 'DeepSeek Settings';

    public ?array $data = [];

    public function mount(): void
    {
        $userId = (int) auth()->id();
        if (!$userId) return;

        $settings = DeepseekSetting::forUser($userId);
        $this->form->fill([
            'api_key' => $settings->api_key,
            'base_url' => $settings->base_url,
            'stream' => $settings->stream,
            'timeout' => $settings->timeout,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('API Configuration')
                    ->description('Configure your DeepSeek API settings')
                    ->schema([
                        Textarea::make('api_key')
                            ->label('API Key')
                            ->placeholder('Enter your DeepSeek API key')
                            ->rows(3)
                            ->required()
                            ->helperText('Your DeepSeek API key. Keep this secure.')
                            ->columnSpanFull(),
                        
                        TextInput::make('base_url')
                            ->label('Base URL')
                            ->placeholder('https://api.deepseek.com')
                            ->helperText('The base URL for DeepSeek API calls')
                            ->default('https://api.deepseek.com'),
                        
                        Toggle::make('stream')
                            ->label('Enable Streaming')
                            ->helperText('Enable streaming responses from the API')
                            ->default(false),
                        
                        TextInput::make('timeout')
                            ->label('Timeout (seconds)')
                            ->numeric()
                            ->minValue(10)
                            ->maxValue(300)
                            ->helperText('Request timeout in seconds')
                            ->default(60),
                    ])
                    ->columns(2)
            ]);
    }

    public function save(): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $data = $this->form->getState();
        
        // Get or create settings for the user
        $settings = DeepseekSetting::forUser($user->id);
        $settings->update($data);

        Notification::make()
            ->title('Settings saved successfully')
            ->success()
            ->send();
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (! $user) return false;
        $allowed = (array) config('deepseek-chat.allow_roles', []);
        if (empty($allowed)) {
            return true;
        }
        if (method_exists($user, 'hasAnyRole')) {
            return $user->hasAnyRole($allowed);
        }
        $role = data_get($user, 'role');
        return $role ? in_array($role, $allowed, true) : false;
    }
}
