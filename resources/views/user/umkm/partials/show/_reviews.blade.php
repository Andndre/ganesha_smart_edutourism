<section class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5">
    <div class="flex items-center justify-between gap-3">
        <h3 class="text-charcoal font-bold">{{ __('Ulasan Pengunjung') }}</h3>
        <span class="shrink-0 text-sm text-gray-500">★ {{ number_format((float) ($umkm->reviews_avg_rating ?? 0), 1) }} ·
            {{ $umkm->reviews_count ?? 0 }}</span>
    </div>
    @auth
        <form method="POST" action="{{ route('umkm.review.store', $umkm) }}" class="mt-4 space-y-3">
            @csrf
            <div class="flex flex-wrap gap-2" aria-label="{{ __('Rating') }}">
                @foreach (range(1, 5) as $rating)
                    <label class="cursor-pointer"><input class="peer sr-only" type="radio" name="rating"
                            value="{{ $rating }}" required><span
                            class="peer-checked:border-secondary peer-checked:bg-secondary/10 peer-checked:text-secondary block rounded-lg border border-gray-200 px-3 py-2 text-sm">{{ $rating }}
                            ★</span></label>
                @endforeach
            </div>
            <textarea name="comment" rows="3" maxlength="1000" class="w-full rounded-xl border border-gray-200 p-3 text-sm"
                placeholder="{{ __('Ceritakan pengalaman Anda (opsional)') }}"></textarea>
            <button
                class="tap-target bg-primary rounded-xl px-4 py-2 text-sm font-semibold text-white">{{ __('Kirim atau perbarui ulasan') }}</button>
        </form>
    @else
        <p class="mt-3 text-sm text-gray-500"><a class="text-primary font-semibold underline"
                href="{{ route('login') }}">{{ __('Masuk') }}</a> {{ __('untuk memberi ulasan.') }}</p>
    @endauth
    <div class="mt-5 space-y-4">
        @forelse ($reviews as $review)
            <article class="border-t border-gray-100 pt-4">
                <p class="text-charcoal text-sm font-semibold">{{ __('Pengunjung') }} · <span
                        class="text-secondary">{{ $review->rating }} ★</span></p>
                @if ($review->comment)
                    <p class="wrap-break-word mt-1 text-sm text-gray-600">{{ $review->comment }}</p>
                @endif
            </article>
        @empty
            <p class="mt-4 text-sm text-gray-500">{{ __('Belum ada ulasan publik.') }}</p>
        @endforelse
    </div>
</section>
