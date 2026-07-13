<nav class="pb-sab w-full bg-white shadow-sm border-t border-slate-200 md:static md:w-20 lg:w-64 md:h-[calc(100vh-2rem)] md:m-4 md:rounded-3xl md:shrink-0 md:border md:border-slate-200 md:shadow-sm md:pb-0 z-50 sticky bottom-0 inset-x-0 md:top-4 md:inset-x-auto"
    role="navigation" aria-label="{{ __('Main navigation') }}">
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
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 8V6a2 2 0 012-2h2M3 16v2a2 2 0 002 2h2M21 8V6a2 2 0 00-2-2h-2M21 16v2a2 2 0 01-2 2h-2M12 8v.01M12 12v.01M12 16v.01" />
                </svg>
                <span class="hidden lg:block text-sm font-semibold">{{ __('AR Scan') }}</span>
            </a>
        </div>

        <a href="{{ route('home') }}" wire:navigate
            class="tap-target {{ $isHome ? 'text-primary lg:bg-primary/10 lg:text-primary-700' : 'text-gray-400 hover:text-gray-600 lg:hover:bg-gray-100' }} group flex h-full flex-1 flex-col items-center justify-center gap-1 md:h-14 md:w-full md:flex-none lg:flex-row lg:justify-start lg:gap-3 lg:rounded-xl lg:px-4">
            @if ($isHome)
                <svg class="h-6 w-6 shrink-0" viewBox="0 0 24 24" fill="currentColor">
                    <path
                        d="M11.47 3.841a.75.75 0 011.06 0l8.69 8.69a.75.75 0 101.06-1.061l-8.689-8.69a2.25 2.25 0 00-3.182 0l-8.69 8.69a.75.75 0 101.061 1.06l8.69-8.689z" />
                    <path
                        d="M12 5.432l8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 01-.75-.75v-4.5a.75.75 0 00-.75-.75h-3a.75.75 0 00-.75.75V21a.75.75 0 01-.75.75H5.625a1.875 1.875 0 01-1.875-1.875v-6.198a2.29 2.29 0 00.091-.086L12 5.432z" />
                </svg>
            @else
                <svg class="h-6 w-6 shrink-0 transition-colors duration-200" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
            @endif
            <span class="text-[10px] font-medium transition-colors duration-200 md:hidden lg:block lg:text-sm lg:font-semibold">Home</span>
        </a>

        <a href="{{ route('explore') }}" wire:navigate
            class="tap-target {{ $isExplore ? 'text-primary lg:bg-primary/10 lg:text-primary-700' : 'text-gray-400 hover:text-gray-600 lg:hover:bg-gray-100' }} group flex h-full flex-1 flex-col items-center justify-center gap-1 md:h-14 md:w-full md:flex-none lg:flex-row lg:justify-start lg:gap-3 lg:rounded-xl lg:px-4">
            @if ($isExplore)
                <svg class="h-6 w-6 shrink-0" viewBox="0 0 24 24" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M8.161 2.58a1.875 1.875 0 011.678 0l4.993 2.498c.106.052.23.052.336 0l3.869-1.935A1.875 1.875 0 0121.75 4.82v12.485c0 .71-.401 1.36-1.037 1.677l-4.875 2.437a1.875 1.875 0 01-1.676 0l-4.994-2.497a.375.375 0 00-.336 0l-3.868 1.935A1.875 1.875 0 012.25 19.18V6.695c0-.71.401-1.36 1.036-1.677l4.875-2.437zM9 6a.75.75 0 01.75.75V15a.75.75 0 01-1.5 0V6.75A.75.75 0 019 6zm6.75 3a.75.75 0 00-1.5 0v8.25a.75.75 0 001.5 0V9z"
                        clip-rule="evenodd" />
                </svg>
            @else
                <svg class="h-6 w-6 shrink-0 transition-colors duration-200" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.752a1.125 1.125 0 00-1.006 0L3.622 6.189C3.24 6.38 3 6.77 3 7.195v10.485c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z" />
                </svg>
            @endif
            <span class="text-[10px] font-medium transition-colors duration-200 md:hidden lg:block lg:text-sm lg:font-semibold">{{ __('Peta') }}</span>
        </a>

        <!-- AR Scan Button Mobile Position (Floating on mobile) -->
        <div class="relative z-50 flex h-full w-20 shrink-0 items-center justify-center md:hidden">
            <a href="{{ route('ar-scan') }}"
                class="bg-primary absolute -top-6 flex h-14 w-14 items-center justify-center rounded-full text-white shadow-sm ring-4 ring-slate-50 transition-all duration-300 hover:-translate-y-1 hover:shadow-md active:scale-95"
                aria-label="{{ __('Buka Kamera AR') }}">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 8V6a2 2 0 012-2h2M3 16v2a2 2 0 002 2h2M21 8V6a2 2 0 00-2-2h-2M21 16v2a2 2 0 01-2 2h-2M12 8v.01M12 12v.01M12 16v.01" />
                </svg>
            </a>
        </div>

        <a href="{{ route('umkm') }}" wire:navigate
            class="tap-target {{ $isUmkm ? 'text-primary lg:bg-primary/10 lg:text-primary-700' : 'text-gray-400 hover:text-gray-600 lg:hover:bg-gray-100' }} group flex h-full flex-1 flex-col items-center justify-center gap-1 md:h-14 md:w-full md:flex-none lg:flex-row lg:justify-start lg:gap-3 lg:rounded-xl lg:px-4">
            @if ($isUmkm)
                <svg class="h-6 w-6 shrink-0" viewBox="0 0 24 24" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M7.5 6v.75H5.513c-.96 0-1.764.724-1.865 1.679l-1.263 12A1.875 1.875 0 004.25 22.5h15.5a1.875 1.875 0 001.865-2.071l-1.263-12a1.875 1.875 0 00-1.865-1.679H16.5V6a4.5 4.5 0 10-9 0zM12 3a3 3 0 00-3 3v.75h6V6a3 3 0 00-3-3zm-3 8.25a3 3 0 106 0v-.75a.75.75 0 011.5 0v.75a4.5 4.5 0 11-9 0v-.75a.75.75 0 011.5 0v.75z"
                        clip-rule="evenodd" />
                </svg>
            @else
                <svg class="h-6 w-6 shrink-0 transition-colors duration-200" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z" />
                </svg>
            @endif
            <span class="text-[10px] font-medium transition-colors duration-200 md:hidden lg:block lg:text-sm lg:font-semibold">{{ __('UMKM') }}</span>
        </a>

        @auth
            <a href="{{ route('profile') }}" wire:navigate
                class="tap-target {{ $isProfile ? 'text-primary lg:bg-primary/10 lg:text-primary-700' : 'text-gray-400 hover:text-gray-600 lg:hover:bg-gray-100' }} group flex h-full flex-1 flex-col items-center justify-center gap-1 md:h-14 md:w-full md:flex-none lg:flex-row lg:justify-start lg:gap-3 lg:rounded-xl lg:px-4">
                @if ($isProfile)
                    <svg class="h-6 w-6 shrink-0 scale-110" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M18.685 19.097A9.723 9.723 0 0021.75 12c0-5.385-4.365-9.75-9.75-9.75S2.25 6.615 2.25 12a9.723 9.723 0 003.065 7.097A9.716 9.716 0 0012 21.75a9.716 9.716 0 006.685-2.653zm-12.54-1.285A7.486 7.486 0 0112 15a7.486 7.486 0 015.855 2.812A8.224 8.224 0 0112 20.25a8.224 8.224 0 01-5.855-2.438zM15.75 9a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"
                            clip-rule="evenodd" />
                    </svg>
                @else
                    <svg class="h-6 w-6 shrink-0 scale-110 transition-colors duration-200" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.35">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                @endif
                <span class="text-[10px] font-medium transition-colors duration-200 md:hidden lg:block lg:text-sm lg:font-semibold">{{ __('Profil') }}</span>
            </a>
        @else
            <a href="{{ route('login') }}" wire:navigate
                class="tap-target {{ $isLogin ? 'text-primary lg:bg-primary/10 lg:text-primary-700' : 'text-gray-400 hover:text-gray-600 lg:hover:bg-gray-100' }} group flex h-full flex-1 flex-col items-center justify-center gap-1 md:h-14 md:w-full md:flex-none lg:flex-row lg:justify-start lg:gap-3 lg:rounded-xl lg:px-4">
                @if ($isLogin)
                    <svg class="h-6 w-6 shrink-0" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M7.5 3.75A1.5 1.5 0 006 5.25v13.5a1.5 1.5 0 001.5 1.5h6a1.5 1.5 0 001.5-1.5V15a.75.75 0 011.5 0v3.75a3 3 0 01-3 3h-6a3 3 0 01-3-3V5.25a3 3 0 013-3h6a3 3 0 013 3V9A.75.75 0 0115 9V5.25a1.5 1.5 0 00-1.5-1.5h-6zm10.72 4.72a.75.75 0 011.06 0l3 3a.75.75 0 010 1.06l-3 3a.75.75 0 11-1.06-1.06l1.72-1.72H9a.75.75 0 010-1.5h10.94l-1.72-1.72a.75.75 0 010-1.06z"
                            clip-rule="evenodd" />
                    </svg>
                @else
                    <svg class="h-6 w-6 shrink-0 transition-colors duration-200" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                    </svg>
                @endif
                <span class="text-[10px] font-medium transition-colors duration-200 md:hidden lg:block lg:text-sm lg:font-semibold">{{ __('Masuk') }}</span>
            </a>
        @endauth

    </div>
</nav>
