<template>
    <div class="min-h-screen bg-slate-950 text-slate-50 font-['Space_Grotesk']">
        <div class="pointer-events-none fixed inset-0 opacity-60">
            <div class="absolute -left-10 top-10 h-64 w-64 rounded-full bg-emerald-500/20 blur-[120px]"></div>
            <div class="absolute right-4 top-20 h-72 w-72 rounded-full bg-cyan-500/20 blur-[120px]"></div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_20%,rgba(94,234,212,0.08),transparent_35%),radial-gradient(circle_at_80%_10%,rgba(14,165,233,0.08),transparent_30%),linear-gradient(145deg,rgba(15,23,42,0.8),rgba(15,23,42,0.95))]"></div>
        </div>

        <div class="relative mx-auto max-w-6xl px-4 py-10">
            <Header />

            <RouterView />
        </div>
    </div>
</template>

<script setup>
import { RouterView } from 'vue-router';
import Header from './components/layout/Header.vue';
</script>
