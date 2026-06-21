{{-- ponytail: partial dipecah untuk keterbacaan --}}
        <!-- Validation Errors -->
        @if ($errors->any())
            <div class="relative z-20 mb-4 rounded-xl border border-red-400 bg-red-100 px-4 py-3 text-red-700 shadow-sm"
                role="alert">
                <ul class="list-disc pl-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Session Error -->
        @if (session('error'))
            <div class="relative z-20 mb-4 rounded-xl border border-red-400 bg-red-100 px-4 py-3 text-red-700 shadow-sm"
                role="alert">
                <span class="block font-medium sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Missing Categories Warning (if partial multi-stop) -->
        @if (session('missing_categories'))
            <div class="relative z-20 mb-4 rounded-xl border border-yellow-400 bg-yellow-50 px-4 py-3 text-yellow-800 shadow-sm"
                role="alert">
                <span class="block font-medium sm:inline">Beberapa pesanan Anda tidak tersedia di UMKM manapun:</span>
                <ul class="mt-1 list-disc pl-5 text-sm">
                    @foreach (session('missing_categories') as $missingName)
                        <li>{{ $missingName }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
