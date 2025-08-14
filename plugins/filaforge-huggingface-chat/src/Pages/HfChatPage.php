<?php

namespace Filaforge\HuggingfaceChat\Pages;

use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Filaforge\HuggingfaceChat\Models\Conversation;

class HfChatPage extends Page implements Tables\Contracts\HasTable
{
	use Tables\Concerns\InteractsWithTable;
	protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-chat-bubble-oval-left-ellipsis';
	protected string $view = 'huggingface-chat::pages.chat';
	protected static ?string $navigationLabel = 'HF Chat';
	protected static \UnitEnum|string|null $navigationGroup = 'System';
	protected static ?int $navigationSort = 10;
	protected static ?string $title = 'HF Chat';

	public ?string $userInput = '';
	public array $messages = [];
	public ?int $conversationId = null;
	/** @var array<int, array{id:int,title:?string,updated_at:string}> */
	public array $conversationList = [];
	/** @var array<int, array{id:int,title:?string,updated_at:string,user_id:int,user_name:string}> */
	public array $allConversationList = [];
	public bool $canViewAllChats = false;

	public function mount(): void
	{
		$this->messages = [];
		$this->conversationId = null;
		$this->canViewAllChats = $this->canViewAllChats();
		$this->loadConversations();
		if ($this->canViewAllChats) {
			$this->loadAllConversations();
		}
	}

	public function table(Table $table): Table
	{
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
				Action::make('open')->label('Open Chat')->icon('heroicon-o-eye')->color('gray')->action(function (Conversation $record): void {
					$this->openConversation((int) $record->id);
					$this->dispatch('toggle-chats');
				}),
				Action::make('delete')->label('Delete')->icon('heroicon-o-trash')->color('danger')->requiresConfirmation()->action(function (Conversation $record): void {
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

	protected function loadAllConversations(): void
	{
		if (! $this->canViewAllChats) {
			$this->allConversationList = [];
			return;
		}

		$this->allConversationList = Conversation::query()
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
			->all();
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

	public function send(): void
	{
		$content = trim((string) $this->userInput);
		if ($content === '') { return; }

		$this->messages[] = ['role' => 'user', 'content' => $content];
		$this->userInput = '';
		$this->dispatch('messageSent');

		$apiKey = auth()->user()?->hf_api_key ?: config('hf-chat.api_key');
		$base = rtrim((string) config('hf-chat.base_url'), '/');
		$model = (string) config('hf-chat.model_id', 'meta-llama/Meta-Llama-3-8B-Instruct');

		if (!$apiKey) {
			$this->messages[] = ['role' => 'assistant', 'content' => 'Missing Hugging Face API token. Set HF_API_TOKEN or save it in your profile.'];
			$this->dispatch('messageReceived');
			return;
		}

		$prompt = $this->buildPrompt($this->messages);

		try {
			$response = Http::withToken($apiKey)
				->timeout((int) config('hf-chat.timeout', 60))
				->post("{$base}/models/{$model}", [
					'inputs' => $prompt,
					'parameters' => [
						'temperature' => 0.7,
						'max_new_tokens' => 512,
						'return_full_text' => false,
					],
				]);

			if ($response->failed()) {
				$this->messages[] = ['role' => 'assistant', 'content' => 'HF API error: '.$response->status().' '.$response->body()];
				$this->dispatch('messageReceived');
				return;
			}

			$data = $response->json();
			$reply = is_array($data)
				? (string) data_get($data, '0.generated_text', 'No response.')
				: (string) data_get($data, 'generated_text', 'No response.');
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
		return [];
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
}


