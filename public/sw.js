const CACHE_NAME = "penglipuran-v1";
const urlsToCache = [
    "/",
    "/offline",
    "/build/app.css",
    "/build/app.js",
    "/icons/icon-home.svg",
    "/icons/icon-explore.svg",
    "/icons/icon-ar-scan.svg",
    "/icons/icon-umkm.svg",
    "/icons/icon-profile.svg",
];

self.addEventListener("install", (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            console.log("Opened cache");
            return cache.addAll(urlsToCache);
        }),
    );
});

self.addEventListener("fetch", (event) => {
    // Network-first for API calls
    if (event.request.url.includes("/api/")) {
        event.respondWith(
            fetch(event.request)
                .then((response) => {
                    return response;
                })
                .catch(() => {
                    return caches.match(event.request);
                }),
        );
    } else {
        // Cache-first for static assets
        event.respondWith(
            caches.match(event.request).then((response) => {
                return response || fetch(event.request);
            }),
        );
    }
});

self.addEventListener("activate", (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                }),
            );
        }),
    );
});
