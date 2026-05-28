<nav class="pb-sab fixed bottom-0 left-0 right-0 z-50 bg-white shadow-[0_-4px_20px_-10px_rgba(0,0,0,0.1)]"
    role="navigation" aria-label="Main navigation">
    <div class="relative flex h-16 items-center justify-around px-2">

        <a href="{{ route('home') }}"
            class="tap-target {{ Route::is('home') ? 'text-primary' : 'text-gray-400 hover:text-gray-600' }} group flex h-full flex-1 flex-col items-center justify-center gap-1">
            <svg class="h-6 w-6 transition-colors duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                stroke-width="{{ Route::is('home') ? '2.5' : '2' }}">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span class="text-[10px] font-medium transition-colors duration-200">Home</span>
        </a>

        <a href="{{ route('explore') }}"
            class="tap-target {{ Route::is('explore') ? 'text-primary' : 'text-gray-400 hover:text-gray-600' }} group flex h-full flex-1 flex-col items-center justify-center gap-1">
            <svg class="h-6 w-6 transition-colors duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                stroke-width="{{ Route::is('explore') ? '2.5' : '2' }}">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
            </svg>
            <span class="text-[10px] font-medium transition-colors duration-200">Peta</span>
        </a>

        <div class="relative z-50 flex h-full w-20 items-center justify-center">
            <a href="{{ route('ar-scan') }}"
                class="bg-primary absolute -top-6 flex h-14 w-14 items-center justify-center rounded-full text-white shadow-[0_8px_16px_-4px_rgba(30,81,40,0.4)] ring-4 ring-white transition-all duration-200 active:scale-95"
                aria-label="Buka Kamera AR">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 8V6a2 2 0 012-2h2M3 16v2a2 2 0 002 2h2M21 8V6a2 2 0 00-2-2h-2M21 16v2a2 2 0 01-2 2h-2M12 8v.01M12 12v.01M12 16v.01" />
                </svg>
            </a>
        </div>

        <a href="{{ route('umkm') }}"
            class="tap-target {{ Route::is('umkm*') ? 'text-primary' : 'text-gray-400 hover:text-gray-600' }} group flex h-full flex-1 flex-col items-center justify-center gap-1">
            <svg class="h-6 w-6 transition-colors duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                stroke-width="{{ Route::is('umkm*') ? '2.5' : '2' }}">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
            <span class="text-[10px] font-medium transition-colors duration-200">UMKM</span>
        </a>

        @auth
            <a href="{{ route('profile') }}"
                class="tap-target {{ Route::is('profile*') ? 'text-primary' : 'text-gray-400 hover:text-gray-600' }} group flex h-full flex-1 flex-col items-center justify-center gap-1">
                <svg class="h-6 w-6 transition-colors duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="{{ Route::is('profile*') ? '2.5' : '2' }}">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span class="text-[10px] font-medium transition-colors duration-200">Profil</span>
            </a>
        @else
            <a href="{{ route('login') }}"
                class="tap-target {{ Route::is('login') || Route::is('register') ? 'text-primary' : 'text-gray-400 hover:text-gray-600' }} group flex h-full flex-1 flex-col items-center justify-center gap-1">
                <svg class="h-6 w-6 transition-colors duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="{{ Route::is('login') || Route::is('register') ? '2.5' : '2' }}">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                </svg>
                <span class="text-[10px] font-medium transition-colors duration-200">Masuk</span>
            </a>
        @endauth

    </div>
</nav>
