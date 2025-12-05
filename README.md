# Realtime Chat with AI Co-Host

Modern Laravel + Vue realtime chat with private rooms, invites, and an optional AI assistant. Tailwind 4 styling, Echo-powered live updates, and TipTap-rich composer.

**Live demo:** https://realtime-chat-k29a.onrender.com/

## Features
- Public “General” room plus private chats with invite codes.
- Auth with API tokens; login/register from the in-app modal.
- Rich message composer (TipTap) with lists, blockquotes, and code blocks.
- Realtime messaging (Laravel Echo) scoped per chat channel.
- AI co-host replies (OpenAI) with chat context (optional).
- Messages show sender alignment/background in private chats.

## Tech Stack
- Laravel backend (API + broadcasting)
- Vue 3 + Pinia + Vite + Tailwind 4
- TipTap editor for rich messages
- Laravel Echo for realtime (Pusher-compatible)

## Setup
1) Install deps
```bash
npm install
composer install
```

2) Env
Copy `.env.example` to `.env`, set DB, `PUSHER_*` (or compatible), and optional `OPENAI_API_KEY`.

3) Migrate & seed default chat
```bash
php artisan migrate
```

4) Dev servers
```bash
php artisan serve
npm run dev
```

## Usage
- Register or sign in via header modal. New accounts auto-login.
- Create private chats (auth required) or join via invite code.
- Switch chats with the selector; messages/AI replies are chat-scoped.
- Compose rich text; send with Cmd/Ctrl+Enter. AI reply via “Send + Ask AI.”
- Pending sends show inline pulse; AI messages broadcast to the room.

## API endpoints (main)
- `POST /api/auth/register` / `POST /api/auth/login` / `POST /api/auth/logout`
- `GET /api/chats` / `POST /api/chats` (auth) / `POST /api/chats/join` (auth)
- `GET /api/messages?chat_id=` / `POST /api/messages` / `POST /api/messages/ai-reply`

## Notes
- Default channel is `chat.{id}`; “General” is created on migrate.
- Private chat creation is restricted to authenticated users; invites are visible to owners.
- If OpenAI key is missing, AI returns an offline echo.
