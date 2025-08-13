# Filaforge Deepseek Chat

A Filament v4 panel plugin that adds a DeepSeek chat page. The API key can be managed via settings in the admin.

## Requirements
- PHP >= 8.1
- Laravel 12 (illuminate/support ^12)
- Filament ^4.0
- guzzlehttp/guzzle ^7.8

## Installation
- Install via Composer:
  - In a consuming app: `composer require filaforge/deepseek-chat`
  - In this monorepo, the root app already maps `plugins/*` as path repositories.
- The service provider is auto-discovered.

## Register the plugin in your panel
```php
use Filaforge\DeepseekChat\Providers\DeepseekChatPanelPlugin;
use Filament\Panel;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugin(DeepseekChatPanelPlugin::make());
}
```

## Usage
Open the “Deepseek Chat” page from your panel navigation. Configure the API key in settings and start chatting.

---
Package: `filaforge/deepseek-chat`
