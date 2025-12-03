import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const pusherKey = import.meta.env.VITE_PUSHER_APP_KEY;
const pusherHost = import.meta.env.VITE_PUSHER_HOST || undefined;
const pusherPort = import.meta.env.VITE_PUSHER_PORT ? Number(import.meta.env.VITE_PUSHER_PORT) : undefined;
const pusherScheme = import.meta.env.VITE_PUSHER_SCHEME || undefined;
const pusherCluster = import.meta.env.VITE_PUSHER_APP_CLUSTER || undefined;

if (pusherKey) {
    import('pusher-js').then(({ default: Pusher }) => {
        window.Pusher = Pusher;

        import('laravel-echo').then(({ default: Echo }) => {
            window.Echo = new Echo({
                broadcaster: 'pusher',
                key: pusherKey,
                wsHost: pusherHost,
                wsPort: pusherPort,
                forceTLS: (pusherScheme ?? 'https') === 'https',
                disableStats: true,
                enabledTransports: ['ws', 'wss'],
                cluster: pusherCluster ?? 'mt1',
            });

            window.dispatchEvent(new CustomEvent('echo-ready'));
        });
    });
}
