<?php

namespace Filaforge\OpensourceChat\Pages;

use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Filaforge\OpensourceChat\Models\ModelProfile;
use Filaforge\OpensourceChat\Models\OschatModel;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Actions\Action as TableAction;

class OsModelsPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationLabel = 'OS Models';
    protected static ?string $title = 'OS Models';
    protected static \UnitEnum|string|null $navigationGroup = 'OS Chat';
    protected static ?int $navigationSort = 61;
    protected string $view = 'opensource-chat::pages.models';

    public function table(Table $table): Table
    {
        return $table
            ->query(OschatModel::query()->orderBy('provider')->orderBy('name'))
            ->columns([
                TextColumn::make('provider')->label('Provider')->sortable()->searchable(),
                TextColumn::make('name')->label('Name')->sortable()->searchable()->wrap(),
                TextColumn::make('model_id')->label('Model ID')->sortable()->searchable(),
                TextColumn::make('base_url')->label('Base URL')->wrap()->searchable(),
                BooleanColumn::make('is_active')->label('Active')->sortable(),
                TextColumn::make('created_at')->label('Created')->dateTime()->sortable(),
            ])
            ->actions([
                TableAction::make('create_profile')
                    ->label('Create Profile')
                    ->icon('heroicon-o-plus')
                    ->action(function (OschatModel $record) {
                        $this->createProfile($record->provider, $record->model_id);
                    }),
            ])
            ->bulkActions([]);
    }

    public function createProfile(string $provider, string $modelId): void
    {
        try {
            $exists = ModelProfile::query()->where('provider', $provider)->where('model_id', $modelId)->exists();
            if ($exists) {
                Notification::make()->title('Profile exists')->body('A profile for this model already exists.')->warning()->send();
                return;
            }

            ModelProfile::create([
                'name' => sprintf('%s (%s)', $modelId, ucfirst($provider)),
                'provider' => $provider,
                'model_id' => $modelId,
                'base_url' => null,
                'api_key' => null,
                'stream' => true,
                'timeout' => 60,
                'system_prompt' => null,
                'is_active' => true,
            ]);

            Notification::make()->title('Profile created')->success()->send();
            $this->dispatchBrowserEvent('filament-refresh');
        } catch (\Throwable $e) {
            Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
        }
    }

    public static function canAccess(): bool
    {
        return (bool) auth()->user();
    }
}
