@php($onDarkBg = $onDarkBg ?? false)
@php($mock = app()->environment('local') ? $mock ?? true : $mock ?? false)
<div x-data="adminBell()" class="relative">
    <button type="button" @click="toggle()"
        class="relative flex items-center gap-3 rounded-xl transition-all {{ $onDarkBg ? 'w-full px-3 py-2.5 text-sm font-medium text-white/50 hover:bg-white/8 hover:text-white' : 'inline-flex h-9 w-9 items-center justify-center text-charcoal hover:bg-gray-100' }}"
        :aria-label="'{{ __('Notifikasi') }}'">
        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        @if($onDarkBg)
            <span class="text-sm font-medium">{{ __('Notifikasi') }}</span>
        @endif
        <span x-show="unread > 0" x-cloak x-text="unread > 9 ? '9+' : unread"
            class="{{ $onDarkBg ? 'ml-auto' : 'absolute -top-0.5 -right-0.5' }} min-w-4.5 rounded-full bg-warning px-1 text-[10px] font-bold leading-[18px] text-white text-center"></span>
    </button>

    <div x-show="open" x-cloak @click.outside="open = false" @keydown.escape.window="open = false"
        x-transition.origin.bottom.left
        class="{{ $onDarkBg ? 'fixed bottom-3 left-67' : 'absolute right-0' }} mt-2 w-96 max-h-[70vh] overflow-y-auto rounded-2xl border border-gray-100 bg-white shadow-xl z-50">
        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
            <p class="font-semibold text-charcoal">{{ __('Notifikasi') }}</p>
            <button type="button" @click="markAllRead()" x-show="unread > 0"
                class="text-xs font-semibold text-primary hover:underline">{{ __('Tandai semua dibaca') }}</button>
        </div>

        <template x-if="items.length === 0">
            <p class="px-4 py-8 text-center text-sm text-gray-400">{{ __('Tidak ada notifikasi.') }}</p>
        </template>

        <template x-for="n in items" :key="n.id">
            <a :href="n.data.action_url || '#'" @click.prevent="goTo(n)"
                class="block border-b border-gray-50 px-4 py-3 hover:bg-gray-50"
                :class="n.read_at ? 'opacity-60' : ''">
                <div class="text-sm font-semibold text-charcoal" x-text="n.data.title"></div>
                <div class="mt-0.5 text-xs text-gray-500" x-text="n.data.body"></div>
                <div class="mt-1 text-[10px] uppercase tracking-wider text-gray-400" x-text="n.created_at"></div>
            </a>
        </template>
    </div>
</div>

<script>
    function adminBell() {
        return {
            open: false,
            unread: 0,
            items: [],
            async load() {
                try {
                    const res = await fetch('{{ route('admin.notifications.index') }}', {
                        headers: { Accept: 'application/json' }
                    });
                    const json = await res.json();
                    this.unread = json.unread;
                    this.items = json.items;
                } catch (e) { /* silent */ }
            },
            toggle() {
                this.open = !this.open;
                if (this.open && !this.items.length) this.load();
            },
            async goTo(n) {
                if (!n.read_at) await this.markRead(n.id);
                if (n.data.action_url) window.location.href = n.data.action_url;
            },
            async markRead(id) {
                await fetch(`/admin/notifications/${id}/read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        Accept: 'application/json'
                    }
                });
                this.load();
            },
            async markAllRead() {
                await fetch('{{ route('admin.notifications.mark-all-read') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        Accept: 'application/json'
                    }
                });
                this.load();
            },
        };
    }
</script>
