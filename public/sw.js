const CACHE_NAME = "penglipuran-v9";
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

    // Skip media requests and range requests completely to allow native browser seek/Accept-Ranges
    if (
        event.request.headers.has("range") ||
        url.pathname.match(/\.(mp3|mp4|wav|ogg|webm|glb)$/i) ||
        url.pathname.includes("/storage/")
    ) {
        return;
    }

    // Skip admin panel, owner panel, staff panel, api, authentication, and livewire requests
    if (
        url.pathname.startsWith("/admin") ||
        url.pathname.startsWith("/owner") ||
        url.pathname.startsWith("/staff") ||
        url.pathname.startsWith("/api") ||
        url.pathname.startsWith("/login") ||
        url.pathname.startsWith("/logout") ||
        url.pathname.startsWith("/register")
    ) {
        return;
    }

    // Strategy 1: Page Navigation (HTML pages) -> Network-First
    if (event.request.mode === "navigate") {
        event.respondWith(
            fetch(event.request)
                .then((networkResponse) => {
                    if (networkResponse.status === 200) {
                        const responseClone = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(event.request, responseClone);
                        });
                    }
                    return networkResponse;
                })
                .catch(async () => {
                    // Fallback to cache if offline, otherwise show offline page
                    const cachedResponse = await caches.match(event.request);
                    return cachedResponse || caches.match(OFFLINE_URL);
                }),
        );
        return;
    }

    // Strategy 2: Static Assets -> Stale-While-Revalidate
    event.respondWith(
        caches.match(event.request).then((cachedResponse) => {
            if (cachedResponse) {
                // Background update
                fetch(event.request)
                    .then((networkResponse) => {
                        if (
                            networkResponse.status === 200 &&
                            (event.request.url.includes("/build/") ||
                                event.request.url.includes("/icons/") ||
                                event.request.url.includes("/images/") ||
                                event.request.url.includes("/fonts."))
                        ) {
                            const responseClone = networkResponse.clone();
                            caches.open(CACHE_NAME).then((cache) => {
                                cache.put(event.request, responseClone);
                            });
                        }
                    })
                    .catch(() => {});
                return cachedResponse;
            }
            return fetch(event.request);
        }),
    );
});
