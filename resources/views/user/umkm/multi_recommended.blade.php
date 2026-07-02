@extends('layouts.app')
@section('title', __('Rute Belanja UMKM - Penglipuran'))
@section('header_title', __('Rute Belanja UMKM'))

@push('styles')
    @include('user.umkm.partials.multi_recommended._styles')
@endpush

@section('content')
    @php
        $totalPrice = 0;
        foreach ($route as $stop) {
            $umkm = $stop['umkm'];
            if (is_object($umkm) && !($umkm instanceof \App\Models\UmkmProfile)) {
                $umkm = json_decode(json_encode($umkm), true);
            }
            if (\is_array($umkm)) {
                $umkmModel = new \App\Models\UmkmProfile();
                $umkmModel->exists = true;
                $umkmModel->forceFill($umkm);
                if (isset($umkm['products'])) {
                    $products = collect($umkm['products'])->map(function ($p) {
                        return new \App\Models\UmkmProduct()->forceFill($p);
                    });
                    $umkmModel->setRelation('products', $products);
                    $umkmModel->setRelation('activeProducts', $products);
                }
                $umkm = $umkmModel;
            }

            $stopCategoryIds = collect($stop['categories'])
                ->map(function ($c) {
                    return \is_array($c) ? $c['id'] ?? $c : (is_object($c) ? $c->id ?? $c : $c);
                })
                ->toArray();

            foreach ($umkm->activeProducts as $product) {
                if (\in_array($product->umkm_product_category_id, $stopCategoryIds)) {
                    $totalPrice += (float) ($product->display_price ?? 0);
                }
            }
        }
    @endphp
    <div class="px-4 pb-32 pt-6">
        <div class="mb-6">
            <h2 class="text-charcoal text-xl font-bold">{{ __('Rute Belanja Anda') }}</h2>
            <p class="mt-1 text-sm text-gray-500">
                {{ __('Kami telah menemukan beberapa UMKM terdekat agar Anda mendapatkan semua pesanan Anda.') }}</p>
        </div>

        <!-- Map Container -->
        <div class="mb-6 rounded-2xl border border-gray-100 bg-white p-2 shadow-sm">
            @include('user.umkm.partials.multi_recommended._map')
        </div>

        @include('user.umkm.partials.multi_recommended._route_steps')
    </div>

    @include('user.umkm.partials.multi_recommended._sticky_cta')
@endsection

@push('scripts')
    @include('user.umkm.partials.multi_recommended._scripts')
@endpush
