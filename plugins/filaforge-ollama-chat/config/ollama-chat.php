<?php

return [
    'api_key' => env('OLLAMA_API_KEY', ''),
    // Local Ollama daemon default URL (adjust via OLLAMA_API_URL if different)
    'api_url' => env('OLLAMA_API_URL', 'http://127.0.0.1:11434'),
    'default_model' => env('OLLAMA_DEFAULT_MODEL', 'llama3:latest'),
    'timeout' => env('OLLAMA_TIMEOUT', 30),
    'retry_attempts' => env('OLLAMA_RETRY_ATTEMPTS', 3),
];