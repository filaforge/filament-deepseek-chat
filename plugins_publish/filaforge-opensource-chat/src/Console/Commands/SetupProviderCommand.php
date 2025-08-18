<?php

namespace Filaforge\OpensourceChat\Console\Commands;

use Illuminate\Console\Command;
use Filaforge\OpensourceChat\Models\ModelProfile;
use Illuminate\Support\Facades\Http;

class SetupProviderCommand extends Command
{
    protected $signature = 'oschat:setup-provider 
                            {provider : The provider to setup (openai, huggingface, ollama)}
                            {--api-key= : API key for the provider}
                            {--base-url= : Custom base URL}
                            {--test : Test the connection after setup}';

    protected $description = 'Setup and configure AI providers for Open Source Chat';

    public function handle()
    {
        $provider = $this->argument('provider');
        $apiKey = $this->option('api-key');
        $baseUrl = $this->option('base-url');
        $test = $this->option('test');

        if (!in_array($provider, ['openai', 'huggingface', 'ollama'])) {
            $this->error('Invalid provider. Supported providers: openai, huggingface, ollama');
            return 1;
        }

        $this->info("Setting up {$provider} provider...");

        // Get provider configuration
        $config = config("opensource-chat.providers.{$provider}");
        if (!$config) {
            $this->error("No configuration found for provider: {$provider}");
            return 1;
        }

        // Update existing profiles or create new ones
        $models = $config['models'] ?? [];
        $defaultBaseUrl = $baseUrl ?: $config['base_url'];
        
        foreach ($models as $modelId => $modelName) {
            $profile = ModelProfile::where('provider', $provider)
                ->where('model_id', $modelId)
                ->first();

            if ($profile) {
                $this->line("Updating existing profile: {$profile->name}");
                $profile->update([
                    'base_url' => $defaultBaseUrl,
                    'api_key' => $apiKey,
                    'is_active' => $provider === 'ollama' || !empty($apiKey),
                ]);
            } else {
                $this->line("Creating new profile: {$modelName}");
                ModelProfile::create([
                    'name' => "{$config['name']} {$modelName}",
                    'provider' => $provider,
                    'model_id' => $modelId,
                    'base_url' => $defaultBaseUrl,
                    'api_key' => $apiKey,
                    'stream' => $provider !== 'huggingface', // HF might not support streaming well
                    'timeout' => $provider === 'huggingface' ? 120 : 60,
                    'system_prompt' => $provider === 'ollama' && str_contains($modelId, 'code') 
                        ? 'You are a helpful coding assistant.' 
                        : 'You are a helpful assistant.',
                    'is_active' => $provider === 'ollama' || !empty($apiKey),
                ]);
            }
        }

        $this->info("✅ Provider {$provider} setup completed!");

        if ($test) {
            $this->testProvider($provider);
        }

        // Show next steps
        $this->showNextSteps($provider, $apiKey);

        return 0;
    }

    protected function testProvider(string $provider): void
    {
        $this->info("Testing {$provider} connection...");

        $profile = ModelProfile::where('provider', $provider)
            ->where('is_active', true)
            ->first();

        if (!$profile) {
            $this->warn("No active profiles found for {$provider}");
            return;
        }

        try {
            $testUrl = match($provider) {
                'openai' => $profile->base_url . '/models',
                'huggingface' => 'https://api-inference.huggingface.co/status',
                'ollama' => $profile->base_url . '/api/tags',
                default => null,
            };

            if (!$testUrl) {
                $this->warn("No test endpoint available for {$provider}");
                return;
            }

            $headers = [];
            if ($provider !== 'ollama' && $profile->api_key) {
                $headers['Authorization'] = "Bearer {$profile->api_key}";
            }

            $response = Http::withHeaders($headers)->timeout(10)->get($testUrl);

            if ($response->successful()) {
                $this->info("✅ {$provider} connection test successful!");
                
                if ($provider === 'ollama') {
                    $models = $response->json('models', []);
                    $this->line("Available Ollama models: " . count($models));
                    foreach (array_slice($models, 0, 3) as $model) {
                        $this->line("  - " . ($model['name'] ?? 'Unknown'));
                    }
                }
            } else {
                $this->error("❌ {$provider} connection test failed: " . $response->status());
            }
        } catch (\Exception $e) {
            $this->error("❌ {$provider} connection test failed: " . $e->getMessage());
        }
    }

    protected function showNextSteps(string $provider, ?string $apiKey): void
    {
        $this->newLine();
        $this->line('<info>Next Steps:</info>');

        match($provider) {
            'openai' => $this->showOpenAINextSteps($apiKey),
            'huggingface' => $this->showHuggingFaceNextSteps($apiKey),
            'ollama' => $this->showOllamaNextSteps(),
        };

        $this->newLine();
        $this->line('💡 You can now use the OS Chat interface to select and test your models!');
        $this->line('💡 Go to Admin Panel > OS Chat > Model Profiles to manage your configurations.');
    }

    protected function showOpenAINextSteps(?string $apiKey): void
    {
        if (!$apiKey) {
            $this->line('1. Get your API key from https://platform.openai.com/api-keys');
            $this->line('2. Run: php artisan oschat:setup-provider openai --api-key=YOUR_KEY --test');
            $this->line('3. Or set OPENAI_API_KEY in your .env file');
        } else {
            $this->line('✅ OpenAI is ready to use!');
            $this->line('💰 Remember: OpenAI charges per token used');
        }
    }

    protected function showHuggingFaceNextSteps(?string $apiKey): void
    {
        if (!$apiKey) {
            $this->line('1. Get your API key from https://huggingface.co/settings/tokens');
            $this->line('2. Run: php artisan oschat:setup-provider huggingface --api-key=YOUR_KEY --test');
            $this->line('3. Or set HF_API_KEY in your .env file');
        } else {
            $this->line('✅ HuggingFace is ready to use!');
            $this->line('🔄 Note: HuggingFace models may take longer to respond (cold starts)');
        }
    }

    protected function showOllamaNextSteps(): void
    {
        $this->line('1. Make sure Ollama is installed: https://ollama.ai');
        $this->line('2. Start Ollama: ollama serve');
        $this->line('3. Pull models: ollama pull llama3:latest');
        $this->line('4. Available models: ollama list');
        $this->line('✅ Ollama runs locally and is free to use!');
    }
}
