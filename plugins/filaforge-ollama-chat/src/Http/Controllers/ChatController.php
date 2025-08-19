<?php

namespace Filaforge\OllamaChat\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Filaforge\OllamaChat\Models\Conversation;
use Filaforge\OllamaChat\Models\Message;

class ChatController extends Controller
{
    public function models()
    {
        $base = rtrim(config('ollama-chat.api_url'), '/');
        try {
            $response = Http::timeout(5)->get($base . '/api/tags');
            if ($response->ok()) {
                $json = $response->json();
                $models = collect($json['models'] ?? [])->map(function ($m) {
                    return [
                        'name' => $m['name'] ?? ($m['model'] ?? null),
                        'modified_at' => $m['modified_at'] ?? null,
                        'size' => $m['size'] ?? null,
                    ];
                })->filter(fn($m)=>!empty($m['name']))->values();
                return response()->json(['models' => $models]);
            }
            return response()->json(['error' => 'Unable to fetch models','models'=>[]], $response->status());
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage(), 'models'=>[]], 500);
        }
    }

    public function send(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string|max:20000',
            'conversation_id' => 'nullable|integer',
            'model' => 'nullable|string|max:100'
        ]);

        $prompt = $request->string('prompt');
        $userId = auth()->id() ?? 'guest';
        $conversation = null;
        if ($request->filled('conversation_id')) {
            $conversation = Conversation::find($request->input('conversation_id'));
        }
        if (! $conversation) {
            $conversation = Conversation::create([
                'user_id' => $userId,
                'conversation_data' => json_encode([]),
            ]);
        }

        // Store user message
        Message::create([
            'conversation_id' => $conversation->id,
            'message' => $prompt,
            'sender' => 'user',
        ]);

        $reply = null; $error = null;
        $base = rtrim(config('ollama-chat.api_url'), '/');
        $model = $request->string('model')->isNotEmpty() ? $request->string('model') : config('ollama-chat.default_model', 'llama3:latest');

        // Simple context rebuild
        $history = json_decode($conversation->conversation_data, true) ?: [];
        $contextText = '';
        foreach ($history as $h) { $contextText .= strtoupper($h['role']).": ".$h['content']."\n"; }
        $finalPrompt = $contextText . 'USER: ' . $prompt;

        try {
            $response = Http::timeout(config('ollama-chat.timeout', 30))
                ->post($base . '/api/generate', [
                    'model' => $model,
                    'prompt' => $finalPrompt,
                    'stream' => false,
                ]);
            if ($response->ok()) {
                $json = $response->json();
                $reply = $json['response'] ?? ($json['message'] ?? '[no response]');
            } else {
                $error = 'Upstream HTTP '.$response->status();
            }
        } catch (\Throwable $e) {
            Log::warning('Ollama request failed: '.$e->getMessage());
            $error = $e->getMessage();
        }
        if (! $reply) { $reply = 'Ollama unavailable.'; }

        // Store assistant message
        Message::create([
            'conversation_id' => $conversation->id,
            'message' => $reply,
            'sender' => 'assistant',
        ]);

        // Append to history
        $history[] = ['role' => 'user', 'content' => $prompt];
        $history[] = ['role' => 'assistant', 'content' => $reply];
        $conversation->conversation_data = json_encode($history);
        $conversation->save();

        return response()->json([
            'reply' => $reply,
            'error' => $error,
            'conversation_id' => $conversation->id,
        ]);
    }
}
