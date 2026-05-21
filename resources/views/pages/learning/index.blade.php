@extends('layouts.app')
@section('title', 'Pocket Book - Penglipuran')

<!-- We hide default header to build a custom elegant one for learning -->
@push('styles')
    <style>
        header {
            display: none !important;
        }
    </style>
@endpush

@section('content')
    <div class="relative min-h-dvh overflow-hidden bg-[#FAF9F6] pb-24">
        <!-- Decorative background elements -->
        <div class="bg-linear-to-b from-primary/10 absolute left-0 top-0 -z-10 h-64 w-full to-transparent"></div>
        <div class="bg-accent/10 absolute -right-24 -top-24 -z-10 h-64 w-64 rounded-full blur-3xl"></div>

        <!-- Custom Header -->
        <div
            class="pt-sat sticky top-0 z-40 flex items-center justify-center border-b border-gray-100 bg-[#FAF9F6]/80 px-4 py-4 backdrop-blur-md">
            <h1 class="text-charcoal font-['Playfair_Display'] text-2xl font-bold">Eksplorasi Budaya</h1>
        </div>

        <div class="relative mx-auto max-w-md px-4 py-6">
            <!-- Glassmorphism Stats Card -->
            <div
                class="mb-10 flex items-center justify-between rounded-2xl border border-white bg-white/70 p-5 shadow-sm backdrop-blur-lg">
                <div class="flex items-center gap-3">
                    <div class="bg-accent/20 text-accent flex h-10 w-10 items-center justify-center rounded-full">
                        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-1.245-1.13-1.892-.15-.647-.05-1.26.55-1.892a1 1 0 00.025-1.763z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs font-medium text-gray-500">Beruntun</div>
                        <div class="text-charcoal text-lg font-bold">12 Hari</div>
                    </div>
                </div>
                <div class="h-10 w-px bg-gray-200"></div>
                <div class="flex items-center gap-3">
                    <div class="bg-primary/10 text-primary flex h-10 w-10 items-center justify-center rounded-full">
                        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs font-medium text-gray-500">Diselesaikan</div>
                        <div class="text-charcoal text-lg font-bold">4 Modul</div>
                    </div>
                </div>
            </div>

            <!-- Path Container -->
            <div class="relative flex flex-col items-center gap-24 py-8">

                <!-- Connection Lines -->
                <div class="absolute bottom-16 top-16 z-0 w-4 rounded-full bg-gray-200"></div>
                <div class="bg-primary absolute top-16 z-0 w-4 rounded-full transition-all duration-1000 ease-in-out"
                    style="height: 30%;">
                    <div class="absolute bottom-0 left-0 h-4 w-full rounded-full bg-white/30"></div>
                </div>

                <!-- Module 1 (Completed) -->
                <div class="relative z-10 flex flex-col items-center">
                    <div class="relative h-24 w-24">
                        <!-- Crown icon for completed -->
                        <div class="text-accent absolute -right-2 -top-3 z-20 rotate-12">
                            <svg class="text-accent h-8 w-8 drop-shadow-[0_2px_4px_rgba(0,0,0,0.15)]" fill="currentColor"
                                viewBox="0 0 24 24">
                                <path d="M2 19h20v2H2v-2zm1-2h18L19 7l-4 4-3-6-3 6-4-4L3 17z" />
                            </svg>
                        </div>
                        <a href="{{ route('learning-module', 1) }}"
                            class="bg-primary flex h-full w-full items-center justify-center rounded-full border-b-8 border-[#153a1d] text-white shadow-[0_10px_20px_rgba(30,81,40,0.3)] transition-all active:translate-y-2 active:scale-95 active:border-b-0">
                            <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </a>
                    </div>
                    <div
                        class="text-charcoal relative mt-4 whitespace-nowrap rounded-2xl border-2 border-gray-100 bg-white px-4 py-2 text-sm font-bold shadow-sm">
                        Sejarah Desa
                        <div
                            class="absolute -top-2 left-1/2 h-4 w-4 -translate-x-1/2 rotate-45 border-l-2 border-t-2 border-gray-100 bg-white">
                        </div>
                    </div>
                </div>

                <!-- Module 2 (Current) -->
                <div class="relative z-10 -ml-20 flex flex-col items-center">
                    <div class="relative h-28 w-28">
                        <!-- Floating Start Label -->
                        <div
                            class="text-primary absolute bottom-full left-1/2 z-20 mb-5 flex -translate-x-1/2 animate-bounce flex-col items-center whitespace-nowrap rounded-2xl border-2 border-gray-200 bg-white px-4 py-1.5 text-xs font-extrabold shadow-md">
                            MULAI
                            <div
                                class="absolute -bottom-2 h-3 w-3 translate-y-0.5 rotate-45 border-b-2 border-r-2 border-gray-200 bg-white">
                            </div>
                        </div>

                        <!-- Pulse animation ring behind the button -->
                        <div class="bg-primary/20 absolute inset-0 z-0 animate-ping rounded-full"></div>
                        <div
                            class="bg-primary/10 border-primary/20 absolute -inset-2 z-0 animate-pulse rounded-full border">
                        </div>

                        <a href="{{ route('learning-module', 2) }}"
                            class="bg-primary relative z-10 flex h-full w-full items-center justify-center rounded-full border-b-8 border-[#153a1d] text-white shadow-[0_12px_25px_rgba(30,81,40,0.4)] transition-all active:translate-y-2 active:scale-95 active:border-b-0">
                            <!-- Progress Ring -->
                            <svg class="pointer-events-none absolute inset-0 h-full w-full -rotate-90"
                                viewBox="0 0 112 112">
                                <circle cx="56" cy="56" r="48" fill="transparent"
                                    stroke="rgba(255,255,255,0.2)" stroke-width="8"></circle>
                                <!-- 50% progress -->
                                <circle cx="56" cy="56" r="48" fill="transparent" stroke="#D4AF37"
                                    stroke-width="8" stroke-dasharray="301" stroke-dashoffset="150" stroke-linecap="round">
                                </circle>
                            </svg>
                            <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </a>
                    </div>
                    <div
                        class="text-charcoal relative mt-4 whitespace-nowrap rounded-2xl border-2 border-gray-100 bg-white px-4 py-2 text-sm font-bold shadow-sm">
                        Arsitektur Bambu
                        <div
                            class="absolute -top-2 left-1/2 h-4 w-4 -translate-x-1/2 rotate-45 border-l-2 border-t-2 border-gray-100 bg-white">
                        </div>
                    </div>
                </div>

                <!-- Module 3 (Locked) -->
                <div class="relative z-10 mr-16 flex flex-col items-center">
                    <div class="relative h-24 w-24">
                        <div
                            class="flex h-full w-full items-center justify-center rounded-full border-b-8 border-gray-300 bg-gray-200 text-gray-400 shadow-inner">
                            <svg class="h-10 w-10" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                    <div
                        class="relative mt-4 whitespace-nowrap rounded-2xl border-2 border-gray-200 bg-gray-100 px-4 py-2 text-sm font-bold text-gray-400">
                        Adat & Tradisi
                        <div
                            class="absolute -top-2 left-1/2 h-4 w-4 -translate-x-1/2 rotate-45 border-l-2 border-t-2 border-gray-200 bg-gray-100">
                        </div>
                    </div>
                </div>

                <!-- Module 4 (Locked) -->
                <div class="relative z-10 ml-8 flex flex-col items-center">
                    <div class="relative h-24 w-24">
                        <div
                            class="flex h-full w-full items-center justify-center rounded-full border-b-8 border-gray-300 bg-gray-200 text-gray-400 shadow-inner">
                            <svg class="h-10 w-10" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                    <div
                        class="relative mt-4 whitespace-nowrap rounded-2xl border-2 border-gray-200 bg-gray-100 px-4 py-2 text-sm font-bold text-gray-400">
                        Filosofi Tri Hita
                        <div
                            class="absolute -top-2 left-1/2 h-4 w-4 -translate-x-1/2 rotate-45 border-l-2 border-t-2 border-gray-200 bg-gray-100">
                        </div>
                    </div>
                </div>

                <!-- Final Treasure/Reward -->
                <div class="relative z-10 mb-8 mt-6 flex flex-col items-center">
                    <div
                        class="bg-accent/20 text-accent border-accent/30 flex h-20 w-20 rotate-3 items-center justify-center rounded-2xl border shadow-inner">
                        <svg class="text-accent h-12 w-12 -rotate-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M5 5a3 3 0 015-2.236A3 3 0 0114.83 6H16a2 2 0 110 4h-5V9a1 1 0 10-2 0v1h-5a2 2 0 110-4h1.17C5.06 5.687 5 5.35 5 5zm4 1V5a1 1 0 10-1.196.98A1 1 0 009 6zm2-1v1a1 1 0 101.196-.98A1 1 0 0011 5zm-1 9a1 1 0 100-2 1 1 0 000 2z"
                                clip-rule="evenodd" />
                            <path d="M9 10a1 1 0 011-1h5v10a1 1 0 01-1 1h-4V10zm-1 0H3v9a1 1 0 001 1h4V10z" />
                        </svg>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
