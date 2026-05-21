@extends('layouts.app')
@section('title', 'Belajar - Penglipuran')

@push('styles')
<style>
    /* Hide default layout header and bottom navigation for full immersive experience */
    header { display: none !important; }
    nav.fixed.bottom-0 { display: none !important; }
</style>
@endpush

@section('content')
<div class="fixed inset-0 z-50 bg-[#FAF9F6] flex flex-col" id="quiz-container">
    
    <!-- Header / Progress (Percentage Based) -->
    <div class="pt-sat px-5 py-4 flex items-center gap-4 bg-white/80 backdrop-blur-md border-b border-gray-100 shadow-sm z-10">
        <a href="{{ route('learning') }}" class="text-gray-400 active:scale-90 transition-transform p-1">
            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
        </a>
        <div class="flex-1 bg-gray-200 h-4 rounded-full overflow-hidden shadow-inner">
            <!-- Progress Bar at 50% -->
            <div class="bg-primary h-full rounded-full w-[50%] transition-all duration-700 ease-out relative">
                <!-- Glare effect -->
                <div class="absolute top-1 bottom-1 left-2 right-2 bg-white/30 rounded-full"></div>
            </div>
        </div>
        <div class="text-sm font-bold text-gray-500 w-10 text-right">
            50%
        </div>
    </div>

    <!-- Content Area -->
    <div class="flex-1 overflow-y-auto px-5 py-8">
        
        <!-- Question -->
        <h2 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-4">Pertanyaan</h2>
        <p class="font-['Playfair_Display'] text-2xl text-charcoal font-bold mb-10 leading-snug">
            Material utama apa yang digunakan untuk membuat atap rumah tradisional (Angkul-angkul) di Desa Penglipuran?
        </p>

        <!-- Options Container -->
        <div class="space-y-4" id="options-container">
            <!-- Option 1 -->
            <button class="quiz-option w-full text-left p-5 rounded-2xl border-2 border-gray-200 bg-white text-gray-700 font-bold border-b-[6px] active:border-b-2 active:translate-y-1 transition-all flex items-center justify-between group">
                <span class="text-lg">Sirap Kayu Ulin</span>
                <div class="radio-circle w-6 h-6 rounded-full border-2 border-gray-300 transition-colors flex items-center justify-center"></div>
            </button>
            
            <!-- Option 2 (This simulates the selected state in JS later, but starting neutral) -->
            <button class="quiz-option w-full text-left p-5 rounded-2xl border-2 border-gray-200 bg-white text-gray-700 font-bold border-b-[6px] active:border-b-2 active:translate-y-1 transition-all flex items-center justify-between group">
                <span class="text-lg">Bambu (Sirap Bambu)</span>
                <div class="radio-circle w-6 h-6 rounded-full border-2 border-gray-300 transition-colors flex items-center justify-center"></div>
            </button>

            <!-- Option 3 -->
            <button class="quiz-option w-full text-left p-5 rounded-2xl border-2 border-gray-200 bg-white text-gray-700 font-bold border-b-[6px] active:border-b-2 active:translate-y-1 transition-all flex items-center justify-between group">
                <span class="text-lg">Ijuk Hitam</span>
                <div class="radio-circle w-6 h-6 rounded-full border-2 border-gray-300 transition-colors flex items-center justify-center"></div>
            </button>
            
            <!-- Option 4 -->
            <button class="quiz-option w-full text-left p-5 rounded-2xl border-2 border-gray-200 bg-white text-gray-700 font-bold border-b-[6px] active:border-b-2 active:translate-y-1 transition-all flex items-center justify-between group">
                <span class="text-lg">Genteng Tanah Liat</span>
                <div class="radio-circle w-6 h-6 rounded-full border-2 border-gray-300 transition-colors flex items-center justify-center"></div>
            </button>
        </div>
    </div>

    <!-- Bottom Check Panel -->
    <div class="border-t border-gray-200 p-5 pb-sab bg-white shadow-[0_-10px_20px_rgba(0,0,0,0.03)]">
        <button id="check-btn" class="w-full bg-gray-200 text-gray-400 py-4 rounded-2xl font-bold text-lg border-b-[6px] border-gray-300 transition-all pointer-events-none" disabled>
            CEK JAWABAN
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const options = document.querySelectorAll('.quiz-option');
        const checkBtn = document.getElementById('check-btn');
        const svgCheck = `<svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>`;
        
        let selectedOption = null;

        options.forEach(option => {
            option.addEventListener('click', function() {
                // Reset all
                options.forEach(opt => {
                    opt.classList.remove('border-primary', 'bg-primary/5', 'text-primary');
                    opt.classList.add('border-gray-200', 'bg-white', 'text-gray-700');
                    const circle = opt.querySelector('.radio-circle');
                    circle.classList.remove('border-primary', 'bg-primary');
                    circle.classList.add('border-gray-300');
                    circle.innerHTML = '';
                });

                // Set active
                this.classList.remove('border-gray-200', 'bg-white', 'text-gray-700');
                this.classList.add('border-primary', 'bg-primary/5', 'text-primary');
                
                const circle = this.querySelector('.radio-circle');
                circle.classList.remove('border-gray-300');
                circle.classList.add('border-primary', 'bg-primary');
                circle.innerHTML = svgCheck;

                selectedOption = this;

                // Enable Check button
                checkBtn.classList.remove('bg-gray-200', 'text-gray-400', 'border-gray-300', 'pointer-events-none');
                checkBtn.classList.add('bg-primary', 'text-white', 'border-[#153a1d]', 'active:border-b-0', 'active:translate-y-[6px]');
                checkBtn.removeAttribute('disabled');
            });
        });
    });
</script>
@endpush
