@extends('layouts.app')
@section('title', 'Katalog UMKM - Penglipuran')
@section('header_title', 'Katalog UMKM')

@section('content')
    <div class="px-4 pt-[calc(env(safe-area-inset-top)+6rem)] pb-40">
        <!-- Validation Errors -->
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl relative mb-4 z-20 shadow-sm" role="alert">
                <ul class="list-disc pl-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Session Error -->
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl relative mb-4 z-20 shadow-sm" role="alert">
                <span class="block sm:inline font-medium">{{ session('error') }}</span>
            </div>
        @endif
        
        <!-- Missing Categories Warning (if partial multi-stop) -->
        @if(session('missing_categories'))
            <div class="bg-yellow-50 border border-yellow-400 text-yellow-800 px-4 py-3 rounded-xl relative mb-4 z-20 shadow-sm" role="alert">
                <span class="block sm:inline font-medium">Beberapa pesanan Anda tidak tersedia di UMKM manapun:</span>
                <ul class="list-disc pl-5 text-sm mt-1">
                    @foreach(session('missing_categories') as $missingName)
                        <li>{{ $missingName }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mb-6">
            <h2 class="text-xl font-bold text-charcoal">Jelajah UMKM</h2>
            <p class="text-sm text-gray-500 mt-1">Pilih satu atau lebih kategori yang Anda inginkan. Sistem kami akan membantu mencarikan lokasi UMKM yang memiliki produk tersebut.</p>
        </div>

        <form action="{{ route('umkm.recommend') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-2 gap-4">
                @foreach($categories as $category)
                <div id="card-cat-{{ $category->id }}" 
                     class="relative bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col transition-all h-full cursor-pointer hover:shadow-md hover:border-gray-200"
                     onclick="openCategoryModal({{ json_encode($category) }}, event)">
                    <div class="aspect-square bg-gray-100 relative">
                        @if($category->image_path)
                            <img src="{{ asset('storage/' . $category->image_path) }}" alt="{{ $category->name }}" class="w-full h-full object-cover">
                        @else
                            <div class="absolute inset-0 flex items-center justify-center text-primary opacity-50">
                                <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                        @endif
                        
                        <!-- Checkbox Container at Top-Right -->
                        <div class="absolute top-2 right-2 z-10" onclick="event.stopPropagation()">
                            <label class="flex items-center justify-center cursor-pointer">
                                <input type="checkbox" name="category_ids[]" value="{{ $category->id }}" 
                                       id="checkbox-cat-{{ $category->id }}"
                                       class="w-5.5 h-5.5 rounded-full text-primary border-gray-300 focus:ring-primary focus:ring-offset-0 transition-all cursor-pointer accent-primary"
                                       onchange="updateCardHighlight({{ $category->id }})">
                            </label>
                        </div>
                    </div>
                    <div class="p-3 flex-1">
                        <h3 class="text-sm font-bold text-charcoal">{{ $category->name }}</h3>
                        @if($category->description)
                            <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ $category->description }}</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            @php
                $hasActiveSession = false;
                if (auth()->check() || session()->has('guest_token')) {
                    $hasActiveSession = \App\Models\RouteSession::where('status', 'active')
                        ->where(function($q) {
                            $q->where('user_id', auth()->id())
                              ->orWhere('guest_token', session('guest_token'));
                        })->exists();
                }
            @endphp
            <!-- Sticky Bottom Bar for Button -->
            <div class="fixed {{ $hasActiveSession ? 'mb-18' : '' }} bottom-[calc(env(safe-area-inset-bottom)+4rem)] left-0 right-0 bg-white/80 backdrop-blur-md border-t border-gray-200 px-4 pt-4 pb-8 z-40 transition-all">
                <button type="submit" class="w-full bg-primary text-white font-semibold py-3.5 rounded-xl active:scale-[0.98] transition-transform shadow-lg flex justify-center items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Temukan UMKM
                </button>
            </div>
        </form>
    </div>
@endsection

@push('modals')
    <!-- Multi-Stop Recommendation Modal -->
    @if(session('multi_stop_recommendations'))
        <x-modal name="multi-stop" maxWidth="sm" :defaultOpen="true">
            <div class="text-center">
                <div class="w-16 h-16 bg-primary/10 text-primary rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-charcoal mb-2">Satu Tempat Tidak Cukup!</h3>
                <p class="text-sm text-gray-500 mb-6">Tapi jangan khawatir, kami telah menyusun <span class="font-bold text-charcoal">rute terdekat</span> agar Anda bisa mendapatkan semua barang pilihan Anda dari beberapa UMKM sekaligus.</p>
                <div class="space-y-3">
                    <a href="{{ route('umkm.multi_recommended') }}" class="block w-full bg-primary text-white font-bold py-3.5 rounded-xl active:scale-[0.98] transition-transform shadow-lg">
                        Lihat Rute Belanja
                    </a>
                    <button @click="isOpen = false" class="block w-full bg-gray-100 text-gray-600 font-bold py-3.5 rounded-xl active:scale-[0.98] transition-transform">
                        Batal
                    </button>
                </div>
            </div>
        </x-modal>
    @endif

    <!-- Category Detail Modal -->
    <div id="category-detail-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-charcoal/50 backdrop-blur-sm transition-all duration-300">
        <div class="relative w-full max-w-sm rounded-3xl bg-white p-6 shadow-2xl transition-all scale-95 opacity-0 duration-300" id="category-modal-content">
            <!-- Close Button -->
            <button type="button" onclick="closeCategoryModal()" class="absolute top-4 right-4 z-10 flex h-8 w-8 items-center justify-center rounded-full bg-white/80 backdrop-blur-sm text-gray-500 shadow-sm border border-gray-100 hover:bg-gray-100 transition-colors">
                <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- Category Image -->
            <div class="aspect-video w-full overflow-hidden rounded-2xl border border-gray-100 bg-gray-50 flex items-center justify-center text-primary" id="modal-category-image-container">
                <img id="modal-category-image" src="" alt="" class="h-full w-full object-cover hidden">
                <svg id="modal-category-fallback" class="w-14 h-14 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>

            <!-- Content -->
            <div class="mt-4">
                <h3 id="modal-category-name" class="font-display text-xl font-bold text-charcoal">Nama Kategori</h3>
                <p id="modal-category-description" class="mt-2 text-sm text-gray-500 leading-relaxed min-h-[50px]">Deskripsi kategori...</p>
            </div>

            <!-- Action Button -->
            <div class="mt-6">
                <button type="button" id="modal-toggle-select-btn" onclick="toggleSelectFromModal()" class="w-full bg-primary text-white font-semibold py-3.5 rounded-xl active:scale-[0.98] transition-transform shadow-lg shadow-primary/20 flex justify-center items-center gap-2">
                    Pilih Kategori Ini
                </button>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
<script>
    let activeModalCategoryId = null;

    function updateCardHighlight(id) {
        const card = document.getElementById(`card-cat-${id}`);
        const checkbox = document.getElementById(`checkbox-cat-${id}`);
        if (!card || !checkbox) return;
        
        if (checkbox.checked) {
            card.classList.add('ring-2', 'ring-primary', 'border-primary', 'bg-primary/[0.01]');
            card.classList.remove('border-gray-100');
        } else {
            card.classList.remove('ring-2', 'ring-primary', 'border-primary', 'bg-primary/[0.01]');
            card.classList.add('border-gray-100');
        }
    }

    function openCategoryModal(category, event) {
        // Prevent opening modal if clicking checkbox container
        if (event.target.closest('[onclick="event.stopPropagation()"]')) {
            return;
        }

        activeModalCategoryId = category.id;
        
        // Set content
        document.getElementById('modal-category-name').innerText = category.name;
        document.getElementById('modal-category-description').innerText = category.description || "Belum ada deskripsi untuk kategori ini.";
        
        const imgEl = document.getElementById('modal-category-image');
        const fallbackEl = document.getElementById('modal-category-fallback');
        
        if (category.image_path) {
            imgEl.src = `/storage/${category.image_path}`;
            imgEl.classList.remove('hidden');
            fallbackEl.classList.add('hidden');
        } else {
            imgEl.src = '';
            imgEl.classList.add('hidden');
            fallbackEl.classList.remove('hidden');
        }
        
        // Update action button text and style based on select state
        const checkbox = document.getElementById(`checkbox-cat-${category.id}`);
        const btn = document.getElementById('modal-toggle-select-btn');
        if (checkbox && checkbox.checked) {
            btn.innerText = 'Batal Pilih Kategori';
            btn.className = 'w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3.5 rounded-xl active:scale-[0.98] transition-transform flex justify-center items-center gap-2';
        } else {
            btn.innerText = 'Pilih Kategori Ini';
            btn.className = 'w-full bg-primary hover:bg-primary-600 text-white font-semibold py-3.5 rounded-xl active:scale-[0.98] transition-transform shadow-lg shadow-primary/20 flex justify-center items-center gap-2';
        }
        
        // Show modal with animation
        const modal = document.getElementById('category-detail-modal');
        const content = document.getElementById('category-modal-content');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        // Force reflow
        void modal.offsetWidth;
        
        content.classList.remove('scale-95', 'opacity-0');
        content.classList.add('scale-100', 'opacity-100');
    }

    function closeCategoryModal() {
        const modal = document.getElementById('category-detail-modal');
        const content = document.getElementById('category-modal-content');
        if (!modal || !content) return;
        
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            activeModalCategoryId = null;
        }, 200);
    }

    function toggleSelectFromModal() {
        if (!activeModalCategoryId) return;
        
        const checkbox = document.getElementById(`checkbox-cat-${activeModalCategoryId}`);
        if (checkbox) {
            checkbox.checked = !checkbox.checked;
            checkbox.dispatchEvent(new Event('change'));
        }
        
        closeCategoryModal();
    }

    // Close modal when clicking outside content area
    document.getElementById('category-detail-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeCategoryModal();
        }
    });

    // Check highlights on load for pre-selected items (if any)
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('input[name="category_ids[]"]').forEach(box => {
            const id = box.value;
            updateCardHighlight(id);
        });
    });
</script>
@endpush