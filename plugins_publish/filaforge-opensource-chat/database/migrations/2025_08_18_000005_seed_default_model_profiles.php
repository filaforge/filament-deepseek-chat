<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Insert default model profiles for different providers
        DB::table('oschat_model_profiles')->insert([
            [
                'name' => 'OpenAI GPT-4',
                'provider' => 'openai',
                'model_id' => 'gpt-4',
                'base_url' => 'https://api.openai.com/v1',
                'api_key' => null, // Users should set their own
                'stream' => true,
                'timeout' => 60,
                'system_prompt' => 'You are a helpful assistant.',
                'is_active' => false, // Requires API key
                'per_minute_limit' => 60,
                'per_day_limit' => 1000,
                'extra' => json_encode(['temperature' => 0.7]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'OpenAI GPT-3.5 Turbo',
                'provider' => 'openai',
                'model_id' => 'gpt-3.5-turbo',
                'base_url' => 'https://api.openai.com/v1',
                'api_key' => null,
                'stream' => true,
                'timeout' => 60,
                'system_prompt' => 'You are a helpful assistant.',
                'is_active' => false,
                'per_minute_limit' => 90,
                'per_day_limit' => 2000,
                'extra' => json_encode(['temperature' => 0.7]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'HuggingFace Llama 3 8B',
                'provider' => 'huggingface',
                'model_id' => 'meta-llama/Meta-Llama-3-8B-Instruct',
                'base_url' => 'https://api-inference.huggingface.co',
                'api_key' => null,
                'stream' => false,
                'timeout' => 120,
                'system_prompt' => 'You are a helpful assistant.',
                'is_active' => false,
                'per_minute_limit' => 30,
                'per_day_limit' => 500,
                'extra' => json_encode(['temperature' => 0.7, 'max_tokens' => 2048]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Ollama Llama 3 Local',
                'provider' => 'ollama',
                'model_id' => 'llama3:latest',
                'base_url' => 'http://localhost:11434',
                'api_key' => null,
                'stream' => false,
                'timeout' => 60,
                'system_prompt' => 'You are a helpful assistant.',
                'is_active' => true, // No API key required
                'per_minute_limit' => null,
                'per_day_limit' => null,
                'extra' => json_encode(['temperature' => 0.7]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Ollama Code Llama Local',
                'provider' => 'ollama',
                'model_id' => 'codellama:latest',
                'base_url' => 'http://localhost:11434',
                'api_key' => null,
                'stream' => false,
                'timeout' => 60,
                'system_prompt' => 'You are a helpful coding assistant.',
                'is_active' => true,
                'per_minute_limit' => null,
                'per_day_limit' => null,
                'extra' => json_encode(['temperature' => 0.1]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('oschat_model_profiles')->truncate();
    }
};
