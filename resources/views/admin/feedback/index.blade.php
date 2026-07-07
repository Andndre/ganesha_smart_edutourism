@extends('layouts.dashboard')

@section('title', 'Ulasan & Feedback')

@section('content')

    <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div id="tour-header">
            <h1 class="font-display text-2xl font-bold text-charcoal">Ulasan & Feedback Wisatawan</h1>
            <p class="mt-0.5 text-sm text-gray-500">Pantau kepuasan pengunjung berdasarkan survei pasca kunjungan.</p>
        </div>
        <div class="flex items-center gap-2">
            <button id="tour-trigger-btn" onclick="startTutorial()"
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-500 transition-all hover:bg-gray-100 active:scale-[0.98]"
                title="Panduan Interaktif">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </button>
        </div>
    </div>

    {{-- Rating Summary --}}
    <div id="tour-rating-summary" class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Rating Rata-rata</p>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-4xl font-bold text-charcoal">{{ $avgRating }}</span>
                <span class="text-secondary">
                    @for ($i = 1; $i <= 5; $i++)
                        {{ $i <= round($avgRating) ? '★' : '☆' }}
                    @endfor
                </span>
            </div>
            <p class="mt-1 text-xs text-gray-400">dari 5.0 · {{ $totalReviews }} ulasan</p>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Ulasan Bulan Ini</p>
            <p class="mt-2 text-4xl font-bold text-charcoal">{{ $thisMonthReviews }}</p>
            <p class="mt-1 text-xs font-semibold text-primary">↑ Aktif Bulan Ini</p>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Distribusi Bintang</p>
            @foreach ($starsDistribution as [$star, $pct])
                <div class="mb-1 flex items-center gap-2 text-xs">
                    <span class="w-4 text-gray-500">{{ $star }}★</span>
                    <div class="flex-1 h-1.5 rounded-full bg-gray-100">
                        <div class="h-full rounded-full bg-secondary" style="width: {{ $pct }}%"></div>
                    </div>
                    <span class="w-8 text-right text-gray-400">{{ $pct }}%</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Feedback List --}}
    <div id="tour-feedback-list" class="space-y-4">
        @forelse ($feedbacks as $f)
            <div @if ($loop->first) id="tour-first-feedback" @endif
                class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary/10 text-sm font-bold text-primary">
                            {{ strtoupper(substr($f->user ? $f->user->name : 'W', 0, 1)) }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <p class="font-semibold text-charcoal">{{ $f->user ? $f->user->name : 'Wisatawan' }}</p>
                                @if ($f->feedback_type === 'umkm')
                                    <span class="inline-flex items-center rounded-full bg-amber-50 px-1.5 py-0.5 text-[10px] font-bold text-amber-700 border border-amber-200">
                                        UMKM: {{ $f->umkmProfile ? $f->umkmProfile->business_name : 'Toko' }}
                                    </span>
                                @elseif ($f->feedback_type === 'cultural')
                                    <span class="inline-flex items-center rounded-full bg-purple-50 px-1.5 py-0.5 text-[10px] font-bold text-purple-700 border border-purple-200">
                                        Objek Budaya
                                    </span>
                                @elseif ($f->feedback_type === 'service')
                                    <span class="inline-flex items-center rounded-full bg-blue-50 px-1.5 py-0.5 text-[10px] font-bold text-blue-700 border border-blue-200">
                                        Pelayanan
                                    </span>
                                @elseif ($f->feedback_type === 'facility')
                                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-1.5 py-0.5 text-[10px] font-bold text-emerald-700 border border-emerald-200">
                                        Fasilitas
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-gray-50 px-1.5 py-0.5 text-[10px] font-bold text-gray-700 border border-gray-200">
                                        Umum
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-400">{{ $f->created_at ? $f->created_at->format('d M Y') : '-' }}</p>
                        </div>
                    </div>
                    <div class="flex shrink-0 items-center gap-0.5 text-secondary">
                        @for ($i = 0; $i < 5; $i++)
                            <span class="text-sm {{ $i < $f->rating ? '' : 'opacity-20' }}">★</span>
                        @endfor
                    </div>
                </div>

                <p class="mt-3 text-sm leading-relaxed text-gray-600">{{ $f->comment }}</p>

                @if ($f->admin_response)
                    <div class="mt-3 rounded-xl bg-gray-50 border border-gray-100 p-4 text-sm text-gray-600">
                        <p class="font-semibold text-charcoal flex items-center gap-2 mb-1">
                            <svg class="h-4 w-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                            </svg>
                            Balasan Admin:
                        </p>
                        <p class="leading-relaxed">{{ $f->admin_response }}</p>
                    </div>
                @endif

                <div @if ($loop->first) id="tour-feedback-actions" @endif class="mt-3 flex gap-2">
                    @if (!$f->admin_response)
                        <button onclick="toggleReplyForm({{ $f->id }})"
                            class="rounded-lg border border-primary/20 px-3 py-1.5 text-xs font-semibold text-primary hover:bg-primary/5 transition-colors">
                            Balas
                        </button>
                    @endif

                    <form action="{{ route('admin.feedback.toggle', $f->id) }}" method="POST" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                            class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-500 hover:bg-gray-50 transition-colors">
                            {{ $f->is_public ? 'Sembunyikan Publik' : 'Tampilkan Publik' }}
                        </button>
                    </form>

                    <form action="{{ route('admin.feedback.destroy', $f->id) }}" method="POST" class="delete-form inline"
                        data-confirm="{{ 'Apakah Anda yakin ingin menghapus ulasan ini?' }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="rounded-lg border border-warning/20 px-3 py-1.5 text-xs font-semibold text-warning hover:bg-warning/5 transition-colors">
                            Hapus
                        </button>
                    </form>
                </div>

                {{-- Reply Form (Hidden by default) --}}
                <div id="reply-form-{{ $f->id }}" class="mt-4 hidden bg-gray-50/50 rounded-xl p-4 border border-gray-100">
                    <form action="{{ route('admin.feedback.reply', $f->id) }}" method="POST">
                        @csrf
                        <label for="admin-response-input-{{ $f->id }}"
                            class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">Tulis Balasan
                            Admin</label>
                        <textarea name="admin_response" id="admin-response-input-{{ $f->id }}" rows="2" required
                            class="w-full rounded-xl border border-gray-200 p-3 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary bg-white text-charcoal mb-3"
                            placeholder="Tulis balasan Anda di sini..."></textarea>
                        <div class="flex justify-end gap-2">
                            <button type="button" onclick="toggleReplyForm({{ $f->id }})"
                                class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-500 hover:bg-gray-50">
                                Batal
                            </button>
                            <button type="submit"
                                class="rounded-lg bg-primary px-3 py-1.5 text-xs font-semibold text-white shadow-md shadow-primary/20 hover:bg-primary-dark">
                                Kirim Balasan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @empty
            <div id="tour-empty-state" class="rounded-2xl border border-gray-100 bg-white p-8 text-center text-gray-400">
                Tidak ada ulasan atau feedback dari wisatawan.
            </div>
        @endforelse
    </div>

    @if ($feedbacks->hasPages())
        <div class="mt-4">
            {{ $feedbacks->links() }}
        </div>
    @endif

    @push('scripts')
        <script>
            function toggleReplyForm(id) {
                const el = document.getElementById('reply-form-' + id);
                if (el.classList.contains('hidden')) {
                    el.classList.remove('hidden');
                } else {
                    el.classList.add('hidden');
                }
            }
        </script>

        <script>
            function startTutorial() {
                const driver = window.driver.js.driver;
                const hasFeedback = document.getElementById('tour-first-feedback') !== null;
                const steps = [];

                // Langkah 1: Pengantar
                steps.push({
                    element: '#tour-header',
                    popover: {
                        title: '👋 Selamat Datang!',
                        description: 'Panduan ini akan menunjukkan cara memantau ulasan dan feedback wisatawan, serta cara membalasnya.',
                        side: 'bottom',
                        align: 'start'
                    }
                });

                // Langkah 2: Ringkasan Rating
                steps.push({
                    element: '#tour-rating-summary',
                    popover: {
                        title: '⭐ Ringkasan Rating',
                        description: 'Lihat rata-rata rating, jumlah ulasan bulan ini, dan distribusi bintang dari seluruh wisatawan di sini.',
                        side: 'bottom',
                        align: 'start'
                    }
                });

                if (hasFeedback) {
                    // Langkah 3: Daftar Feedback
                    steps.push({
                        element: '#tour-feedback-list',
                        popover: {
                            title: '📋 Daftar Ulasan',
                            description: 'Setiap ulasan wisatawan beserta rating bintang dan komentarnya akan tampil di sini.',
                            side: 'top',
                            align: 'start'
                        }
                    });

                    // Langkah 4: Aksi pada Feedback
                    steps.push({
                        element: '#tour-feedback-actions',
                        popover: {
                            title: '💬 Balas & Kelola Ulasan',
                            description: 'Gunakan tombol "Balas" untuk menanggapi ulasan, atur visibilitasnya secara publik, atau hapus jika diperlukan.',
                            side: 'top',
                            align: 'start'
                        }
                    });
                } else {
                    // Langkah Alternatif jika kosong
                    steps.push({
                        element: '#tour-empty-state',
                        popover: {
                            title: '📭 Belum Ada Ulasan',
                            description: 'Setelah wisatawan mengisi survei kepuasan, ulasan mereka akan muncul di area ini.',
                            side: 'top',
                            align: 'start'
                        }
                    });
                }

                const driverObj = driver({
                    showProgress: true,
                    allowClose: true,
                    steps: steps,
                    popoverClass: 'driverjs-theme'
                });

                driverObj.drive();
            }

            // Auto-run for first-time visitors
            document.addEventListener('DOMContentLoaded', () => {
                const tourCompleted = localStorage.getItem('admin_feedback_tour_completed');
                if (!tourCompleted) {
                    setTimeout(() => {
                        startTutorial();
                        localStorage.setItem('admin_feedback_tour_completed', 'true');
                    }, 1000);
                }
            });
        </script>
    @endpush

@endsection