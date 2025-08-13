# Filaforge System Packages

A Filament v4 panel plugin that lists installed Composer packages in a searchable, paginated table.

## Requirements
- PHP >= 8.1
- Laravel 12 (illuminate/support ^12)
- Filament ^4.0

## Installation
- Install via Composer:
  - In a consuming app: `composer require filaforge/system-packages`
  - In this monorepo, the root app already maps `plugins/*` as path repositories.
- The service provider is auto-discovered.

## Register the plugin in your panel
```php
use Filaforge\SystemPackages\SystemPackagesPlugin;
use Filament\Panel;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugin(SystemPackagesPlugin::make());
}
```

## Usage
- Navigate to the Composer Packages resource in your panel navigation.
- A legacy “System Packages” page is provided and redirects to the resource automatically.

---
Package: `filaforge/system-packages`
