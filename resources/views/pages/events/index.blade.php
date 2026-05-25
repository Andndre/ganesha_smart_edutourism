@extends('layouts.app')
@section('title', 'Kalender Event & Budaya')
@section('header_title', 'Kalender Event')

@section('content')
    <div class="px-4 py-6">
        <!-- Category Tabs/Pills -->
        <div class="no-scrollbar mb-6 flex gap-2 overflow-x-auto pb-2">
            <button class="bg-primary shrink-0 rounded-full px-4 py-2 text-sm font-medium text-white">Semua</button>
            <button
                class="text-charcoal shrink-0 rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-medium transition-colors active:bg-gray-50">Upacara
                Adat</button>
            <button
                class="text-charcoal shrink-0 rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-medium transition-colors active:bg-gray-50">Festival</button>
            <button
                class="text-charcoal shrink-0 rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-medium transition-colors active:bg-gray-50">Workshop</button>
        </div>

        <div class="mb-4">
            <h2 class="text-charcoal text-lg font-bold">Acara Mendatang</h2>
            <p class="mt-1 text-xs text-gray-500">Jangan lewatkan momen budaya yang spesial.</p>
        </div>

        <!-- Timeline Container -->
        <div class="relative mt-6 space-y-8 border-l-2 border-gray-200 pl-5">

            <!-- Timeline Item 1 (Upcoming) -->
            <div class="relative">
                <!-- Timeline Dot -->
                <div
                    class="-left-6.75 bg-accent absolute top-1 z-10 h-5 w-5 rounded-full border-4 border-[#E5E3DF] shadow-sm">
                </div>

                <!-- Date Highlight -->
                <div class="mb-3">
                    <span class="text-accent text-sm font-bold">Besok, 15 Agustus 2026</span>
                </div>

                <!-- Event Card -->
                <a href="#"
                    class="block overflow-hidden rounded-2xl border border-gray-100 bg-white p-4 shadow-sm transition-transform active:scale-[0.98]">
                    <div class="mb-2 flex items-start justify-between">
                        <span
                            class="rounded-lg border border-amber-100 bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-600">Upacara
                            Adat</span>
                        <span class="text-primary rounded-md bg-green-50 px-2 py-1 text-xs font-bold">Gratis</span>
                    </div>
                    <h3 class="text-charcoal mb-1 text-base font-bold">Ngusaba Kadasa</h3>
                    <p class="mb-4 line-clamp-2 text-sm leading-relaxed text-gray-500">
                        Upacara persembahan agung yang diadakan di Pura Penataran untuk memohon kesejahteraan alam, hasil
                        panen melimpah, dan ketenteraman warga desa.
                    </p>
                    <div class="flex items-center gap-4 text-xs text-gray-500">
                        <div class="flex items-center gap-1.5">
                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            08:00 - Selesai
                        </div>
                        <div class="flex items-center gap-1.5">
                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Pura Penataran
                        </div>
                    </div>
                </a>
            </div>

            <!-- Timeline Item 2 -->
            <div class="relative">
                <!-- Timeline Dot -->
                <div class="-left-6.5 absolute top-1 z-10 h-4 w-4 rounded-full border-4 border-[#E5E3DF] bg-gray-300">
                </div>

                <div class="mb-3">
                    <span class="text-sm font-bold text-gray-600">Sabtu, 22 Agustus 2026</span>
                </div>

                <!-- Event Card -->
                <a href="#"
                    class="block overflow-hidden rounded-2xl border border-gray-100 bg-white p-4 shadow-sm transition-transform active:scale-[0.98]">
                    <div class="mb-2 flex items-start justify-between">
                        <span
                            class="rounded-lg border border-blue-100 bg-blue-50 px-2.5 py-1 text-xs font-bold text-blue-600">Workshop</span>
                        <span class="text-charcoal rounded-md bg-gray-100 px-2 py-1 text-xs font-bold">Rp 50.000</span>
                    </div>
                    <h3 class="text-charcoal mb-1 text-base font-bold">Kelas Menganyam Bambu</h3>
                    <p class="mb-4 line-clamp-2 text-sm leading-relaxed text-gray-500">
                        Pelajari teknik dasar menganyam bambu tradisional bersama para perajin lokal berpengalaman. Hasil
                        karya bisa dibawa pulang.
                    </p>
                    <div class="flex items-center gap-4 text-xs text-gray-500">
                        <div class="flex items-center gap-1.5">
                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            09:00 - 12:00
                        </div>
                        <div class="flex items-center gap-1.5">
                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Bale Banjar
                        </div>
                    </div>
                </a>
            </div>

            <!-- Timeline Item 3 -->
            <div class="relative">
                <!-- Timeline Dot -->
                <div class="-left-6.5 absolute top-1 z-10 h-4 w-4 rounded-full border-4 border-[#E5E3DF] bg-gray-300">
                </div>

                <div class="mb-3">
                    <span class="text-sm font-bold text-gray-600">Rabu, 2 September 2026</span>
                </div>

                <!-- Event Card -->
                <a href="#"
                    class="block overflow-hidden rounded-2xl border border-gray-100 bg-white p-4 shadow-sm transition-transform active:scale-[0.98]">
                    <div class="mb-2 flex items-start justify-between">
                        <span
                            class="rounded-lg border border-purple-100 bg-purple-50 px-2.5 py-1 text-xs font-bold text-purple-600">Festival</span>
                        <span class="text-primary rounded-md bg-green-50 px-2 py-1 text-xs font-bold">Gratis</span>
                    </div>
                    <h3 class="text-charcoal mb-1 text-base font-bold">Penglipuran Village Festival</h3>
                    <p class="mb-4 line-clamp-2 text-sm leading-relaxed text-gray-500">
                        Puncak acara festival budaya tahunan menampilkan parade busana adat, pameran kuliner, dan tarian
                        sakral di sepanjang jalan utama desa.
                    </p>
                    <div class="flex items-center gap-4 text-xs text-gray-500">
                        <div class="flex items-center gap-1.5">
                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            15:00 - 22:00
                        </div>
                        <div class="flex items-center gap-1.5">
                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Jalan Utama Desa
                        </div>
                    </div>
                </a>
            </div>

        </div>

        <div class="mb-4 mt-8 text-center">
            <p class="text-xs text-gray-400">Tidak ada acara lagi di bulan ini.</p>
        </div>
    </div>
@endsection