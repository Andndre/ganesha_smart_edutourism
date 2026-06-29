<div id="{{ $playerId }}" class="mt-1 hidden">
    <div class="flex items-center gap-1.5 rounded-lg border border-gray-200 bg-gray-50 px-2 py-1.5">
        <button type="button" onclick="window.toggleMiniAudio(this)"
            class="mini-audio-btn shrink-0 rounded-full p-0.5 text-primary transition-colors hover:bg-primary/10">
            <svg class="mini-audio-play h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M8 5v14l11-7z"/>
            </svg>
            <svg class="mini-audio-pause hidden h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
            </svg>
        </button>
        <span class="mini-audio-name min-w-0 flex-1 truncate text-[10px] text-gray-500" title=""></span>
        <audio class="mini-audio-el" preload="none"></audio>
    </div>
</div>
