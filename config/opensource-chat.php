<?php

return [
    // Roles allowed to access open source chat (empty = all authenticated users)
    'allow_roles' => [],

    // Roles that can view all conversations from all users (admin style)
    'admin_roles' => [],

    // Default model identifier (can be overridden per user setting or per profile)
    'default_model_id' => env('OSCHAT_MODEL_ID', 'gpt-3.5-turbo'),

    // Base URL for inference endpoint (OpenAI-compatible or custom)
    'base_url' => env('OSCHAT_BASE_URL', 'https://api.openai.com'),

    // Whether to attempt OpenAI-compatible chat/completions endpoint first
    'use_openai' => env('OSCHAT_USE_OPENAI', true),

    // Default streaming preference
    'stream' => env('OSCHAT_STREAM', false),

    // Generic timeouts
    'timeout' => env('OSCHAT_TIMEOUT', 120),
    'connect_timeout' => env('OSCHAT_CONNECT_TIMEOUT', 30),

    // Provider-specific configurations
    'providers' => [
        'openai' => [
            'name' => 'OpenAI',
            'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
            'api_key' => env('OPENAI_API_KEY'),
            'models' => [
                'gpt-4' => 'GPT-4',
                'gpt-4-turbo' => 'GPT-4 Turbo',
                'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
            ],
            'endpoint' => '/chat/completions',
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer {api_key}',
            ],
        ],
        'huggingface' => [
            'name' => 'HuggingFace',
            'base_url' => env('HF_BASE_URL', 'https://api-inference.huggingface.co'),
            'api_key' => env('HF_API_KEY'),
            'models' => [
                'meta-llama/Meta-Llama-3-8B-Instruct' => 'Llama 3 8B Instruct',
                'meta-llama/Meta-Llama-3-70B-Instruct' => 'Llama 3 70B Instruct',
                'microsoft/DialoGPT-medium' => 'DialoGPT Medium',
                'microsoft/DialoGPT-large' => 'DialoGPT Large',
            ],
            'endpoint' => '/models/{model}/v1/chat/completions',
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer {api_key}',
            ],
        ],
        'ollama' => [
            'name' => 'Ollama (Local)',
            'base_url' => env('OLLAMA_BASE_URL', 'http://localhost:11434'),
            'api_key' => null, // Ollama doesn't require API key for local
            'models' => [
                'llama3:latest' => 'Llama 3 Latest',
                'llama3:8b' => 'Llama 3 8B',
                'llama3:70b' => 'Llama 3 70B',
                'codellama:latest' => 'Code Llama Latest',
                'mistral:latest' => 'Mistral Latest',
                'phi3:latest' => 'Phi-3 Latest',
            ],
            'endpoint' => '/v1/chat/completions',
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ],
    ],

    // Ollama local defaults (backward compatibility)
    'ollama' => [
        'base_url' => env('OLLAMA_BASE_URL', 'http://localhost:11434'),
        'default_model_id' => env('OLLAMA_MODEL_ID', 'llama3:latest'),
    ],
];
