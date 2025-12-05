<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $user = $this->resolveUser($request);

        $publicChats = Chat::where('is_private', false)->get();

        if (! $user) {
            return response()->json($publicChats);
        }

        $privateChats = Chat::where('owner_id', $user->id)
            ->orWhereHas('users', fn ($q) => $q->where('users.id', $user->id))
            ->get();

        $all = $publicChats->merge($privateChats)->unique('id')->values()->map(function (Chat $chat) use ($user) {
            return $this->formatChat($chat, $user);
        });

        return response()->json($all);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Authentication required'], 401);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ]);

        $chat = Chat::create([
            'name' => $data['name'],
            'is_private' => true,
            'invite_code' => Str::random(10),
            'owner_id' => $user->id,
        ]);

        $chat->users()->attach($user->id);

        return response()->json($this->formatChat($chat, $user), 201);
    }

    public function join(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Authentication required'], 401);
        }

        $data = $request->validate([
            'code' => ['required', 'string'],
        ]);

        $chat = Chat::where('invite_code', $data['code'])->first();

        if (! $chat) {
            return response()->json(['message' => 'Invite not found.'], 404);
        }

        $chat->users()->syncWithoutDetaching($user->id);

        return response()->json($this->formatChat($chat, $user));
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

    protected function formatChat(Chat $chat, ?User $user = null): array
    {
        $isOwner = $user ? $chat->owner_id === $user->id : false;
        $isMember = $user ? ($isOwner || $chat->users->contains(fn ($u) => $u->id === $user->id)) : false;

        return [
            'id' => $chat->id,
            'name' => $chat->name,
            'is_private' => $chat->is_private,
            'invite_code' => $isOwner ? $chat->invite_code : null,
            'owned' => $isOwner,
            'member' => $isMember,
        ];
    }
}
