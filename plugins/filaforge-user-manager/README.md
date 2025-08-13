# Filaforge User Manager

A Filament v4 panel plugin that provides a User resource for managing users (and optionally generating fake users for testing).

## Requirements
- PHP >= 8.1
- Laravel 12 (illuminate/support ^12)
- Filament ^4.0

## Installation
- Install via Composer:
  - In a consuming app: `composer require filaforge/user-manager`
  - In this monorepo, the root app already maps `plugins/*` as path repositories.
- The service provider is auto-discovered.

## Register the plugin in your panel
```php
use Filaforge\UserManager\UserManagerPlugin;
use Filament\Panel;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugin(UserManagerPlugin::make());
}
```

## Usage
The “Users” resource appears in navigation. Use it to browse, create, and edit users.

---
Package: `filaforge/user-manager`## Filaforge User Manager

Filament resource(s) to manage application users.

Usage:

```php
->plugin(\Filaforge\UserManager\UserManagerPlugin::make())
```

The resource appears under "Users" in the admin navigation.


