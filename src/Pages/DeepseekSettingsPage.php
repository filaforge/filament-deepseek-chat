<?php

namespace Filaforge\DeepseekChat\Pages;

use Filament\Pages\Page;
use Filament\Notifications\Notification;

class DeepseekSettingsPage extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected string $view = 'deepseek-chat::pages.settings';
    protected static ?string $navigationLabel = 'DeepSeek Settings';
    protected static \UnitEnum|string|null $navigationGroup = null;
    protected static ?int $navigationSort = 11;
    protected static ?string $title = 'DeepSeek Settings';

    public ?string $apiKey = null;

    public function mount(): void
    {
        $this->apiKey = auth()->user()?->deepseek_api_key;
    }

    public function save(): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $user->forceFill(['deepseek_api_key' => $this->apiKey])->save();

        Notification::make()
            ->title('Saved')
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
