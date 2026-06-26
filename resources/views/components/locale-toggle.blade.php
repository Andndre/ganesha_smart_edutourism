<div class="sticky top-0 z-10 bg-white py-2.5 border-b border-gray-100 mb-4 flex gap-2">
    <button @click="locale = 'en'" :class="locale === 'en' ? 'bg-primary text-white' : 'bg-gray-200 text-gray-600'"
        class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all" type="button">English</button>
    <button @click="locale = 'id'" :class="locale === 'id' ? 'bg-primary text-white' : 'bg-gray-200 text-gray-600'"
        class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all" type="button">Indonesia</button>
</div>
