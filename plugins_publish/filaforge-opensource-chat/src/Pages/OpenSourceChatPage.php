<?php

namespace Filaforge\OpensourceChat\Pages;

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
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filaforge\OpensourceChat\Models\Conversation;
use Filaforge\OpensourceChat\Models\Setting;
use Filaforge\OpensourceChat\Models\ModelProfile;

/**
 * Simplified open source chat page modeled after HuggingFace chat page.
 * Key differences:
 * - Provider-agnostic: uses config('opensource-chat.*')
 * - Profiles / conversations / settings split into modes
 * - Minimal rate limiting (no usage table yet)
 */
class OpenSourceChatPage extends Page implements Tables\Contracts\HasTable, HasForms
{
    use Tables\Concerns\InteractsWithTable;
    use InteractsWithForms;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-chat-bubble-oval-left-ellipsis';
    protected static ?string $navigationLabel = 'OS Chat';
    protected static ?string $title = 'Open Source Chat';
    protected static \UnitEnum|string|null $navigationGroup = 'System';
    protected static ?int $navigationSort = 50;
    protected string $view = 'opensource-chat::pages.chat';

    // Chat state
    public ?string $userInput = '';
    /** @var array<int,array{role:string,content:string}> */
    public array $messages = [];
    public ?int $conversationId = null;
    public ?int $selectedProfileId = null;
    /** @var array<int,array{id:int,name:string,model_id:string,stream:bool,timeout:int}> */
    public array $availableProfiles = [];
    public array $settings = [];
    public array $settingsData = [];
    public string $viewMode = 'chat'; // chat|settings|profiles|conversations
    public array $profileForm = [
        'name' => '',
        'provider' => 'opensource',
        'model_id' => '',
        'base_url' => '',
        'api_key' => '',
        'stream' => true,
        'timeout' => 60,
        'system_prompt' => '',
    ];
    public ?int $editingProfileId = null;
    public bool $showProfileForm = false;
    public array $conversationList = [];
    public bool $canViewAllChats = false; // configurable via roles later

    public function mount(): void
    {
        $this->messages = [];
        $this->conversationId = null;
        $this->canViewAllChats = $this->canViewAllChats();
        $this->loadSettings();
        $this->loadProfiles();
        $this->restoreUserLastProfile();
        $this->loadConversations();
    }

    /* ================= Profiles ================= */
    protected function loadProfiles(): void
    {
        $this->availableProfiles = ModelProfile::query()->orderBy('name')->get([
            'id','name','provider','model_id','stream','timeout'
        ])->map(fn($p)=>[
            'id'=>(int)$p->id,
            'name'=>(string)$p->name,
            'provider'=>(string)$p->provider,
            'model_id'=>(string)$p->model_id,
            'stream'=>(bool)$p->stream,
            'timeout'=>(int)($p->timeout ?? 60),
        ])->all();
    }

    protected function restoreUserLastProfile(): void
    {
        $user = auth()->user();
        if ($user && $user->oschat_last_profile_id) {
            $this->selectedProfileId = (int) $user->oschat_last_profile_id;
        } elseif (!$this->selectedProfileId && !empty($this->availableProfiles)) {
            $this->selectedProfileId = (int) $this->availableProfiles[0]['id'];
        }
    }

    public function updatedSelectedProfileId($value): void
    {
        $user = auth()->user();
        if ($user) {
            $user->forceFill(['oschat_last_profile_id' => $value ?: null])->save();
        }
        $this->newConversation();
    }

    public function showProfiles(): void
    {
        $this->loadProfiles();
        $this->viewMode = 'profiles';
        $this->showProfileForm = false;
        if (method_exists($this, 'resetTable')) { $this->resetTable(); }
    }

    public function newProfile(): void
    {
        $this->editingProfileId = null;
        $this->profileForm = [ 'name'=>'','provider'=>'opensource','model_id'=>'','base_url'=>'','api_key'=>'','stream'=>true,'timeout'=>60,'system_prompt'=>'' ];
        $this->showProfileForm = true;
        $this->viewMode = 'profiles';
    }

    public function cancelProfileForm(): void
    {
        $this->editingProfileId = null;
        $this->showProfileForm = false;
    }

    public function saveProfile(): void
    {
        $data = $this->profileForm;
        $data['name'] = trim((string) $data['name']);
        $data['model_id'] = trim((string) $data['model_id']);
        if ($data['name'] === '' || $data['model_id'] === '') { return; }
        if ($this->editingProfileId) {
            $profile = ModelProfile::find($this->editingProfileId);
            if ($profile) {
                $profile->update([
                    'name' => $data['name'],
                    'provider' => $data['provider'] ?: 'opensource',
                    'model_id' => $data['model_id'],
                    'base_url' => $data['base_url'] ?: null,
                    'api_key' => $data['api_key'] ?: null,
                    'stream' => (bool) $data['stream'],
                    'timeout' => (int) ($data['timeout'] ?: 60),
                    'system_prompt' => $data['system_prompt'] ?: null,
                ]);
            }
        } else {
            $profile = ModelProfile::create([
                'name' => $data['name'],
                'provider' => $data['provider'] ?: 'opensource',
                'model_id' => $data['model_id'],
                'base_url' => $data['base_url'] ?: null,
                'api_key' => $data['api_key'] ?: null,
                'stream' => (bool) $data['stream'],
                'timeout' => (int) ($data['timeout'] ?: 60),
                'system_prompt' => $data['system_prompt'] ?: null,
            ]);
        }
        $this->profileForm = [ 'name'=>'','provider'=>'opensource','model_id'=>'','base_url'=>'','api_key'=>'','stream'=>true,'timeout'=>60,'system_prompt'=>'' ];
        $this->editingProfileId = null;
        $this->showProfileForm = false;
        $this->loadProfiles();
        if (isset($profile) && $profile) { $this->selectedProfileId = (int) $profile->id; }
        $this->restoreUserLastProfile();
    }

    public function editProfile(int $id): void
    {
        $p = ModelProfile::find($id);
        if (! $p) return;
        $this->editingProfileId = (int) $p->id;
        $this->profileForm = [
            'name' => (string)$p->name,
            'provider' => (string)$p->provider,
            'model_id' => (string)$p->model_id,
            'base_url' => (string)($p->base_url ?? ''),
            'api_key' => '',
            'stream' => (bool)$p->stream,
            'timeout' => (int)($p->timeout ?? 60),
            'system_prompt' => (string)($p->system_prompt ?? ''),
        ];
        $this->viewMode = 'profiles';
        $this->showProfileForm = true;
    }

    public function deleteProfile(int $id): void
    {
        $p = ModelProfile::find($id);
        if ($p) { $p->delete(); }
        if ($this->selectedProfileId === $id) { $this->selectedProfileId = null; }
        if ($this->editingProfileId === $id) { $this->editingProfileId = null; }
        $this->loadProfiles();
        $this->restoreUserLastProfile();
    }

    public function setProfile(int $id): void
    {
        if (ModelProfile::query()->whereKey($id)->exists()) {
            $this->selectedProfileId = $id;
            $this->updatedSelectedProfileId($id);
        }
    }

    /* ================= Conversations ================= */
    protected function loadConversations(): void
    {
        $userId = (int) auth()->id();
        if (! $userId) { $this->conversationList = []; return; }
        $this->conversationList = Conversation::query()->where('user_id', $userId)->latest('updated_at')->limit(50)->get(['id','title','updated_at'])->map(fn($c)=>[
            'id'=>(int)$c->id,
            'title'=>(string)($c->title ?? 'Untitled'),
            'updated_at'=>(string)$c->updated_at,
        ])->all();
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
        $conv = Conversation::query()->where('user_id', $userId)->find($id);
        if (! $conv) return;
        $this->conversationId = (int) $conv->id;
        $this->messages = (array) $conv->messages;
    }

    public function deleteConversation(int $id): void
    {
        $userId = (int) auth()->id();
        if (! $userId) return;
        $conv = Conversation::query()->where('user_id', $userId)->find($id);
        if (! $conv) return;
        $conv->delete();
        if ($this->conversationId === $id) { $this->newConversation(); }
        $this->loadConversations();
    }

    /* ================= Settings ================= */
    public function loadSettings(): void
    {
        $userId = (int) auth()->id();
        $record = Setting::query()->where('user_id', $userId)->latest('id')->first();
        $this->settings = [
            'model_id' => (string) (data_get($record,'model_id') ?? config('opensource-chat.default_model_id')),
            'base_url' => (string) (data_get($record,'base_url') ?? config('opensource-chat.base_url')),
            'stream' => (bool) (data_get($record,'stream') ?? config('opensource-chat.stream')),
            'timeout' => (int) (data_get($record,'timeout') ?? config('opensource-chat.timeout')),
            'system_prompt' => (string) (data_get($record,'system_prompt') ?? 'You are a helpful assistant.'),
        ];
    }

    public function showSettings(): void
    {
        $this->loadSettings();
        $this->settingsData = $this->settings;
        if (method_exists($this,'form')) { $this->form->fill($this->settingsData); }
        $this->viewMode = 'settings';
        $this->showProfileForm = false;
    }

    public function showChat(): void
    {
        $this->viewMode = 'chat';
        $this->showProfileForm = false;
    }

    public function showConversations(): void
    {
        $this->viewMode = 'conversations';
        $this->showProfileForm = false;
        if (method_exists($this,'resetTable')) { $this->resetTable(); }
    }

    public function saveSettingsForm(): void
    {
        $userId = (int) auth()->id();
        if (! $userId) return;
        $formState = $this->settingsData;
        $payload = [
            'model_id' => (string) data_get($formState,'model_id'),
            'base_url' => (string) data_get($formState,'base_url'),
            'stream' => (bool) data_get($formState,'stream', false),
            'timeout' => (int) data_get($formState,'timeout', 60),
            'system_prompt' => (string) data_get($formState,'system_prompt', ''),
        ];
        Setting::updateOrCreate(['user_id'=>$userId], $payload + ['user_id'=>$userId]);
        $this->settings = $payload;
        $this->viewMode = 'chat';
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\TextInput::make('model_id')->label('Model ID')->required()->maxLength(190),
            Forms\Components\TextInput::make('base_url')->label('Base URL')->placeholder('https://api.openai.com')->columnSpanFull(),
            Forms\Components\Toggle::make('stream')->label('Stream Responses'),
            Forms\Components\TextInput::make('timeout')->label('Timeout (s)')->numeric()->default(60)->minValue(5)->maxValue(600)->columnSpan(1),
            Forms\Components\Textarea::make('system_prompt')->label('System Prompt')->rows(3)->columnSpanFull(),
        ];
    }

    protected function getFormStatePath(): string
    {
        return 'settingsData';
    }

    /* ================= Sending ================= */
    public function send(): void
    {
        $content = trim((string) $this->userInput);
        if ($content === '') { return; }
        $this->messages[] = ['role'=>'user','content'=>$content];
        $this->userInput = '';
        $this->dispatch('messageSent');

        $base = rtrim((string) ($this->settings['base_url'] ?? config('opensource-chat.base_url')), '/');
        $model = trim((string) ($this->settings['model_id'] ?? config('opensource-chat.default_model_id')));
        $useOpenAi = (bool) config('opensource-chat.use_openai', true);

        // Profile overrides
        $profile = $this->selectedProfileId ? ModelProfile::find($this->selectedProfileId) : null;
        if ($profile) {
            $model = $profile->model_id ?: $model;
            if ($profile->base_url) { $base = rtrim($profile->base_url,'/'); }
            // If provider is ollama and no explicit base given, default to config ollama base
            if (!$profile->base_url && strtolower((string) $profile->provider) === 'ollama') {
                $base = rtrim((string) config('opensource-chat.ollama.base_url', 'http://localhost:11434'), '/');
            }
        }

        $prompt = $this->buildPrompt($this->messages);
        try {
            $isOllama = str_contains($base, 'localhost:11434') || str_contains($base, '127.0.0.1:11434') || ($profile && strtolower((string) $profile->provider) === 'ollama');
            $req = Http::acceptJson()
                ->timeout((int) config('opensource-chat.timeout',120))
                ->connectTimeout((int) config('opensource-chat.connect_timeout',30));

            if ($isOllama) {
                // Ollama local API (/api/chat)
                $endpoint = rtrim($base, '/').'/api/chat';
                $payload = [
                    'model' => $model ?: (string) config('opensource-chat.ollama.default_model_id', 'llama3.1'),
                    'messages' => $this->buildOpenAiMessages($this->messages, (string) ($this->settings['system_prompt'] ?? '')),
                    'stream' => false,
                    'options' => [
                        'temperature' => 0.7,
                        'num_predict' => 512,
                    ],
                ];
                $response = $req->post($endpoint, $payload);
            } else {
                $endpoint = $useOpenAi ? $base.'/v1/chat/completions' : $base.'/models/'.$model;
                $payload = $useOpenAi
                    ? [
                        'model' => $model,
                        'messages' => $this->buildOpenAiMessages($this->messages, (string) ($this->settings['system_prompt'] ?? 'You are a helpful assistant.')),
                        'max_tokens' => 512,
                        'temperature' => 0.7,
                    ]
                    : [
                        'inputs' => $prompt,
                        'parameters' => [
                            'temperature' => 0.7,
                            'max_new_tokens' => 512,
                        ],
                    ];
                $response = $req->post($endpoint, $payload);
            }
            if ($response->failed()) {
                $this->messages[] = ['role'=>'assistant','content'=>'API error: '.$response->status().' '.str($response->body())->limit(200)];
                $this->dispatch('messageReceived');
                return;
            }
            $data = $response->json();
            if ($isOllama) {
                $reply = (string) data_get($data, 'message.content', 'No response.');
            } else {
                $reply = $useOpenAi
                    ? (string) data_get($data,'choices.0.message.content','No response.')
                    : (string) data_get($data,'0.generated_text', data_get($data,'generated_text','No response.'));
            }
            $this->messages[] = ['role'=>'assistant','content'=>$reply];
            $user = auth()->user();
            if ($user) {
                if ($this->conversationId) {
                    $conv = Conversation::find($this->conversationId);
                    if ($conv && $conv->user_id === $user->id) { $conv->update(['messages'=>$this->messages]); }
                } else {
                    $conv = Conversation::create(['user_id'=>$user->id,'title'=>str($content)->limit(60),'messages'=>$this->messages]);
                    $this->conversationId = $conv->id;
                }
                $this->loadConversations();
            }
            $this->dispatch('messageReceived');
        } catch (\Throwable $e) {
            $this->messages[] = ['role'=>'assistant','content'=>'Request failed: '.$e->getMessage()];
            $this->dispatch('messageReceived');
        }
    }

    /* ================= Access Control & UI ================= */
    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (! $user) return false;
        $allowed = (array) config('opensource-chat.allow_roles', []);
        if (empty($allowed)) return true;
        if (method_exists($user,'hasAnyRole')) { return $user->hasAnyRole($allowed); }
        $role = data_get($user,'role');
        return $role ? in_array($role,$allowed,true) : false;
    }

    protected function canViewAllChats(): bool
    {
        $user = auth()->user();
        if (! $user) return false;
        $adminRoles = (array) config('opensource-chat.admin_roles', []);
        if (! empty($adminRoles)) {
            if (method_exists($user,'hasAnyRole') && $user->hasAnyRole($adminRoles)) return true;
            $role = data_get($user,'role');
            if ($role && in_array($role,$adminRoles,true)) return true;
        }
        return false;
    }

    protected function getHeaderActions(): array
    {
        $mode = $this->viewMode;
        return [
            Action::make('oschat_conversations')
                ->label('Conversations')
                ->icon('heroicon-o-table-cells')
                ->color($mode === 'conversations' ? 'primary' : 'gray')
                ->action(fn()=> $this->showConversations()),
            Action::make('oschat_profiles')
                ->label('Profiles')
                ->icon('heroicon-o-cog-8-tooth')
                ->color($mode === 'profiles' ? 'primary' : 'gray')
                ->action(fn()=> $this->showProfiles()),
            Action::make('oschat_settings')
                ->label('Settings')
                ->icon('heroicon-o-cog-6-tooth')
                ->color($mode === 'settings' ? 'primary' : 'gray')
                ->action(fn()=> $this->showSettings()),
            Action::make('oschat_new_chat')
                ->label('New Chat')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->action(function(){ $this->newConversation(); $this->showChat(); }),
        ];
    }

    public function table(Table $table): Table
    {
        if ($this->viewMode === 'profiles') {
            return $table->query(ModelProfile::query()->orderBy('name'))
                ->columns([
                    TextColumn::make('name')->label('Name')->sortable()->searchable(),
                    TextColumn::make('model_id')->label('Model')->limit(40)->tooltip(fn($r)=>$r->model_id),
                    TextColumn::make('provider')->label('Provider')->toggleable(isToggledHiddenByDefault: true),
                    TextColumn::make('updated_at')->since()->label('Updated'),
                ])
                ->actions([
                    Action::make('use')->label('Use')->icon('heroicon-o-check')->color('primary')->action(fn(ModelProfile $r)=>$this->setProfile((int)$r->id)),
                    Action::make('edit')->label('Edit')->icon('heroicon-o-pencil-square')->color('gray')->action(fn(ModelProfile $r)=>$this->editProfile((int)$r->id)),
                    Action::make('delete')->label('Delete')->icon('heroicon-o-trash')->color('danger')->requiresConfirmation()->action(fn(ModelProfile $r)=>$this->deleteProfile((int)$r->id)),
                ])
                ->defaultPaginationPageOption(10)
                ->striped();
        }
        $query = Conversation::query();
        $userId = auth()->id();
        $query->where('user_id',$userId);
        $expr = $this->jsonArrayLengthExpr('messages');
        if ($expr) { $query->select($query->getModel()->getTable().'.*')->selectRaw($expr.' as messages_count'); }
        $query->latest('updated_at');
        return $table->query($query)
            ->columns([
                TextColumn::make('title')->label('Title')->searchable()->sortable()->limit(60),
                TextColumn::make('messages_count')->label('Messages')->visible((bool)$expr)->sortable(['messages_count'])->formatStateUsing(fn($s)=>(string)($s??0)),
                TextColumn::make('updated_at')->label('Updated')->since()->sortable(),
            ])
            ->actions([
                Action::make('open')->label('Open')->icon('heroicon-o-eye')->color('gray')->action(fn(Conversation $r)=>($this->openConversation((int)$r->id) || $this->showChat())),
                Action::make('delete')->label('Delete')->icon('heroicon-o-trash')->color('danger')->requiresConfirmation()->action(fn(Conversation $r)=>$this->deleteConversation((int)$r->id)),
            ])
            ->defaultPaginationPageOption(10)
            ->striped();
    }

    private function jsonArrayLengthExpr(string $column): ?string
    {
        $driver = DB::connection()->getDriverName();
        return match($driver) {
            'mysql' => "JSON_LENGTH($column)",
            'pgsql' => "json_array_length(($column)::json)",
            'sqlite' => "json_array_length($column)",
            default => null,
        };
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
     * @param array<int,array{role:string,content:string}> $messages
     * @return array<int,array{role:string,content:string}>
     */
    protected function buildOpenAiMessages(array $messages, string $systemPrompt): array
    {
        $out = [];
        if ($systemPrompt !== '') { $out[] = ['role'=>'system','content'=>$systemPrompt]; }
        foreach ($messages as $m) {
            $role = $m['role'] === 'assistant' ? 'assistant' : 'user';
            $out[] = ['role'=>$role,'content'=>(string)$m['content']];
        }
        return $out;
    }
}
