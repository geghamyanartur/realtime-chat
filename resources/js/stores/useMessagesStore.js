import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import axios from 'axios';

export const useMessagesStore = defineStore('messages', () => {
    const messages = ref([]);
    const loading = ref(false);
    const sending = ref(false);
    const aiThinking = ref(false);
    const error = ref('');
    const realtimeStatus = ref('checking'); // checking | live | offline
    const currentChatId = ref(null);

    const hasMessages = computed(() => messages.value.length > 0);

    const pushMessage = (incoming) => {
        // If matching temp marker, replace; otherwise append if not duplicate.
        if (incoming.localTempId) {
            const idx = messages.value.findIndex((m) => m.localTempId === incoming.localTempId);
            if (idx !== -1) {
                messages.value[idx] = { ...messages.value[idx], ...incoming, pending: false, error: false };
                return;
            }
        }

        const exists = messages.value.some((m) => m.id === incoming.id && m.id !== undefined);
        if (!exists) {
            messages.value.push({ pending: false, error: false, ...incoming });
        }
    };

    const loadMessages = async (chatId) => {
        currentChatId.value = chatId;
        loading.value = true;
        try {
            const { data } = await axios.get('/api/messages', { params: { chat_id: chatId } });
            messages.value = data;
        } catch (err) {
            error.value = 'Unable to load messages right now.';
        } finally {
            loading.value = false;
        }
    };

    const sendMessage = async ({ body, sender, activeSender, chatId, senderId }) => {
        if (!body) return null;
        const targetChatId = chatId || currentChatId.value;
        sending.value = true;
        error.value = '';

        const tempId = `local-${Date.now()}-${Math.random().toString(16).slice(2)}`;
        const optimistic = {
            id: undefined,
            localTempId: tempId,
            sender: activeSender,
            body,
            is_ai: false,
            sender_id: senderId,
            chat_id: targetChatId,
            created_at: null,
            pending: true,
            error: false,
        };

        messages.value.push(optimistic);

        try {
            const { data } = await axios.post('/api/messages', { sender, body, chat_id: targetChatId });
            pushMessage({ ...data, localTempId: tempId });
            return body;
        } catch (err) {
            error.value = 'Could not send message.';
            const idx = messages.value.findIndex((m) => m.localTempId === tempId);
            if (idx !== -1) {
                messages.value[idx] = { ...messages.value[idx], pending: false, error: true, created_at: null };
            }
            return null;
        } finally {
            sending.value = false;
        }
    };

    const sendWithAi = async ({ body, sender, activeSender, chatId, senderId }) => {
        if (!body) return null;
        const sent = await sendMessage({ body, sender, activeSender, chatId, senderId });
        if (!sent) return null;

        aiThinking.value = true;
        try {
            const { data } = await axios.post('/api/messages/ai-reply', { sender: activeSender, body, chat_id: chatId || currentChatId.value });
            pushMessage(data);
            return data;
        } catch (err) {
            error.value = 'AI is unavailable; try again shortly.';
            return null;
        } finally {
            aiThinking.value = false;
        }
    };

    const setRealtimeStatus = (state) => {
        realtimeStatus.value = state;
    };

    return {
        messages,
        loading,
        sending,
        aiThinking,
        error,
        realtimeStatus,
        hasMessages,
        loadMessages,
        sendMessage,
        sendWithAi,
        pushMessage,
        setRealtimeStatus,
    };
});
