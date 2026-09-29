<script>
    (() => {
        const grid = document.querySelector('[data-umkm-grid]');
        const pagination = document.querySelector('[data-umkm-pagination]');
        const nextLink = pagination?.querySelector('[data-umkm-next]');

        if (!grid || !pagination || !nextLink || !('IntersectionObserver' in window)) return;

        let loading = false;
        let failed = false;
        let disposed = false;
        const controller = new AbortController();
        const observer = new IntersectionObserver(entries => {
            if (entries.some(entry => entry.isIntersecting)) loadNext();
        }, { rootMargin: '300px 0px' });

        async function loadNext() {
            if (loading || failed || disposed || !nextLink.href || pagination.closest('[x-show]')?.style.display === 'none') return;

            loading = true;
            observer.unobserve(pagination);
            nextLink.setAttribute('aria-busy', 'true');
            nextLink.classList.add('animate-pulse');

            try {
                const response = await fetch(nextLink.href, {
                    headers: { 'X-UMKM-Fragment': 'directory-page' },
                    signal: controller.signal,
                });
                if (!response.ok) throw new Error(`UMKM page request failed: ${response.status}`);

                const template = document.createElement('template');
                template.innerHTML = await response.text();
                const newGrid = template.content.querySelector('[data-umkm-grid]');
                if (!newGrid?.children.length) throw new Error('UMKM page response was empty');

                grid.append(...Array.from(newGrid.children));
                const newNextLink = template.content.querySelector('[data-umkm-next]');
                if (newNextLink) {
                    nextLink.href = newNextLink.href;
                } else {
                    observer.disconnect();
                    pagination.remove();
                }
            } catch (error) {
                if (error.name !== 'AbortError') {
                    failed = true;
                    observer.disconnect();
                }
            } finally {
                loading = false;
                nextLink.removeAttribute('aria-busy');
                nextLink.classList.remove('animate-pulse');
                if (!failed && !disposed && pagination.isConnected) observer.observe(pagination);
            }
        }

        nextLink.addEventListener('click', event => {
            if (failed) return;
            event.preventDefault();
            loadNext();
        });
        observer.observe(pagination);

        document.addEventListener('livewire:navigating', () => {
            disposed = true;
            observer.disconnect();
            controller.abort();
        }, { once: true });
    })();
</script>
