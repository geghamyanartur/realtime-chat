<template>
    <ModalFrame :open="open" :label="modeLabel" :title="modeTitle" :subtitle="modeSubtitle" @close="$emit('close')">
        <div class="space-y-3">
            <div v-if="mode === 'register'" class="space-y-1">
                <label class="text-xs uppercase tracking-[0.2em] text-slate-400">Name</label>
                <input
                    v-model="form.name"
                    class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-sm text-slate-50 outline-none focus:border-emerald-400 focus:ring-1 focus:ring-emerald-400"
                    placeholder="Your name"
                />
            </div>
            <div class="space-y-1">
                <label class="text-xs uppercase tracking-[0.2em] text-slate-400">Email</label>
                <input
                    v-model="form.email"
                    type="email"
                    class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-sm text-slate-50 outline-none focus:border-emerald-400 focus:ring-1 focus:ring-emerald-400"
                    placeholder="you@example.com"
                />
            </div>
            <div class="space-y-1">
                <label class="text-xs uppercase tracking-[0.2em] text-slate-400">Password</label>
                <input
                    v-model="form.password"
                    type="password"
                    class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-sm text-slate-50 outline-none focus:border-emerald-400 focus:ring-1 focus:ring-emerald-400"
                    placeholder="••••••"
                />
            </div>
        </div>

        <div class="mt-5 flex items-center justify-between">
            <button
                class="rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-emerald-950 shadow hover:bg-emerald-400 disabled:opacity-60"
                :disabled="loading"
                @click="submit"
            >
                {{ loading ? 'Working…' : mode === 'login' ? 'Sign in' : 'Create account' }}
            </button>
            <button class="text-xs text-cyan-200 hover:text-cyan-100" @click="toggleMode">
                {{ mode === 'login' ? 'Need an account?' : 'Have an account?' }}
            </button>
        </div>

        <p v-if="error" class="mt-3 text-xs text-rose-300">{{ error }}</p>
    </ModalFrame>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue';
import axios from 'axios';
import { useAuthStore } from '@/stores/useAuthStore';
import ModalFrame from './ModalFrame.vue';

const props = defineProps({
    open: { type: Boolean, default: false },
    initialMode: { type: String, default: 'login' },
});

const emit = defineEmits(['close', 'authenticated']);
const authStore = useAuthStore();

const mode = ref(props.initialMode);
const loading = ref(false);
const error = ref('');
const form = reactive({
    name: '',
    email: '',
    password: '',
});

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            mode.value = props.initialMode;
            error.value = '';
            loading.value = false;
            form.name = '';
            form.email = '';
            form.password = '';
        }
    },
);

const modeLabel = computed(() => (mode.value === 'login' ? 'Sign in' : 'Register'));
const modeTitle = computed(() => (mode.value === 'login' ? 'Welcome back' : 'Create your account'));
const modeSubtitle = computed(() =>
    mode.value === 'login'
        ? 'Log in to sync your display name across devices.'
        : 'Sign up to chat with your saved identity.',
);

const toggleMode = () => {
    mode.value = mode.value === 'login' ? 'register' : 'login';
    error.value = '';
    if (mode.value === 'login') {
        form.name = '';
    }
};

const submit = async () => {
    loading.value = true;
    error.value = '';
    try {
        const payload = {
            email: form.email,
            password: form.password,
        };

        if (mode.value === 'register') {
            payload.name = form.name;
            await axios.post('/api/auth/register', payload);
        }

        const { data } = await axios.post('/api/auth/login', payload);
        if (data?.token) {
            authStore.setAuthToken(data.token);
        }
        const me = await authStore.fetchMe();
        emit('authenticated', { token: data?.token, user: me || data?.user });
        emit('close');
    } catch (err) {
        error.value = err?.response?.data?.message || 'Could not authenticate.';
    } finally {
        loading.value = false;
    }
};
</script>
