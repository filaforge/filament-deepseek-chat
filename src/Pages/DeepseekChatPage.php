<?php

namespace Filaforge\DeepseekChat\Pages;

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
use Filaforge\DeepseekChat\Models\Conversation;
use Filaforge\DeepseekChat\Models\DeepseekSetting;
use Filaforge\DeepseekChat\Pages\Actions\SetApiKey;

class DeepseekChatPage extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-chat-bubble-oval-left-ellipsis';
    protected string $view = 'deepseek-chat::pages.chat';
    protected static ?string $navigationLabel = 'DeepSeek Chat';
    protected static \UnitEnum|string|null $navigationGroup = 'System';
    protected static ?int $navigationSort = 10;
    protected static ?string $title = 'DeepSeek Chat';

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
        // Build a standard Filament table for conversations
    $query = Conversation::query();

        if (! $this->canViewAllChats) {
            $userId = (int) auth()->id();
            $query->where('user_id', $userId);
        } else {
            $query->with('user:id,name');
        }

        // Select a driver-aware messages_count so we can display/sort/filter by it
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
                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable()
                    ->limit(60)
                    ->wrap(false),
                TextColumn::make('user.name')
                    ->label('User')
                    ->visible($this->canViewAllChats),
                TextColumn::make('messages_count')
                    ->label('Messages')
                    ->visible((bool) $expr)
                    ->sortable(['messages_count'])
                    ->formatStateUsing(fn ($state) => (string) ($state ?? 0)),
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name')
                    ->visible($this->canViewAllChats),
                TernaryFilter::make('has_replies')
                    ->label('Has replies')
                    ->queries(
                        true: function (Builder $q) use ($expr): Builder {
                            return $expr
                                ? $q->whereRaw($expr . ' > 1')
                                : $q; // Fallback: skip if expr unsupported
                        },
                        false: function (Builder $q) use ($expr): Builder {
                            return $expr
                                ? $q->whereRaw($expr . ' <= 1')
                                : $q; // Fallback
                        }
                    ),
            ])
            ->actions([
                Action::make('open')
                    ->label('Open Chat')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->action(function (Conversation $record): void {
                        $this->openConversation((int) $record->id);
                        // Close the table view and return to chat (forward to Alpine/window listener)
                        $this->dispatch('toggle-chats');
                    }),
                Action::make('delete')
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Conversation $record): void {
                        if ($this->canViewAllChats) {
                            $this->adminDeleteConversation((int) $record->id);
                        } else {
                            $this->deleteConversation((int) $record->id);
                        }
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

        $conv = Conversation::query()
            ->where('user_id', $userId)
            ->find($id);

        if (! $conv) return;

        $conv->update([
            'title' => str($title)->limit(60),
        ]);

        $this->loadConversations();
    }

    public function saveApiKey(string $apiKey): void
    {
        $user = auth()->user();
        if (! $user) return;

        // Get or create settings for the user
        $settings = DeepseekSetting::forUser($user->id);
        $settings->update(['api_key' => $apiKey]);
    }

    public function send(): void
    {
        $content = trim((string) $this->userInput);
        if ($content === '') {
            return;
        }

        $this->messages[] = ['role' => 'user', 'content' => $content];
        $this->userInput = '';

        // Emit event for frontend typing indicator
        $this->dispatch('messageSent');

        $userId = (int) auth()->id();
        $apiKey = DeepseekSetting::getApiKeyForUser($userId);
        $baseUrl = DeepseekSetting::getBaseUrlForUser($userId);
        $timeout = DeepseekSetting::getTimeoutForUser($userId);

        if (!$apiKey) {
            $this->messages[] = ['role' => 'assistant', 'content' => 'Missing DeepSeek API key. Set it in config or .env.'];
            $this->dispatch('messageReceived');
            return;
        }

        try {
            $response = Http::withToken($apiKey)
                ->timeout($timeout)
                ->post(rtrim($baseUrl, '/').'/v1/chat/completions', [
                    'model' => 'deepseek-chat',
                    'messages' => array_map(fn($m) => ['role' => $m['role'], 'content' => $m['content']], $this->messages),
                    'stream' => false,
                ]);

            if ($response->failed()) {
                $this->messages[] = ['role' => 'assistant', 'content' => 'DeepSeek API error: '.$response->status().' '.$response->body()];
                $this->dispatch('messageReceived');
                return;
            }

            $data = $response->json();
            $reply = (string) data_get($data, 'choices.0.message.content', 'No response.');
            $this->messages[] = ['role' => 'assistant', 'content' => $reply];

            // Persist conversation
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

            // Emit event for frontend to handle completion
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

        return DeepseekSetting::userHasAccess($user->id);
    }

    protected function canViewAllChats(): bool
    {
        $user = auth()->user();
        if (! $user) return false;

        $adminRoles = (array) config('deepseek-chat.admin_roles', []);
        if (! empty($adminRoles)) {
            if (method_exists($user, 'hasAnyRole')) {
                if ($user->hasAnyRole($adminRoles)) return true;
            }
            $role = data_get($user, 'role');
            if ($role && in_array($role, $adminRoles, true)) return true;
        }

        // Fallbacks
        if (property_exists($user, 'is_admin') && (bool) $user->is_admin) return true;
        return false;
    }

    protected function getHeaderActions(): array
    {
        return [
            // Keep registered but hidden; UI buttons live in the page body
            SetApiKey::make()->hidden()
        ];
    }
}
