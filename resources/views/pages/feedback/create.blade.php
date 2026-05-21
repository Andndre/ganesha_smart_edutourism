@extends('layouts.app')
@section('title', 'Penilaian & Ulasan - Penglipuran')
@section('header_title', 'Beri Ulasan')

@section('content')
<div class="px-5 py-6">
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-bold text-charcoal mb-2" style="font-family: 'Playfair Display', serif;">Bagaimana Pengalaman Anda?</h2>
        <p class="text-sm text-gray-500">Masukan Anda sangat berharga untuk pengembangan Desa Wisata Penglipuran di masa depan.</p>
    </div>

    <!-- Rating Card -->
    <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm mb-6 flex flex-col items-center">
        <div class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">Tingkat Kepuasan</div>
        
        <div class="flex items-center gap-2 mb-2" id="star-container">
            <!-- 5 Stars -->
            <button class="star-btn w-12 h-12 text-gray-200 hover:text-accent transition-colors focus:outline-none" data-value="1" onclick="if(navigator.vibrate) navigator.vibrate(20)">
                <svg class="w-full h-full drop-shadow-sm" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            </button>
            <button class="star-btn w-12 h-12 text-gray-200 hover:text-accent transition-colors focus:outline-none" data-value="2" onclick="if(navigator.vibrate) navigator.vibrate(20)">
                <svg class="w-full h-full drop-shadow-sm" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            </button>
            <button class="star-btn w-12 h-12 text-gray-200 hover:text-accent transition-colors focus:outline-none" data-value="3" onclick="if(navigator.vibrate) navigator.vibrate(20)">
                <svg class="w-full h-full drop-shadow-sm" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            </button>
            <button class="star-btn w-12 h-12 text-gray-200 hover:text-accent transition-colors focus:outline-none" data-value="4" onclick="if(navigator.vibrate) navigator.vibrate(20)">
                <svg class="w-full h-full drop-shadow-sm" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            </button>
            <button class="star-btn w-12 h-12 text-gray-200 hover:text-accent transition-colors focus:outline-none" data-value="5" onclick="if(navigator.vibrate) navigator.vibrate(20)">
                <svg class="w-full h-full drop-shadow-sm" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            </button>
        </div>
        
        <div id="rating-text" class="text-sm font-bold text-accent mt-2 h-5 opacity-0 transition-opacity">Sangat Memuaskan!</div>
    </div>
    
    <!-- Comment Form -->
    <div class="mb-6">
        <label class="block text-sm font-bold text-charcoal mb-2">Tulis Ulasan (Opsional)</label>
        <textarea rows="5" class="w-full bg-white border border-gray-200 rounded-2xl p-4 text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors shadow-sm resize-none" placeholder="Ceritakan pengalaman unik Anda selama berada di desa wisata..."></textarea>
    </div>
    
    <!-- Photo Upload (Mock) -->
    <div class="mb-8">
        <label class="block text-sm font-bold text-charcoal mb-2">Lampirkan Foto</label>
        <div class="flex gap-3 overflow-x-auto no-scrollbar pb-2">
            <!-- Add Photo Button -->
            <button class="w-20 h-20 shrink-0 bg-gray-50 border-2 border-dashed border-gray-300 rounded-2xl flex flex-col items-center justify-center text-gray-400 active:bg-gray-100 transition-colors">
                <svg class="w-6 h-6 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                <span class="text-[10px] font-semibold">Tambah</span>
            </button>
        </div>
    </div>
    
    <!-- Submit Button -->
    <button onclick="submitFeedback()" class="w-full bg-primary text-white font-bold h-14 rounded-2xl active:scale-[0.98] transition-all shadow-lg shadow-primary/30 flex items-center justify-center">
        Kirim Penilaian
    </button>
</div>

<!-- Success Toast (Hidden by default) -->
<div id="toast" class="fixed top-4 inset-x-4 bg-charcoal text-white rounded-2xl p-4 shadow-2xl flex items-center gap-3 transform -translate-y-[150%] transition-transform duration-300 z-50">
    <div class="w-8 h-8 rounded-full bg-green-500 flex items-center justify-center text-white shrink-0">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
    </div>
    <div class="flex-1">
        <h4 class="font-bold text-sm">Terima Kasih!</h4>
        <p class="text-xs text-gray-300">Ulasan Anda berhasil dikirim.</p>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const stars = document.querySelectorAll('.star-btn');
        const ratingText = document.getElementById('rating-text');
        
        const ratingLabels = [
            "Sangat Buruk",
            "Kurang Memuaskan",
            "Cukup Baik",
            "Memuaskan",
            "Sangat Memuaskan!"
        ];
        
        let currentRating = 0;
        
        stars.forEach(star => {
            star.addEventListener('click', function() {
                const value = parseInt(this.getAttribute('data-value'));
                currentRating = value;
                
                // Update star visuals
                stars.forEach(s => {
                    if (parseInt(s.getAttribute('data-value')) <= value) {
                        s.classList.remove('text-gray-200');
                        s.classList.add('text-accent');
                    } else {
                        s.classList.remove('text-accent');
                        s.classList.add('text-gray-200');
                    }
                });
                
                // Update text
                ratingText.textContent = ratingLabels[value - 1];
                ratingText.classList.remove('opacity-0');
            });
        });
    });
    
    function submitFeedback() {
        if(navigator.vibrate) navigator.vibrate(100);
        
        const toast = document.getElementById('toast');
        toast.classList.remove('-translate-y-[150%]');
        
        setTimeout(() => {
            toast.classList.add('-translate-y-[150%]');
            // Navigate back to home after successful submission
            setTimeout(() => {
                window.location.href = "{{ route('home') }}";
            }, 500);
        }, 2500);
    }
</script>
@endpush
