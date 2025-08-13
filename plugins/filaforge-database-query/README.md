# Filaforge Database Query

A Filament v4 panel plugin that provides a SQL query explorer page to run read-only queries safely within your panel.

## Requirements
- PHP >= 8.1
- Laravel 12 (illuminate/support ^12)
- Filament ^4.0

## Installation
- Install via Composer:
  - In a consuming app: `composer require filaforge/database-query`
  - In this monorepo, the root app already maps `plugins/*` as path repositories.
- The service provider is auto-discovered.

## Register the plugin in your panel
```php
use Filaforge\DatabaseQuery\DatabaseQueryPlugin;
use Filament\Panel;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugin(DatabaseQueryPlugin::make());
}
```

## Usage
Open the “Database Query” page in your panel, enter a read-only SQL query, and execute. Results appear in a table.

## Notes
- Intentionally limited to safe, read-only operations. Configure any further constraints inside the plugin if needed.

---
Package: `filaforge/database-query`## Filaforge Database Query

Run ad-hoc SQL queries from a Filament page (read-only recommended).

Usage:

```php
->plugin(\Filaforge\DatabaseQuery\DatabaseQueryPlugin::make())
```

The page appears as "Database Query" in the admin navigation.


