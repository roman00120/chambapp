const CACHE_NAME = 'chambapp-static-v3';
const OFFLINE_URL = '/offline.html';
const PRECACHE_URLS = [
    OFFLINE_URL,
    '/manifest.webmanifest',
    '/images/pwa/icon-192.png',
    '/images/pwa/icon-512.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(caches.open(CACHE_NAME).then((cache) => cache.addAll(PRECACHE_URLS)));
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => Promise.all(
            keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key)),
        )),
    );
    self.clients.claim();
});

const isSensitivePath = (pathname) => /^(\/cliente|\/profesional|\/admin|\/trabajos|\/cotizaciones|\/pagos|\/notificaciones|\/webhooks)/.test(pathname);
const isStaticAsset = (request, url) => request.method === 'GET'
    && url.origin === self.location.origin
    && (url.pathname.startsWith('/build/') || url.pathname.startsWith('/images/') || url.pathname === '/manifest.webmanifest');

self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    if (request.method !== 'GET' || url.origin !== self.location.origin || isSensitivePath(url.pathname)) {
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(fetch(request).catch(() => caches.match(OFFLINE_URL)));
        return;
    }

    if (isStaticAsset(request, url)) {
        event.respondWith(
            caches.match(request).then((cached) => {
                if (cached && cached.status === 200) {
                    return cached;
                }

                return fetch(request).then((response) => {
                    if (response && response.status === 200 && (response.type === 'basic' || response.type === 'default')) {
                        const copy = response.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
                    }
                    return response;
                });
            }),
        );
    }
});
