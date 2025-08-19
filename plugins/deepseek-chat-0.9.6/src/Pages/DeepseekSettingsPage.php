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
            'allow_roles' => $this->formatAllowRolesForForm($settings->allow_roles),
        ]);
    }

    protected function formatAllowRolesForForm(?array $roles): string
    {
        if (empty($roles)) {
            return '';
        }
        return implode(', ', $roles);
    }

    protected function parseAllowRolesFromForm(string $rolesString): array
    {
        if (empty(trim($rolesString))) {
            return [];
        }

        $roles = explode(',', $rolesString);
        return array_map('trim', array_filter($roles));
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

                        TextInput::make('allow_roles')
                            ->label('Allowed Roles')
                            ->placeholder('admin,staff,user')
                            ->helperText('Comma-separated list of roles that can access DeepSeek Chat. Leave empty to allow all authenticated users.')
                            ->columnSpanFull(),
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

        // Parse allow_roles from comma-separated string to array
        if (isset($data['allow_roles'])) {
            $data['allow_roles'] = $this->parseAllowRolesFromForm($data['allow_roles']);
        }

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

        return DeepseekSetting::userHasAccess($user->id);
    }
}
