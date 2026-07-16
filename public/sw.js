// caosce_app/sw.js
const CACHE_NAME = 'caosce-offline-shell-v8';

const ASSETS_TO_CACHE = [
    './public/assets/localforage.min.js',
    './public/assets/alpine.min.js',
    './public/assets/tailwind.js',
    'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap'
];

self.addEventListener('install', (event) => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME).then(async (cache) => {
            for (let asset of ASSETS_TO_CACHE) {
                try { await cache.add(asset); } catch (e) {}
            }
        })
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) return caches.delete(cacheName);
                })
            );
        })
    );
    self.clients.claim(); 
});

self.addEventListener('fetch', (event) => {
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request).catch(async () => {
                const cache = await caches.open(CACHE_NAME);
                
                // Return the Universal Master Shell
                const fallback = await cache.match('/offline-master-shell');
                if (fallback) return fallback;
                
                return cache.match(event.request, { ignoreSearch: true });
            })
        );
        return;
    }

    event.respondWith(
        caches.match(event.request, { ignoreSearch: true }).then((cachedResponse) => {
            return cachedResponse || fetch(event.request);
        })
    );
});