<?php

namespace Filaforge\SystemPackages\Pages;

use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class SystemPackagesPage extends Page implements HasTable
{
	use InteractsWithTable;

	protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-rectangle-stack';

	protected static \UnitEnum|string|null $navigationGroup = 'System';

	protected static ?string $navigationLabel = 'System Packages';

	protected static ?string $title = 'System Packages';

	protected string $view = 'system-packages::pages.system-packages-page';

	protected static ?string $slug = 'system-packages-page';

	public function table(Table $table): Table
	{
		return $table
			->records(fn () => $this->getTableRecords())
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
					->sortable()
					->badge()
					->color(fn (string $state): string => match ($state) {
						'Filament' => 'warning', // yellow
						'Laravel' => 'success', // green
						'Vendor' => 'info',      // blue
						'Core' => 'danger',      // red
						'Plugin' => 'gray',      // gray
						'Dev' => 'secondary',
						default => 'gray',
					})
					->icon(fn (string $state): string => match ($state) {
						'Core' => 'heroicon-m-heart',
						'Plugin' => 'heroicon-m-puzzle-piece',
						'Filament' => 'heroicon-m-cube',
						'Laravel' => 'heroicon-m-bolt',
						'Vendor' => 'heroicon-m-briefcase',
						'Dev' => 'heroicon-m-wrench-screwdriver',
						default => 'heroicon-m-question-mark-circle',
					}),

				Tables\Columns\TextColumn::make('vendor')
					->label('Vendor')
					->sortable()
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
			->filters([
				Tables\Filters\SelectFilter::make('vendor')
					->label('Vendor')
					->options(fn () => $this->getVendorOptions()),
				Tables\Filters\SelectFilter::make('type')
					->label('Type')
					->options(fn () => $this->getTypeOptions()),
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

	public function getTableRecords(): Collection|Paginator|CursorPaginator
	{
		$packages = $this->getFilteredSortedPackages();
		$perPage = (int) ($this->getTableRecordsPerPage() ?? 10);
		if ($perPage <= 0) {
			$perPage = 10;
		}
		$page = (int) ($this->getTablePage() ?? 1);
		if ($page <= 0) {
			$page = 1;
		}

		$items = $packages->slice(($page - 1) * $perPage, $perPage)->values();

		return new LengthAwarePaginator(
			$items,
			$packages->count(),
			$perPage,
			$page,
			['path' => request()->url(), 'pageName' => 'page']
		);
	}

	public function getTableRecordKey($record): string
	{
		return $record['key'] ?? $record['name'] ?? '';
	}

	protected function getFilteredSortedPackages(): Collection
	{
		$packages = $this->getPackageRecords();

		// Search across several fields
		$search = (string) ($this->getTableSearch() ?? '');
		if ($search !== '') {
			$needle = mb_strtolower($search);
			$packages = $packages->filter(function (array $pkg) use ($needle) {
				return str_contains(mb_strtolower($pkg['name'] ?? ''), $needle)
					|| str_contains(mb_strtolower((string) ($pkg['description'] ?? '')), $needle)
					|| str_contains(mb_strtolower((string) ($pkg['version'] ?? '')), $needle)
					|| str_contains(mb_strtolower((string) ($pkg['vendor'] ?? '')), $needle)
					|| str_contains(mb_strtolower((string) ($pkg['type'] ?? '')), $needle);
			})->values();
		}

		// Apply filters
		$filtersState = method_exists($this, 'getTableFiltersForm') ? (array) $this->getTableFiltersForm()->getState() : [];
		$vendorFilter = $this->extractFilterValue($filtersState, 'vendor');
		$typeFilter = $this->extractFilterValue($filtersState, 'type');

		if ($vendorFilter) {
			$packages = $packages->where('vendor', $vendorFilter)->values();
		}
		if ($typeFilter) {
			$packages = $packages->where('type', $typeFilter)->values();
		}

		// Apply sorting
		$sortColumn = method_exists($this, 'getTableSortColumn') ? ($this->getTableSortColumn() ?? 'name') : 'name';
		$sortDirection = method_exists($this, 'getTableSortDirection') ? ($this->getTableSortDirection() ?? 'asc') : 'asc';
		$packages = $packages->sortBy(fn (array $pkg) => $pkg[$sortColumn] ?? null, SORT_REGULAR, strtolower($sortDirection) === 'desc')->values();

		return $packages;
	}

	protected function extractFilterValue(array $filtersState, string $key)
	{
		if (! array_key_exists($key, $filtersState)) {
			return null;
		}
		$value = $filtersState[$key];
		if (is_scalar($value) || $value === null) {
			return $value;
		}
		if (is_array($value)) {
			if (array_key_exists('value', $value)) {
				return $value['value'];
			}
			if (array_key_exists('values', $value)) {
				return $value['values'];
			}
			foreach ($value as $v) {
				if (is_scalar($v) && $v !== '') {
					return $v;
				}
			}
		}
		return null;
	}

	protected function getVendorOptions(): array
	{
		return $this->getPackageRecords()
			->pluck('vendor')
			->filter()
			->unique()
			->sort()
			->mapWithKeys(fn ($v) => [$v => ucfirst((string) $v)])
			->all();
	}

	protected function getTypeOptions(): array
	{
		return $this->getPackageRecords()
			->pluck('type')
			->filter()
			->unique()
			->sort()
			->mapWithKeys(fn ($v) => [$v => (string) $v])
			->all();
	}

	protected function getPackageRecords(): Collection
	{
		$packages = [];

		if (class_exists(\Composer\InstalledVersions::class)) {
			try {
				$names = \Composer\InstalledVersions::getInstalledPackages();
				foreach ($names as $name) {
					$packages[$name] = [
						'key' => $name,
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
					$devSection = $section === 'packages-dev';
					foreach (($composerLock[$section] ?? []) as $pkg) {
						$name = (string) ($pkg['name'] ?? '');
						if ($name === '') continue;

						$record = $packages[$name] ?? [
							'key' => $name,
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

						if ($devSection) {
							$record['type'] = 'Dev';
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
							'key' => $name,
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
		// Filament packages
		if ($name === 'filament/filament' || str_starts_with($name, 'filament/')) {
			return 'Filament';
		}
		// Laravel core packages
		if ($name === 'laravel/framework' || str_starts_with($name, 'laravel/') || str_starts_with($name, 'illuminate/')) {
			return 'Laravel';
		}
		// Filaforge plugins
		if (str_starts_with($name, 'filaforge/')) {
			return 'Plugin';
		}
		// Heuristic
		if (str_contains($name, 'plugin')) {
			return 'Plugin';
		}
		// Default
		return 'Vendor';
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
