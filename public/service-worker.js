const CACHE_NAME = 'quincapro-shell-v2';
const OFFLINE_PAGE = new URL('offline.html', self.registration.scope).href;
const APP_ICON = new URL('images/quincapro-icon.svg', self.registration.scope).href;

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll([OFFLINE_PAGE, APP_ICON]))
            .then(() => self.skipWaiting()),
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((cacheNames) => Promise.all(
                cacheNames
                    .filter((cacheName) => cacheName.startsWith('quincapro-shell-') && cacheName !== CACHE_NAME)
                    .map((cacheName) => caches.delete(cacheName)),
            ))
            .then(() => self.clients.claim()),
    );
});

self.addEventListener('fetch', (event) => {
    const requestUrl = new URL(event.request.url);

    if (event.request.method !== 'GET' || requestUrl.origin !== self.location.origin) {
        return;
    }

    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request).catch(async () => (
                await caches.match(OFFLINE_PAGE)
                    ?? new Response('Connexion Internet requise.', {
                        status: 503,
                        headers: { 'Content-Type': 'text/plain; charset=utf-8' },
                    })
            )),
        );
    }
});
