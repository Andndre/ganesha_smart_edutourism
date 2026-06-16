import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

let wsHost = import.meta.env.VITE_REVERB_HOST;
if (!wsHost || wsHost === 'localhost') {
    wsHost = window.location.hostname;
}

const isProduction = window.location.protocol === 'https:';

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: wsHost,
    wsPort: wsHost === 'localhost' ? (import.meta.env.VITE_REVERB_PORT ?? 8081) : 80,
    wssPort: wsHost === 'localhost' ? (import.meta.env.VITE_REVERB_PORT ?? 8081) : 443,
    forceTLS: isProduction,
    enabledTransports: ['ws', 'wss'],
});
