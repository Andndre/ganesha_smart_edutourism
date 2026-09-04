{{-- ponytail: partial dipecah untuk keterbacaan --}}
<!-- Sticky Bottom Bar for Button -->
<div
    {{-- ponytail: stays `fixed` at every breakpoint. `md:absolute` had no positioned
         ancestor, so on desktop it anchored to the document bottom, not the viewport.
         The +4rem only clears the mobile tab bar, which md: hides — hence md:bottom-0. --}}
    class="fixed bottom-[calc(env(safe-area-inset-bottom)+4rem+var(--route-banner-h,0px))] left-0 right-0 z-40 border-t border-gray-200 bg-white/80 px-4 pb-8 pt-4 backdrop-blur-md transition-all md:bottom-0 md:pb-4">
    <!-- Inner wrapper: constrain width on larger screens + center -->
    <div class="mx-auto w-full md:max-w-md">
        <!-- Selected Categories Pills Container -->
        <div id="selected-categories-pills"
            class="no-scrollbar mb-3 hidden flex-row flex-nowrap gap-2 overflow-x-auto pb-1">
            <!-- Dynamic pills will be injected here -->
        </div>

        {{-- ponytail: disabled saat belum ada pilihan — cegah error validasi, bukan laporkan.
             State diatur updateSelectedPills() di _scripts. --}}
        <button type="submit" id="find-umkm-btn" disabled
            class="bg-primary flex w-full items-center justify-center gap-2 rounded-xl py-3.5 font-semibold text-white shadow-lg transition-transform active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50 disabled:active:scale-100">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <span id="find-umkm-label">{{ __('Pilih kategori dulu') }}</span>
        </button>
    </div>
</div>
