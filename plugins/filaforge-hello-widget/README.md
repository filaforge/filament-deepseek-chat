# Filaforge Hello Widget

A simple Filament v4 dashboard widget plugin used as an example skeleton.

## Requirements
- PHP >= 8.1
- Laravel 12 (illuminate/support ^12)
- Filament ^4.0

## Installation
- Install via Composer:
  - In a consuming app: `composer require filaforge/hello-widget`
  - In this monorepo, the root app already maps `plugins/*` as path repositories.
- The service provider is auto-discovered.

## Register the plugin in your panel
```php
use Filaforge\HelloWidget\HelloWidgetPlugin;
use Filament\Panel;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugin(HelloWidgetPlugin::make());
}
```

## Usage
After registration, the “Hello Widget” appears on the dashboard.

---
Package: `filaforge/hello-widget`
