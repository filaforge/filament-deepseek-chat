<?php

namespace Filaforge\OpensourceChat\Pages;

use Filament\Pages\Page;
use Filament\Notifications\Notification;

class OpenSourceSettingsPage extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'OS Chat Settings';
    protected static ?string $title = 'Open Source Chat Settings';
    protected static ?int $navigationSort = 51;
    protected string $view = 'opensource-chat::pages.settings';

    public ?string $apiKey = null; // if per-user key becomes needed

    public function mount(): void
    {
        $this->apiKey = auth()->user()?->oschat_api_key; // placeholder column if added later
    }

    public function save(): void
    {
        $user = auth()->user();
        if (! $user) { return; }
        $user->forceFill(['oschat_api_key' => $this->apiKey])->save();
        Notification::make()->title('Saved')->success()->send();
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return (bool) $user; // adjust with roles if desired (config driven)
    }
}
