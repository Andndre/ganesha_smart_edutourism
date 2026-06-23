@extends('layouts.app')
@section('title', __('Perbarui Ulasan'))
@section('header_title', __('Perbarui Ulasan'))

@section('content')
<div class="mx-auto max-w-2xl px-5 py-6">
    <div class="mb-6 text-center">
        <h2 class="text-charcoal mb-2 text-2xl font-bold" style="font-family: 'Playfair Display', serif;">{{ __('Perbarui Penilaian Anda') }}</h2>
        <p class="text-sm text-gray-500">{{ __('Ubah rating, komentar, atau foto ulasan Anda.') }}</p>
    </div>

    <form id="feedback-form" action="{{ route('feedback.update', $feedback) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <input type="hidden" name="rating" id="rating-value" value="{{ $feedback->rating }}">
        @error('rating')<p class="mb-2 text-xs text-red-500">{{ $message }}</p>@enderror

        <!-- Rating Card -->
        <div class="mb-6 flex flex-col items-center rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
            <div class="mb-4 text-sm font-bold uppercase tracking-wider text-gray-500">{{ __('Tingkat Kepuasan') }}</div>
            <div class="mb-2 flex items-center gap-2" id="star-container">
                @for ($i = 1; $i <= 5; $i++)
                    <button type="button" class="star-btn h-12 w-12 transition-colors focus:outline-none {{ $i <= $feedback->rating ? 'text-accent' : 'text-gray-200' }}" data-value="{{ $i }}">
                        <svg class="h-full w-full drop-shadow-sm" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    </button>
                @endfor
            </div>
            <div id="rating-text" class="text-accent mt-2 h-5 text-sm font-bold opacity-100 transition-opacity">
                @php
                    $labels = [__('Sangat Buruk'), __('Kurang Memuaskan'), __('Cukup Baik'), __('Memuaskan'), __('Sangat Memuaskan!')];
                @endphp
                {{ $labels[$feedback->rating - 1] ?? __('Sangat Memuaskan!') }}
            </div>
        </div>

        <!-- Comment -->
        <div class="mb-6">
            <label class="text-charcoal mb-2 block text-sm font-bold">{{ __('Ulasan (Opsional)') }}</label>
            <textarea name="comment" rows="5"
                class="focus:border-primary focus:ring-primary w-full resize-none rounded-2xl border border-gray-200 bg-white p-4 text-sm shadow-sm transition-colors focus:outline-none focus:ring-1"
                placeholder="{{ __('Ceritakan pengalaman Anda...') }}">{{ $feedback->comment }}</textarea>
            @error('comment')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>

        <!-- Existing Photos -->
        @if ($feedback->photos && count($feedback->photos) > 0)
            <div class="mb-6">
                <label class="text-charcoal mb-2 block text-sm font-bold">{{ __('Foto Saat Ini') }}</label>
                <div class="flex gap-3 overflow-x-auto pb-2">
                    @foreach ($feedback->photos as $photo)
                        <div class="relative h-20 w-20 shrink-0 overflow-hidden rounded-2xl border border-gray-200">
                            <img src="{{ Storage::url($photo) }}" class="h-full w-full object-cover">
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- New Photos -->
        <div class="mb-8">
            <label class="text-charcoal mb-2 block text-sm font-bold">{{ __('Tambah Foto Baru (Opsional, Maks. 5)') }}</label>
            <input type="file" name="photos[]" accept="image/*" multiple class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
            @error('photos.*')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>

        <!-- Submit -->
        <button type="submit"
            class="bg-primary shadow-primary/30 flex h-14 w-full items-center justify-center rounded-2xl font-bold text-white shadow-lg transition-all active:scale-[0.98]">
            {{ __('Perbarui Penilaian') }}
        </button>
    </form>
</div>
@endsection

@push('scripts')
<script>
    (function() {
        const stars = document.querySelectorAll('.star-btn');
        const ratingInput = document.getElementById('rating-value');
        const ratingText = document.getElementById('rating-text');
        const labels = ["{{ __('Sangat Buruk') }}", "{{ __('Kurang Memuaskan') }}", "{{ __('Cukup Baik') }}", "{{ __('Memuaskan') }}", "{{ __('Sangat Memuaskan!') }}"];
        
        stars.forEach(star => {
            star.addEventListener('click', function() {
                const value = parseInt(this.getAttribute('data-value'));
                ratingInput.value = value;
                stars.forEach(s => {
                    if (parseInt(s.getAttribute('data-value')) <= value) {
                        s.classList.remove('text-gray-200');
                        s.classList.add('text-accent');
                    } else {
                        s.classList.remove('text-accent');
                        s.classList.add('text-gray-200');
                    }
                });
                if (ratingText) {
                    ratingText.textContent = labels[value - 1];
                    ratingText.classList.remove('opacity-0');
                }
            });
        });

        document.querySelector('input[name="photos[]"]')?.addEventListener('change', function() {
            const maxSize = 2 * 1024 * 1024;
            const oversized = Array.from(this.files || []).find(f => f.size > maxSize);
            if (oversized) {
                Swal.fire({ title: '{{ __('Ukuran File Terlalu Besar') }}', text: '{{ __('Maksimal 2MB per foto.') }}', icon: 'warning', confirmButtonColor: '#1E5128', confirmButtonText: '{{ __('Mengerti') }}', background: '#ffffff' });
                this.value = '';
            }
        });
    })();
</script>
@endpush
