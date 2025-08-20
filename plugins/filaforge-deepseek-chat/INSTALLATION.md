# DeepSeek Chat Installation Guide

## Simple Installation Process

The DeepSeek Chat plugin has been simplified to prevent installation issues. Follow these steps for a safe installation:

### 1. Install the Package
```bash
composer require filaforge/deepseek-chat
```

### 2. Publish Assets (Recommended)
```bash
# Publish configuration
php artisan vendor:publish --tag=deepseek-chat-config

# Publish views
php artisan vendor:publish --tag=deepseek-chat-views

# Publish migrations
php artisan vendor:publish --tag=deepseek-chat-migrations
```

### 3. Run Migrations
```bash
php artisan migrate
```

### 4. Register in Filament Panel
Add this to your `app/Providers/Filament/AdminPanelProvider.php`:

```php
public function panel(Panel $panel): Panel
{
    return $panel
        // ... other configuration
        ->plugin(\Filaforge\DeepseekChat\Providers\DeepseekChatPanelPlugin::make());
}
```

### 5. Done!
The plugin is now ready to use.

## Why This Approach?

- **Safer**: No automatic operations that could break your site
- **Predictable**: You control when migrations and optimizations happen
- **Debuggable**: If something goes wrong, you know exactly what step failed
- **Production Ready**: Manual control is better for production environments

## Troubleshooting

If you encounter issues:

1. **Check logs**: Look in `storage/logs/laravel.log`
2. **Clear caches**: `php artisan cache:clear && php artisan config:clear`
3. **Reinstall**: Remove and reinstall the package if needed

## Previous Version Users

If you're upgrading from v0.6.x or earlier, the plugin will automatically detect existing installations and won't interfere with your current setup.
