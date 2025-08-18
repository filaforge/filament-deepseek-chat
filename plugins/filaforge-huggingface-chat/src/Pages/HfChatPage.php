<?php

namespace Filaforge\HuggingfaceChat\Pages;

use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Filaforge\HuggingfaceChat\Models\Conversation;
use Filaforge\HuggingfaceChat\Models\Setting;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

class HfChatPage extends Page implements Tables\Contracts\HasTable, HasForms
{
	use Tables\Concerns\InteractsWithTable;
	use InteractsWithForms;
	protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-chat-bubble-oval-left-ellipsis';
	protected string $view = 'huggingface-chat::pages.chat';
	protected static ?string $navigationLabel = 'HF Chat';
	protected static \UnitEnum|string|null $navigationGroup = 'System';
	protected static ?int $navigationSort = 10;
	protected static ?string $title = 'HF Chat';

	public ?string $userInput = '';
	public array $messages = [];
	public ?int $conversationId = null;
    public ?string $hfApiKey = null;
    public ?string $selectedModelId = null;
    public array $settings = [];
	public array $settingsForm = [];
	public ?array $settingsData = [];
    public string $viewMode = 'chat';
	/** @var array<int, array{id:int,title:?string,updated_at:string}> */
	public array $conversationList = [];
	public bool $canViewAllChats = false; // admin flag

	public function mount(): void
	{
		$this->messages = [];
		$this->conversationId = null;
		$this->canViewAllChats = $this->canViewAllChats();
		$this->hfApiKey = auth()->user()?->hf_api_key;
        $this->selectedModelId = (string) config('hf-chat.model_id', 'meta-llama/Meta-Llama-3-8B-Instruct');
        $this->loadSettings();
		$this->settingsForm = $this->settings; // legacy compatibility
		$this->loadConversations();
		if ($this->canViewAllChats) {
			$this->loadAllConversations();
		}
	}

	public function table(Table $table): Table
	{
		// Conversations table only (settings now use a dedicated Filament form)
        $query = Conversation::query();

        if (! $this->canViewAllChats) {
            $userId = (int) auth()->id();
            $query->where('user_id', $userId);
        } else {
            $query->with('user:id,name');
        }

        $expr = $this->jsonArrayLengthExpr('messages');
        if ($expr) {
            $query->select($query->getModel()->getTable() . '.*')
                ->selectRaw($expr . ' as messages_count');
        }

        $query->latest('updated_at');

        return $table
            ->query($query)
            ->defaultPaginationPageOption(10)
            ->columns([
                TextColumn::make('title')->label('Title')->searchable()->sortable()->limit(60)->wrap(false),
                TextColumn::make('user.name')->label('User')->visible($this->canViewAllChats),
                TextColumn::make('messages_count')->label('Messages')->visible((bool) $expr)->sortable(['messages_count'])->formatStateUsing(fn ($state) => (string) ($state ?? 0)),
                TextColumn::make('updated_at')->label('Updated')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('user_id')->label('User')->relationship('user', 'name')->visible($this->canViewAllChats),
                TernaryFilter::make('has_replies')->label('Has replies')->queries(
                    true: function (Builder $q) use ($expr): Builder { return $expr ? $q->whereRaw($expr . ' > 1') : $q; },
                    false: function (Builder $q) use ($expr): Builder { return $expr ? $q->whereRaw($expr . ' <= 1') : $q; }
                ),
            ])
            ->actions([
                Action::make('open')
                    ->label('Open Chat')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->action(function (Conversation $record): void {
                        $this->openConversation((int) $record->id);
                        $this->showChat();
                    }),
                Action::make('delete')
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Conversation $record): void {
                        if ($this->canViewAllChats) { $this->adminDeleteConversation((int) $record->id); } else { $this->deleteConversation((int) $record->id); }
                    }),
            ])
            ->striped()
            ->emptyStateHeading('No chats found.');
	}

	private function jsonArrayLengthExpr(string $column): ?string
	{
		$driver = DB::connection()->getDriverName();
		return match ($driver) {
			'mysql' => "JSON_LENGTH($column)",
			'pgsql' => "json_array_length(($column)::json)",
			'sqlite' => "json_array_length($column)",
			default => null,
		};
	}

	protected function loadConversations(): void
	{
		$userId = (int) auth()->id();
		if (! $userId) {
			$this->conversationList = [];
			return;
		}

		$this->conversationList = Conversation::query()
			->where('user_id', $userId)
			->latest('updated_at')
			->limit(50)
			->get(['id', 'title', 'updated_at'])
			->map(fn ($c) => [
				'id' => (int) $c->id,
				'title' => (string) ($c->title ?? 'Untitled'),
				'updated_at' => (string) $c->updated_at,
			])
			->all();
	}

	public function loadAllConversations(): void
	{
		if (! $this->canViewAllChats) {
			return;
		}

		Conversation::query()
			->with('user:id,name')
			->latest('updated_at')
			->limit(200)
			->get(['id', 'title', 'updated_at', 'user_id'])
			->map(function ($c) {
				return [
					'id' => (int) $c->id,
					'title' => (string) ($c->title ?? 'Untitled'),
					'updated_at' => (string) $c->updated_at,
					'user_id' => (int) $c->user_id,
					'user_name' => (string) optional($c->user)->name ?? 'User #'.$c->user_id,
				];
			})
			->all(); // side-effect intentionally discarded (was previously stored)
	}


	public function newConversation(): void
	{
		$this->conversationId = null;
		$this->messages = [];
	}

	public function openConversation(int $id): void
	{
		$userId = (int) auth()->id();
		if (! $userId) return;

		$query = Conversation::query();
		if (! $this->canViewAllChats) {
			$query->where('user_id', $userId);
		}
		$conv = $query->find($id);

		if (! $conv) return;

		$this->conversationId = (int) $conv->id;
		$this->messages = (array) $conv->messages;
	}

	public function deleteConversation(int $id): void
	{
		$userId = (int) auth()->id();
		if (! $userId) return;

		$query = Conversation::query()->where('user_id', $userId);
		$conv = $query->find($id);

		if (! $conv) return;

		$conv->delete();
		if ($this->conversationId === $id) {
			$this->newConversation();
		}
		$this->loadConversations();
		if ($this->canViewAllChats) {
			$this->loadAllConversations();
		}
	}

	public function adminDeleteConversation(int $id): void
	{
		if (! $this->canViewAllChats) return;

		$conv = Conversation::find($id);
		if (! $conv) return;

		$conv->delete();
		if ($this->conversationId === $id) {
			$this->newConversation();
		}
		$this->loadConversations();
		$this->loadAllConversations();
	}

	public function renameConversation(int $id, string $title): void
	{
		$userId = (int) auth()->id();
		if (! $userId) return;

		$title = trim($title);
		if ($title === '') return;

		$conv = Conversation::query()->where('user_id', $userId)->find($id);
		if (! $conv) return;

		$conv->update(['title' => str($title)->limit(60)]);
		$this->loadConversations();
	}

	public function saveApiKey(string $hf_api_key): void
	{
		$user = auth()->user();
		if (! $user) return;
		$user->forceFill(['hf_api_key' => $hf_api_key])->save();
	}

    public function saveSettings(): void
    {
        $this->saveApiKey((string) $this->hfApiKey);
    }

    public function loadSettings(): void
    {
        $userId = (int) auth()->id();
        $record = Setting::query()->where('user_id', $userId)->latest('id')->first();
        $this->settings = [
            'model_id' => (string) (data_get($record, 'model_id') ?? config('hf-chat.model_id')),
            'base_url' => (string) (data_get($record, 'base_url') ?? config('hf-chat.base_url')),
            'use_openai' => (bool) (data_get($record, 'use_openai') ?? config('hf-chat.use_openai')),
            'stream' => (bool) (data_get($record, 'stream') ?? config('hf-chat.stream')),
            'timeout' => (int) (data_get($record, 'timeout') ?? config('hf-chat.timeout')),
            'system_prompt' => (string) (data_get($record, 'system_prompt') ?? config('hf-chat.system_prompt')),
        ];
        $this->selectedModelId = (string) $this->settings['model_id'];
    }

    public function showSettings(): void
    {
        $this->loadSettings();
        $this->settingsForm = $this->settings;
		$this->settingsData = $this->settings + ['hf_api_key' => $this->hfApiKey];
		// Fill Filament form state if initialized
		if (method_exists($this, 'form')) { $this->form->fill($this->settingsData); }
        $this->viewMode = 'settings';
    }

    public function showChat(): void
    {
        $this->viewMode = 'chat';
    }

	public function showConversations(): void
	{
		$this->viewMode = 'conversations';
	}

    public function newChatFromInput(): void
    {
        $this->newConversation();
        $this->showChat();
    }

    public function saveSettingsForm(): void
    {
		$userId = (int) auth()->id();
		if (! $userId) { return; }
		// Prefer Filament form state if available
		$formState = $this->settingsData ?? $this->settingsForm;
		$payload = [
			'model_id' => (string) data_get($formState, 'model_id'),
			'base_url' => (string) data_get($formState, 'base_url'),
			'use_openai' => (bool) data_get($formState, 'use_openai', true),
			'stream' => (bool) data_get($formState, 'stream', false),
			'timeout' => (int) data_get($formState, 'timeout', 60),
			'system_prompt' => (string) data_get($formState, 'system_prompt', ''),
		];
		Setting::updateOrCreate(['user_id' => $userId], $payload + ['user_id' => $userId]);
		$this->settings = $payload + ['user_id' => $userId];
		$this->selectedModelId = (string) $payload['model_id'];
		$apiKey = (string) data_get($formState, 'hf_api_key', (string) $this->hfApiKey);
		if ($apiKey !== '') { $this->hfApiKey = $apiKey; $this->saveApiKey($apiKey); }
		$this->viewMode = 'chat';
    }

	protected function getFormSchema(): array
	{
		return [
			Forms\Components\TextInput::make('model_id')->label('Model ID')->required()->maxLength(190),
			Forms\Components\TextInput::make('base_url')->label('Base URL')->placeholder('https://api-inference.huggingface.co')->columnSpanFull(),
			Forms\Components\Toggle::make('use_openai')->label('Use OpenAI Compatible API')->default(true),
			Forms\Components\Toggle::make('stream')->label('Stream Responses'),
			Forms\Components\TextInput::make('timeout')->label('Timeout (s)')->numeric()->default(60)->minValue(5)->maxValue(600)->columnSpan(1),
			Forms\Components\Textarea::make('system_prompt')->label('System Prompt')->rows(3)->columnSpanFull(),
			Forms\Components\TextInput::make('hf_api_key')->label('HuggingFace API Key')->password()->revealable()->columnSpanFull(),
		];
	}

	protected function getFormStatePath(): string
	{
		return 'settingsData';
	}

	public function send(): void
	{
		$content = trim((string) $this->userInput);
		if ($content === '') { return; }

		$this->messages[] = ['role' => 'user', 'content' => $content];
		$this->userInput = '';
		$this->dispatch('messageSent');

        $apiKey = auth()->user()?->hf_api_key ?: config('hf-chat.api_key');
        $base = rtrim((string) ($this->settings['base_url'] ?? config('hf-chat.base_url')), '/');
        $model = trim((string) ($this->selectedModelId ?: ($this->settings['model_id'] ?? config('hf-chat.model_id', 'meta-llama/Meta-Llama-3-8B-Instruct'))));

		if (!$apiKey) {
			$this->messages[] = ['role' => 'assistant', 'content' => 'Missing Hugging Face API token. Set HF_API_TOKEN or save it in your profile.'];
			$this->dispatch('messageReceived');
			return;
		}

		$prompt = $this->buildPrompt($this->messages);

		try {
            $endpointBase = $base;
            $useOpenAi = (bool) ($this->settings['use_openai'] ?? config('hf-chat.use_openai', true));
			$isDedicatedEndpoint = str_contains($endpointBase, 'endpoints.huggingface');
			$urlPrimary = $useOpenAi
				? rtrim($endpointBase, '/') . '/v1/chat/completions'
				: ($isDedicatedEndpoint ? $endpointBase : rtrim($endpointBase, '/') . '/models/' . $model);

			$req = Http::withToken($apiKey)
				->acceptJson()
				->timeout((int) config('hf-chat.timeout', 60))
				;

			$payload = $useOpenAi
				? [
					'model' => $model,
					'messages' => $this->buildOpenAiMessages($this->messages, (string) config('hf-chat.system_prompt', 'You are a helpful assistant.')),
					'max_tokens' => 512,
					'temperature' => 0.7,
				]
				: [
					'inputs' => $prompt,
					'parameters' => [
						'temperature' => 0.7,
						'max_new_tokens' => 512,
						'return_full_text' => false,
					],
					'options' => [
						'wait_for_model' => true,
						'use_cache' => false,
					],
				];

			$response = $req->post($urlPrimary, $payload);

			// Fallbacks on 404:
			// 1) If OpenAI path failed, try the standard Inference API models endpoint with non-OpenAI payload
			if ($response->status() === 404 && $useOpenAi) {
				$response = Http::withToken($apiKey)
					->acceptJson()
					->timeout((int) config('hf-chat.timeout', 60))
					->post(rtrim($endpointBase, '/') . '/models/' . $model, [
						'inputs' => $prompt,
						'parameters' => [
							'temperature' => 0.7,
							'max_new_tokens' => 512,
							'return_full_text' => false,
						],
						'options' => [
							'wait_for_model' => true,
							'use_cache' => false,
						],
					]);
			}

			// 2) If still 404 and not dedicated endpoint, try the pipeline path
			if ($response->status() === 404 && ! $isDedicatedEndpoint) {
				$response = Http::withToken($apiKey)
					->acceptJson()
					->timeout((int) config('hf-chat.timeout', 60))
					->post(rtrim($endpointBase, '/') . '/pipeline/text-generation/' . $model, [
						'inputs' => $prompt,
						'parameters' => [
							'temperature' => 0.7,
							'max_new_tokens' => 512,
							'return_full_text' => false,
						],
						'options' => [
							'wait_for_model' => true,
							'use_cache' => false,
						],
					]);
			}

			if ($response->failed()) {
				$body = (string) $response->body();
				$this->messages[] = ['role' => 'assistant', 'content' => 'HF API error: '.$response->status().' '.str($body)->limit(300)];
				$this->dispatch('messageReceived');
				return;
			}

			$data = $response->json();
			$reply = $useOpenAi
				? (string) data_get($data, 'choices.0.message.content', 'No response.')
				: (is_array($data)
					? (string) data_get($data, '0.generated_text', 'No response.')
					: (string) data_get($data, 'generated_text', 'No response.'));
			$this->messages[] = ['role' => 'assistant', 'content' => $reply];

			$user = auth()->user();
			if ($user) {
				if ($this->conversationId) {
					$conv = Conversation::find($this->conversationId);
					if ($conv && $conv->user_id === $user->id) {
						$conv->update(['messages' => $this->messages]);
					}
				} else {
					$conv = Conversation::create([
						'user_id' => $user->id,
						'title' => str($content)->limit(60),
						'messages' => $this->messages,
					]);
					$this->conversationId = $conv->id;
				}
				$this->loadConversations();
			}

			$this->dispatch('messageReceived');
		} catch (\Throwable $e) {
			$this->messages[] = ['role' => 'assistant', 'content' => 'Request failed: '.$e->getMessage()];
			$this->dispatch('messageReceived');
		}
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

	protected function canViewAllChats(): bool
	{
		$user = auth()->user();
		if (! $user) return false;

		$adminRoles = (array) config('hf-chat.admin_roles', []);
		if (! empty($adminRoles)) {
			if (method_exists($user, 'hasAnyRole')) {
				if ($user->hasAnyRole($adminRoles)) return true;
			}
			$role = data_get($user, 'role');
			if ($role && in_array($role, $adminRoles, true)) return true;
		}

		if (property_exists($user, 'is_admin') && (bool) $user->is_admin) return true;
		return false;
	}

	protected function getHeaderActions(): array
	{
		return [
			Action::make('hf_conversations_btn')
				->label('Conversations')
				->icon('heroicon-o-table-cells')
				->color('gray')
				->action(fn () => $this->showConversations())
				->extraAttributes(['id' => 'hf-conversations-btn', 'wire:key' => 'hf-conversations-btn', 'type' => 'button']),
			Action::make('hf_settings_btn')
				->label('Settings')
				->icon('heroicon-o-cog-6-tooth')
				->color('gray')
				->action(fn () => $this->showSettings())
				->extraAttributes(['id' => 'hf-settings-btn', 'wire:key' => 'hf-settings-btn', 'type' => 'button']),
			Action::make('hf_new_chat_btn')
				->label('New Chat')
				->icon('heroicon-o-plus')
				->color('primary')
				->action(function (): void { $this->newConversation(); $this->showChat(); })
				->extraAttributes(['id' => 'hf-new-chat-btn', 'wire:key' => 'hf-new-chat-btn', 'type' => 'button']),
		];
	}

	protected function buildPrompt(array $messages): string
	{
		$lines = [];
		foreach ($messages as $m) {
			$role = $m['role'] === 'assistant' ? 'Assistant' : 'User';
			$lines[] = "$role: {$m['content']}";
		}
		$lines[] = 'Assistant:';
		return implode("\n", $lines);
	}

	/**
	 * Build OpenAI-compatible messages array for HF v1/chat/completions.
	 * @param array<int,array{role:string,content:string}> $messages
	 * @return array<int,array{role:string,content:string}>
	 */
	protected function buildOpenAiMessages(array $messages, string $systemPrompt): array
	{
		$out = [];
		if ($systemPrompt !== '') {
			$out[] = ['role' => 'system', 'content' => $systemPrompt];
		}
		foreach ($messages as $m) {
			$role = $m['role'] === 'assistant' ? 'assistant' : 'user';
			$out[] = ['role' => $role, 'content' => (string) $m['content']];
		}
		return $out;
	}
}


