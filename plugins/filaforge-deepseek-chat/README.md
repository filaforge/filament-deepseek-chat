# Filaforge Deepseek Chat

A Filament v4 ## Usage

After installation and registration, you'll find the "Deepseek Chat" page in your Filament panel navigation. Configure your DeepSeek API key in the settings and start chatting with AI.

## Configuration

You'll need a DeepSeek API key from [DeepSeek's website](https://www.deepseek.com). Configure it through the admin settings page.

## Features

- ✅ Real-time AI chat interface
- ✅ Secure API key management  
- ✅ Conversation history
- ✅ Responsive design

---

**Package**: `filaforge/deepseek-chat`  
**License**: MIT  
**Requirements**: PHP ^8.1, Laravel ^12, Filament ^4.0, guzzlehttp/guzzle ^7.8 plugin that adds a DeepSeek chat page. The API key can be managed via settings in the admin.

![Screenshot](screenshot.png)

## Requirements
- PHP >= 8.1
- Laravel 12 (illuminate/support ^12)
- Filament ^4.0
- guzzlehttp/guzzle ^7.8

## Installation

### Step 1: Install via Composer
```bash
composer require filaforge/deepseek-chat
```

### Step 2: Service Provider Registration
The service provider is auto-discovered, so no manual registration is required.

### Step 3: Publish Assets (Optional)
If the plugin includes publishable assets, you can publish them:
```bash
php artisan vendor:publish --provider="Filaforge\DeepseekChat\Providers\DeepseekChatServiceProvider"
```

### Step 4: Register the plugin in your panel
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
