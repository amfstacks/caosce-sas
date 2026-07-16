// caosce_app/sw.js
const CACHE_NAME = 'caosce-offline-shell-v5';

// 1. INSTALL PHASE
self.addEventListener('install', (event) => {
    self.skipWaiting();
    
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll([
                './exam', 
                './public/assets/localforage.min.js',
                './public/assets/alpine.min.js',
                './public/assets/tailwind.js',
                // './public/js/sync-engine.js',
                'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap'
            ]);
        })
    );
});

// 2. ACTIVATE PHASE
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    self.clients.claim(); // Take control of the page immediately
});

// 3. FETCH PHASE (Bulletproof Offline Catch)
self.addEventListener('fetch', (event) => {
    // A. Handle Page Navigation (Hitting refresh, changing URLs)
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request).catch(() => {
                // FORCE the cache to return the Master Shell, ignoring any URL differences
                const shellUrl = new URL('./exam', self.registration.scope).href;
                return caches.match(shellUrl, { ignoreSearch: true });
            })
        );
        return;
    }

    // B. Handle Static Assets
    event.respondWith(
        caches.match(event.request, { ignoreSearch: true }).then((cachedResponse) => {
            return cachedResponse || fetch(event.request);
        })
    );
});