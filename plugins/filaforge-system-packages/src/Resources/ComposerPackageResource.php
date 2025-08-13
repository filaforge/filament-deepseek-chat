<?php

namespace Filaforge\SystemPackages\Resources;

use Filaforge\SystemPackages\Models\ComposerPackage;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ComposerPackageResource extends Resource
{
    protected static ?string $model = ComposerPackage::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static \UnitEnum|string|null $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'System Packages';

    protected static ?string $modelLabel = 'Composer Package';

    protected static ?string $pluralModelLabel = 'Composer Packages';

    protected static ?string $slug = 'system-packages';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Package Name')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('version')
                    ->label('Version')
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->formatStateUsing(fn (?string $state) => $state ?: 'N/A'),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Core' => 'danger',
                        'Plugin' => 'warning',
                        'Extension' => 'info',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'Core' => 'heroicon-m-heart',
                        'Plugin' => 'heroicon-m-puzzle-piece',
                        'Extension' => 'heroicon-m-cube',
                        default => 'heroicon-m-question-mark-circle',
                    }),

                Tables\Columns\TextColumn::make('vendor')
                    ->label('Vendor')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'filament' => 'info',
                        'filaforge' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => ucfirst($state)),

                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->searchable()
                    ->wrap()
                    ->limit(100)
                    ->formatStateUsing(fn (?string $state) => $state && trim($state) !== '' ? $state : 'No description provided')
                    ->tooltip(function (ComposerPackage $record) {
                        $desc = (string) ($record->description ?? '');
                        return strlen($desc) > 100 ? $desc : null;
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'Core' => 'Core',
                        'Plugin' => 'Plugin',
                        'Extension' => 'Extension',
                        'Unknown' => 'Unknown',
                    ]),

                Tables\Filters\SelectFilter::make('vendor')
                    ->options([
                        'filament' => 'Filament',
                        'filaforge' => 'Filaforge',
                        'laravel' => 'Laravel',
                        'symfony' => 'Symfony',
                    ]),
            ])
            ->actions([
                \Filament\Actions\Action::make('packagist')
                    ->label('Packagist')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn (ComposerPackage $record) => "https://packagist.org/packages/{$record->name}")
                    ->openUrlInNewTab(),
                \Filament\Actions\Action::make('github')
                    ->label('GitHub')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn (ComposerPackage $record) => (string) $record->github_url)
                    ->visible(fn (ComposerPackage $record) => !empty($record->github_url))
                    ->openUrlInNewTab(),
            ])
            ->defaultSort('name')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(10)
            ->searchable()
            ->searchPlaceholder('Search packages...')
            ->emptyStateHeading('No packages found')
            ->heading('Installed Composer Packages');
    }

    public static function getEloquentQuery(): Builder
    {
        return ComposerPackage::query();
    }

    public static function getPages(): array
    {
        return [
            'index' => ComposerPackageResource\Pages\ListComposerPackages::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }
}
