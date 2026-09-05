@php
    // Sidik jari state sesi yang dipakai halaman ini saat dirender di server.
    $sessionState = app()->getLocale() . '|' . (auth()->id() ?? 'guest') . '|' . ($activeEdutourismSession->id ?? '');
@endphp

{{--
    Ada dua jalur "halaman dipulihkan tanpa render ulang di server":
    bfcache browser (tombol Back setelah full page load) dan snapshot cache Livewire
    navigate (Back antar halaman wire:navigate). Dua-duanya menampilkan HTML lama, jadi
    state sesi ikut basi -- bahasa baru diganti tapi Back masih Inggris, atau rute
    edutourism sudah dihentikan tapi banner "Smart Edutourism Aktif" masih nempel.

    ponytail: satu sidik jari + sessionStorage sebagai "state terbaru per tab" sudah cukup
    untuk mendeteksi keduanya tanpa request apa pun. location.reload() mengganti entri
    history saat ini (bukan push), jadi urutan Back/Forward tetap utuh.
--}}
<div id="session-state" data-state="{{ $sessionState }}" hidden></div>
<script data-navigate-once>
    (function() {
        const rendered = () => document.getElementById('session-state')?.dataset.state ?? '';

        const store = (value) => {
            try {
                sessionStorage.setItem('session_state', value);
            } catch { /* private mode: abaikan */ }
        };

        const check = () => {
            let latest = null;
            try {
                latest = sessionStorage.getItem('session_state');
            } catch { /* private mode: abaikan */ }

            if (latest !== null && latest !== rendered()) location.reload();
        };

        store(rendered());

        // Jalur 1: bfcache browser.
        window.addEventListener('pageshow', (e) => {
            if (e.persisted) check();
        });

        // Jalur 2: Livewire navigate. Navigasi maju mengambil HTML segar dari server
        // (jadikan acuan), Back/Forward memakai snapshot lama (harus dicek).
        let popped = false;
        window.addEventListener('popstate', () => {
            popped = true;
        });
        document.addEventListener('livewire:navigated', () => {
            if (popped) {
                popped = false;
                check();
            } else {
                store(rendered());
            }
        });
    })();
</script>
