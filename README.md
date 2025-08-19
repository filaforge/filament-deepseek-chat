## Filaforge DeepSeek Chat for Filament v4

[![Packagist Version](https://img.shields.io/packagist/v/filaforge/deepseek-chat.svg)](https://packagist.org/packages/filaforge/deepseek-chat)
[![Downloads](https://img.shields.io/packagist/dt/filaforge/deepseek-chat.svg)](https://packagist.org/packages/filaforge/deepseek-chat)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE.md)
[![PHP](https://img.shields.io/badge/PHP-^8.1-777bb4?logo=php)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-^12-ff2d20?logo=laravel)](https://laravel.com)
[![Filament](https://img.shields.io/badge/Filament-^4-16a34a)](https://filamentphp.com)

DeepSeek Chat adds an AI chat page to your Filament panel. It supports per-user API keys (stored on the user), optional streaming responses, conversation history, and a clean out‑of‑the‑box UI.

This plugin is built for the Filaforge Platform, but it works in any Filament v4 app.

![Screenshot](screenshot.png)

### Highlights
- Real-time AI chat interface (optional streaming)
- Per-user API key management via a settings page
- Conversation history stored in your database
- Simple install, ships with config, views, and migrations
- **Fully automatic installation** - no manual commands required
- **Automatic asset publishing** - config, views, and migrations published automatically
- **Automatic optimization** - runs `php artisan optimize` after installation

---

## Requirements
- PHP >= 8.1
- Laravel 12 (illuminate/support ^12)
- Filament ^4.0
- guzzlehttp/guzzle ^7.8

---

## Installation

### Step 1: Require the package
```bash
composer require filaforge/deepseek-chat
```

**That's it!** The package will automatically:
- Publish configuration files to `config/deepseek-chat.php`
- Publish view files to `resources/views/vendor/deepseek-chat/`
- Publish migration files to `database/migrations/`
- Run database migrations to create required tables
- Execute `php artisan optimize` to cache routes and config

### Step 2: Register the plugin in your Filament panel (important)
Add the panel plugin to your panel provider so the pages show in the sidebar.

- File in this app: `app/Providers/Filament/AdminPanelProvider.php`
- Method: `public function panel(Panel $panel): Panel`

Insert the plugin registration (if not already present):
```php
// app/Providers/Filament/AdminPanelProvider.php

// ...existing use statements...

public function panel(\Filament\Panel $panel): \Filament\Panel
{
    return $panel
        // ...other panel setup...
        ->plugin(\Filaforge\DeepseekChat\Providers\DeepseekChatPanelPlugin::make());
}
```
Note: If your app has multiple panels, add the plugin to whichever panel should expose the chat.

---

### Step 3: Start using the plugin!
The plugin is now fully installed and ready to use. No additional setup required.

---

## Configuration
The package automatically publishes `config/deepseek-chat.php` with these options:

```php
return [
    'api_key' => env('DEEPSEEK_API_KEY'),
    'base_url' => env('DEEPSEEK_BASE_URL', 'https://api.deepseek.com'),
    'allow_roles' => [], // Empty = allow all authenticated users
    'stream' => env('DEEPSEEK_STREAM', false),
    'timeout' => env('DEEPSEEK_TIMEOUT', 60),
];
```

Common `.env` settings:
```env
DEEPSEEK_API_KEY=your-key-here
DEEPSEEK_BASE_URL=https://api.deepseek.com
DEEPSEEK_STREAM=false
DEEPSEEK_TIMEOUT=60
```

Restrict access by role (leave empty to allow all authenticated users):
```php
// config/deepseek-chat.php
return [
    // ...
    'allow_roles' => ['admin', 'staff'],
];
```

---

## Usage
- Open "DeepSeek Chat" from your Filament navigation.
- Go to "DeepSeek Settings" to set your personal API key (stored on the user). If you prefer, you can set a default in `.env` (see below) and users can override it.
- Start chatting. Conversations are saved to the `deepseek_conversations` table.

---

## Development & Testing

### Running Tests
```bash
composer test
```

### Running Tests with Coverage
```bash
composer test-coverage
```

### Installation Test
```bash
php install-test.php
```

---

## Updates

```bash
composer update filaforge/deepseek-chat
```

**Note:** All assets, migrations, and optimization are automatically handled during package installation and updates.

---

## Troubleshooting
- **Migrations are automatic** - no need to run `php artisan migrate` manually
- **Assets are auto-published** - no need to run `php artisan vendor:publish` manually
- **Optimization is automatic** - no need to run `php artisan optimize` manually
- If you encounter any issues, the plugin logs all operations to Laravel's log files
- Make sure you added the panel plugin in Step 2; otherwise pages won't be registered

---

## License
MIT. See LICENSE.md.
