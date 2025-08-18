<?php

namespace Filaforge\HuggingfaceChat\Pages;

use Filament\Pages\Page;
use Filament\Notifications\Notification;

class HfSettingsPage extends Page
{
	protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-cog-6-tooth';
	protected string $view = 'huggingface-chat::pages.settings';
	protected static ?string $navigationLabel = 'HF Settings';
	protected static \UnitEnum|string|null $navigationGroup = null;
	protected static ?int $navigationSort = 11;
	protected static ?string $title = 'Hugging Face Settings';

	public ?string $apiKey = null;

	public function mount(): void
	{
		$this->apiKey = auth()->user()?->hf_api_key;
	}

	public function save(): void
	{
		$user = auth()->user();
		if (! $user) { return; }
		$user->forceFill(['hf_api_key' => $this->apiKey])->save();

		Notification::make()->title('Saved')->success()->send();
	}

	public static function canAccess(): bool
	{
		$user = auth()->user();
		if (! $user) return false;
		$allowed = (array) config('hf-chat.allow_roles', []);
		if (empty($allowed)) { return true; }
		if (method_exists($user, 'hasAnyRole')) { return $user->hasAnyRole($allowed); }
		$role = data_get($user, 'role');
		return $role ? in_array($role, $allowed, true) : false;
	}
}





