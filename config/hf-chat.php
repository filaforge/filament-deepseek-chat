<?php

return [
	'api_key' => env('HF_API_TOKEN'),
	'base_url' => env('HF_BASE_URL', 'https://api-inference.huggingface.co'),
	'model_id' => env('HF_MODEL_ID', 'meta-llama/Meta-Llama-3-8B-Instruct'),
	'stream' => env('HF_STREAM', false),
	'allow_roles' => [],
	'admin_roles' => [],
	'timeout' => env('HF_TIMEOUT', 60),
];


