@extends('layouts.dashboard')

@section('title', 'Ulasan & Feedback')

@section('content')

    <div class="mb-6">
        <h1 class="font-display text-2xl font-bold text-charcoal">Ulasan & Feedback Wisatawan</h1>
        <p class="mt-0.5 text-sm text-gray-500">Pantau kepuasan pengunjung berdasarkan survei pasca kunjungan.</p>
    </div>

    {{-- Rating Summary --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
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
    <div class="space-y-4">
        @forelse ($feedbacks as $f)
            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary/10 text-sm font-bold text-primary">
                            {{ strtoupper(substr($f->user ? $f->user->name : 'W', 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-semibold text-charcoal">{{ $f->user ? $f->user->name : 'Wisatawan' }}</p>
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

                <div class="mt-3 flex gap-2">
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
                        data-confirm="{{ __('Apakah Anda yakin ingin menghapus ulasan ini?') }}">
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
            <div class="rounded-2xl border border-gray-100 bg-white p-8 text-center text-gray-400">
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
    @endpush

@endsection