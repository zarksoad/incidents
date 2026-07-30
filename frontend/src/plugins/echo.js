import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

window.Pusher = Pusher

const isHttps = window.location.protocol === 'https:'
const wsHost = import.meta.env.VITE_REVERB_HOST || window.location.hostname
const wsPort = import.meta.env.VITE_REVERB_PORT
    ? parseInt(import.meta.env.VITE_REVERB_PORT)
    : 8080

try {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY || 'reverbkey',
        wsHost: wsHost,
        wsPort: isHttps ? 443 : wsPort,
        wssPort: isHttps ? 443 : wsPort,
        forceTLS: isHttps,
        enabledTransports: ['ws', 'wss'],
        disableStats: true,
        enableLogging: false,
    })
} catch (e) {
    console.warn('[Echo] Could not connect to WebSocket server:', e.message)
}
