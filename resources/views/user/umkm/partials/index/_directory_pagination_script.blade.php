<script>
    (() => {
        const panel = document.getElementById('umkm-directory-panel');
        if (!panel) return;

        let disposed = false;
        let filterController = null;
        let cancelPagination = () => {};

        function setupPagination(content) {
            const grid = content.querySelector('[data-umkm-grid]');
            const pagination = content.querySelector('[data-umkm-pagination]');
            const nextLink = pagination?.querySelector('[data-umkm-next]');
            if (!grid || !nextLink || !('IntersectionObserver' in window)) return () => {};

            let loading = false;
            let failed = false;
            let stopped = false;
            const controller = new AbortController();
            const observer = new IntersectionObserver(entries => {
                if (entries.some(entry => entry.isIntersecting)) loadNext();
            }, { rootMargin: '300px 0px' });

            async function loadNext() {
                if (loading || failed || stopped || disposed || panel.style.display === 'none') return;

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
                    if (!failed && !stopped && !disposed && pagination.isConnected) observer.observe(pagination);
                }
            }

            nextLink.addEventListener('click', event => {
                if (failed) return;
                event.preventDefault();
                loadNext();
            });
            observer.observe(pagination);

            return () => {
                stopped = true;
                observer.disconnect();
                controller.abort();
            };
        }

        async function loadDirectory(url, pushHistory) {
            filterController?.abort();
            cancelPagination();

            const controller = new AbortController();
            filterController = controller;
            const currentContent = panel.querySelector('[data-umkm-directory-content]');
            currentContent.setAttribute('aria-busy', 'true');

            try {
                const response = await fetch(url, {
                    headers: { 'X-UMKM-Fragment': 'directory-content' },
                    signal: controller.signal,
                });
                if (!response.ok) throw new Error(`UMKM filter request failed: ${response.status}`);

                const template = document.createElement('template');
                template.innerHTML = await response.text();
                const newContent = template.content.querySelector('[data-umkm-directory-content]');
                if (!newContent) throw new Error('UMKM filter response was invalid');

                currentContent.replaceWith(newContent);
                cancelPagination = setupPagination(newContent);

                if (pushHistory) {
                    const previousUrl = new URL(window.location.href);
                    if (previousUrl.searchParams.get('tab') !== 'direktori') {
                        previousUrl.searchParams.set('tab', 'direktori');
                    }
                    history.replaceState({ ...history.state, umkmDirectory: true }, '', previousUrl);
                    history.pushState({ ...history.state, umkmDirectory: true }, '', url);
                }
            } catch (error) {
                if (error.name !== 'AbortError') window.location.assign(url);
            } finally {
                if (filterController === controller) {
                    currentContent.removeAttribute('aria-busy');
                    filterController = null;
                }
            }
        }

        panel.addEventListener('click', event => {
            const link = event.target.closest('[data-umkm-filter]');
            if (!link) return;

            event.preventDefault();
            const isCurrentFilter = link.getAttribute('aria-current') === 'true';
            if (isCurrentFilter && !filterController) return;
            loadDirectory(link.href, !isCurrentFilter);
        });

        const onPopState = event => {
            if (event.state?.umkmDirectory && window.location.pathname === panel.querySelector('[data-umkm-filter]')?.pathname) {
                loadDirectory(window.location.href, false);
            }
        };
        window.addEventListener('popstate', onPopState);
        cancelPagination = setupPagination(panel.querySelector('[data-umkm-directory-content]'));

        document.addEventListener('livewire:navigating', () => {
            disposed = true;
            filterController?.abort();
            cancelPagination();
            window.removeEventListener('popstate', onPopState);
        }, { once: true });
    })();
</script>
