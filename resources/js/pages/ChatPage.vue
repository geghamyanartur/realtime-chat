<template>
    <div class="flex flex-col gap-6">
        <header class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm uppercase tracking-[0.2em] text-emerald-200">Realtime + AI</p>
                <h1 class="text-3xl font-semibold leading-tight text-white lg:text-4xl">
                    Vue chat on Laravel, with an AI co-host
                </h1>
                <p class="mt-2 max-w-2xl text-sm text-slate-300">
                    Send messages instantly, let the AI jump in, and watch updates stream across everyone connected.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <span
                    class="inline-flex items-center gap-2 rounded-full bg-emerald-500/15 px-3 py-1 text-xs font-semibold text-emerald-100 ring-1 ring-emerald-400/30"
                >
                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    Live chat: {{ currentChat?.name || 'General' }}
                </span>
                <span
                    class="inline-flex items-center gap-2 rounded-full bg-cyan-500/15 px-3 py-1 text-xs font-semibold text-cyan-100 ring-1 ring-cyan-400/30"
                >
                    AI model: {{ aiModelLabel }}
                </span>
            </div>
        </header>

        <div class="flex flex-col gap-3 rounded-2xl border border-slate-800/70 bg-slate-900/60 p-4">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div class="flex flex-col gap-1">
                    <p class="text-xs uppercase tracking-[0.2em] text-emerald-200">Chat</p>
                    <h3 class="text-lg font-semibold text-white">
                        {{ currentChat?.name || 'General' }}
                        <span v-if="currentChat?.is_private" class="ml-2 rounded-full border border-amber-400/40 bg-amber-500/10 px-2 py-[2px] text-[11px] text-amber-200">
                            Private
                        </span>
                    </h3>
                    <p class="text-xs text-slate-400">Switch rooms or create a new private chat.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <select
                        class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-sm text-slate-100"
                        :value="chatsStore.selectedChatId || ''"
                        @change="chatsStore.setSelected(Number($event.target.value))"
                    >
                        <option v-for="chat in chatsStore.chats" :key="chat.id" :value="chat.id">
                            {{ chat.name }} {{ chat.is_private ? '(private)' : '' }}
                        </option>
                    </select>
                </div>
            </div>
                <div class="flex flex-col gap-3 md:flex-row">
                    <div class="flex-1">
                        <label class="text-xs uppercase tracking-[0.2em] text-slate-400">Create private chat</label>
                        <div class="mt-2 flex gap-2">
                            <input
                                v-model="newChatName"
                                class="w-full rounded-lg border border-slate-700 bg-slate-900/80 px-3 py-2 text-sm text-slate-100 outline-none focus:border-emerald-400 focus:ring-1 focus:ring-emerald-400"
                                placeholder="Team sync"
                                :disabled="!currentUser"
                            />
                            <button
                                class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-sm font-semibold text-slate-50 transition hover:border-slate-600 hover:bg-slate-700 disabled:opacity-60"
                                :disabled="!newChatName || !currentUser"
                                @click="handleCreateChat"
                            >
                                Create
                            </button>
                        </div>
                        <p v-if="!currentUser" class="mt-1 text-xs text-slate-400">Sign in to create private chats.</p>
                    </div>
                    <div class="flex-1">
                        <label class="text-xs uppercase tracking-[0.2em] text-slate-400">Join via invite</label>
                        <div class="mt-2 flex gap-2">
                            <input
                                v-model="joinCode"
                                class="w-full rounded-lg border border-slate-700 bg-slate-900/80 px-3 py-2 text-sm text-slate-100 outline-none focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400"
                                placeholder="Invite code"
                            />
                            <button
                                class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-sm font-semibold text-slate-50 transition hover:border-slate-600 hover:bg-slate-700 disabled:opacity-60"
                                :disabled="!joinCode"
                                @click="handleJoinChat"
                            >
                                Join
                            </button>
                        </div>
                    </div>
                <div v-if="currentChat?.owned && currentChat.invite_code" class="flex-1 rounded-lg border border-slate-800/70 bg-slate-900/70 p-3 text-sm text-slate-100">
                    <p class="text-xs uppercase tracking-[0.2em] text-emerald-200">Invite</p>
                    <div class="mt-2 flex items-center gap-2">
                        <code class="rounded-md bg-slate-800 px-2 py-1 text-emerald-100">{{ currentChat.invite_code }}</code>
                        <button
                            class="rounded-md px-2 py-1 text-xs font-semibold text-slate-200 transition hover:bg-slate-800/80"
                            @click="copyInvite(currentChat.invite_code)"
                        >
                            Copy
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-5 lg:grid-cols-[1.6fr,1fr]">
            <section class="rounded-2xl border border-slate-800/70 bg-slate-900/70 p-4 shadow-lg backdrop-blur">
                <div class="mb-3 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <label class="flex flex-col text-xs uppercase tracking-[0.2em] text-slate-400">
                        Display name
                        <input
                            v-model="sender"
                            :disabled="Boolean(currentUser)"
                            class="mt-1 rounded-lg border border-slate-700/80 bg-slate-800/80 px-3 py-2 text-sm text-slate-50 outline-none ring-emerald-400/40 focus:border-emerald-400/50 focus:ring-2 disabled:cursor-not-allowed disabled:opacity-70"
                            :placeholder="currentUser ? 'Using account name' : 'Your handle'"
                        />
                        <span v-if="currentUser" class="mt-1 text-[11px] text-emerald-200">Using signed-in name (cannot edit).</span>
                        <span v-else class="mt-1 text-[11px] text-slate-400">Guests can pick a handle for this session.</span>
                    </label>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="h-2.5 w-2.5 rounded-full" :class="statusDot"></span>
                        <span class="text-slate-300">{{ realtimeStatusLabel }}</span>
                    </div>
                </div>

                <div
                    class="relative flex h-[56vh] flex-col overflow-hidden rounded-xl border border-slate-800 bg-slate-950/70"
                    ref="messagesPane"
                >
                    <div ref="messagesScroller" class="flex-1 space-y-3 overflow-y-auto p-4">
                        <div v-if="messagesStore.loading" class="space-y-3">
                            <div v-for="n in 4" :key="n" class="h-16 rounded-xl bg-slate-800/60"></div>
                        </div>
                        <div v-else-if="!messagesStore.messages.length" class="grid h-full place-items-center text-sm text-slate-400">
                            Start the conversation with a message below.
                        </div>
                        <template v-else>
                            <article
                                v-for="message in messagesStore.messages"
                                :key="message.id"
                                class="flex flex-col gap-1"
                                :class="getMessageAlignment(message)"
                            >
                                <div class="flex items-center gap-2 text-xs text-slate-400">
                                    <span class="font-semibold" :class="message.is_ai ? 'text-emerald-200' : 'text-cyan-200'">
                                        {{ message.sender }}
                                    </span>
                                    <span>·</span>
                                    <span :class="message.error ? 'text-rose-300' : 'text-slate-400'">
                                        {{ formatTime(message) }}
                                    </span>
                                    <span v-if="message.pending" class="ml-2 inline-block h-2 w-2 animate-pulse rounded-full bg-emerald-300"></span>
                                    <span v-else-if="message.error" class="ml-2 text-rose-200 text-xs">error</span>
                                </div>
                                <div
                                class="max-w-[90%] rounded-2xl px-4 py-3 text-sm leading-relaxed shadow-lg shadow-slate-950/40"
                                    :class="[
                                        message.is_ai
                                            ? 'bg-emerald-500/10 border border-emerald-500/30'
                                            : isOwnMessage(message)
                                                ? 'bg-cyan-500/10 border border-cyan-400/40'
                                                : 'bg-slate-800/70 border border-slate-700/70',
                                        message.error ? 'border-rose-400/60 text-rose-100' : '',
                                        message.pending ? 'opacity-80' : '',
                                    ]"
                                >
                                    <div class="chat-rich space-y-1" v-html="renderBody(message.body)"></div>
                                </div>
                            </article>
                        </template>
                    </div>
                </div>

                <div class="mt-4 flex flex-col gap-3 md:flex-row md:items-end">
                    <div class="flex-1">
                        <label class="text-xs uppercase tracking-[0.2em] text-slate-400">Message</label>
                        <ChatEditor v-model="draft" class="mt-2" placeholder="Share updates, questions, or @ mention the AI." @submit="sendMessage" />
                        <p class="mt-1 text-[11px] text-slate-400">Cmd/Ctrl + Enter to send.</p>
                    </div>
                    <div class="flex gap-2">
                        <button
                            class="rounded-xl border border-slate-700 bg-slate-800 px-4 py-2 text-sm font-semibold text-slate-50 transition hover:border-slate-600 hover:bg-slate-700 disabled:cursor-not-allowed disabled:opacity-60"
                            :disabled="messagesStore.sending"
                            @click="sendMessage"
                        >
                            {{ messagesStore.sending ? 'Sending…' : 'Send' }}
                        </button>
                        <button
                            class="rounded-xl border border-emerald-400/60 bg-emerald-500/80 px-4 py-2 text-sm font-semibold text-emerald-950 shadow-lg shadow-emerald-500/30 transition hover:-translate-y-px hover:bg-emerald-400 disabled:cursor-not-allowed disabled:opacity-70"
                            :disabled="messagesStore.sending || messagesStore.aiThinking"
                            @click="sendWithAi"
                        >
                            {{ messagesStore.aiThinking ? 'AI is thinking…' : 'Send + Ask AI' }}
                        </button>
                    </div>
                </div>

                <p v-if="messagesStore.error" class="mt-3 text-sm text-rose-300">
                    {{ messagesStore.error }}
                </p>
            </section>

            <aside
                class="flex flex-col gap-4 rounded-2xl border border-slate-800/70 bg-linear-to-b from-slate-900/80 to-slate-950/80 p-5 shadow-lg backdrop-blur"
            >
                <div>
                    <p class="text-xs uppercase tracking-[0.2em] text-emerald-200">AI playbook</p>
                    <h2 class="mt-2 text-xl font-semibold text-white">What the co-host can do</h2>
                    <ul class="mt-3 space-y-2 text-sm text-slate-300">
                        <li>· Answer questions and summarize ongoing threads.</li>
                        <li>· Keep replies short (under ~80 words).</li>
                        <li>· Uses the last few messages as context to stay on topic.</li>
                    </ul>
                </div>
                <div class="rounded-xl border border-slate-800 bg-slate-900/70 p-4 text-sm text-slate-200">
                    <p class="font-semibold text-white">API endpoints</p>
                    <ul class="mt-2 space-y-1 text-slate-300">
                        <li><code>/api/messages</code> – list + create messages</li>
                        <li><code>/api/messages/ai-reply</code> – request AI response</li>
                    </ul>
                    <p class="mt-3 text-xs text-slate-500">
                        Set <code>OPENAI_API_KEY</code> to enable the live model; otherwise you’ll get a friendly offline response.
                    </p>
                </div>
            </aside>
        </div>

        <footer class="mt-2 flex flex-col gap-2 border-t border-slate-800/70 pt-6 text-sm text-slate-400 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-full border border-slate-800 bg-slate-900 px-3 py-1 text-xs text-slate-200">
                    Laravel · Vue · Echo
                </span>
                <span class="rounded-full border border-slate-800 bg-slate-900 px-3 py-1 text-xs text-slate-200">
                    AI replies optional per send
                </span>
            </div>
            <p class="text-slate-500">Built for realtime collaboration; keep this tab open to stay synced.</p>
        </footer>
    </div>
</template>

<script setup>
import { computed, nextTick, onMounted, onBeforeUnmount, ref, watch } from 'vue';
import { storeToRefs } from 'pinia';
import ChatEditor from '@/components/editor/ChatEditor.vue';
import { useAuthStore } from '../stores/useAuthStore';
import { useMessagesStore } from '../stores/useMessagesStore';
import { useChatsStore } from '../stores/useChatsStore';

const messagesStore = useMessagesStore();
const chatsStore = useChatsStore();
const draft = ref('');
const sender = ref('You');
const authStore = useAuthStore();
const { token: authToken, user: currentUser } = storeToRefs(authStore);
const { currentChat } = storeToRefs(chatsStore);
const newChatName = ref('');
const joinCode = ref('');
const currentChannelName = ref('');
const authLoading = ref(false);

const messagesPane = ref(null);
const messagesScroller = ref(null);

const aiModelLabel = computed(() => import.meta.env.VITE_AI_MODEL ?? 'gpt-4o-mini');

const activeSender = computed(() => currentUser.value?.name || sender.value);

const statusDot = computed(() => {
    switch (messagesStore.realtimeStatus) {
        case 'live':
            return 'bg-emerald-400 shadow-[0_0_0_6px_rgba(16,185,129,0.12)]';
        case 'checking':
            return 'bg-amber-300 shadow-[0_0_0_6px_rgba(253,224,71,0.12)]';
        default:
            return 'bg-slate-500 shadow-[0_0_0_6px_rgba(148,163,184,0.12)]';
    }
});

const realtimeStatusLabel = computed(() => {
    if (messagesStore.realtimeStatus === 'live') return 'Realtime ready';
    if (messagesStore.realtimeStatus === 'checking') return 'Waiting for websocket...';
    return 'Realtime disabled (using HTTP)';
});

const formatTime = (message) => {
    if (message.pending) return 'sending…';
    if (message.error) return 'failed';
    const date = message.created_at ? new Date(message.created_at) : new Date();
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

const scrollToBottom = async () => {
    await nextTick();
    const target = messagesScroller.value || messagesPane.value;
    if (target) {
        target.scrollTop = target.scrollHeight;
    }
};

const loadMessages = async (chatId) => {
    if (!chatId) return;
    await messagesStore.loadMessages(chatId);
    scrollToBottom();
};

const sendToApi = async (body) => {
    const sent = await messagesStore.sendMessage({
        body,
        sender: sender.value,
        activeSender: activeSender.value,
        chatId: chatsStore.selectedChatId,
        senderId: currentUser.value?.id,
    });
    await scrollToBottom();
    return sent;
};

const sendMessage = async () => {
    const body = sanitizeHtml(draft.value);
    if (!body) return;
    draft.value = '';
    await sendToApi(body);
};

const sendWithAi = async () => {
    const body = sanitizeHtml(draft.value);
    if (!body) return;
    draft.value = '';

    const sent = await messagesStore.sendWithAi({
        body,
        sender: sender.value,
        activeSender: activeSender.value,
        chatId: chatsStore.selectedChatId,
        senderId: currentUser.value?.id,
    });
    if (sent) {
        await scrollToBottom();
    }
};

const subscribeRealtime = (chatId) => {
    if (typeof window === 'undefined' || !chatId) return;

    const connect = () => {
        if (currentChannelName.value && window.Echo) {
            window.Echo.leave(currentChannelName.value);
        }

        if (!window.Echo) {
            messagesStore.setRealtimeStatus('offline');
            return;
        }

        const channelName = `chat.${chatId}`;
        currentChannelName.value = channelName;
        messagesStore.setRealtimeStatus('checking');

        try {
            window.Echo.channel(channelName)
                .listen('.message.created', (payload) => {
                    if (payload.chat_id !== chatId) return;
                    messagesStore.setRealtimeStatus('live');
                    messagesStore.pushMessage(payload);
                    scrollToBottom();
                })
                .error(() => {
                    messagesStore.setRealtimeStatus('offline');
                });
        } catch {
            messagesStore.setRealtimeStatus('offline');
        }
    };

    if (window.Echo) {
        connect();
    } else {
        messagesStore.setRealtimeStatus('checking');
        window.addEventListener('echo-ready', connect, { once: true });
    }
};

onMounted(async () => {
    await chatsStore.fetchChats();
    const chatId = chatsStore.selectedChatId;
    if (chatId) {
        await loadMessages(chatId);
        subscribeRealtime(chatId);
    }
    sender.value = currentUser.value?.name || 'You';

    if (typeof window !== 'undefined') {
        window.addEventListener('chats-refresh', refreshChats);
    }
});

onBeforeUnmount(() => {
    if (typeof window !== 'undefined') {
        window.removeEventListener('chats-refresh', refreshChats);
    }
});

watch(
    () => messagesStore.messages.length,
    () => scrollToBottom(),
);

watch(
    () => currentUser.value?.name,
    (name) => {
        sender.value = name || 'You';
    },
);

watch(
    () => chatsStore.selectedChatId,
    async (chatId, prev) => {
        if (chatId && chatId !== prev) {
            await loadMessages(chatId);
            subscribeRealtime(chatId);
        }
    },
);

watch(
    () => currentUser.value,
    async (user, prev) => {
        if (prev && !user) {
            await chatsStore.fetchChats();
            if (chatsStore.chats.length) {
                chatsStore.setSelected(chatsStore.chats[0].id);
                await loadMessages(chatsStore.selectedChatId);
                subscribeRealtime(chatsStore.selectedChatId);
            } else {
                messagesStore.messages = [];
            }
        }
    },
);

const refreshChats = async () => {
    await chatsStore.fetchChats();
    if (chatsStore.selectedChatId) {
        await loadMessages(chatsStore.selectedChatId);
        subscribeRealtime(chatsStore.selectedChatId);
    }
};

const handleCreateChat = async () => {
    if (!newChatName.value.trim()) return;
    await chatsStore.createChat(newChatName.value.trim());
    newChatName.value = '';
};

const handleJoinChat = async () => {
    if (!joinCode.value.trim()) return;
    await chatsStore.joinChat(joinCode.value.trim());
    joinCode.value = '';
};

const copyInvite = async (code) => {
    if (!code || !navigator?.clipboard) return;
    try {
        await navigator.clipboard.writeText(code);
    } catch (_) {
        // ignore
    }
};

const sanitizeHtml = (html) => {
    if (!html) return '';
    const allowed = new Set(['P', 'BR', 'STRONG', 'B', 'EM', 'I', 'UL', 'OL', 'LI', 'CODE', 'PRE', 'BLOCKQUOTE']);
    const parser = new DOMParser();
    const doc = parser.parseFromString(html, 'text/html');

    const cleanse = (node) => {
        const children = Array.from(node.childNodes);
        for (const child of children) {
            if (child.nodeType === Node.ELEMENT_NODE) {
                if (!allowed.has(child.tagName)) {
                    const text = document.createTextNode(child.textContent || '');
                    node.replaceChild(text, child);
                    continue;
                }
                while (child.attributes.length) {
                    child.removeAttribute(child.attributes[0].name);
                }
                cleanse(child);
            }
        }
    };

    cleanse(doc.body);
    return doc.body.innerHTML.trim();
};

const renderBody = (body) => {
    return sanitizeHtml(body);
};

const isOwnMessage = (message) => {
    if (!currentUser.value) return false;
    return message.sender_id === currentUser.value.id;
};

const getMessageAlignment = (message) => {
    if (message.is_ai) return 'items-start';
    if (isOwnMessage(message) && currentChat?.value?.is_private) return 'items-end';
    return 'items-start';
};
</script>

<style scoped>
:deep(.chat-rich ul) {
    list-style: disc;
    padding-left: 1.25rem;
    margin: 0.4rem 0;
}

:deep(.chat-rich ol) {
    list-style: decimal;
    padding-left: 1.25rem;
    margin: 0.4rem 0;
}

:deep(.chat-rich li) {
    margin: 0.15rem 0;
}

:deep(.chat-rich code) {
    background: rgba(148, 163, 184, 0.2);
    padding: 0.1rem 0.25rem;
    border-radius: 0.25rem;
}

:deep(.chat-rich blockquote) {
    border-left: 3px solid rgba(255, 255, 255, 0.25);
    margin: 1rem 0;
    padding-left: 1rem;
}

:deep(.chat-rich pre) {
    background: rgba(255, 255, 255, 0.25);
    border-radius: 0.5rem;
    color: inherit;
    font-family: 'JetBrainsMono', monospace;
    margin: 1.5rem 0;
    padding: 0.75rem 1rem;
}

:deep(.chat-rich pre code) {
    background: none;
    color: inherit;
    font-size: 0.8rem;
    padding: 0;
}
</style>
