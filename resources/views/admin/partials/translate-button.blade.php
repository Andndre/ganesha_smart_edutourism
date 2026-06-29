{{--
    Auto-translate for multilingual forms.
    Injects a "Terjemahkan" button next to every locale tab (the `@click="locale = 'id'"`
    toggle), so no per-form markup is needed. On click it reads the active locale from
    Alpine's x-show display state, grabs every `name="x[active]"` field (TipTap-aware),
    sends it through /translate (LibreTranslate), and fills the matching `name="x[other]"`.
--}}
<script>
(function () {
    const URL = '{{ route('translate') }}';
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content;

    function makeBtn() {
        const b = document.createElement('button');
        b.type = 'button';
        b.dataset.translateBtn = '1';
        b.className = 'ml-auto inline-flex items-center gap-1.5 rounded-xl bg-secondary/20 px-3 py-1.5 text-xs font-bold text-charcoal transition-all hover:bg-secondary/30 disabled:opacity-50';
        b.innerHTML = '<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m1.05 9.5A18 18 0 016.4 9m6.1 9h7M11 21l5-10 5 10M12.75 5C11.78 10.77 8.07 15.61 3 18.13"/></svg><span>Terjemahkan</span>';
        b.addEventListener('click', () => run(b));
        return b;
    }

    // Active locale = the visible x-show="locale === 'xx'" block (Alpine toggles inline display)
    function activeLocale(scope) {
        for (const el of scope.querySelectorAll('[x-show]')) {
            const m = (el.getAttribute('x-show') || '').match(/locale === '(\w+)'/);
            if (m && el.style.display !== 'none') return m[1];
        }
        return 'id';
    }

    function fieldValue(el) {
        const c = el.closest('.tiptap-editor-container');
        if (c && c.editorInstance) return { value: c.editorInstance.getHTML(), format: 'html' };
        return { value: el.value, format: 'text' };
    }

    async function translate(q, source, target, format) {
        const res = await fetch(URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ q, source, target, format }),
        });
        if (!res.ok) throw new Error('translate failed');
        return (await res.json()).translatedText || '';
    }

    async function run(btn) {
        const scope = btn.closest('[x-data]') || btn.closest('form');
        if (!scope) return;
        const source = activeLocale(scope);
        const target = source === 'en' ? 'id' : 'en';

        const jobs = [];
        scope.querySelectorAll('[name$="[' + source + ']"]').forEach(src => {
            const name = src.getAttribute('name');
            const dst = scope.querySelector('[name="' + name.slice(0, -4) + '[' + target + ']"]');
            if (!dst) return;
            const { value, format } = fieldValue(src);
            if (!value || !value.trim() || value === '<p></p>') return;
            jobs.push({ dst, value, format });
        });

        if (!jobs.length) {
            await Swal.fire({
                icon: 'info',
                title: 'Tidak ada teks',
                text: 'Isi dulu tab ' + source.toUpperCase() + ' sebelum menerjemahkan.',
                confirmButtonColor: '#1E5128'
            });
            return;
        }

        const label = btn.querySelector('span');
        const original = label.textContent;
        btn.disabled = true;
        label.textContent = 'Menerjemahkan…';
        try {
            for (const job of jobs) {
                const out = await translate(job.value, source, target, job.format);
                if (job.format === 'html') {
                    window.setTiptapContent(job.dst, out);
                } else {
                    job.dst.value = out;
                    job.dst.dispatchEvent(new Event('input', { bubbles: true }));
                }
            }
        } catch (e) {
            await Swal.fire({
                icon: 'error',
                title: 'Gagal menerjemahkan',
                text: 'Layanan terjemahan tidak merespons. Coba lagi.',
                confirmButtonColor: '#1E5128'
            });
        } finally {
            btn.disabled = false;
            label.textContent = original;
        }
    }

    function inject() {
        document.querySelectorAll('button').forEach(tab => {
            const click = tab.getAttribute('@click') || tab.getAttribute('x-on:click') || '';
            if (!/locale\s*=\s*'id'/.test(click)) return;
            const parent = tab.parentElement;
            if (!parent || parent.querySelector('[data-translate-btn]')) return;
            parent.appendChild(makeBtn());
        });
    }

    document.addEventListener('DOMContentLoaded', inject);
})();
</script>
