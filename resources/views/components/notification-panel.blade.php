{{-- Notification Dropdown Panel --}}
<div x-show="open" x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
     x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
     @click.outside="open = false"
     class="absolute right-0 top-full mt-2 w-80 max-w-[calc(100vw-2rem)] overflow-hidden rounded-2xl border border-gray-100/80 bg-white/95 shadow-2xl backdrop-blur-md z-50"
     style="display: none;">

    {{-- Header --}}
    <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
        <h3 class="text-sm font-bold text-gray-900">Notifikasi</h3>
        <button type="button" @click="clearAllNotifications()" class="text-[10px] font-bold uppercase tracking-wider text-red-500 transition-colors hover:text-red-700">
            Hapus Semua
        </button>
    </div>

    {{-- Notification List --}}
    <div class="no-scrollbar max-h-72 overflow-y-auto">
        <template x-if="notifications.length === 0">
            <div class="flex flex-col items-center justify-center gap-2 py-10 text-gray-400">
                <svg class="h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <p class="text-xs font-medium">Belum ada notifikasi</p>
            </div>
        </template>

        <template x-for="(notif, index) in notifications" :key="notif.id">
            <div class="flex items-start gap-3 border-b border-gray-50 px-4 py-3 transition-colors hover:bg-gray-50/50"
                 :class="{ 'bg-green-50/30': !notif.read }">
                {{-- Icon by type --}}
                <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg"
                     :class="{
                         'bg-red-50 text-red-600': notif.level === 'critical',
                         'bg-amber-50 text-amber-600': notif.level === 'warning',
                         'bg-green-50 text-[#1E5128]': notif.level === 'info'
                     }">
                    <template x-if="notif.type === 'crowd'">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </template>
                    <template x-if="notif.type === 'event'">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </template>
                    <template x-if="notif.type === 'geofence'">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </template>
                </div>

                {{-- Content --}}
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-bold leading-tight text-gray-900" x-text="notif.title"></p>
                    <p class="mt-0.5 text-[11px] leading-relaxed text-gray-500" x-text="notif.body"></p>
                    <p class="mt-1 text-[10px] font-medium text-gray-400" x-text="timeAgo(notif.timestamp)"></p>
                </div>

                {{-- Dismiss --}}
                <button type="button" @click.stop="dismissNotification(index)"
                        class="shrink-0 rounded-full p-1 text-gray-300 transition-colors hover:text-gray-500">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </template>
    </div>
</div>
