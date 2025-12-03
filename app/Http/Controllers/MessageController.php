<?php

namespace App\Http\Controllers;

use App\Events\MessageCreated;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MessageController extends Controller
{
    public function index()
    {
        $messages = Message::latest()
            ->take(50)
            ->get()
            ->sortBy('created_at')
            ->values();

        return response()->json($messages);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sender' => ['required', 'string', 'max:50'],
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $message = Message::create($data);

        broadcast(new MessageCreated($message))->toOthers();

        return response()->json($message, 201);
    }

    public function aiReply(Request $request)
    {
        $data = $request->validate([
            'sender' => ['required', 'string', 'max:50'],
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $context = Message::latest()
            ->take(12)
            ->get()
            ->sortBy('created_at')
            ->map(function (Message $message) {
                return [
                    'role' => $message->is_ai ? 'assistant' : 'user',
                    'content' => "{$message->sender}: {$message->body}",
                ];
            })
            ->values()
            ->all();

        $aiMessage = $this->callAi($data['body'], $context);

        $message = Message::create([
            'sender' => 'AI Assistant',
            'body' => $aiMessage,
            'is_ai' => true,
        ]);

        broadcast(new MessageCreated($message))->toOthers();

        return response()->json($message, 201);
    }

    protected function callAi(string $prompt, array $context): string
    {
        $apiKey = env('OPENAI_API_KEY');

        if (! $apiKey) {
            return "AI is offline (missing OPENAI_API_KEY). Here's an echo: {$prompt}";
        }

        $payload = [
            'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a concise, friendly assistant living in a realtime group chat. Keep replies under 80 words and avoid heavy formatting.',
                ],
                ...$context,
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
            'max_tokens' => 180,
            'temperature' => 0.4,
        ];

        try {
            $response = Http::withToken($apiKey)
                ->timeout(12)
                ->post('https://api.openai.com/v1/chat/completions', $payload)
                ->json();

            return trim($response['choices'][0]['message']['content'] ?? 'AI did not return a message.');
        } catch (\Throwable $e) {
            return 'AI is unavailable right now. Please try again soon.';
        }
    }
}
