# Filaforge System Monitor

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
