# Filaforge System Widget

A Filament v4 dashboard widget plugin for real-time system monitoring, using a unique namespace to avoid collisions.

## Requirements
- PHP >= 8.1
- Laravel 12 (illuminate/support ^12)
- Filament ^4.0
- symfony/process ^7.0

## Installation
- Install via Composer:
  - In a consuming app: `composer require filaforge/system-widget`
  - In this monorepo, the root app already maps `plugins/*` as path repositories.
- The service provider is auto-discovered.

## Register the plugin in your panel
```php
use Filaforge\SystemWidget\SystemWidgetPlugin;
use Filament\Panel;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugin(SystemWidgetPlugin::make());
}
```

## Usage
After registration, the “System Monitor” widget appears on the dashboard.

---
Package: `filaforge/system-widget`# Filaforge System Monitor

A Filament v4 dashboard widget for real-time server monitoring.

## Installation

1) Require the package (this repo is included as a local path repo in this project):

```
composer require filaforge/system-monitor
```

2) Publish the config (optional):

```
php artisan vendor:publish --tag=config --provider="Filaforge\SystemMonitor\Providers\SystemMonitorServiceProvider"
```

3) Register the plugin on your target panel provider:

```php
use Filaforge\SystemMonitor\SystemMonitorPlugin;

public function panel(\Filament\Panel $panel): \Filament\Panel
{
    return $panel
        // ...
        ->plugin(SystemMonitorPlugin::make());
}
```

The widget will be available on the dashboard and only visible to authorized users, based on the `allow_roles` setting in the published config.
