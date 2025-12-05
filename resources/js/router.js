import { createRouter, createWebHistory } from 'vue-router';
import ChatPage from './pages/ChatPage.vue';

const routes = [
    {
        path: '/',
        name: 'chat',
        component: ChatPage,
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior() {
        return { top: 0 };
    },
});

export default router;
