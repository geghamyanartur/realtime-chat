<?php

namespace App\Http\Controllers;

use App\Events\MessageCreated;
use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $chat = $this->resolveChat($request);

        $messages = Message::where('chat_id', $chat->id)
            ->latest()
            ->take(50)
            ->get()
            ->sortBy('created_at')
            ->values();

        return response()->json($messages);
    }

    public function store(Request $request)
    {
        $chat = $this->resolveChat($request);

        $data = $request->validate([
            'sender' => ['required', 'string', 'max:50'],
            'body' => ['required', 'string', 'max:2000'],
            'chat_id' => ['required', 'integer', 'exists:chats,id'],
        ]);

        $user = $this->resolveUser($request);
        $senderName = $user?->name ?? $data['sender'];

        $message = Message::create([
            'sender' => $senderName,
            'sender_id' => $user?->id,
            'body' => $data['body'],
            'chat_id' => $chat->id,
        ]);

        broadcast(new MessageCreated($message))->toOthers();

        return response()->json($message, 201);
    }

    public function aiReply(Request $request)
    {
        $chat = $this->resolveChat($request);

        $data = $request->validate([
            'sender' => ['required', 'string', 'max:50'],
            'body' => ['required', 'string', 'max:2000'],
            'chat_id' => ['required', 'integer', 'exists:chats,id'],
        ]);

        $user = $this->resolveUser($request);
        $senderName = $user?->name ?? $data['sender'];

        $context = Message::where('chat_id', $chat->id)
            ->latest()
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
            'sender_id' => null,
            'body' => $aiMessage,
            'is_ai' => true,
            'chat_id' => $chat->id,
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

    protected function resolveChat(Request $request): Chat
    {
        $chatId = $request->input('chat_id') ?? $request->query('chat_id');
        $chat = $chatId ? Chat::find($chatId) : Chat::where('is_private', false)->first();

        if (! $chat) {
            abort(404, 'Chat not found.');
        }

        if ($chat->is_private) {
            $user = $this->resolveUser($request);
            if (! $user || ! $this->userInChat($user, $chat)) {
                abort(403, 'You do not have access to this chat.');
            }
        }

        return $chat;
    }

    protected function resolveUser(Request $request): ?User
    {
        if ($request->user()) {
            return $request->user();
        }

        $token = $request->bearerToken();
        if (! $token) {
            return null;
        }

        return User::where('api_token', $token)->first();
    }

    protected function userInChat(User $user, Chat $chat): bool
    {
        if ($chat->owner_id === $user->id) {
            return true;
        }

        return $chat->users()->where('users.id', $user->id)->exists();
    }
}
