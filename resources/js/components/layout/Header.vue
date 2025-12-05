<template>
    <header class="mb-6 flex items-center justify-between rounded-2xl border border-slate-800/70 bg-slate-900/80 px-4 py-3 shadow-lg backdrop-blur">
        <RouterLink to="/" class="flex items-center gap-3">
            <span class="grid h-10 w-10 place-items-center rounded-xl bg-emerald-500/15 text-emerald-100 ring-1 ring-emerald-400/30">
                <span class="h-2.5 w-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
            </span>
            <div>
                <p class="text-sm uppercase tracking-[0.2em] text-emerald-200">Realtime chat</p>
                <p class="text-base font-semibold text-white">Connected conversations with AI</p>
            </div>
        </RouterLink>
        <div class="flex items-center gap-2">
            <template v-if="currentUser">
                <span class="hidden text-sm text-slate-200 sm:inline">Hi, {{ currentUser.name }}</span>
                <button
                    class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-xs font-semibold text-slate-50 transition hover:border-slate-600 hover:bg-slate-700"
                    @click.prevent="logout"
                >
                    Logout
                </button>
            </template>
            <template v-else>
                <button
                    class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-xs font-semibold text-slate-50 transition hover:border-slate-600 hover:bg-slate-700"
                    @click.prevent="openAuth('login')"
                >
                    Sign in
                </button>
                <button
                    class="rounded-lg bg-emerald-500 px-3 py-2 text-xs font-semibold text-emerald-950 shadow hover:bg-emerald-400"
                    @click.prevent="openAuth('register')"
                >
                    Sign up
                </button>
            </template>
        </div>
    </header>

    <AuthModal :open="showAuthModal" :initial-mode="authModalMode" @close="showAuthModal = false" @authenticated="handleAuthSuccess" />
</template>


<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { storeToRefs } from 'pinia';
import { useAuthStore } from '@/stores/useAuthStore';
import AuthModal from '@/components/modals/AuthModal.vue';

const auth = useAuthStore();
const { user: currentUser } = storeToRefs(auth);
const showAuthModal = ref(false);
const authModalMode = ref('login');

const openAuth = (mode = 'login') => {
    authModalMode.value = mode;
    showAuthModal.value = true;
};

const logout = async () => {
    await auth.logout();
    showAuthModal.value = false;
    authModalMode.value = 'login';
};

const handleAuthSuccess = async ({ token, user }) => {
    if (token) {
        auth.setAuthToken(token);
    }
    if (user) {
        auth.user = user;
        showAuthModal.value = false;
        return;
    }
    await auth.fetchMe();
    showAuthModal.value = false;
};

const handleOpenRequest = (event) => {
    authModalMode.value = event?.detail?.mode || 'login';
    showAuthModal.value = true;
};

onMounted(() => {
    auth.fetchMe();
    if (typeof window !== 'undefined') {
        window.addEventListener('open-auth-modal', handleOpenRequest);
    }
});

onBeforeUnmount(() => {
    if (typeof window !== 'undefined') {
        window.removeEventListener('open-auth-modal', handleOpenRequest);
    }
});
</script>
