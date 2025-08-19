<?php

namespace Filaforge\OllamaChat\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class OllamaClient
{
    protected $client;
    protected $baseUri;

    public function __construct()
    {
    $this->baseUri = rtrim(config('ollama-chat.api_url'), '/');
        $this->client = new Client(['base_uri' => $this->baseUri]);
    }

    public function sendMessage($message)
    {
        try {
            $response = $this->client->post('/api/generate', [
                'json' => [
                    'model' => config('ollama-chat.default_model', 'llama2'),
                    'prompt' => $message,
                    'stream' => false,
                ]
            ]);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            // Handle the exception as needed
            return ['error' => $e->getMessage()];
        }
    }

    public function getConversations()
    {
        try {
            $response = $this->client->get('/conversations'); // placeholder
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            // Handle the exception as needed
            return ['error' => $e->getMessage()];
        }
    }

    public function getSettings()
    {
        try {
            $response = $this->client->get('/settings');
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            // Handle the exception as needed
            return ['error' => $e->getMessage()];
        }
    }
}