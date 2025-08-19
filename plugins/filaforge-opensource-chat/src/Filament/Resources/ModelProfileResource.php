<?php

namespace Filaforge\OpensourceChat\Filament\Resources;

use Filaforge\OpensourceChat\Models\ModelProfile;
use Filaforge\OpensourceChat\Services\ChatApiService;
use Filament\Resources\Resource;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Resources\Pages;
use Filament\Notifications\Notification;

class ModelProfileResource extends Resource
{
    protected static ?string $model = ModelProfile::class;
    protected static ?string $navigationIcon = 'heroicon-o-cog-8-tooth';
    protected static ?string $navigationGroup = 'OS Chat';
    protected static ?int $navigationSort = 60;

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Section::make('Basic Information')
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(190)
                        ->helperText('Friendly name for this model profile'),
                    
                    Select::make('provider')
                        ->required()
                        ->options([
                            'openai' => 'OpenAI',
                            'huggingface' => 'HuggingFace',
                            'ollama' => 'Ollama (Local)',
                        ])
                        ->default('openai')
                        ->reactive()
                        ->afterStateUpdated(fn (callable $set) => $set('model_id', null))
                        ->helperText('Select the AI provider'),
                    
                    Select::make('model_id')
                        ->label('Model')
                        ->required()
                        ->searchable()
                        ->options(function (callable $get) {
                            $provider = $get('provider');
                            return config("opensource-chat.providers.{$provider}.models", []);
                        })
                        ->allowHtml()
                        ->helperText('Select the specific model to use'),
                    
                    Toggle::make('is_active')
                        ->default(true)
                        ->helperText('Enable this profile for use'),
                ])
                ->columns(2),

            Section::make('API Configuration')
                ->schema([
                    TextInput::make('base_url')
                        ->url()
                        ->nullable()
                        ->helperText('Override default API base URL (optional)')
                        ->visible(fn (callable $get) => $get('provider') !== 'ollama'),
                    
                    TextInput::make('api_key')
                        ->password()
                        ->revealable()
                        ->nullable()
                        ->helperText('API key for this provider (leave empty to use global config)')
                        ->visible(fn (callable $get) => $get('provider') !== 'ollama'),
                    
                    TextInput::make('base_url')
                        ->url()
                        ->nullable()
                        ->default('http://localhost:11434')
                        ->helperText('Ollama server URL')
                        ->visible(fn (callable $get) => $get('provider') === 'ollama'),
                ])
                ->columns(2),

            Section::make('Model Settings')
                ->schema([
                    Toggle::make('stream')
                        ->default(true)
                        ->helperText('Enable streaming responses'),
                    
                    TextInput::make('timeout')
                        ->numeric()
                        ->default(60)
                        ->minValue(5)
                        ->maxValue(600)
                        ->suffix('seconds')
                        ->helperText('Request timeout in seconds'),
                    
                    Textarea::make('system_prompt')
                        ->rows(3)
                        ->columnSpanFull()
                        ->helperText('Default system prompt for this model (optional)'),
                ])
                ->columns(2),

            Section::make('Rate Limiting')
                ->schema([
                    TextInput::make('per_minute_limit')
                        ->numeric()
                        ->nullable()
                        ->minValue(1)
                        ->suffix('requests')
                        ->helperText('Maximum requests per minute (optional)'),
                    
                    TextInput::make('per_day_limit')
                        ->numeric()
                        ->nullable()
                        ->minValue(1)
                        ->suffix('requests')
                        ->helperText('Maximum requests per day (optional)'),
                ])
                ->columns(2),

            Section::make('Advanced Settings')
                ->schema([
                    KeyValue::make('extra')
                        ->columnSpanFull()
                        ->reorderable()
                        ->nullable()
                        ->helperText('Additional parameters to send with requests (JSON format)'),
                ])
                ->collapsible(),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable()
                    ->weight('bold'),
                
                BadgeColumn::make('provider')
                    ->colors([
                        'success' => 'openai',
                        'warning' => 'huggingface', 
                        'info' => 'ollama',
                    ])
                    ->icons([
                        'heroicon-o-globe-alt' => 'openai',
                        'heroicon-o-face-smile' => 'huggingface',
                        'heroicon-o-computer-desktop' => 'ollama',
                    ]),
                
                TextColumn::make('model_id')
                    ->label('Model')
                    ->limit(30)
                    ->tooltip(fn($record) => $record->model_id),
                
                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                
                IconColumn::make('stream')
                    ->boolean()
                    ->label('Stream'),
                
                TextColumn::make('per_minute_limit')
                    ->label('Rate/Min')
                    ->default('∞'),
                
                TextColumn::make('updated_at')
                    ->since()
                    ->label('Updated'),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\SelectFilter::make('provider')
                    ->options([
                        'openai' => 'OpenAI',
                        'huggingface' => 'HuggingFace',
                        'ollama' => 'Ollama',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
                Tables\Actions\Action::make('test_connection')
                    ->icon('heroicon-o-signal')
                    ->color('info')
                    ->action(function (ModelProfile $record) {
                        try {
                            $service = new ChatApiService($record);
                            $result = $service->testConnection();
                            
                            if ($result['success']) {
                                Notification::make()
                                    ->title('Connection Test Successful')
                                    ->body('The model profile is working correctly.')
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Connection Test Failed')
                                    ->body($result['message'])
                                    ->danger()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Connection Test Error')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRecords::route('/'),
            'create' => Pages\CreateRecord::route('/create'),
            'edit' => Pages\EditRecord::route('/{record}/edit'),
        ];
    }
}
