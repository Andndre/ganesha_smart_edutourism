<nav class="pb-sab w-full bg-white shadow-sm border-t border-slate-200 md:static md:w-20 lg:w-64 md:h-[calc(100vh-2rem)] md:m-4 md:rounded-3xl md:shrink-0 md:border md:border-slate-200 md:shadow-sm md:pb-0 z-50 sticky md:top-4"
    role="navigation" aria-label="Main navigation">
    <div class="relative flex h-16 items-center justify-around px-2 md:h-full md:flex-col md:justify-start md:pt-8 md:gap-2 lg:px-4">

        @php
            $isHome = Route::is('home');
            $isExplore = Route::is('explore');
            $isUmkm = Route::is('umkm*');
            $isProfile = Route::is('profile*');
            $isLogin = Route::is('login') || Route::is('register');
        @endphp

        <!-- AR Scan Button Desktop Position -->
        <div class="hidden md:flex w-full mb-6 lg:px-0">
            <a href="{{ route('ar-scan') }}"
                class="bg-primary flex w-full flex-col lg:flex-row items-center justify-center lg:justify-start gap-2 rounded-xl py-3 text-white shadow-sm transition-all duration-300 hover:bg-primary-dark hover:-translate-y-0.5 hover:shadow-md active:scale-95 px-4 mx-2 lg:mx-0">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 8V6a2 2 0 012-2h2M3 16v2a2 2 0 002 2h2M21 8V6a2 2 0 00-2-2h-2M21 16v2a2 2 0 01-2 2h-2M12 8v.01M12 12v.01M12 16v.01" />
                </svg>
                <span class="hidden lg:block text-sm font-semibold">AR Scan</span>
            </a>
        </div>

        <a href="{{ route('home') }}" wire:navigate
            class="tap-target {{ $isHome ? 'text-primary lg:bg-primary/10 lg:text-primary-700' : 'text-gray-400 hover:text-gray-600 lg:hover:bg-gray-100' }} group flex h-full flex-1 flex-col items-center justify-center gap-1 md:h-14 md:w-full md:flex-none lg:flex-row lg:justify-start lg:gap-3 lg:rounded-xl lg:px-4">
            <svg class="h-6 w-6 shrink-0 transition-colors duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                stroke-width="{{ $isHome ? '2.5' : '2' }}">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span class="text-[10px] font-medium transition-colors duration-200 md:hidden lg:block lg:text-sm lg:font-semibold">Home</span>
        </a>

        <a href="{{ route('explore') }}" wire:navigate
            class="tap-target {{ $isExplore ? 'text-primary lg:bg-primary/10 lg:text-primary-700' : 'text-gray-400 hover:text-gray-600 lg:hover:bg-gray-100' }} group flex h-full flex-1 flex-col items-center justify-center gap-1 md:h-14 md:w-full md:flex-none lg:flex-row lg:justify-start lg:gap-3 lg:rounded-xl lg:px-4">
            <svg class="h-6 w-6 shrink-0 transition-colors duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                stroke-width="{{ $isExplore ? '2.5' : '2' }}">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
            </svg>
            <span class="text-[10px] font-medium transition-colors duration-200 md:hidden lg:block lg:text-sm lg:font-semibold">Peta</span>
        </a>

        <!-- AR Scan Button Mobile Position (Floating on mobile) -->
        <div class="relative z-50 flex h-full w-20 shrink-0 items-center justify-center md:hidden">
            <a href="{{ route('ar-scan') }}"
                class="bg-primary absolute -top-6 flex h-14 w-14 items-center justify-center rounded-full text-white shadow-sm ring-4 ring-slate-50 transition-all duration-300 hover:-translate-y-1 hover:shadow-md active:scale-95"
                aria-label="Buka Kamera AR">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 8V6a2 2 0 012-2h2M3 16v2a2 2 0 002 2h2M21 8V6a2 2 0 00-2-2h-2M21 16v2a2 2 0 01-2 2h-2M12 8v.01M12 12v.01M12 16v.01" />
                </svg>
            </a>
        </div>

        <a href="{{ route('umkm') }}" wire:navigate
            class="tap-target {{ $isUmkm ? 'text-primary lg:bg-primary/10 lg:text-primary-700' : 'text-gray-400 hover:text-gray-600 lg:hover:bg-gray-100' }} group flex h-full flex-1 flex-col items-center justify-center gap-1 md:h-14 md:w-full md:flex-none lg:flex-row lg:justify-start lg:gap-3 lg:rounded-xl lg:px-4">
            <svg class="h-6 w-6 shrink-0 transition-colors duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                stroke-width="{{ $isUmkm ? '2.5' : '2' }}">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
            <span class="text-[10px] font-medium transition-colors duration-200 md:hidden lg:block lg:text-sm lg:font-semibold">UMKM</span>
        </a>

        @auth
            <a href="{{ route('profile') }}" wire:navigate
                class="tap-target {{ $isProfile ? 'text-primary lg:bg-primary/10 lg:text-primary-700' : 'text-gray-400 hover:text-gray-600 lg:hover:bg-gray-100' }} group flex h-full flex-1 flex-col items-center justify-center gap-1 md:h-14 md:w-full md:flex-none lg:flex-row lg:justify-start lg:gap-3 lg:rounded-xl lg:px-4">
                <svg class="h-6 w-6 shrink-0 transition-colors duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="{{ $isProfile ? '2.5' : '2' }}">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span class="text-[10px] font-medium transition-colors duration-200 md:hidden lg:block lg:text-sm lg:font-semibold">Profil</span>
            </a>
        @else
            <a href="{{ route('login') }}" wire:navigate
                class="tap-target {{ $isLogin ? 'text-primary lg:bg-primary/10 lg:text-primary-700' : 'text-gray-400 hover:text-gray-600 lg:hover:bg-gray-100' }} group flex h-full flex-1 flex-col items-center justify-center gap-1 md:h-14 md:w-full md:flex-none lg:flex-row lg:justify-start lg:gap-3 lg:rounded-xl lg:px-4">
                <svg class="h-6 w-6 shrink-0 transition-colors duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="{{ $isLogin ? '2.5' : '2' }}">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                </svg>
                <span class="text-[10px] font-medium transition-colors duration-200 md:hidden lg:block lg:text-sm lg:font-semibold">Masuk</span>
            </a>
        @endauth

    </div>
</nav>
