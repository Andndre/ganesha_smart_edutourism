{{-- VIEW MODE 1: INTERACTIVE CALENDAR --}}
<div x-show="viewMode === 'calendar'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
    class="rounded-3xl border border-gray-100 bg-white p-4 shadow-sm md:p-6" style="display: none;">
    <div id="calendar-public"></div>
</div>
