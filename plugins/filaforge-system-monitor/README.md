## Filaforge System Monitor

Stats overview widgets showing CPU load, memory, and disk usage, plus extra widgets.

### Install

1) Require the package (path repo or Packagist):
```
composer require filaforge/system-monitor
```

2) (Optional) Publish config and views:
```
php artisan vendor:publish --provider="Filaforge\SystemMonitor\Providers\SystemMonitorServiceProvider" --tag=config
php artisan vendor:publish --provider="Filaforge\SystemMonitor\Providers\SystemMonitorServiceProvider" --tag=views
```
This publishes `config/filaforge-system-monitor.php`.

3) Register the plugin on your panel:
```php
use Filaforge\SystemMonitor\SystemMonitorPlugin;

return $panel
    // ...
    ->plugin(SystemMonitorPlugin::make());
```

If your project also calls `$panel->widgets([...])` elsewhere, ensure you include the widgets in that single call or place it before/after the plugin without overriding:
```php
->widgets([
    \Filaforge\SystemMonitor\Widgets\SystemMonitorWidget::class,
    \Filaforge\SystemMonitor\Widgets\SystemInfoWidget::class,
    \Filaforge\SystemMonitor\Widgets\SystemProcessesWidget::class,
])
```

### Route
`/filaforge-system-monitor/metrics` (auth)

### Config
`config/filaforge-system-monitor.php`

Widgets are added to your panel automatically by the plugin.

## Configuration

Edit `config/filaforge-system-monitor.php`:

```php
return [
    'refresh_interval_seconds' => env('FILAFORGE_SYSTEM_MONITOR_INTERVAL', 5),
    'top_processes' => 5,
    'allow_roles' => ['admin'],
    'enable_shell_commands' => true,
    'restricted_ips' => [],
];
```

## Permissions

By default, only authenticated users can view the widgets. Customize access control by overriding the `canView()` method in each widget class.

## Requirements

- PHP 8.1+
- Laravel 12.0+
- Filament 4.0+
- Linux/Unix system (for full functionality)

## Design Philosophy

This plugin follows Filament v4 design patterns:
- Uses native Filament components and styling
- Consistent with built-in widget designs
- Mobile-responsive layouts
- Proper dark mode support
- Accessible color schemes

## Screenshots

The widgets integrate seamlessly with your existing Filament dashboard, showing:
- Real-time system metrics in beautiful stat cards
- Clean system information display
- Professional process monitoring table

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This plugin is open-sourced software licensed under the [MIT license](LICENSE).
