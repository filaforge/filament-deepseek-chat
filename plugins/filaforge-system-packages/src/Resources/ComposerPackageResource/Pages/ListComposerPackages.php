<?php

namespace Filaforge\SystemPackages\Resources\ComposerPackageResource\Pages;

use Filaforge\SystemPackages\Models\ComposerPackage;
use Filaforge\SystemPackages\Resources\ComposerPackageResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ListComposerPackages extends ListRecords
{
    protected static string $resource = ComposerPackageResource::class;

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->query(null)
            ->records(fn () => $this->buildPaginator());
    }

    public function getTableQuery(): Builder
    {
        return (new ComposerPackage())->newQuery()->whereRaw('1 = 0');
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

    protected function makeFilteredSortedCollection(): Collection
    {
        $packages = ComposerPackage::getAllPackages();

        $search = (string) ($this->getTableSearch() ?? '');
        if ($search !== '') {
            $needle = mb_strtolower($search);
            $packages = $packages->filter(function (ComposerPackage $pkg) use ($needle) {
                return str_contains(mb_strtolower($pkg->name), $needle)
                    || str_contains(mb_strtolower((string) $pkg->description), $needle)
                    || str_contains(mb_strtolower((string) $pkg->version), $needle)
                    || str_contains(mb_strtolower((string) $pkg->vendor), $needle)
                    || str_contains(mb_strtolower((string) $pkg->type), $needle);
            })->values();
        }

        $filtersState = method_exists($this, 'getTableFiltersForm') ? (array) $this->getTableFiltersForm()->getState() : [];
        $typeFilter = $this->extractFilterValue($filtersState, 'type');
        $vendorFilter = $this->extractFilterValue($filtersState, 'vendor');

        if ($typeFilter) {
            $packages = $packages->where('type', $typeFilter)->values();
        }

        if ($vendorFilter) {
            $packages = $packages->where('vendor', $vendorFilter)->values();
        }

        $sortColumn = method_exists($this, 'getTableSortColumn') ? ($this->getTableSortColumn() ?? 'name') : 'name';
        $sortDirection = method_exists($this, 'getTableSortDirection') ? ($this->getTableSortDirection() ?? 'asc') : 'asc';

        $packages = $packages->sortBy(fn (ComposerPackage $pkg) => data_get($pkg, $sortColumn), SORT_REGULAR, strtolower($sortDirection) === 'desc')->values();

        return $packages;
    }

    protected function buildPaginator(): LengthAwarePaginator
    {
        $packages = $this->makeFilteredSortedCollection();

        $perPage = method_exists($this, 'getTableRecordsPerPage') ? (int) $this->getTableRecordsPerPage() : 10;
        $page = (int) request()->get('page', 1);

        if ($perPage <= 0) {
            $perPage = max($packages->count(), 1);
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

    public function getTitle(): string
    {
        return 'System Packages';
    }
}
