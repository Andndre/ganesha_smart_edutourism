<!-- Contact Info -->
<div>
    <h3 class="text-charcoal mb-3 font-bold">Informasi Kontak Pemesan</h3>
    <div class="space-y-3">
        <input type="text" name="guest_name" value="{{ auth()->user()->name }}" required placeholder="Nama Lengkap"
            class="focus:border-primary focus:ring-primary w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3.5 text-sm transition-colors focus:outline-none focus:ring-1">
        <input type="email" name="guest_email" value="{{ auth()->user()->email }}" required
            placeholder="Alamat Email (Untuk E-Ticket)"
            class="focus:border-primary focus:ring-primary w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3.5 text-sm transition-colors focus:outline-none focus:ring-1">
        <input type="tel" name="guest_phone" value="{{ auth()->user()->phone ?? '' }}" required
            placeholder="Nomor WhatsApp aktif"
            class="focus:border-primary focus:ring-primary w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3.5 text-sm transition-colors focus:outline-none focus:ring-1">
    </div>
</div>
