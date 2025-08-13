<?php

return [
    'api_key' => env('DEEPSEEK_API_KEY'),
    'base_url' => env('DEEPSEEK_BASE_URL', 'https://api.deepseek.com'),
    'allow_roles' => [], // Empty array means allow all authenticated users
    'stream' => env('DEEPSEEK_STREAM', false),
];
