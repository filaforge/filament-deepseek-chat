# Filaforge System Tools

A Filament v4 panel plugin that adds a System Tools page with handy server utilities.

## Requirements
- PHP >= 8.1
- Laravel 12 (illuminate/support ^12)
- Filament ^4.0

## Installation
- Install via Composer:
  - In a consuming app: `composer require filaforge/system-tools`
  - In this monorepo, the root app already maps `plugins/*` as path repositories.
- The service provider is auto-discovered.

## Register the plugin in your panel
```php
use Filaforge\SystemTools\SystemToolsPlugin;
use Filament\Panel;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugin(SystemToolsPlugin::make());
}
```

## Usage
Open the “System Tools” page from your panel navigation.

---
Package: `filaforge/system-tools`## Filaforge System Tools

Utility page(s) to perform maintenance and system actions within Filament.

Usage:

```php
->plugin(\Filaforge\SystemTools\SystemToolsPlugin::make())
```

The page appears as "System Tools" in the admin navigation.


