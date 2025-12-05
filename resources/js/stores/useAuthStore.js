import { defineStore } from 'pinia';
import { ref } from 'vue';
import axios from 'axios';

export const useAuthStore = defineStore('auth', () => {
    const user = ref(null);
    const token = ref(localStorage.getItem('chat_token') || '');
    const loading = ref(false);
    const error = ref('');

    const setAuthToken = (value) => {
        token.value = value || '';
        if (value) {
            localStorage.setItem('chat_token', value);
            axios.defaults.headers.common.Authorization = `Bearer ${value}`;
        } else {
            localStorage.removeItem('chat_token');
            delete axios.defaults.headers.common.Authorization;
        }
    };

    const fetchMe = async () => {
        if (!token.value) return null;
        loading.value = true;
        error.value = '';
        setAuthToken(token.value);
        try {
            const { data } = await axios.get('/api/auth/me');
            user.value = data;
            return data;
        } catch (err) {
            setAuthToken('');
            user.value = null;
            error.value = err?.response?.data?.message || 'Session expired.';
            return null;
        } finally {
            loading.value = false;
        }
    };

    const logout = async () => {
        try {
            await axios.post('/api/auth/logout');
        } catch {
            // ignore
        }
        setAuthToken('');
        user.value = null;
    };

    return {
        user,
        token,
        loading,
        error,
        setAuthToken,
        fetchMe,
        logout,
    };
});
