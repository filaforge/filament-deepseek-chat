# Filaforge Database Viewer

A Filament v4 panel plugin that provides a database browser and data explorer view for quick inspection of tables and records.

## Requirements
- PHP >= 8.1
- Laravel 12 (illuminate/support ^12)
- Filament ^4.0

## Installation
- Install via Composer:
  - In a consuming app: `composer require filaforge/database-viewer`
  - In this monorepo, the root app already maps `plugins/*` as path repositories.
- The service provider is auto-discovered.

## Register the plugin in your panel
```php
use Filaforge\DatabaseViewer\DatabaseViewerPlugin;
use Filament\Panel;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugin(DatabaseViewerPlugin::make());
}
```

## Usage
Navigate to the “Database Viewer” page. Select a table to browse columns and preview data.

---
Package: `filaforge/database-viewer`## Filaforge Database Viewer

Browse tables and explore data from a Filament page.

Usage:

```php
->plugin(\Filaforge\DatabaseViewer\DatabaseViewerPlugin::make())
```

The page appears as "Database Viewer" in the admin navigation.


