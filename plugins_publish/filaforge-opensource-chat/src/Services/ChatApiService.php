<?php

namespace Filaforge\OpensourceChat\Services;

use Illuminate\Support\Facades\Http;
use Filaforge\OpensourceChat\Models\ModelProfile;

class ChatApiService
{
    protected ModelProfile $profile;
    protected array $config;

    public function __construct(ModelProfile $profile)
    {
        $this->profile = $profile;
        $this->config = config('opensource-chat.providers.' . $profile->provider, []);
    }

    /**
     * Send a chat completion request to the configured provider
     */
    public function chatCompletion(array $messages, bool $stream = false): array
    {
        $provider = $this->profile->provider;
        
        switch ($provider) {
            case 'openai':
                return $this->openaiChatCompletion($messages, $stream);
            case 'huggingface':
                return $this->huggingfaceChatCompletion($messages, $stream);
            case 'ollama':
                return $this->ollamaChatCompletion($messages, $stream);
            default:
                throw new \Exception("Unsupported provider: {$provider}");
        }
    }

    /**
     * OpenAI API chat completion
     */
    protected function openaiChatCompletion(array $messages, bool $stream = false): array
    {
        $baseUrl = $this->profile->base_url ?: $this->config['base_url'];
        $apiKey = $this->profile->api_key ?: $this->config['api_key'];
        
        $payload = [
            'model' => $this->profile->model_id,
            'messages' => $messages,
            'stream' => $stream,
            'temperature' => 0.7,
        ];

        if ($this->profile->system_prompt) {
            array_unshift($payload['messages'], [
                'role' => 'system',
                'content' => $this->profile->system_prompt
            ]);
        }

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer {$apiKey}",
        ])
        ->timeout($this->profile->timeout ?: 60)
        ->post($baseUrl . '/chat/completions', $payload);

        if (!$response->successful()) {
            throw new \Exception("API request failed: " . $response->body());
        }

        return $response->json();
    }

    /**
     * HuggingFace API chat completion
     */
    protected function huggingfaceChatCompletion(array $messages, bool $stream = false): array
    {
        $baseUrl = $this->profile->base_url ?: $this->config['base_url'];
        $apiKey = $this->profile->api_key ?: $this->config['api_key'];
        $modelId = $this->profile->model_id;
        
        // HuggingFace uses different endpoint structure
        $endpoint = str_replace('{model}', $modelId, $this->config['endpoint']);
        
        $payload = [
            'model' => $modelId,
            'messages' => $messages,
            'stream' => $stream,
            'temperature' => 0.7,
            'max_tokens' => 2048,
        ];

        if ($this->profile->system_prompt) {
            array_unshift($payload['messages'], [
                'role' => 'system',
                'content' => $this->profile->system_prompt
            ]);
        }

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer {$apiKey}",
        ])
        ->timeout($this->profile->timeout ?: 120)
        ->post($baseUrl . $endpoint, $payload);

        if (!$response->successful()) {
            throw new \Exception("HuggingFace API request failed: " . $response->body());
        }

        return $response->json();
    }

    /**
     * Ollama local API chat completion
     */
    protected function ollamaChatCompletion(array $messages, bool $stream = false): array
    {
        $baseUrl = $this->profile->base_url ?: $this->config['base_url'];
        
        $payload = [
            'model' => $this->profile->model_id,
            'messages' => $messages,
            'stream' => $stream,
            'options' => [
                'temperature' => 0.7,
            ],
        ];

        if ($this->profile->system_prompt) {
            array_unshift($payload['messages'], [
                'role' => 'system',
                'content' => $this->profile->system_prompt
            ]);
        }

        // Ollama doesn't require authentication for local instances
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])
        ->timeout($this->profile->timeout ?: 60)
        ->post($baseUrl . '/v1/chat/completions', $payload);

        if (!$response->successful()) {
            throw new \Exception("Ollama API request failed: " . $response->body());
        }

        return $response->json();
    }

    /**
     * Test the connection to the provider
     */
    public function testConnection(): array
    {
        try {
            $testMessages = [
                ['role' => 'user', 'content' => 'Hello, this is a connection test.']
            ];
            
            $response = $this->chatCompletion($testMessages, false);
            
            return [
                'success' => true,
                'message' => 'Connection successful',
                'response' => $response
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'response' => null
            ];
        }
    }

    /**
     * Get available models for the current provider
     */
    public function getAvailableModels(): array
    {
        return $this->config['models'] ?? [];
    }

    /**
     * Check if the provider requires an API key
     */
    public function requiresApiKey(): bool
    {
        return $this->profile->provider !== 'ollama';
    }
}
