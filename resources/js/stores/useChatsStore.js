import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import axios from 'axios';

export const useChatsStore = defineStore('chats', () => {
    const chats = ref([]);
    const loading = ref(false);
    const selectedChatId = ref(null);
    const error = ref('');

    const currentChat = computed(() => chats.value.find((c) => c.id === selectedChatId.value) || null);
    const inviteCode = computed(() => (currentChat.value && currentChat.value.owned ? currentChat.value.invite_code : null));

    const setSelected = (id) => {
        selectedChatId.value = id;
    };

    const fetchChats = async () => {
        loading.value = true;
        error.value = '';
        try {
            const { data } = await axios.get('/api/chats');
            chats.value = data;
            if (!selectedChatId.value && data.length) {
                selectedChatId.value = data[0].id;
            }
        } catch (_) {
            error.value = 'Unable to load chats.';
        } finally {
            loading.value = false;
        }
    };

    const createChat = async (name) => {
        error.value = '';
        const { data } = await axios.post('/api/chats', { name, is_private: true });
        chats.value.push(data);
        selectedChatId.value = data.id;
        return data;
    };

    const joinChat = async (code) => {
        error.value = '';
        const { data } = await axios.post('/api/chats/join', { code });
        const exists = chats.value.some((c) => c.id === data.id);
        if (!exists) {
            chats.value.push(data);
        }
        selectedChatId.value = data.id;
        return data;
    };

    return {
        chats,
        loading,
        error,
        selectedChatId,
        currentChat,
        inviteCode,
        fetchChats,
        createChat,
        joinChat,
        setSelected,
    };
});
