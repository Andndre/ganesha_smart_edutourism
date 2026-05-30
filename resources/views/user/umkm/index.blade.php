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
                <label class="relative block cursor-pointer">
                    <input type="checkbox" name="category_ids[]" value="{{ $category->id }}" class="peer sr-only">
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col peer-checked:ring-2 peer-checked:ring-primary peer-checked:border-primary transition-all h-full">
                        <div class="aspect-square bg-gray-100 relative">
                            @if($category->image_path)
                                <img src="{{ asset('storage/' . $category->image_path) }}" alt="{{ $category->name }}" class="w-full h-full object-cover">
                            @else
                                <div class="absolute inset-0 flex items-center justify-center text-primary opacity-50">
                                    @if($category->icon)
                                        <i class="{{ $category->icon }} text-4xl"></i>
                                    @else
                                        <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                    @endif
                                </div>
                            @endif
                            
                            <!-- Check icon overlay when selected -->
                            <div class="absolute top-2 right-2 w-7 h-7 bg-primary text-white rounded-full flex items-center justify-center opacity-0 peer-checked:opacity-100 transition-opacity shadow-md">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="p-3 flex-1">
                            <h3 class="text-sm font-bold text-charcoal">{{ $category->name }}</h3>
                            @if($category->description)
                                <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ $category->description }}</p>
                            @endif
                        </div>
                    </div>
                </label>
                @endforeach
            </div>

            <!-- Sticky Bottom Bar for Button -->
            <div class="fixed bottom-[calc(env(safe-area-inset-bottom)+4rem)] left-0 right-0 bg-white/80 backdrop-blur-md border-t border-gray-200 px-4 pt-4 pb-8 z-40">
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
@endpush