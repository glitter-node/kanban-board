import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

const pusher = new Pusher('xi8cgvcxkjpn6r3rrxir', {
    wsHost: 'reverb-ws.glitter.tw',
    wssHost: 'reverb-ws.glitter.tw',
    wsPort: 443,
    wssPort: 443,
    forceTLS: true,
    enabledTransports: ['ws'],
    disableStats: true,
});

window.Pusher = Pusher;
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: 'xi8cgvcxkjpn6r3rrxir',
    client: pusher,
});
