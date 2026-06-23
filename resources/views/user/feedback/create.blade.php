@extends('layouts.app')
@section('title', isset($feedback) ? __('Edit Ulasan') : __('Penilaian & Ulasan - Penglipuran'))
@section('header_title', isset($feedback) ? __('Edit Ulasan') : __('Beri Ulasan'))

@section('content')
    <div class="mx-auto max-w-2xl px-5 py-6">
        <div class="mb-6 text-center">
            <h2 class="text-charcoal mb-2 text-2xl font-bold" style="font-family: 'Playfair Display', serif;">
                {{ isset($feedback) ? __('Edit Ulasan Anda') : __('Bagaimana Pengalaman Anda?') }}
            </h2>
            <p class="text-sm text-gray-500">{{ __('Masukan Anda sangat berharga untuk pengembangan Desa Wisata Penglipuran di masa depan.') }}</p>
        </div>

        @if(isset($feedback) && $feedback->admin_response)
        <!-- Admin Reply -->
        <div class="mb-6 rounded-3xl border border-blue-100 bg-blue-50 p-5">
            <div class="mb-2 flex items-center gap-2">
                <svg class="h-5 w-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
                <span class="text-sm font-bold text-blue-900">{{ __('Balasan Admin') }}</span>
            </div>
            <p class="text-sm text-blue-800">{{ $feedback->admin_response }}</p>
        </div>
        @endif

        <!-- Rating Card -->
        <div class="mb-6 flex flex-col items-center rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
            <div class="mb-4 text-sm font-bold uppercase tracking-wider text-gray-500">{{ __('Tingkat Kepuasan') }}</div>

            <div class="mb-2 flex items-center gap-2" id="star-container">
                <!-- 5 Stars -->
                <button class="star-btn hover:text-accent h-12 w-12 text-gray-200 transition-colors focus:outline-none"
                    data-value="1" onclick="if(navigator.vibrate) navigator.vibrate(20)">
                    <svg class="h-full w-full drop-shadow-sm" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                </button>
                <button class="star-btn hover:text-accent h-12 w-12 text-gray-200 transition-colors focus:outline-none"
                    data-value="2" onclick="if(navigator.vibrate) navigator.vibrate(20)">
                    <svg class="h-full w-full drop-shadow-sm" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                </button>
                <button class="star-btn hover:text-accent h-12 w-12 text-gray-200 transition-colors focus:outline-none"
                    data-value="3" onclick="if(navigator.vibrate) navigator.vibrate(20)">
                    <svg class="h-full w-full drop-shadow-sm" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                </button>
                <button class="star-btn hover:text-accent h-12 w-12 text-gray-200 transition-colors focus:outline-none"
                    data-value="4" onclick="if(navigator.vibrate) navigator.vibrate(20)">
                    <svg class="h-full w-full drop-shadow-sm" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                </button>
                <button class="star-btn hover:text-accent h-12 w-12 text-gray-200 transition-colors focus:outline-none"
                    data-value="5" onclick="if(navigator.vibrate) navigator.vibrate(20)">
                    <svg class="h-full w-full drop-shadow-sm" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                </button>
            </div>
            <input type="hidden" name="rating" id="rating-value" value="0">

            <div id="rating-text" class="text-accent mt-2 h-5 text-sm font-bold opacity-0 transition-opacity">{{ __('Sangat Memuaskan!') }}</div>
        </div>

        <!-- Comment Form -->
        <div class="mb-6">
            <label class="text-charcoal mb-2 block text-sm font-bold">{{ __('Tulis Ulasan (Opsional)') }}</label>
            <textarea id="comment-textarea" rows="5"
                class="focus:border-primary focus:ring-primary w-full resize-none rounded-2xl border border-gray-200 bg-white p-4 text-sm shadow-sm transition-colors focus:outline-none focus:ring-1"
                placeholder="{{ __('Ceritakan pengalaman unik Anda selama berada di desa wisata...') }}">{{ isset($feedback) ? $feedback->comment : '' }}</textarea>
        </div>

        <!-- Photo Upload (Real) -->
        <div class="mb-8">
            <label class="text-charcoal mb-2 block text-sm font-bold">{{ __('Lampirkan Foto (Maks. 5)') }}</label>
            <div class="no-scrollbar flex gap-3 overflow-x-auto pb-2" id="photo-preview-container">
                <!-- Preview will be added here via JS -->
            </div>
            <input type="file" id="photo-input" accept="image/*" multiple class="hidden">
            <button type="button" id="add-photo-btn" onclick="document.getElementById('photo-input').click()"
                class="flex h-20 w-20 shrink-0 flex-col items-center justify-center rounded-2xl border-2 border-dashed border-gray-300 bg-gray-50 text-gray-400 transition-colors active:bg-gray-100">
                <svg class="mb-1 h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                <span class="text-[10px] font-semibold">{{ __('Tambah') }}</span>
            </button>
        </div>

        <!-- Submit Button -->
        <button onclick="submitFeedback()"
            class="bg-primary shadow-primary/30 flex h-14 w-full items-center justify-center rounded-2xl font-bold text-white shadow-lg transition-all active:scale-[0.98]">
            {{ isset($feedback) ? __('Perbarui Ulasan') : __('Kirim Penilaian') }}
        </button>
    </div>

    <!-- Success Toast (Hidden by default) -->
    <div id="toast"
        class="bg-charcoal fixed inset-x-4 top-4 z-50 flex translate-y-[-150%] transform items-center gap-3 rounded-2xl p-4 text-white shadow-2xl transition-transform duration-300">
        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-green-500 text-white">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        <div class="flex-1">
            <h4 class="text-sm font-bold">{{ __('Terima Kasih!') }}</h4>
            <p class="text-xs text-gray-300">{{ __('Ulasan Anda berhasil dikirim.') }}</p>
        </div>
    </div>
    <script>
        const isEditMode = {{ isset($feedback) ? 'true' : 'false' }};
        const feedbackId = {{ isset($feedback) ? $feedback->id : 'null' }};
        const existingPhotos = @json(isset($feedback) && $feedback->photos ? $feedback->photos : []);
        const existingRating = {{ isset($feedback) ? $feedback->rating : 0 }};
        
        let selectedFiles = [];

        (function() {
            if (!window.feedbackListenersRegistered) {
                document.body.addEventListener('click', function(e) {
                    const star = e.target.closest('.star-btn');
                    if (star) {
                        const starsContainer = star.closest('.flex');
                        if (starsContainer) {
                            const stars = starsContainer.querySelectorAll('.star-btn');
                            const value = parseInt(star.getAttribute('data-value'));
                            document.getElementById('rating-value').value = value;

                            stars.forEach(s => {
                                if (parseInt(s.getAttribute('data-value')) <= value) {
                                    s.classList.remove('text-gray-200');
                                    s.classList.add('text-accent');
                                } else {
                                    s.classList.remove('text-accent');
                                    s.classList.add('text-gray-200');
                                }
                            });

                            const ratingText = document.getElementById('rating-text');
                            const ratingLabels = ["{{ __('Sangat Buruk') }}", "{{ __('Kurang Memuaskan') }}", "{{ __('Cukup Baik') }}", "{{ __('Memuaskan') }}", "{{ __('Sangat Memuaskan!') }}"];
                            if (ratingText) {
                                ratingText.textContent = ratingLabels[value - 1];
                                ratingText.classList.remove('opacity-0');
                            }
                        }
                    }
                });
                window.feedbackListenersRegistered = true;
            }

            // Pre-fill rating in edit mode
            if (isEditMode && existingRating > 0) {
                const stars = document.querySelectorAll('.star-btn');
                stars.forEach(s => {
                    if (parseInt(s.getAttribute('data-value')) <= existingRating) {
                        s.classList.remove('text-gray-200');
                        s.classList.add('text-accent');
                    }
                });
                document.getElementById('rating-value').value = existingRating;
                const ratingText = document.getElementById('rating-text');
                const ratingLabels = ["{{ __('Sangat Buruk') }}", "{{ __('Kurang Memuaskan') }}", "{{ __('Cukup Baik') }}", "{{ __('Memuaskan') }}", "{{ __('Sangat Memuaskan!') }}"];
                if (ratingText) {
                    ratingText.textContent = ratingLabels[existingRating - 1];
                    ratingText.classList.remove('opacity-0');
                }
            }

            // Photo input handler
            const photoInput = document.getElementById('photo-input');
            photoInput.addEventListener('change', function(e) {
                const files = Array.from(e.target.files);
                const maxSize = 2 * 1024 * 1024;
                const oversized = files.find(f => f.size > maxSize);
                if (oversized) {
                    Swal.fire({ title: '{{ __('Ukuran File Terlalu Besar') }}', text: '{{ __('Maksimal 2MB per foto.') }}', icon: 'warning', confirmButtonColor: '#1E5128', confirmButtonText: '{{ __('Mengerti') }}', background: '#ffffff' });
                    photoInput.value = '';
                    return;
                }
                if (selectedFiles.length + files.length > 5) {
                    Swal.fire({ title: '{{ __('Maksimal 5 Foto') }}', text: '{{ __('Anda hanya dapat mengunggah maksimal 5 foto.') }}', icon: 'warning', confirmButtonColor: '#1E5128', confirmButtonText: '{{ __('Mengerti') }}', background: '#ffffff' });
                    return;
                }
                selectedFiles = selectedFiles.concat(files);
                renderPhotoPreviews();
                photoInput.value = '';
            });

            function renderPhotoPreviews() {
                const container = document.getElementById('photo-preview-container');
                container.innerHTML = '';
                
                selectedFiles.forEach((file, idx) => {
                    const preview = document.createElement('div');
                    preview.className = 'relative h-20 w-20 shrink-0';
                    const img = document.createElement('img');
                    img.src = URL.createObjectURL(file);
                    img.className = 'h-full w-full rounded-2xl object-cover';
                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.innerHTML = '<svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>';
                    removeBtn.className = 'absolute -right-2 -top-2 flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-white shadow-lg';
                    removeBtn.onclick = () => { selectedFiles.splice(idx, 1); renderPhotoPreviews(); };
                    preview.appendChild(img);
                    preview.appendChild(removeBtn);
                    container.appendChild(preview);
                });

                document.getElementById('add-photo-btn').style.display = selectedFiles.length >= 5 ? 'none' : 'flex';
            }
            window.renderPhotoPreviews = renderPhotoPreviews;
        })();

        async function submitFeedback() {
            if (navigator.vibrate) navigator.vibrate(100);

            const rating = document.getElementById('rating-value').value;
            const comment = document.getElementById('comment-textarea').value;

            if (rating === '0') {
                Swal.fire({ title: '{{ __('Rating Belum Dipilih') }}', text: '{{ __('Silakan pilih rating terlebih dahulu.') }}', icon: 'warning', confirmButtonColor: '#1E5128', confirmButtonText: '{{ __('Mengerti') }}', background: '#ffffff' });
                return;
            }

            const formData = new FormData();
            formData.append('rating', rating);
            formData.append('comment', comment);
            selectedFiles.forEach(file => formData.append('photos[]', file));

            const url = isEditMode ? `/feedback/${feedbackId}` : '/feedback';
            const method = isEditMode ? 'POST' : 'POST';
            
            if (isEditMode) formData.append('_method', 'PUT');

            try {
                const res = await fetch(url, {
                    method,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                        'Accept': 'application/json',
                    },
                    body: formData
                });

                if (res.ok) {
                    const data = await res.json();
                    window.location.href = `/feedback/thank-you/${data.data.id}`;
                } else {
                    const err = await res.json();
                    const msg = err.message || Object.values(err.errors || {}).flat().join(', ') || '{{ __('Terjadi kesalahan.') }}';
                    Swal.fire({ title: '{{ __('Gagal') }}', text: msg, icon: 'error', confirmButtonColor: '#1E5128', confirmButtonText: '{{ __('Mengerti') }}', background: '#ffffff' });
                }
            } catch (e) {
                Swal.fire({ title: '{{ __('Koneksi Terputus') }}', text: '{{ __('Periksa koneksi internet Anda dan coba lagi.') }}', icon: 'error', confirmButtonColor: '#1E5128', confirmButtonText: '{{ __('Mengerti') }}', background: '#ffffff' });
            }
        }
    </script>
@endsection
