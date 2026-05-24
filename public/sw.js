const CACHE_NAME = "penglipuran-v1";
const OFFLINE_URL = "/offline";

const urlsToCache = ["/", OFFLINE_URL];

self.addEventListener("install", (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            console.log("PWA: Membuka cache dan menyimpan halaman dasar");
            return cache.addAll(urlsToCache);
        }),
    );
    self.skipWaiting();
});

self.addEventListener("activate", (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        console.log("PWA: Menghapus cache usang:", cacheName);
                        return caches.delete(cacheName);
                    }
                }),
            );
        }),
    );
    self.clients.claim();
});

self.addEventListener("fetch", (event) => {
    if (event.request.method !== "GET") return;

    const url = new URL(event.request.url);

    // Skip admin panel, api, and livewire requests
    if (
        url.pathname.startsWith('/admin') ||
        url.pathname.startsWith('/api') ||
        url.pathname.includes('/livewire/')
    ) {
        return;
    }

    event.respondWith(
        caches.match(event.request).then((cachedResponse) => {
            const fetchPromise = fetch(event.request)
                .then((networkResponse) => {
                    if (
                        networkResponse.status === 200 &&
                        (event.request.mode === "navigate" ||
                            event.request.url.includes("/build/") ||
                            event.request.url.includes("/icons/") ||
                            event.request.url.includes("/images/") ||
                            event.request.url.includes("/fonts."))
                    ) {
                        const responseClone = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(event.request, responseClone);
                        });
                    }
                    return networkResponse;
                })
                .catch(() => {
                    // Fallback to offline page if fetching navigation page fails
                    if (event.request.mode === "navigate") {
                        return caches.match(OFFLINE_URL);
                    }
                });

            // Return cached response instantly if available, otherwise fetch from network
            return cachedResponse || fetchPromise;
        })
    );
});
