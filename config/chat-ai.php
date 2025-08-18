<?php

return [
	'api_key' => env('CHAT_AI_API_TOKEN'),
	'base_url' => env('CHAT_AI_BASE_URL', 'https://router.huggingface.co'),
	'model_id' => env('CHAT_AI_MODEL_ID', 'openai/gpt-oss-120b:fireworks-ai'),
	'stream' => env('CHAT_AI_STREAM', true),
	'use_openai' => env('CHAT_AI_USE_OPENAI', true),
	'system_prompt' => env('CHAT_AI_SYSTEM_PROMPT', 'You are a helpful assistant.'),
	'allow_roles' => [],
	'admin_roles' => [],
	'timeout' => env('CHAT_AI_TIMEOUT', 60),

	// Default HF models to create if they don't exist
	'default_profiles' => [
		[
			'name' => 'Llama 3 Latest (Ollama)',
			'provider' => 'ollama',
			'model_id' => 'llama3:latest',
			'base_url' => 'http://localhost:11434',
			'api_key' => null,
			'stream' => true,
			'timeout' => 120,
			'system_prompt' => 'You are a helpful AI assistant powered by Llama 3.',
			'is_active' => false,
		],
		[
			'name' => 'Default Llama (HF)',
			'provider' => 'huggingface',
			'model_id' => 'meta-llama/Meta-Llama-3-8B-Instruct',
			'base_url' => 'https://api-inference.huggingface.co',
			'api_key' => null,
			'stream' => true,
			'timeout' => 120,
			'system_prompt' => 'You are a helpful AI assistant powered by Llama 3.',
			'is_active' => false,
		],
		[
			'name' => 'GPT-OSS 120B (Open Ai)',
			'provider' => 'Open Ai',
			'model_id' => 'openai/gpt-oss-120b:fireworks-ai',
			'base_url' => 'https://router.huggingface.co',
			'api_key' => null,
			'stream' => true,
			'timeout' => 120,
			'system_prompt' => 'You are a helpful AI assistant.',
			'is_active' => false,
		],
		[
			'name' => 'DeepSeek Chat',
			'provider' => 'deepseek (API)',
			'model_id' => 'deepseek-chat',
			'base_url' => 'https://api.deepseek.com',
			'api_key' => null,
			'stream' => true,
			'timeout' => 120,
			'system_prompt' => 'You are a helpful AI assistant.',
			'is_active' => false,
		],
	],
];


