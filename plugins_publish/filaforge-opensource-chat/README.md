# Filaforge Open Source Chat

FilamentPHP plugin providing a multi-provider chat interface with support for:

- **OpenAI API** (GPT-4, GPT-3.5-turbo, etc.)
- **HuggingFace API** (Llama 3, DialoGPT, etc.)
- **Ollama Local** (Self-hosted models)

## Features

- 🤖 **Multiple AI Providers**: Switch between OpenAI, HuggingFace, and local Ollama models
- 👤 **Model Profiles**: Create and manage different model configurations
- 💬 **User Settings**: Per-user chat preferences and API keys
- 📝 **Conversations**: Stored chat history and conversation management
- 🔧 **Connection Testing**: Built-in tools to test provider connectivity
- ⚡ **Streaming Support**: Real-time streaming responses (where supported)
- 🛡️ **Rate Limiting**: Configurable per-minute and per-day limits

## Installation

```bash
composer require filaforge/opensource-chat
php artisan vendor:publish --tag="opensource-chat-migrations"
php artisan migrate
```

### Optional Configuration
```bash
php artisan vendor:publish --tag="opensource-chat-config"
```

## Quick Setup

### 1. Setup Ollama (Local, Free)
```bash
# Install Ollama
curl -fsSL https://ollama.ai/install.sh | sh

# Start Ollama
ollama serve

# Pull a model
ollama pull llama3:latest

# Setup in your app
php artisan oschat:setup-provider ollama --test
```

### 2. Setup OpenAI
```bash
# Get API key from https://platform.openai.com/api-keys
php artisan oschat:setup-provider openai --api-key=YOUR_API_KEY --test

# Or set in .env
OPENAI_API_KEY=your_openai_api_key_here
```

### 3. Setup HuggingFace
```bash
# Get API key from https://huggingface.co/settings/tokens
php artisan oschat:setup-provider huggingface --api-key=YOUR_API_KEY --test

# Or set in .env
HF_API_KEY=your_huggingface_api_key_here
```

## Configuration

The plugin supports multiple providers with different configurations:

### Environment Variables
```env
# OpenAI
OPENAI_API_KEY=your_openai_api_key
OPENAI_BASE_URL=https://api.openai.com/v1

# HuggingFace
HF_API_KEY=your_hf_api_key
HF_BASE_URL=https://api-inference.huggingface.co

# Ollama
OLLAMA_BASE_URL=http://localhost:11434
OLLAMA_MODEL_ID=llama3:latest

# General
OSCHAT_TIMEOUT=120
OSCHAT_STREAM=true
```

### Supported Models

#### OpenAI
- `gpt-4` - GPT-4 (most capable)
- `gpt-4-turbo` - GPT-4 Turbo (faster)
- `gpt-3.5-turbo` - GPT-3.5 Turbo (cost-effective)

#### HuggingFace
- `meta-llama/Meta-Llama-3-8B-Instruct` - Llama 3 8B
- `meta-llama/Meta-Llama-3-70B-Instruct` - Llama 3 70B
- `microsoft/DialoGPT-medium` - DialoGPT Medium
- `microsoft/DialoGPT-large` - DialoGPT Large

#### Ollama (Local)
- `llama3:latest` - Llama 3 Latest
- `llama3:8b` - Llama 3 8B
- `llama3:70b` - Llama 3 70B
- `codellama:latest` - Code Llama (for coding)
- `mistral:latest` - Mistral
- `phi3:latest` - Phi-3

## Usage

### Admin Interface

1. **OS Chat**: Main chat interface
   - Select model profiles
   - Start conversations
   - View chat history

2. **OS Chat Settings**: User preferences
   - Default model selection
   - API key management
   - System prompts

3. **Model Profiles**: Manage AI models
   - Create/edit model configurations
   - Test connections
   - Set rate limits
   - Configure system prompts

### Model Profile Management

Create custom model profiles for different use cases:

```php
// Example: Create a coding assistant profile
ModelProfile::create([
    'name' => 'Code Assistant',
    'provider' => 'ollama',
    'model_id' => 'codellama:latest',
    'base_url' => 'http://localhost:11434',
    'system_prompt' => 'You are an expert coding assistant. Provide clear, well-commented code examples.',
    'is_active' => true,
]);
```

### API Usage

The plugin provides a service for programmatic access:

```php
use Filaforge\OpensourceChat\Services\ChatApiService;
use Filaforge\OpensourceChat\Models\ModelProfile;

$profile = ModelProfile::where('name', 'GPT-4')->first();
$service = new ChatApiService($profile);

$messages = [
    ['role' => 'user', 'content' => 'Hello, how are you?']
];

$response = $service->chatCompletion($messages);
$reply = $response['choices'][0]['message']['content'];
```

## Provider Comparison

| Provider | Cost | Speed | Privacy | Setup | Models |
|----------|------|-------|---------|-------|--------|
| **Ollama** | Free | Fast | High | Medium | Local models |
| **OpenAI** | Paid | Fast | Medium | Easy | GPT-4, GPT-3.5 |
| **HuggingFace** | Free/Paid | Medium | Medium | Easy | Open source |

### Recommendations

- **For Development**: Start with Ollama (free, private)
- **For Production**: OpenAI (reliable, high quality)
- **For Experimentation**: HuggingFace (many models, free tier)

## Troubleshooting

### Common Issues

1. **Ollama Connection Failed**
   ```bash
   # Ensure Ollama is running
   ollama serve
   
   # Check if models are available
   ollama list
   
   # Pull required models
   ollama pull llama3:latest
   ```

2. **OpenAI API Errors**
   - Verify API key is correct
   - Check billing/quota limits
   - Ensure model is available in your region

3. **HuggingFace Timeouts**
   - Models may take time to "warm up"
   - Increase timeout in model profile
   - Try different models

### Debug Mode

Enable debug logging in your `.env`:
```env
LOG_LEVEL=debug
```

Check logs for detailed error information:
```bash
tail -f storage/logs/laravel.log
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests
5. Submit a pull request

## License

This package is open-sourced software licensed under the MIT license.

## Acknowledgments

- Built on FilamentPHP
- Inspired by ChatGPT and similar interfaces
- Community-driven development
