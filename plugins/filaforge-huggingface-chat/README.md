# Filaforge Hugging Face Chat

Filament panel plugin to chat with open-source Hugging Face models via the Inference API (or self-hosted TGI/vLLM).

## Install (in app context)

- Add provider automatically via package tools. Or manually register the panel plugin in your Filament panel:

```php
->plugins([\\Filaforge\\HuggingfaceChat\\Providers\\HfChatPanelPlugin::make()])
```

## Env

```bash
HF_API_TOKEN=your_token
HF_MODEL_ID=meta-llama/Meta-Llama-3-8B-Instruct
# HF_BASE_URL=https://api-inference.huggingface.co
```

## Notes

- Non-streaming by default. For streaming, use a server that supports SSE and adjust the request accordingly.

