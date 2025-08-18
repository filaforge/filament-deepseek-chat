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
    public function send(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string|max:20000',
            'conversation_id' => 'nullable|integer',
            'model' => 'nullable|string|max:100'
        ]);

        $prompt = $request->string('prompt');
        $userId = auth()->id() ?? 'guest';

        // Find or create conversation
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
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'message' => $prompt,
            'sender' => 'user',
        ]);

        $reply = null;
        $error = null;

        $base = rtrim(config('ollama-chat.api_url'), '/');
        $model = $request->string('model')->isNotEmpty() ? $request->string('model') : config('ollama-chat.default_model', 'llama3:latest');

        // Build context (simple concatenation)
        $history = json_decode($conversation->conversation_data, true) ?: [];
        $contextText = '';
        foreach ($history as $h) {
            $contextText .= strtoupper($h['role']).": ".$h['content']."\n";
        }
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
                // Ollama returns 'response' when done
                $reply = $json['response'] ?? ($json['message'] ?? '[no response]');
            } else {
                $error = 'Upstream HTTP ' . $response->status();
            }
        } catch (\Throwable $e) {
            Log::warning('Ollama request failed: ' . $e->getMessage());
            $error = $e->getMessage();
        }

        if (! $reply) {
            $reply = 'Ollama unavailable.';
        }

        // Store assistant reply
        Message::create([
            'conversation_id' => $conversation->id,
            'message' => $reply,
            'sender' => 'assistant',
        ]);

        // Update conversation_data history (simple append array of messages)
        $history = json_decode($conversation->conversation_data, true) ?: [];
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
