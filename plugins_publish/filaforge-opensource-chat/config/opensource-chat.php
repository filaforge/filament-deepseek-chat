<?php

return [
    // Roles allowed to access open source chat (empty = all authenticated users)
    'allow_roles' => [],

    // Roles that can view all conversations from all users (admin style)
    'admin_roles' => [],

    // Default model identifier (can be overridden per user setting or per profile)
    'default_model_id' => env('OSCHAT_MODEL_ID', 'YourModel/Identifier'),

    // Base URL for inference endpoint (OpenAI-compatible or custom)
    'base_url' => env('OSCHAT_BASE_URL', 'https://api.openai.com'),

    // Whether to attempt OpenAI-compatible chat/completions endpoint first
    'use_openai' => env('OSCHAT_USE_OPENAI', true),

    // Default streaming preference
    'stream' => env('OSCHAT_STREAM', false),

    // Generic timeouts
    'timeout' => env('OSCHAT_TIMEOUT', 120),
    'connect_timeout' => env('OSCHAT_CONNECT_TIMEOUT', 30),
];
