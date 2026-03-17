import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: 'xi8cgvcxkjpn6r3rrxir',
    wsHost: 'reverb-ws.glitter.tw',
    wsPort: 443,
    wssPort: 443,
    forceTLS: true,
    enabledTransports: ['ws'],
    disableStats: true,
});

window.Echo.connector.pusher.config.wsHost = 'reverb-ws.glitter.tw';
window.Echo.connector.pusher.config.wssHost = 'reverb-ws.glitter.tw';
window.Echo.connector.pusher.config.httpHost = 'reverb-ws.glitter.tw';
window.Echo.connector.pusher.config.httpsPort = 443;
window.Echo.connector.pusher.config.wsPort = 443;
window.Echo.connector.pusher.config.wssPort = 443;
window.Echo.connector.pusher.config.forceTLS = true;
