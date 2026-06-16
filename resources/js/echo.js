import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

const reverbKey = window.Laravel?.reverbKey || import.meta.env.VITE_REVERB_APP_KEY;
const reverbHost = window.Laravel?.reverbHost || import.meta.env.VITE_REVERB_HOST || window.location.hostname;
const reverbPort = window.Laravel?.reverbPort || import.meta.env.VITE_REVERB_PORT || 8081;

let wsHost = reverbHost;
if (!wsHost || wsHost === 'localhost' || wsHost === '127.0.0.1' || !wsHost.includes('.')) {
    wsHost = window.location.hostname;
}

const isProduction = window.location.protocol === 'https:';

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: reverbKey,
    wsHost: wsHost,
    wsPort: wsHost === 'localhost' ? reverbPort : 80,
    wssPort: wsHost === 'localhost' ? reverbPort : 443,
    forceTLS: isProduction,
    enabledTransports: ['ws', 'wss'],
});
