<?php

namespace Filaforge\SystemPackages\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Collection;

class SystemPackagesWidget extends BaseWidget
{
    protected static ?string $heading = 'Installed Composer Packages';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->records(fn () => $this->getPackageRecords())
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
                    ->tooltip(function (array $record) {
                        $desc = (string) ($record['description'] ?? '');
                        return strlen($desc) > 100 ? $desc : null;
                    }),
            ])
            ->actions([
                \Filament\Actions\Action::make('packagist')
                    ->label('Packagist')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn (array $record) => "https://packagist.org/packages/{$record['name']}")
                    ->openUrlInNewTab(),
                \Filament\Actions\Action::make('github')
                    ->label('GitHub')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn (array $record) => $record['github_url'] ?? null)
                    ->visible(fn (array $record) => !empty($record['github_url']))
                    ->openUrlInNewTab(),
            ])
            ->defaultSort('name')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->searchable()
            ->searchPlaceholder('Search packages...')
            ->emptyStateHeading('No packages found');
    }

    protected function getPackageRecords(): Collection
    {
        $packages = [];

        if (class_exists(\Composer\InstalledVersions::class)) {
            try {
                $names = \Composer\InstalledVersions::getInstalledPackages();
                foreach ($names as $name) {
                    $packages[$name] = [
                        'name' => $name,
                        'version' => \Composer\InstalledVersions::getPrettyVersion($name) ?? 'N/A',
                        'description' => '',
                        'vendor' => $this->extractVendor($name),
                        'type' => $this->getPackageType($name),
                        'github_url' => null,
                    ];
                }
            } catch (\Throwable $e) {}
        }

        try {
            $composerLockPath = base_path('composer.lock');
            if (file_exists($composerLockPath)) {
                $composerLock = json_decode(file_get_contents($composerLockPath), true);
                foreach (['packages', 'packages-dev'] as $section) {
                    foreach (($composerLock[$section] ?? []) as $pkg) {
                        $name = (string) ($pkg['name'] ?? '');
                        if ($name === '') continue;

                        $record = $packages[$name] ?? [
                            'name' => $name,
                            'version' => $pkg['version'] ?? 'N/A',
                            'description' => '',
                            'vendor' => $this->extractVendor($name),
                            'type' => $this->getPackageType($name),
                            'github_url' => null,
                        ];

                        if (($record['description'] ?? '') === '' && isset($pkg['description'])) {
                            $record['description'] = (string) $pkg['description'];
                        }

                        $sourceUrl = (string) data_get($pkg, 'source.url', '');
                        $homepage = (string) ($pkg['homepage'] ?? '');
                        $githubUrl = $this->resolveGithubUrl($sourceUrl ?: $homepage);
                        if ($githubUrl && empty($record['github_url'])) {
                            $record['github_url'] = $githubUrl;
                        }

                        $packages[$name] = $record;
                    }
                }
            }
        } catch (\Throwable $e) {}

        try {
            $composerJsonPath = base_path('composer.json');
            if (file_exists($composerJsonPath)) {
                $composerJson = json_decode(file_get_contents($composerJsonPath), true);
                foreach ([...array_keys($composerJson['require'] ?? []), ...array_keys($composerJson['require-dev'] ?? [])] as $name) {
                    if (! isset($packages[$name])) {
                        $packages[$name] = [
                            'name' => $name,
                            'version' => (string) (($composerJson['require'][$name] ?? $composerJson['require-dev'][$name]) ?? 'N/A'),
                            'description' => '',
                            'vendor' => $this->extractVendor($name),
                            'type' => $this->getPackageType($name),
                            'github_url' => null,
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {}

        ksort($packages);

        return collect(array_values($packages));
    }

    protected function extractVendor(string $packageName): string
    {
        return strtolower(strtok($packageName, '/')) ?: 'other';
    }

    protected function getPackageType(string $name): string
    {
        if ($name === 'filament/filament') {
            return 'Core';
        }
        if (str_starts_with($name, 'filament/')) {
            return 'Extension';
        }
        if (str_starts_with($name, 'filaforge/')) {
            return 'Plugin';
        }
        if (str_contains($name, 'plugin')) {
            return 'Plugin';
        }
        return 'Unknown';
    }

    protected function resolveGithubUrl(string $url): ?string
    {
        if ($url === '') return null;
        if (preg_match('~github.com[:/][^\s]+~i', $url, $m)) {
            $candidate = $m[0];
            $candidate = preg_replace('~^git@~', 'https://', $candidate);
            $candidate = preg_replace('~^https://github.com:~', 'https://github.com/', $candidate);
            $candidate = preg_replace('~\.git$~', '', $candidate);
            if (! str_starts_with($candidate, 'https://')) {
                $candidate = 'https://' . ltrim($candidate, '/');
            }
            return $candidate;
        }
        return null;
    }
}
