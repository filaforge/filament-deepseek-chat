## Filaforge Quick Links

A simple per-user bookmarks widget for the Filament dashboard.

Features:
- Add label + URL entries
- Per-user storage in `dashboard_bookmarks`
- Delete and list bookmarks; reordering can be added later

Usage:
```php
->plugin(\Filaforge\QuickLinks\QuickLinksPlugin::make())
```

Run migrations to create the table:
```bash
php artisan migrate
```



