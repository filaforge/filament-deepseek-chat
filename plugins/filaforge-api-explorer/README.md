# Filaforge API Explorer

A Filament v4 panel plugin that adds an API Explorer page for testing HTTP endpoints (similar to Postman) inside your admin panel.

## Requirements
- PHP >= 8.1
- Laravel 12 (illuminate/support ^12)
- Filament ^4.0

## Installation
- Install via Composer:
  - In a consuming app: `composer require filaforge/api-explorer`
  - For local development in this monorepo, the root app already maps `plugins/*` as path repositories.
- The service provider is auto-discovered.

## Register the plugin in your panel
```php
use Filaforge\ApiExplorer\ApiExplorerPlugin;
use Filament\Panel;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugin(ApiExplorerPlugin::make());
}
```

## Usage
After registering, the plugin adds an API Explorer page to your panel. The exact URL depends on your panel path and navigation. Open your panel and look for “API Explorer”.

## Configuration
No configuration is required.

---
Package: `filaforge/api-explorer`## Filaforge API Explorer

A Filament plugin that provides a simple API testing page similar to Postman, with lazy‑loaded CSS assets.

Usage:

- Register in your panel provider:

```php
->plugin(\Filaforge\ApiExplorer\ApiExplorerPlugin::make())
```

The page appears as "API Explorer" in the admin navigation.


