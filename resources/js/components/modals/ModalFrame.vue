<template>
    <teleport to="body">
        <transition name="fade">
            <div
                v-if="open"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 px-4 backdrop-blur"
                @click="handleBackdrop"
            >
                <div
                    class="relative w-full max-w-md rounded-2xl border border-slate-800 bg-slate-900/90 p-6 shadow-2xl shadow-emerald-500/10"
                    role="dialog"
                    aria-modal="true"
                    @click.stop
                >
                    <button
                        class="absolute right-3 top-3 rounded-full p-2 text-slate-400 hover:bg-slate-800 hover:text-slate-200"
                        aria-label="Close"
                        @click="$emit('close')"
                    >
                        ✕
                    </button>

                    <p v-if="label" class="text-xs uppercase tracking-[0.2em] text-emerald-200">{{ label }}</p>
                    <h2 v-if="title" class="mt-1 text-xl font-semibold text-white">{{ title }}</h2>
                    <p v-if="subtitle" class="mt-1 text-sm text-slate-400">{{ subtitle }}</p>

                    <div class="mt-4">
                        <slot />
                    </div>
                </div>
            </div>
        </transition>
    </teleport>
</template>

<script setup>
import { onBeforeUnmount, watch } from 'vue';

const props = defineProps({
    open: { type: Boolean, default: false },
    label: { type: String, default: '' },
    title: { type: String, default: '' },
    subtitle: { type: String, default: '' },
});

const emit = defineEmits(['close']);

let previousOverflow = '';
let isLocked = false;

const lockBody = () => {
    if (typeof document === 'undefined' || isLocked) return;
    previousOverflow = document.body.style.overflow;
    document.body.style.overflow = 'hidden';
    isLocked = true;
};

const unlockBody = () => {
    if (typeof document === 'undefined' || !isLocked) return;
    document.body.style.overflow = previousOverflow;
    isLocked = false;
};

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            lockBody();
        } else {
            unlockBody();
        }
    },
    { immediate: true },
);

onBeforeUnmount(() => {
    unlockBody();
});

const handleBackdrop = (event) => {
    if (event.target === event.currentTarget) {
        emit('close');
    }
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
