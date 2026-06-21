<div id="cookie-consent-banner"
     role="dialog" aria-modal="true" aria-label="Cookie Consent"
     class="pointer-events-none fixed bottom-0 left-0 right-0 z-[99999] translate-y-full opacity-0 transition-all duration-500 ease-out">
    <div class="mx-auto max-w-lg border-t border-gray-100 bg-white px-5 pb-[calc(env(safe-area-inset-bottom)+0.75rem)] pt-4 shadow-[0_-4px_20px_rgba(0,0,0,0.08)]">
        <p class="mb-3 text-xs leading-relaxed text-gray-600">
            Dengan menggunakan aplikasi ini, Anda menyetujui
            <a href="{{ route('terms') }}" class="font-semibold text-[#1E5128] underline underline-offset-2">persyaratan dan ketentuan</a>
            serta <a href="{{ route('privacy') }}" class="font-semibold text-[#1E5128] underline underline-offset-2">kebijakan privasi</a>
            dan penggunaan <span class="font-semibold text-[#191A19]">cookie</span> yang diperlukan untuk menjalankan layanan ini.
        </p>
        <button id="accept-cookie-btn"
            class="w-full rounded-xl bg-[#1E5128] px-6 py-2.5 text-sm font-semibold text-white shadow-md transition-all duration-200 active:scale-95 active:shadow-inner">
            Setuju & Lanjutkan
        </button>
    </div>
</div>

<script data-navigate-once>
    (function() {
        var banner = document.getElementById('cookie-consent-banner');
        var btn = document.getElementById('accept-cookie-btn');
        if (!banner || !btn) return;
        if (localStorage.getItem('cookie_consent_accepted') === 'true') return;

        // Show with animation + push content up so it's not hidden behind banner
        requestAnimationFrame(function() {
            requestAnimationFrame(function() {
                banner.classList.remove('pointer-events-none', 'translate-y-full', 'opacity-0');
                var main = document.getElementById('main-content');
                if (main) main.style.paddingBottom = banner.offsetHeight + 'px';
            });
        });

        btn.addEventListener('click', function() {
            localStorage.setItem('cookie_consent_accepted', 'true');
            banner.classList.add('pointer-events-none', 'translate-y-full', 'opacity-0');
            var main = document.getElementById('main-content');
            if (main) main.style.paddingBottom = '';
            try { navigator.vibrate(50); } catch(e) {}
        });
    })();
</script>
