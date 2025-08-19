<?php

namespace Filaforge\OpensourceChat\Pages;

use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Filaforge\OpensourceChat\Models\Setting;

class OpenSourceSettingsPage extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'OS Chat Settings';
    protected static ?string $title = 'Open Source Chat Settings';
    protected static \UnitEnum|string|null $navigationGroup = 'OS Chat';
    protected static ?int $navigationSort = 51;
    protected string $view = 'opensource-chat::pages.settings';

    public ?string $apiKey = null; // if per-user key becomes needed

    public function mount(): void
    {
        $userId = auth()->id();
        if ($userId) {
            $record = Setting::query()->where('user_id', $userId)->latest('id')->first();
            $this->apiKey = $record->api_key ?? null;
        }
    }

    public function save(): void
    {
        $userId = auth()->id();
        if (! $userId) { return; }

        Setting::updateOrCreate(
            ['user_id' => $userId],
            ['api_key' => $this->apiKey, 'user_id' => $userId]
        );

        Notification::make()->title('Saved')->success()->send();
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return (bool) $user; // adjust with roles if desired (config driven)
    }
}
