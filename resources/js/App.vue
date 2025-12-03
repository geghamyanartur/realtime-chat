<template>
    <div class="min-h-screen bg-slate-950 text-slate-50 font-['Space_Grotesk']">
        <div class="pointer-events-none fixed inset-0 opacity-60">
            <div class="absolute -left-10 top-10 h-64 w-64 rounded-full bg-emerald-500/20 blur-[120px]"></div>
            <div class="absolute right-4 top-20 h-72 w-72 rounded-full bg-cyan-500/20 blur-[120px]"></div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_20%,rgba(94,234,212,0.08),transparent_35%),radial-gradient(circle_at_80%_10%,rgba(14,165,233,0.08),transparent_30%),linear-gradient(145deg,rgba(15,23,42,0.8),rgba(15,23,42,0.95))]"></div>
        </div>

        <div class="relative mx-auto flex max-w-6xl flex-col gap-6 px-4 py-10">
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
                        Live channel: public.chat
                    </span>
                    <span
                        class="inline-flex items-center gap-2 rounded-full bg-cyan-500/15 px-3 py-1 text-xs font-semibold text-cyan-100 ring-1 ring-cyan-400/30"
                    >
                        AI model: {{ aiModelLabel }}
                    </span>
                </div>
            </header>

            <div class="grid gap-5 lg:grid-cols-[1.6fr,1fr]">
                <section class="rounded-2xl border border-slate-800/70 bg-slate-900/70 p-4 shadow-lg backdrop-blur">
                    <div class="mb-3 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <label class="flex flex-col text-xs uppercase tracking-[0.2em] text-slate-400">
                            Display name
                            <input
                                v-model="sender"
                                class="mt-1 rounded-lg border border-slate-700/80 bg-slate-800/80 px-3 py-2 text-sm text-slate-50 outline-none ring-emerald-400/40 focus:border-emerald-400/50 focus:ring-2"
                                placeholder="Your handle"
                            />
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
                            <div v-if="loadingMessages" class="space-y-3">
                                <div v-for="n in 4" :key="n" class="h-16 rounded-xl bg-slate-800/60"></div>
                            </div>
                            <div v-else-if="!messages.length" class="grid h-full place-items-center text-sm text-slate-400">
                                Start the conversation with a message below.
                            </div>
                            <template v-else>
                                <article
                                    v-for="message in messages"
                                    :key="message.id"
                                    class="flex flex-col gap-1"
                                    :class="message.is_ai ? 'items-start' : 'items-end'"
                                >
                                    <div class="flex items-center gap-2 text-xs text-slate-400">
                                        <span class="font-semibold" :class="message.is_ai ? 'text-emerald-200' : 'text-cyan-200'">
                                            {{ message.sender }}
                                        </span>
                                        <span>·</span>
                                        <span>{{ formatTime(message.created_at) }}</span>
                                    </div>
                                    <div
                                        class="max-w-[90%] rounded-2xl px-4 py-3 text-sm leading-relaxed shadow-lg shadow-slate-950/40"
                                        :class="message.is_ai ? 'bg-emerald-500/10 border border-emerald-500/30' : 'bg-slate-800/70 border border-slate-700/70'"
                                    >
                                        {{ message.body }}
                                    </div>
                                </article>
                            </template>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-col gap-3 md:flex-row md:items-end">
                        <div class="flex-1">
                            <label class="text-xs uppercase tracking-[0.2em] text-slate-400">Message</label>
                            <textarea
                                v-model="draft"
                                rows="3"
                                class="mt-2 w-full rounded-xl border border-slate-800 bg-slate-900/80 px-3 py-2 text-sm text-slate-50 outline-none ring-cyan-400/40 focus:border-cyan-400/60 focus:ring-2"
                                placeholder="Share updates, questions, or @ mention the AI."
                                @keydown.meta.enter.prevent="sendMessage"
                                @keydown.ctrl.enter.prevent="sendMessage"
                            ></textarea>
                            <p class="mt-1 text-[11px] text-slate-400">Cmd/Ctrl + Enter to send.</p>
                        </div>
                        <div class="flex gap-2">
                            <button
                                class="rounded-xl border border-slate-700 bg-slate-800 px-4 py-2 text-sm font-semibold text-slate-50 transition hover:border-slate-600 hover:bg-slate-700 disabled:cursor-not-allowed disabled:opacity-60"
                                :disabled="sending"
                                @click="sendMessage"
                            >
                                {{ sending ? 'Sending…' : 'Send' }}
                            </button>
                            <button
                                class="rounded-xl border border-emerald-400/60 bg-emerald-500/80 px-4 py-2 text-sm font-semibold text-emerald-950 shadow-lg shadow-emerald-500/30 transition hover:-translate-y-[1px] hover:bg-emerald-400 disabled:cursor-not-allowed disabled:opacity-70"
                                :disabled="sending || aiThinking"
                                @click="sendWithAi"
                            >
                                {{ aiThinking ? 'AI is thinking…' : 'Send + Ask AI' }}
                            </button>
                        </div>
                    </div>

                    <p v-if="error" class="mt-3 text-sm text-rose-300">
                        {{ error }}
                    </p>
                </section>

                <aside class="flex flex-col gap-4 rounded-2xl border border-slate-800/70 bg-gradient-to-b from-slate-900/80 to-slate-950/80 p-5 shadow-lg backdrop-blur">
                    <div>
                        <p class="text-xs uppercase tracking-[0.2em] text-emerald-200">AI playbook</p>
                        <h2 class="mt-2 text-xl font-semibold text-white">What the co-host can do</h2>
                        <ul class="mt-3 space-y-2 text-sm text-slate-300">
                            <li>· Answer questions and summarize ongoing threads.</li>
                            <li>· Keep replies short (under ~80 words).</li>
                            <li>· Uses the last few messages as context to stay on topic.</li>
                        </ul>
                    </div>
                    <div class="rounded-xl border border-cyan-400/30 bg-cyan-500/10 p-4 text-sm text-cyan-50">
                        <p class="font-semibold text-cyan-100">Quick tip</p>
                        <p class="mt-2">
                            Realtime uses the <code class="text-emerald-200">public.chat</code> channel. Configure your Pusher-compatible
                            server (or Laravel Reverb) and set <code class="text-emerald-200">BROADCAST_CONNECTION=pusher</code> to make it live.
                        </p>
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
        </div>
    </div>
</template>

<script setup>
import axios from 'axios';
import { computed, nextTick, onMounted, ref, watch } from 'vue';

const messages = ref([]);
const loadingMessages = ref(true);
const sending = ref(false);
const aiThinking = ref(false);
const draft = ref('');
const sender = ref('You');
const error = ref('');
const realtimeStatus = ref('checking'); // checking | live | offline

const messagesPane = ref(null);
const messagesScroller = ref(null);

const aiModelLabel = computed(() => import.meta.env.VITE_AI_MODEL ?? 'gpt-4o-mini');

const statusDot = computed(() => {
    switch (realtimeStatus.value) {
        case 'live':
            return 'bg-emerald-400 shadow-[0_0_0_6px_rgba(16,185,129,0.12)]';
        case 'checking':
            return 'bg-amber-300 shadow-[0_0_0_6px_rgba(253,224,71,0.12)]';
        default:
            return 'bg-slate-500 shadow-[0_0_0_6px_rgba(148,163,184,0.12)]';
    }
});

const realtimeStatusLabel = computed(() => {
    if (realtimeStatus.value === 'live') return 'Realtime ready';
    if (realtimeStatus.value === 'checking') return 'Waiting for websocket...';
    return 'Realtime disabled (using HTTP)';
});

const formatTime = (value) => {
    const date = value ? new Date(value) : new Date();
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

const pushMessage = (incoming) => {
    const exists = messages.value.some((m) => m.id === incoming.id);
    if (!exists) {
        messages.value.push(incoming);
    }
};

const scrollToBottom = async () => {
    await nextTick();
    const target = messagesScroller.value || messagesPane.value;
    if (target) {
        target.scrollTop = target.scrollHeight;
    }
};

const loadMessages = async () => {
    loadingMessages.value = true;
    try {
        const { data } = await axios.get('/api/messages');
        messages.value = data;
    } catch (err) {
        error.value = 'Unable to load messages right now.';
    } finally {
        loadingMessages.value = false;
        scrollToBottom();
    }
};

const sendToApi = async (body) => {
    if (!body) return null;
    sending.value = true;
    error.value = '';
    try {
        const { data } = await axios.post('/api/messages', { sender: sender.value, body });
        pushMessage(data);
        await scrollToBottom();
        return body;
    } catch (err) {
        error.value = 'Could not send message.';
        return null;
    } finally {
        sending.value = false;
    }
};

const sendMessage = async () => {
    const body = draft.value.trim();
    if (!body) return;
    draft.value = '';
    await sendToApi(body);
};

const sendWithAi = async () => {
    const body = draft.value.trim();
    if (!body) return;
    draft.value = '';

    const sent = await sendToApi(body);
    if (!sent) return;

    aiThinking.value = true;
    try {
        const { data } = await axios.post('/api/messages/ai-reply', { sender: sender.value, body });
        pushMessage(data);
        await scrollToBottom();
    } catch (err) {
        error.value = 'AI is unavailable; try again shortly.';
    } finally {
        aiThinking.value = false;
    }
};

const initRealtime = () => {
    if (typeof window === 'undefined') return;

    const connect = () => {
        if (!window.Echo) {
            realtimeStatus.value = 'offline';
            return;
        }

        realtimeStatus.value = 'checking';
        try {
            window.Echo.channel('public.chat')
                .listen('.message.created', (payload) => {
                    realtimeStatus.value = 'live';
                    pushMessage(payload);
                    scrollToBottom();
                })
                .error(() => {
                    realtimeStatus.value = 'offline';
                });
        } catch {
            realtimeStatus.value = 'offline';
        }
    };

    // Connect immediately if Echo is ready, otherwise wait for bootstrap to emit.
    if (window.Echo) {
        connect();
    } else {
        realtimeStatus.value = 'checking';
        window.addEventListener('echo-ready', connect, { once: true });
    }
};

onMounted(() => {
    loadMessages();
    initRealtime();
});

watch(
    () => messages.value.length,
    () => scrollToBottom(),
);
</script>
