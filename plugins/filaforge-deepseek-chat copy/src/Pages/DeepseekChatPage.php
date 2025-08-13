<?php

namespace Filaforge\DeepseekChat\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Http;
use Filaforge\DeepseekChat\Models\Conversation;
use Filaforge\DeepseekChat\Pages\Actions\SetApiKey;
use Filament\Actions\Action;

class DeepseekChatPage extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-chat-bubble-oval-left-ellipsis';
    protected string $view = 'deepseek-chat::pages.chat';
    protected static ?string $navigationLabel = 'DeepSeek Chat';
    protected static \UnitEnum|string|null $navigationGroup = null;
    protected static ?int $navigationSort = 10;
    protected static ?string $title = 'DeepSeek Chat';

    public ?string $userInput = '';
    public array $messages = [];
    public ?int $conversationId = null;
    /** @var array<int, array{id:int,title:?string,updated_at:string}> */
    public array $conversationList = [];

    public function mount(): void
    {
        $this->messages = [];
        $this->conversationId = null;
        $this->loadConversations();
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

    public function newConversation(): void
    {
        $this->conversationId = null;
        $this->messages = [];
    }

    public function openConversation(int $id): void
    {
        $userId = (int) auth()->id();
        if (! $userId) return;

        $conv = Conversation::query()
            ->where('user_id', $userId)
            ->find($id);

        if (! $conv) return;

        $this->conversationId = (int) $conv->id;
        $this->messages = (array) $conv->messages;
    }

    public function deleteConversation(int $id): void
    {
        $userId = (int) auth()->id();
        if (! $userId) return;

        $conv = Conversation::query()
            ->where('user_id', $userId)
            ->find($id);

        if (! $conv) return;

        $conv->delete();
        if ($this->conversationId === $id) {
            $this->newConversation();
        }
        $this->loadConversations();
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

        $apiKey = auth()->user()?->deepseek_api_key ?: config('deepseek-chat.api_key');
        $base = rtrim((string) config('deepseek-chat.base_url'), '/');

        if (!$apiKey) {
            $this->messages[] = ['role' => 'assistant', 'content' => 'Missing DeepSeek API key. Set it in config or .env.'];
            $this->dispatch('messageReceived');
            return;
        }

        try {
            $response = Http::withToken($apiKey)
                ->timeout(30)
                ->post($base.'/v1/chat/completions', [
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

    protected function getHeaderActions(): array
    {
        return [
            // Start a fresh conversation
            Action::make('newConversation')
                ->label('New conversation')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->action('newConversation'),

            // Conversations modal toggle
            Action::make('conversations')
                ->label('Conversations')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->modalHeading('Conversations')
                ->modalWidth('lg')
                ->modalSubmitAction(false)
                ->modalContent(fn () => view('deepseek-chat::components.conversations-modal')),

            // Set per-user API key (push far right)
            SetApiKey::make(),
        ];
    }
}
