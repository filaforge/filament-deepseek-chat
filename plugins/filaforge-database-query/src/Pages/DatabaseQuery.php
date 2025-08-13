<?php

namespace Filaforge\DatabaseQuery\Pages;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class DatabaseQuery extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationLabel = 'Database Query';
    protected static ?string $title = 'Database Query';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-command-line';
    protected static string | \UnitEnum | null $navigationGroup = 'System';

    protected string $view = 'database-query::pages.database-query';

    public array $data = [];
    public ?string $queryResult = null;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        $connections = $this->getAvailableConnections();

        return $schema
            ->components([
                Select::make('connection')
                    ->label('Database Connection')
                    ->options($connections)
                    ->default(config('database.default')),

                Select::make('preset_query')
                    ->label('Preset Queries')
                    ->placeholder('Select a preset query...')
                    ->options(fn () => $this->getPresetQueries())
                    ->reactive()
                    ->afterStateUpdated(function ($state) {
                        if ($state) {
                            $this->data['sql'] = $state;
                            $this->data['preset_query'] = null;
                        }
                    }),

                Textarea::make('sql')
                    ->label('SQL Query')
                    ->placeholder('SELECT * FROM users LIMIT 10')
                    ->rows(6)
                    ->helperText('Only SELECT queries are allowed for security.'),
            ])
            ->statePath('data');
    }

    public function executeQuery(): void
    {
        $sql = trim($this->data['sql'] ?? '');

        if (empty($sql)) {
            Notification::make()->title('SQL query is required')->danger()->send();
            return;
        }

        if (!preg_match('/^\s*SELECT\s+/i', $sql)) {
            Notification::make()->title('Only SELECT queries are allowed')->danger()->send();
            return;
        }

        try {
            $connection = $this->getConnectionName();
            $results = DB::connection($connection)->select($sql);
            $this->queryResult = empty($results) ? 'No results found.' : $this->formatQueryResults($results);
            Notification::make()->title('Query executed successfully')->success()->send();
        } catch (QueryException $e) {
            $this->queryResult = 'Error: ' . $e->getMessage();
            Notification::make()->title('Query failed')->body($e->getMessage())->danger()->send();
        }
    }

    protected function getAvailableConnections(): array
    {
        $connections = [];
        $databaseConfig = config('database.connections', []);
        foreach ($databaseConfig as $name => $config) {
            if (isset($config['driver'])) {
                $connections[$name] = ucfirst($name) . ' (' . $config['driver'] . ')';
            }
        }
        return $connections;
    }

    protected function getConnectionName(): string
    {
        return $this->data['connection'] ?? config('database.default');
    }

    protected function getPresetQueries(): array
    {
        $connection = $this->getConnectionName();
        $driverName = config("database.connections.{$connection}.driver", 'mysql');

        $queries = [];
        if ($driverName === 'sqlite') {
            $queries["SELECT name FROM sqlite_master WHERE type='table' ORDER BY name"] = 'List all tables';
        } elseif ($driverName === 'mysql') {
            $queries['SHOW TABLES'] = 'List all tables';
        } elseif ($driverName === 'pgsql') {
            $queries["SELECT tablename FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename"] = 'List all tables';
        }
        $queries['SELECT * FROM users'] = 'Show all users';
        $queries['SELECT COUNT(*) as total FROM users'] = 'Count users';

        return $queries;
    }

    private function formatQueryResults(array $results): string
    {
        if (empty($results)) {
            return 'No results found.';
        }

        $first = (array) $results[0];
        $columns = array_keys($first);
        $columnWidths = [];
        foreach ($columns as $column) {
            $columnWidths[$column] = max(strlen($column), 10);
        }
        $displayResults = array_slice($results, 0, 100);
        foreach ($displayResults as $row) {
            $rowArray = (array) $row;
            foreach ($columns as $column) {
                $value = $rowArray[$column] ?? '';
                $displayValue = $value === null ? 'NULL' : (string) $value;
                $columnWidths[$column] = max($columnWidths[$column], strlen($displayValue));
            }
        }
        foreach ($columnWidths as $column => $width) {
            $columnWidths[$column] = min($width, 50);
        }
        $output = "Query Results (" . count($results) . ")\n\n";
        $headerParts = [];
        $separatorParts = [];
        foreach ($columns as $column) {
            $width = $columnWidths[$column];
            $headerParts[] = str_pad($column, $width);
            $separatorParts[] = str_repeat('─', $width);
        }
        $output .= '┌─' . implode('─┬─', $separatorParts) . "─┐\n";
        $output .= '│ ' . implode(' │ ', $headerParts) . " │\n";
        $output .= '├─' . implode('─┼─', $separatorParts) . "─┤\n";
        foreach ($displayResults as $row) {
            $rowArray = (array) $row;
            $valueParts = [];
            foreach ($columns as $column) {
                $value = $rowArray[$column] ?? '';
                $displayValue = $value === null ? 'NULL' : (string) $value;
                if (strlen($displayValue) > $columnWidths[$column]) {
                    $displayValue = substr($displayValue, 0, $columnWidths[$column] - 3) . '...';
                }
                $valueParts[] = str_pad($displayValue, $columnWidths[$column]);
            }
            $output .= '│ ' . implode(' │ ', $valueParts) . " │\n";
        }
        $output .= '└─' . implode('─┴─', $separatorParts) . "─┘\n";
        if (count($results) > 100) {
            $output .= "\n... and " . (count($results) - 100) . " more rows (showing first 100)\n";
        }
        return $output;
    }
}


