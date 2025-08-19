# Filaforge DeepSeek Chat

A powerful Filament plugin that integrates DeepSeek AI chat capabilities directly into your admin panel.

## Features

- **DeepSeek AI Integration**: Chat with advanced AI models powered by DeepSeek
- **Conversation Management**: Save, organize, and continue chat conversations
- **Customizable Settings**: Configure API keys, models, and chat parameters
- **Real-time Chat**: Live chat experience with streaming responses
- **Conversation History**: Keep track of all your AI conversations
- **Export Conversations**: Save and share chat transcripts
- **Role-based Access**: Configurable user permissions and access control
- **Multi-model Support**: Switch between different DeepSeek models
- **Context Awareness**: Maintain conversation context across sessions

## Installation

### 1. Install via Composer

```bash
composer require filaforge/deepseek-chat
```

### 2. Publish & Migrate

```bash
# Publish provider groups (config, views, migrations)
php artisan vendor:publish --provider="Filaforge\\DeepseekChat\\Providers\\DeepseekChatServiceProvider"

# Run migrations
php artisan migrate
```

### 3. Register Plugin

Add the plugin to your Filament panel provider:

```php
use Filament\Panel;

public function panel(Panel $panel): Panel
{
    return $panel
        // ... other configuration
        ->plugin(\Filaforge\DeepseekChat\Providers\DeepseekChatPanelPlugin::make());
}
```

## Setup

### Configuration

The plugin will automatically:
- Publish configuration files to `config/deepseek-chat.php`
- Publish view files to `resources/views/vendor/deepseek-chat/`
- Publish migration files to `database/migrations/`
- Register necessary routes and middleware

### DeepSeek API Configuration

Configure your DeepSeek API in the published config file:

```php
// config/deepseek-chat.php
return [
    'api_key' => env('DEEPSEEK_API_KEY'),
    'base_url' => env('DEEPSEEK_BASE_URL', 'https://api.deepseek.com'),
    'default_model' => env('DEEPSEEK_MODEL', 'deepseek-chat'),
    'max_tokens' => env('DEEPSEEK_MAX_TOKENS', 4096),
    'temperature' => env('DEEPSEEK_TEMPERATURE', 0.7),
    'stream' => env('DEEPSEEK_STREAM', true),
    'timeout' => env('DEEPSEEK_TIMEOUT', 60),
];
```

### Environment Variables

Add these to your `.env` file:

```env
DEEPSEEK_API_KEY=your_deepseek_api_key_here
DEEPSEEK_BASE_URL=https://api.deepseek.com
DEEPSEEK_MODEL=deepseek-chat
DEEPSEEK_MAX_TOKENS=4096
DEEPSEEK_TEMPERATURE=0.7
DEEPSEEK_STREAM=true
DEEPSEEK_TIMEOUT=60
```

### Getting Your DeepSeek API Key

1. Visit [DeepSeek Platform](https://platform.deepseek.com/)
2. Create an account or sign in
3. Navigate to API Keys section
4. Generate a new API key
5. Copy the key to your `.env` file

## Usage

### Accessing DeepSeek Chat

1. Navigate to your Filament admin panel
2. Look for the "DeepSeek Chat" menu item
3. Start chatting with AI models

### Starting a Conversation

1. **Select Model**: Choose from available DeepSeek models
2. **Type Your Message**: Enter your question or prompt
3. **Send Message**: Submit your message to the AI
4. **View Response**: See the AI's response in real-time
5. **Continue Chat**: Keep the conversation going

### Managing Conversations

1. **New Chat**: Start a fresh conversation
2. **Save Chat**: Automatically save important conversations
3. **Load Chat**: Resume previous conversations
4. **Export Chat**: Download conversation transcripts
5. **Delete Chat**: Remove unwanted conversations

### Advanced Features

- **Model Selection**: Switch between different DeepSeek models
- **Parameter Tuning**: Adjust temperature, max tokens, and other settings
- **Context Management**: Maintain conversation context across sessions
- **Streaming Responses**: Real-time AI responses for better user experience

## Troubleshooting

### Common Issues

- **API key errors**: Verify your DeepSeek API key is correct and has sufficient credits
- **Rate limiting**: Check your DeepSeek API rate limits and usage
- **Model not available**: Ensure the selected model is available in your plan
- **Connection timeouts**: Check network connectivity and timeout settings

### Debug Steps

1. Check the plugin configuration:
```bash
php artisan config:show deepseek-chat
```

2. Verify routes are registered:
```bash
php artisan route:list | grep deepseek-chat
```

3. Test API connectivity:
```bash
php artisan tinker
# Test your API key manually
```

4. Check environment variables:
```bash
php artisan tinker
echo env('DEEPSEEK_API_KEY');
```

5. Clear caches:
```bash
php artisan optimize:clear
```

6. Check logs for errors:
```bash
tail -f storage/logs/laravel.log
```

### API Error Codes

- **401 Unauthorized**: Invalid or expired API key
- **429 Too Many Requests**: Rate limit exceeded
- **500 Internal Server Error**: DeepSeek service issue
- **Timeout**: Request took too long to complete

## Security Considerations

### Access Control

- **Role-based permissions**: Restrict access to authorized users only
- **API key security**: Never expose API keys in client-side code
- **User isolation**: Ensure users can only access their own conversations
- **Audit logging**: Track all chat activities and API usage

### Best Practices

- Use environment variables for API keys
- Implement proper user authentication
- Monitor API usage and costs
- Regularly rotate API keys
- Set appropriate rate limits

## Uninstall

### 1. Remove Plugin Registration

Remove the plugin from your panel provider:
```php
// remove ->plugin(\Filaforge\DeepseekChat\Providers\DeepseekChatPanelPlugin::make())
```

### 2. Roll Back Migrations (Optional)

```bash
php artisan migrate:rollback
# or roll back specific published files if needed
```

### 3. Remove Published Assets (Optional)

```bash
rm -f config/deepseek-chat.php
rm -rf resources/views/vendor/deepseek-chat
```

### 4. Remove Package and Clear Caches

```bash
composer remove filaforge/deepseek-chat
php artisan optimize:clear
```

### 5. Clean Up Environment Variables

Remove these from your `.env` file:
```env
DEEPSEEK_API_KEY=your_deepseek_api_key_here
DEEPSEEK_BASE_URL=https://api.deepseek.com
DEEPSEEK_MODEL=deepseek-chat
DEEPSEEK_MAX_TOKENS=4096
DEEPSEEK_TEMPERATURE=0.7
DEEPSEEK_STREAM=true
DEEPSEEK_TIMEOUT=60
```

## Support

- **Documentation**: [GitHub Repository](https://github.com/filaforge/deepseek-chat)
- **Issues**: [GitHub Issues](https://github.com/filaforge/deepseek-chat/issues)
- **Discussions**: [GitHub Discussions](https://github.com/filaforge/deepseek-chat/discussions)

## Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

## License

This plugin is open-sourced software licensed under the [MIT license](LICENSE).

---

**Made with ❤️ by the Filaforge Team**
