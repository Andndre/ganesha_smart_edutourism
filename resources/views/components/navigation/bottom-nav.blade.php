<nav class="bg-surface dark:bg-dark-surface pb-sab fixed bottom-0 left-0 right-0 z-50 border-t border-gray-200 dark:border-gray-700"
    role="navigation" aria-label="Main navigation">
    <div class="bg-surface/95 dark:bg-dark-surface/95 flex h-16 items-end justify-around backdrop-blur">

        <!-- Tab 1: Home -->
        <a href="{{ route('home') }}"
            class="tap-target {{ Route::is('home') ? 'text-primary' : 'text-gray-500' }} flex h-full flex-1 flex-col items-center justify-center">
            <img src="/icons/icon-home.svg" alt="Home" class="h-6 w-6" />
            <span class="mt-1 text-xs">Home</span>
        </a>

        <!-- Tab 2: Explore/Map -->
        <a href="{{ route('explore') }}"
            class="tap-target {{ Route::is('explore') ? 'text-primary' : 'text-gray-500' }} flex h-full flex-1 flex-col items-center justify-center">
            <img src="/icons/icon-explore.svg" alt="Explore" class="h-6 w-6" />
            <span class="mt-1 text-xs">Explore</span>
        </a>

        <!-- Tab 3: AR Scan (Center, Prominent) -->
        <a href="{{ route('ar-scan') }}"
            class="bg-primary tap-target relative -top-4 flex h-16 w-16 items-center justify-center rounded-full text-white shadow-lg">
            <img src="/icons/icon-ar-scan.svg" alt="AR Scan" class="h-8 w-8" />
        </a>

        <!-- Tab 4: UMKM -->
        <a href="{{ route('umkm') }}"
            class="tap-target {{ Route::is('umkm*') ? 'text-primary' : 'text-gray-500' }} flex h-full flex-1 flex-col items-center justify-center">
            <img src="/icons/icon-umkm.svg" alt="UMKM" class="h-6 w-6" />
            <span class="mt-1 text-xs">UMKM</span>
        </a>

        <!-- Tab 5: Profile -->
        <a href="{{ route('profile') }}"
            class="tap-target {{ Route::is('profile*') ? 'text-primary' : 'text-gray-500' }} flex h-full flex-1 flex-col items-center justify-center">
            <img src="/icons/icon-profile.svg" alt="Profile" class="h-6 w-6" />
            <span class="mt-1 text-xs">Profil</span>
        </a>
    </div>
</nav>
