<!-- 1. Scanner View -->
<div id="scanner-view" class="absolute inset-0 z-0 bg-black">
    <div id="reader" class="h-full w-full object-cover"></div>

    <!-- Scanner overlay removed since it auto-starts -->

    <!-- UI Overlay for Scanner (Reticle) -->
    <div class="pointer-events-none absolute inset-0 z-20 flex items-center justify-center">
        <div class="relative h-64 w-64 rounded-3xl border-2 border-white/50">
            <div class="absolute -left-1 -top-1 h-8 w-8 rounded-tl-3xl border-l-4 border-t-4 border-[#1E5128]"></div>
            <div class="absolute -right-1 -top-1 h-8 w-8 rounded-tr-3xl border-r-4 border-t-4 border-[#1E5128]"></div>
            <div class="absolute -bottom-1 -left-1 h-8 w-8 rounded-bl-3xl border-b-4 border-l-4 border-[#1E5128]"></div>
            <div class="absolute -bottom-1 -right-1 h-8 w-8 rounded-br-3xl border-b-4 border-r-4 border-[#1E5128]">
            </div>
        </div>
    </div>
</div>
