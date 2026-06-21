{{-- ponytail: partial dipecah untuk keterbacaan --}}
            <!-- Sticky Bottom Bar for Button -->
            <div
                class="{{ $hasActiveSession ? 'mb-18' : '' }} fixed bottom-[calc(env(safe-area-inset-bottom)+4rem)] left-0 right-0 z-40 border-t border-gray-200 bg-white/80 px-4 pb-8 pt-4 backdrop-blur-md transition-all">
                <!-- Selected Categories Pills Container -->
                <div id="selected-categories-pills"
                    class="no-scrollbar mb-3 hidden flex-row flex-nowrap gap-2 overflow-x-auto pb-1">
                    <!-- Dynamic pills will be injected here -->
                </div>

                <button type="submit"
                    class="bg-primary flex w-full items-center justify-center gap-2 rounded-xl py-3.5 font-semibold text-white shadow-lg transition-transform active:scale-[0.98]">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Temukan UMKM
                </button>
            </div>
