self.addEventListener('push', event => {
    const data = event.data?.json() ?? {};
    event.waitUntil(
        self.registration.showNotification(data.title ?? 'Penglipuran', {
            body: data.body ?? '',
            icon: data.icon ?? '/favicon.ico',
            badge: data.badge,
            data: data.data ?? {},
        })
    );
});

self.addEventListener('notificationclick', event => {
    event.notification.close();
    const url = event.notification.data?.url;
    if (url) {
        event.waitUntil(clients.openWindow(url));
    }
});
