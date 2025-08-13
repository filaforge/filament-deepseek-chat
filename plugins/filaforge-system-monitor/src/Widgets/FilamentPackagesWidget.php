<?php

namespace Filaforge\SystemMonitor\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class FilamentPackagesWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected function getTableQuery(): ?Builder
    {
        return null;
    }

    public function getTableRecords(): Collection|Paginator|CursorPaginator
    {
        $packages = $this->getFilamentPackages();
        $records = [];

        foreach ($packages as $name => $package) {
            $unique = md5($name);
            $records[] = [
                '__key' => $unique,
                'id' => $unique,
                'name' => $name,
                'version' => $package['version'] ?? 'Unknown',
                'description' => $package['description'] ?? 'No description available',
                'type' => $package['type'] ?? 'Unknown',
                'is_plugin' => str_contains($name, 'plugin') || str_contains($name, 'filaforge/'),
                'homepage' => $this->getPackageHomepage($name),
            ];
        }

        return collect($records);
    }

    public function table(Table $table): Table
    {
        return $table
            ->records(fn () => $this->getTableRecords())
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Package Name')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('primary')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('version')
                    ->label('Version')
                    ->sortable()
                    ->badge()
                    ->color('success'),

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

                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->searchable()
                    ->wrap()
                    ->limit(100)
                    ->tooltip(function (array $record) {
                        $desc = (string) ($record['description'] ?? '');
                        return strlen($desc) > 100 ? $desc : null;
                    }),

                Tables\Columns\IconColumn::make('is_plugin')
                    ->label('Plugin')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'Core' => 'Core',
                        'Plugin' => 'Plugin',
                        'Extension' => 'Extension',
                    ]),
                Tables\Filters\TernaryFilter::make('is_plugin')
                    ->label('Plugins Only')
                    ->placeholder('All packages')
                    ->trueLabel('Plugins only')
                    ->falseLabel('Core/Extensions only'),
            ])
            ->actions([
                \Filament\Actions\Action::make('view_on_packagist')
                    ->label('Packagist')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn (array $record) => "https://packagist.org/packages/{$record['name']}")
                    ->openUrlInNewTab(),
                \Filament\Actions\Action::make('copy_name')
                    ->label('Copy')
                    ->icon('heroicon-m-clipboard')
                    ->action(function (array $record) {
                        return $record['name'];
                    }),
            ])
            ->defaultSort('name')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->searchable()
            ->searchPlaceholder('Search packages...')
            ->emptyStateHeading('No Filament packages found')
            ->emptyStateDescription('Install some Filament packages to see them here.')
            ->emptyStateIcon('heroicon-o-puzzle-piece');
    }

    protected function getTableHeading(): string
    {
        return 'Installed Filament Packages';
    }

    protected function getFilamentPackages(): array
    {
        $packages = [];

        try {
            $composerLockPath = base_path('composer.lock');
            if (file_exists($composerLockPath)) {
                $composerLock = json_decode(file_get_contents($composerLockPath), true);

                if (isset($composerLock['packages'])) {
                    foreach ($composerLock['packages'] as $package) {
                        $name = $package['name'] ?? '';

                        if (str_contains($name, 'filament/') || str_contains($name, 'filaforge/')) {
                            $packages[$name] = [
                                'version' => $package['version'] ?? 'Unknown',
                                'description' => $package['description'] ?? 'No description available',
                                'type' => $this->getPackageType($name, $package),
                                'homepage' => $package['homepage'] ?? null,
                            ];
                        }
                    }
                }
            }

            $composerJsonPath = base_path('composer.json');
            if (file_exists($composerJsonPath)) {
                $composerJson = json_decode(file_get_contents($composerJsonPath), true);
                if (isset($composerJson['require'])) {
                    foreach ($composerJson['require'] as $packageName => $version) {
                        if ((str_contains($packageName, 'filament/') || str_contains($packageName, 'filaforge/')) && ! isset($packages[$packageName])) {
                            $packages[$packageName] = [
                                'version' => $version,
                                'description' => 'Required package (version from composer.json)',
                                'type' => $this->getPackageType($packageName),
                                'homepage' => null,
                            ];
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $packages = [
                'filament/filament' => [
                    'version' => '^4.0',
                    'description' => 'A collection of beautiful full-stack components for Laravel',
                    'type' => 'Core',
                    'homepage' => 'https://filamentphp.com',
                ],
                'filaforge/system-monitor' => [
                    'version' => 'dev-main',
                    'description' => 'System monitoring dashboard widget for Filament',
                    'type' => 'Plugin',
                    'homepage' => null,
                ],
            ];
        }

        return $packages;
    }

    protected function getPackageType(string $name, array $package = []): string
    {
        if (str_contains($name, 'filaforge/')) {
            return 'Plugin';
        }

        if (str_contains($name, 'plugin')) {
            return 'Plugin';
        }

        if ($name === 'filament/filament') {
            return 'Core';
        }

        if (str_contains($name, 'filament/')) {
            return 'Extension';
        }

        return 'Unknown';
    }

    protected function getPackageHomepage(string $name): ?string
    {
        if (str_contains($name, 'filament/')) {
            return 'https://filamentphp.com';
        }

        return "https://packagist.org/packages/{$name}";
    }
}


