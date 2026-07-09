<script>
window.missionState = window.missionState || {};   // { [pointIdx]: Mission[] }
let currentMissionPoint = null;
let missionSnapshot = '';
let missionDirty = false;

const MISSION_TYPES = [
    { value: 'matching', label: 'Mencocokkan (Matching)' },
    { value: 'sequence', label: 'Urutan (Sequence)' },
    { value: 'word_search', label: 'Cari Kata (Word Search)' },
    { value: 'decision', label: 'Skenario Keputusan (Decision)' },
    { value: 'riddle', label: 'Teka-teki (Riddle)' },
    { value: 'quiz', label: 'Kuis Pilihan Ganda (Quiz)' },
];

// Per-type config editors are registered by Tasks 5-8 into MISSION_CONFIG_BUILDERS[type].
// Each builder: (container: HTMLElement, config: object) => void  (renders inputs, wires oninput -> markMissionDirty).
window.MISSION_CONFIG_BUILDERS = window.MISSION_CONFIG_BUILDERS || {};
// Each reader: (container: HTMLElement) => object (serializes the type's inputs back to a config object).
window.MISSION_CONFIG_READERS = window.MISSION_CONFIG_READERS || {};

function markMissionDirty() { missionDirty = true; }

// Escapes a config-derived string for safe interpolation into an HTML attribute
// value (or any other markup position built via innerHTML/template literals).
// Order matters: `&` must be escaped first, otherwise the entities inserted by
// the later replacements would themselves get re-escaped.
function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

window.openMissionModal = function (pointIdx) {
    currentMissionPoint = pointIdx;
    const list = document.getElementById('missions-list');
    list.innerHTML = '';
    (window.missionState[pointIdx] || []).forEach(m => addMissionField(m));
    missionSnapshot = JSON.stringify(window.missionState[pointIdx] || []);
    missionDirty = false;
    window.dispatchEvent(new CustomEvent('open-missions-modal'));
};

window.missionsModalCloseAttempt = function (proceed) {
    if (!missionDirty) { proceed(); return; }
    Swal.fire({
        icon: 'warning',
        title: 'Buang perubahan?',
        text: 'Yakin ingin membuang perubahan pada misi ini?',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Buang Perubahan',
        cancelButtonText: 'Batal',
    }).then(r => {
        if (!r.isConfirmed) return;
        window.missionState[currentMissionPoint] = JSON.parse(missionSnapshot);
        missionDirty = false;
        proceed();
    });
};

function closeMissionModal() {
    collectMissions();                       // read DOM -> window.missionState[currentMissionPoint]

    // Write back into the point object itself (source of truth for scripts.blade.php's
    // updateBuilder(), which resyncs window.missionState from point.missions on every
    // redraw). Without this, the "Kelola Misi (N)" badge count would go stale after an
    // edit, and reordering/removing points would desync missionState from the wrong point.
    if (typeof selectedPoints !== 'undefined' && selectedPoints[currentMissionPoint]) {
        selectedPoints[currentMissionPoint].missions = window.missionState[currentMissionPoint];
    }

    serializeMissions(currentMissionPoint);  // -> hidden input
    missionDirty = false;
    window.dispatchEvent(new CustomEvent('close-missions-modal'));
    if (typeof updateBuilder === 'function') updateBuilder(); // refresh "Kelola Misi (N)" badge
}

window.serializeMissions = function (pointIdx) {
    const input = document.getElementById(`missions-input-${pointIdx}`);
    if (input) input.value = JSON.stringify(window.missionState[pointIdx] || []);
};

// Build one mission row. `mission` may be null (new).
function addMissionField(mission = null) {
    const list = document.getElementById('missions-list');
    const idx = list.children.length;
    const m = mission || { type: 'matching', title: { en: '', id: '' }, points: 100, time_limit_seconds: null, config: {} };

    const row = document.createElement('div');
    row.className = 'mission-item bg-white p-4 rounded-xl border border-gray-100 shadow-sm';
    row.dataset.missionId = m.id || '';
    // Preserve the ORIGINAL config + type for existing missions so an unregistered
    // reader (no game-type editor wired up yet) doesn't wipe real content to {}
    // on close. Only set for missions seeded from window.missionState (mission !== null);
    // brand-new missions (added via the "+" button) have nothing to preserve.
    if (mission) {
        row.dataset.originalType = mission.type || '';
        row.dataset.originalConfig = JSON.stringify(mission.config || {});
    }
    row.setAttribute('x-data', "{ locale: 'id' }");
    row.innerHTML = `
        <div class="flex items-center justify-between">
            <button type="button" onclick="toggleMissionBody(this)" class="mission-toggle-btn flex min-w-0 flex-1 items-center gap-2 py-1 text-left">
                <svg class="h-4 w-4 shrink-0 text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                <div class="min-w-0">
                    <span class="text-sm font-bold text-gray-700">Misi ${idx + 1}</span>
                    <p class="mission-summary truncate text-[10px] text-gray-400">${escapeHtml(missionTypeLabel(m.type))} · ${escapeHtml(m.title?.id || m.title?.en || '(tanpa judul)')}</p>
                </div>
            </button>
            <div class="flex shrink-0 items-center gap-1.5">
                <button @click="locale='id'" :class="locale==='id'?'bg-primary text-white':'bg-gray-100 text-gray-500'" class="px-2 py-0.5 rounded text-[10px] font-semibold" type="button">ID</button>
                <button @click="locale='en'" :class="locale==='en'?'bg-primary text-white':'bg-gray-100 text-gray-500'" class="px-2 py-0.5 rounded text-[10px] font-semibold" type="button">EN</button>
                <button type="button" onclick="translateMissionTitle(this)" class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-secondary/20 hover:bg-secondary/30 text-charcoal flex items-center gap-0.5 transition-all">
                    <svg class="h-2.5 w-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m1.05 9.5A18 18 0 016.4 9m6.1 9h7M11 21l5-10 5 10M12.75 5C11.78 10.77 8.07 15.61 3 18.13"/></svg>
                    <span>Terjemahkan</span>
                </button>
                <button type="button" onclick="this.closest('.mission-item').remove(); markMissionDirty();" class="p-1 text-gray-400 hover:text-red-500 rounded-lg hover:bg-red-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
            </div>
        </div>
        <div class="mission-body hidden mt-3">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-3">
                <div>
                    <label class="mb-1 block text-xs font-semibold text-gray-600">Tipe Misi</label>
                    <select class="m-type w-full rounded-lg border border-gray-200 px-3 py-1.5 text-sm" onchange="onMissionTypeChange(this)">
                        ${MISSION_TYPES.map(t => `<option value="${t.value}" ${m.type === t.value ? 'selected' : ''}>${t.label}</option>`).join('')}
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-gray-600">Poin</label>
                    <input type="number" class="m-points w-full rounded-lg border border-gray-200 px-3 py-1.5 text-sm" value="${m.points ?? 100}" oninput="markMissionDirty()">
                </div>
                <div>
                    <div x-show="locale==='id'">
                        <label class="mb-1 block text-xs font-semibold text-gray-600">Judul (ID)</label>
                        <input type="text" class="m-title-id w-full rounded-lg border border-gray-200 px-3 py-1.5 text-sm" value="${escapeHtml(m.title?.id)}" oninput="markMissionDirty(); updateMissionSummary(this)">
                    </div>
                    <div x-show="locale==='en'">
                        <label class="mb-1 block text-xs font-semibold text-gray-600">Judul (EN)</label>
                        <input type="text" class="m-title-en w-full rounded-lg border border-gray-200 px-3 py-1.5 text-sm" value="${escapeHtml(m.title?.en)}" oninput="markMissionDirty(); updateMissionSummary(this)">
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-gray-600">Batas Waktu (detik, opsional)</label>
                    <input type="number" class="m-timelimit w-full rounded-lg border border-gray-200 px-3 py-1.5 text-sm" value="${m.time_limit_seconds ?? ''}" oninput="markMissionDirty()">
                </div>
            </div>
            <div class="m-config border-t border-gray-100 pt-3"></div>
        </div>
    `;
    list.appendChild(row);
    window.Alpine?.initTree(row);
    renderMissionConfig(row, m.type, m.config || {});
    markMissionDirty();
}

// Collapsed by default (per-mission accordion): the body stays in the DOM (just
// hidden via a CSS class), so toggling never loses input values.
function toggleMissionBody(btn) {
    const row = btn.closest('.mission-item');
    row.querySelector('.mission-body').classList.toggle('hidden');
    btn.querySelector('svg').classList.toggle('rotate-90');
}

function missionTypeLabel(type) {
    return MISSION_TYPES.find(t => t.value === type)?.label || type;
}

// Keeps the collapsed-state summary line in sync with the title/type inputs.
function updateMissionSummary(el) {
    const row = el.closest('.mission-item');
    const type = row.querySelector('.m-type').value;
    const title = row.querySelector('.m-title-id').value || row.querySelector('.m-title-en').value || '(tanpa judul)';
    row.querySelector('.mission-summary').textContent = `${missionTypeLabel(type)} · ${title}`;
}

function onMissionTypeChange(select) {
    updateMissionSummary(select);
    const row = select.closest('.mission-item');
    renderMissionConfig(row, select.value, {});   // reset config UI on type change
    markMissionDirty();
}

function renderMissionConfig(row, type, config) {
    const container = row.querySelector('.m-config');
    container.innerHTML = '';
    const builder = window.MISSION_CONFIG_BUILDERS[type];
    if (builder) builder(container, config);
    else container.innerHTML = '<p class="text-xs text-gray-400">Editor untuk tipe ini belum tersedia.</p>';
}

// DOM -> window.missionState[currentMissionPoint]
function collectMissions() {
    const rows = document.querySelectorAll('#missions-list .mission-item');
    const missions = [];
    rows.forEach(row => {
        const type = row.querySelector('.m-type').value;
        const reader = window.MISSION_CONFIG_READERS[type];
        // No reader registered for this type yet: fall back to whatever config this
        // row was seeded with (if any), rather than {}, so untouched missions survive
        // a close-and-save cycle unchanged. Only valid when the type wasn't changed
        // in this session — if the admin switched types, the old config no longer
        // applies to the new type, so there's nothing sane to preserve.
        let config;
        if (reader) {
            config = reader(row.querySelector('.m-config'));
        } else if (row.dataset.originalConfig && row.dataset.originalType === type) {
            config = JSON.parse(row.dataset.originalConfig);
        } else {
            config = {};
        }
        missions.push({
            id: row.dataset.missionId ? Number(row.dataset.missionId) : undefined,
            type,
            title: { id: row.querySelector('.m-title-id').value, en: row.querySelector('.m-title-en').value },
            points: Number(row.querySelector('.m-points').value) || 0,
            time_limit_seconds: row.querySelector('.m-timelimit').value ? Number(row.querySelector('.m-timelimit').value) : null,
            config,
        });
    });
    window.missionState[currentMissionPoint] = missions;
}

// Shared helper used by game builders: an ID/EN paired text input group.
// Returns HTML; caller wires oninput. `name` is a CSS class marker for the reader.
function bilingualInput(cls, value = { en: '', id: '' }, label = '') {
    return `
      <div x-data="{ l:'id' }" class="rounded-lg border border-gray-100 p-2">
        ${label ? `<div class="flex items-center justify-between"><span class="text-[10px] font-semibold text-gray-500">${label}</span>
          <span class="flex gap-1 items-center">
            <button type="button" @click="l='id'" :class="l==='id'?'bg-primary text-white':'bg-gray-100'" class="px-1.5 py-0.5 rounded text-[10px] font-semibold">ID</button>
            <button type="button" @click="l='en'" :class="l==='en'?'bg-primary text-white':'bg-gray-100'" class="px-1.5 py-0.5 rounded text-[10px] font-semibold">EN</button>
            <button type="button" onclick="translateBilingualField(this, '${cls}')" class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-secondary/20 hover:bg-secondary/30 text-charcoal flex items-center gap-0.5 transition-all">
              <svg class="h-2.5 w-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m1.05 9.5A18 18 0 016.4 9m6.1 9h7M11 21l5-10 5 10M12.75 5C11.78 10.77 8.07 15.61 3 18.13"/></svg>
              <span>Terjemahkan</span>
            </button>
          </span></div>` : ''}
        <input type="text" x-show="l==='id'" class="${cls}-id w-full rounded border border-gray-200 px-2 py-1 text-sm mt-1" value="${escapeHtml(value?.id)}" oninput="markMissionDirty()">
        <input type="text" x-show="l==='en'" class="${cls}-en w-full rounded border border-gray-200 px-2 py-1 text-sm mt-1" value="${escapeHtml(value?.en)}" oninput="markMissionDirty()">
      </div>`;
}
function readBilingual(scope, cls) {
    return { id: scope.querySelector(`.${cls}-id`)?.value || '', en: scope.querySelector(`.${cls}-en`)?.value || '' };
}

async function translateBilingualField(btn, cls) {
    const container = btn.closest('[x-data]');
    if (!container) return;
    const active = window.Alpine ? window.Alpine.$data(container).l : 'id';
    const sourceLocale = active;
    const targetLocale = sourceLocale === 'en' ? 'id' : 'en';

    const sourceInput = container.querySelector(`.${cls}-${sourceLocale}`);
    const targetInput = container.querySelector(`.${cls}-${targetLocale}`);
    if (!sourceInput || !targetInput) return;

    const sourceVal = sourceInput.value.trim();
    if (!sourceVal) {
        if (window.Swal) {
            window.Swal.fire({
                icon: 'info',
                title: 'Tidak ada teks',
                text: 'Isi dulu tab ' + sourceLocale.toUpperCase() + ' sebelum menerjemahkan.',
                confirmButtonColor: '#1E5128'
            });
        }
        return;
    }

    const labelSpan = btn.querySelector('span');
    const originalText = labelSpan ? labelSpan.textContent : 'Terjemahkan';
    if (labelSpan) labelSpan.textContent = 'Menerjemahkan…';
    btn.disabled = true;

    try {
        if (window.translateText) {
            const out = await window.translateText(sourceVal, sourceLocale, targetLocale, 'text');
            targetInput.value = out;
            targetInput.dispatchEvent(new Event('input', { bubbles: true }));
            markMissionDirty();
        }
    } catch (e) {
        if (window.Swal) {
            window.Swal.fire({
                icon: 'error',
                title: 'Gagal menerjemahkan',
                text: 'Layanan terjemahan tidak merespons. Coba lagi.',
                confirmButtonColor: '#1E5128'
            });
        }
    } finally {
        btn.disabled = false;
        if (labelSpan) labelSpan.textContent = originalText;
    }
}

async function translateMissionTitle(btn) {
    const item = btn.closest('.mission-item');
    if (!item) return;
    const active = window.Alpine ? window.Alpine.$data(item).locale : 'id';
    const sourceLocale = active;
    const targetLocale = sourceLocale === 'en' ? 'id' : 'en';

    const sourceInput = item.querySelector(`.m-title-${sourceLocale}`);
    const targetInput = item.querySelector(`.m-title-${targetLocale}`);
    if (!sourceInput || !targetInput) return;

    const sourceVal = sourceInput.value.trim();
    if (!sourceVal) {
        if (window.Swal) {
            window.Swal.fire({
                icon: 'info',
                title: 'Tidak ada teks',
                text: 'Isi dulu tab ' + sourceLocale.toUpperCase() + ' sebelum menerjemahkan.',
                confirmButtonColor: '#1E5128'
            });
        }
        return;
    }

    const labelSpan = btn.querySelector('span');
    const originalText = labelSpan ? labelSpan.textContent : 'Terjemahkan';
    if (labelSpan) labelSpan.textContent = 'Menerjemahkan…';
    btn.disabled = true;

    try {
        if (window.translateText) {
            const out = await window.translateText(sourceVal, sourceLocale, targetLocale, 'text');
            targetInput.value = out;
            targetInput.dispatchEvent(new Event('input', { bubbles: true }));
            markMissionDirty();
        }
    } catch (e) {
        if (window.Swal) {
            window.Swal.fire({
                icon: 'error',
                title: 'Gagal menerjemahkan',
                text: 'Layanan terjemahan tidak merespons. Coba lagi.',
                confirmButtonColor: '#1E5128'
            });
        }
    } finally {
        btn.disabled = false;
        if (labelSpan) labelSpan.textContent = originalText;
    }
}
window.translateBilingualField = translateBilingualField;
window.translateMissionTitle = translateMissionTitle;

// --- Task 5: matching config editor -------------------------------------------------

window.MISSION_CONFIG_BUILDERS['matching'] = function (c, cfg) {
    const mode = cfg.mode || 'pick';
    c.innerHTML = `
      <label class="text-xs font-semibold text-gray-600">Mode</label>
      <select class="mc-mode w-full rounded-lg border border-gray-200 px-2 py-1 text-sm mb-2" onchange="markMissionDirty(); window.MISSION_CONFIG_BUILDERS['matching'](this.closest('.m-config'), window.MISSION_CONFIG_READERS['matching'](this.closest('.m-config')))">
        <option value="pick" ${mode==='pick'?'selected':''}>Pilih yang benar (pick)</option>
        <option value="match" ${mode==='match'?'selected':''}>Pasangkan (match)</option>
      </select>
      <div class="mc-prompt mb-2">${bilingualInput('mc-prompt', cfg.prompt || {en:'',id:''}, 'Instruksi (prompt)')}</div>
      <div class="flex items-center gap-3 mb-2">
        ${mode === 'pick' ? `<label class="text-xs text-gray-600">Jumlah benar (pick_count)
          <input type="number" min="1" class="mc-pick-count w-16 rounded border border-gray-200 px-2 py-1 text-sm ml-1" value="${cfg.pick_count ?? ''}" oninput="markMissionDirty()"></label>` : ''}
        <label class="text-xs text-gray-600">Penalti (opsional)
          <input type="number" min="0" class="mc-penalty w-16 rounded border border-gray-200 px-2 py-1 text-sm ml-1" value="${cfg.penalty ?? ''}" oninput="markMissionDirty()"></label>
      </div>
      <div class="mc-rows space-y-3"></div>
      <button type="button" class="mc-add mt-3 text-xs text-primary font-semibold hover:underline">+ Tambah ${mode==='pick'?'Kartu':'Pasangan'}</button>`;
    const rows = c.querySelector('.mc-rows');
    const addRow = (data = {}) => {
        const el = document.createElement('div');
        if (mode === 'pick') {
            el.className = 'mc-row rounded-xl border border-gray-100 p-3 bg-gray-50/30 flex gap-4 items-start relative group';
            el.innerHTML = `
              <!-- Left side: Image Upload Container -->
              <div class="img-container relative w-16 h-16 shrink-0 rounded-xl border-2 border-dashed border-gray-200 bg-gray-50 hover:bg-gray-100/50 hover:border-primary/50 transition-all flex items-center justify-center overflow-hidden cursor-pointer" 
                   onclick="this.querySelector('input[type=file]').click()">
                <input type="hidden" class="mc-image" value="${escapeHtml(data.image)}">
                <input type="file" accept="image/*" class="hidden" onchange="uploadMissionAsset(this, '.mc-image')">
                <div class="img-preview-wrap w-full h-full flex items-center justify-center">
                  ${data.image ? `
                    <img src="${escapeHtml(data.image)}" alt="" class="mc-image-preview w-full h-full object-cover">
                    <div class="absolute inset-0 bg-black/40 opacity-0 hover:opacity-100 transition-opacity flex items-center justify-center text-[10px] text-white font-semibold">Ganti</div>
                  ` : `
                    <div class="text-gray-400 flex flex-col items-center gap-0.5 placeholder-wrap">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                      <span class="text-[8px] font-medium">Gambar</span>
                    </div>
                  `}
                </div>
              </div>
              
              <!-- Right side: Inputs -->
              <div class="flex-1 min-w-0 space-y-2">
                ${bilingualInput('mc-label', data.label || {en:'',id:''}, 'Label')}
                ${bilingualInput('mc-explanation', data.explanation || {en:'',id:''}, 'Penjelasan (opsional)')}
                <div class="flex items-center justify-between">
                  <label class="flex items-center gap-1.5 text-xs font-semibold text-gray-600 cursor-pointer">
                    <input type="checkbox" class="mc-correct rounded text-primary focus:ring-primary border-gray-300 w-4 h-4" ${data.correct?'checked':''} onchange="markMissionDirty()">
                    <span>Pilihan Benar</span>
                  </label>
                  
                  <button type="button" onclick="this.closest('.mc-row').remove(); markMissionDirty()" class="p-1 text-gray-400 hover:text-red-500 rounded-lg hover:bg-red-50 transition-colors flex items-center justify-center" title="Hapus">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                  </button>
                </div>
              </div>`;
        } else {
            el.className = 'mc-row rounded-xl border border-gray-100 p-3 bg-gray-50/30 flex gap-4 items-start relative group';
            el.innerHTML = `
              <!-- Left side: Image Upload Container -->
              <div class="img-container relative w-16 h-16 shrink-0 rounded-xl border-2 border-dashed border-gray-200 bg-gray-50 hover:bg-gray-100/50 hover:border-primary/50 transition-all flex items-center justify-center overflow-hidden cursor-pointer" 
                   onclick="this.querySelector('input[type=file]').click()">
                <input type="hidden" class="mc-image" value="${escapeHtml(data.image)}">
                <input type="file" accept="image/*" class="hidden" onchange="uploadMissionAsset(this, '.mc-image')">
                <div class="img-preview-wrap w-full h-full flex items-center justify-center">
                  ${data.image ? `
                    <img src="${escapeHtml(data.image)}" alt="" class="mc-image-preview w-full h-full object-cover">
                    <div class="absolute inset-0 bg-black/40 opacity-0 hover:opacity-100 transition-opacity flex items-center justify-center text-[10px] text-white font-semibold">Ganti</div>
                  ` : `
                    <div class="text-gray-400 flex flex-col items-center gap-0.5 placeholder-wrap">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                      <span class="text-[8px] font-medium">Gambar</span>
                    </div>
                  `}
                </div>
              </div>
              
              <!-- Right side: Inputs -->
              <div class="flex-1 min-w-0 space-y-2">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                  ${bilingualInput('mc-left', data.left || {en:'',id:''}, 'Kiri')}
                  ${bilingualInput('mc-right', data.right || {en:'',id:''}, 'Kanan (jawaban)')}
                </div>
                <div class="flex items-center gap-2">
                  <span class="text-[10px] font-semibold text-gray-500">Audio (opsional):</span>
                  <input type="hidden" class="mc-audio" value="${escapeHtml(data.audio)}">
                  <input type="file" accept=".mp3,.ogg,.wav,.m4a" class="text-[10px]" onchange="uploadMissionAudio(this, '.mc-audio')">
                </div>
                <div class="mc-audio-preview">${data.audio ? `<audio src="/audio-stream/${escapeHtml(data.audio)}" controls class="mt-1 h-6 w-full"></audio>` : ''}</div>
                <div class="flex justify-end">
                  <button type="button" onclick="this.closest('.mc-row').remove(); markMissionDirty()" class="p-1 text-gray-400 hover:text-red-500 rounded-lg hover:bg-red-50 transition-colors flex items-center justify-center" title="Hapus">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                  </button>
                </div>
              </div>`;
        }
        rows.appendChild(el);
        window.Alpine?.initTree(el);
    };
    (mode === 'pick' ? (cfg.items || []) : (cfg.pairs || [])).forEach(addRow);
    c.querySelector('.mc-add').onclick = () => { addRow(); markMissionDirty(); };
};

window.MISSION_CONFIG_READERS['matching'] = function (c) {
    const mode = c.querySelector('.mc-mode')?.value || 'pick';
    const out = { mode };
    const prompt = readBilingual(c.querySelector('.mc-prompt'), 'mc-prompt'); if (prompt.id || prompt.en) out.prompt = prompt;
    const penalty = c.querySelector('.mc-penalty')?.value; if (penalty !== '' && penalty != null) out.penalty = Number(penalty);
    const rows = [...c.querySelectorAll('.mc-row')];
    if (mode === 'pick') {
        const pickCount = c.querySelector('.mc-pick-count')?.value; if (pickCount !== '' && pickCount != null) out.pick_count = Number(pickCount);
        out.items = rows.map(r => {
            const item = { label: readBilingual(r, 'mc-label'), correct: r.querySelector('.mc-correct').checked };
            const image = r.querySelector('.mc-image').value; if (image) item.image = image;
            const explanation = readBilingual(r, 'mc-explanation'); if (explanation.id || explanation.en) item.explanation = explanation;
            return item;
        });
    } else {
        out.pairs = rows.map(r => {
            const pair = { left: readBilingual(r, 'mc-left'), right: readBilingual(r, 'mc-right') };
            const image = r.querySelector('.mc-image').value; if (image) pair.image = image;
            const audio = r.querySelector('.mc-audio')?.value; if (audio) pair.audio = audio;
            return pair;
        });
    }
    return out;
};

// Audio-specific sibling to uploadMissionAsset: stores the returned relative storage
// `path` (not the public `url`) since audio playback goes through the ranged
// `audio.stream` route, mirroring the cultural-object/AR-model audio pattern.
function uploadMissionAudio(fileInput, hiddenSelector) {
    const file = fileInput.files[0];
    if (!file) return;
    const fd = new FormData();
    fd.append('file', file);
    fd.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value);

    fileInput.disabled = true;

    fetch('{{ route('admin.route-missions.upload-asset') }}', { method: 'POST', body: fd })
        .then(r => {
            if (!r.ok) throw new Error('Upload failed');
            return r.json();
        })
        .then(d => {
            if (d.path) {
                const scope = fileInput.closest('.mc-row');
                scope.querySelector(hiddenSelector).value = d.path;
                const preview = scope.querySelector('.mc-audio-preview');
                if (preview) preview.innerHTML = `<audio src="/audio-stream/${d.path}" controls class="mt-1 h-6 w-full"></audio>`;
                markMissionDirty();
            }
        })
        .catch(() => Swal.fire({ icon: 'error', title: 'Upload audio gagal', confirmButtonColor: '#1E5128' }))
        .finally(() => {
            fileInput.disabled = false;
        });
}

// Shared asset uploader: uploads the picked file, stores returned URL into the sibling hidden input.
// Scoped to the nearest `.mc-row` or `.ds-scenario` ancestor so both Task 5 (matching) and
// Task 8 (decision scenarios) can reuse this same helper without rewriting it.
function uploadMissionAsset(fileInput, hiddenSelector) {
    const file = fileInput.files[0];
    if (!file) return;
    const fd = new FormData();
    fd.append('file', file);
    fd.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value);
    
    fileInput.disabled = true;
    
    fetch('{{ route('admin.route-missions.upload-asset') }}', { method: 'POST', body: fd })
        .then(r => {
            if (!r.ok) throw new Error('Upload failed');
            return r.json();
        })
        .then(d => {
            if (d.url) {
                const scope = fileInput.closest('.mc-row, .ds-scenario');
                scope.querySelector(hiddenSelector).value = d.url;
                
                // Update preview container
                const container = fileInput.closest('.img-container');
                if (container) {
                    const previewWrap = container.querySelector('.img-preview-wrap');
                    if (previewWrap) {
                        previewWrap.innerHTML = `
                          <img src="${escapeHtml(d.url)}" alt="" class="mc-image-preview w-full h-full object-cover">
                          <div class="absolute inset-0 bg-black/40 opacity-0 hover:opacity-100 transition-opacity flex items-center justify-center text-[10px] text-white font-semibold">Ganti</div>
                        `;
                    }
                } else {
                    let preview = fileInput.parentNode.querySelector('.mc-image-preview');
                    if (!preview) {
                        preview = document.createElement('img');
                        preview.className = 'mc-image-preview h-8 w-8 rounded object-cover border border-gray-200';
                        fileInput.insertAdjacentElement('afterend', preview);
                    }
                    preview.src = d.url;
                }
                markMissionDirty();
            }
        })
        .catch(() => Swal.fire({ icon: 'error', title: 'Upload gagal', confirmButtonColor: '#1E5128' }))
        .finally(() => {
            fileInput.disabled = false;
        });
}

// --- Task 6: sequence config editor -------------------------------------------------
// Config shape: { prompt?:{en,id}, reveal_first?:bool, items:[{text:{en,id}}] }
// Field audit: `prompt` is OPTIONAL — guarded below exactly like matching's `prompt`,
// so opening/closing an existing sequence mission without edits doesn't inject an
// empty prompt object into its config. `reveal_first` is a checkbox-backed boolean:
// it always has a concrete value once rendered (true/false), so it's always emitted —
// there's no meaningful "absent" state to guard away. `items[].text` is REQUIRED for
// every item, so it's always emitted with no presence guard.

window.MISSION_CONFIG_BUILDERS['sequence'] = function (c, cfg) {
    c.innerHTML = `
      <div class="sq-prompt mb-2">${bilingualInput('sq-prompt', cfg.prompt || {en:'',id:''}, 'Instruksi (prompt)')}</div>
      <div class="sq-explanation mb-2">${bilingualInput('sq-explanation', cfg.explanation || {en:'',id:''}, 'Penjelasan setelah periksa (opsional)')}</div>
      <label class="flex items-center gap-2 text-xs mb-2"><input type="checkbox" class="sq-reveal" ${cfg.reveal_first?'checked':''} onchange="markMissionDirty()"> Sembunyikan dulu (reveal first)</label>
      <p class="text-[10px] text-gray-400 mb-1">Urutkan item dari atas ke bawah sesuai kronologi yang BENAR.</p>
      <div class="sq-rows space-y-2"></div>
      <button type="button" class="sq-add mt-2 text-xs text-primary font-semibold">+ Tambah Langkah</button>`;
    const rows = c.querySelector('.sq-rows');
    const addRow = (data = {}) => {
        const el = document.createElement('div');
        el.className = 'sq-row flex items-start gap-2';
        el.innerHTML = `<div class="flex-1">${bilingualInput('sq-text', data.text || {en:'',id:''}, 'Teks')}</div>
          <button type="button" class="text-red-400 text-xs mt-2" onclick="this.closest('.sq-row').remove(); markMissionDirty()">hapus</button>`;
        rows.appendChild(el); window.Alpine?.initTree(el);
    };
    (cfg.items || []).forEach(addRow);
    c.querySelector('.sq-add').onclick = () => { addRow(); markMissionDirty(); };
};

window.MISSION_CONFIG_READERS['sequence'] = function (c) {
    const out = {};
    // Optional: only include `prompt` when it actually has content, mirroring
    // matching's guard — otherwise a no-op open/close/save would add an empty
    // prompt object to missions that never had one.
    const prompt = readBilingual(c.querySelector('.sq-prompt'), 'sq-prompt'); if (prompt.id || prompt.en) out.prompt = prompt;
    const explanation = readBilingual(c.querySelector('.sq-explanation'), 'sq-explanation'); if (explanation.id || explanation.en) out.explanation = explanation;
    // Always present: a checkbox always has a concrete boolean value.
    out.reveal_first = c.querySelector('.sq-reveal').checked;
    // Always present: every item requires text.
    out.items = [...c.querySelectorAll('.sq-row')].map(r => ({ text: readBilingual(r, 'sq-text') }));
    return out;
};

// --- Task 7: word_search config editor ----------------------------------------------
// Config shape: { prompt?:{en,id}, words:["BAMBU", ...], grid_size?:int }
// Field audit: `prompt` and `grid_size` are OPTIONAL — both guarded below (prompt via the
// same id/en presence check as matching/sequence; grid_size via non-empty input check) so a
// no-op open/close/save doesn't inject empty values into missions that never had them.
// `words` is REQUIRED (flat string array, not translatable) and always emitted.

window.MISSION_CONFIG_BUILDERS['word_search'] = function (c, cfg) {
    c.innerHTML = `
      <div class="ws-prompt mb-2">${bilingualInput('ws-prompt', cfg.prompt || {en:'',id:''}, 'Instruksi (prompt)')}</div>
      <label class="text-xs font-semibold text-gray-600">Ukuran grid (opsional)</label>
      <input type="number" class="ws-grid w-24 rounded border border-gray-200 px-2 py-1 text-sm mb-2 block" value="${cfg.grid_size || ''}" oninput="markMissionDirty()">
      <label class="text-xs font-semibold text-gray-600">Kata (satu per baris, huruf saja)</label>
      <textarea class="ws-words w-full rounded border border-gray-200 px-2 py-1 text-sm" rows="4" oninput="markMissionDirty()">${(cfg.words || []).join('\n')}</textarea>`;
};
window.MISSION_CONFIG_READERS['word_search'] = function (c) {
    const out = {};
    const prompt = readBilingual(c.querySelector('.ws-prompt'), 'ws-prompt'); if (prompt.id || prompt.en) out.prompt = prompt;
    out.words = c.querySelector('.ws-words').value.split('\n').map(w => w.trim()).filter(Boolean);
    const g = c.querySelector('.ws-grid').value; if (g) out.grid_size = Number(g);
    return out;
};

// --- Task 8: decision config editor -------------------------------------------------
// Config shape: { scenarios:[{ text:{en,id}, image?, image_after?, options:[{text:{en,id}, correct:bool, explanation?:{en,id}}] }] }
// Field audit: scenario `image`, `image_after`, and option `explanation` are OPTIONAL and must
// be guarded so a no-op open/close/save preserves them precisely if they were originally absent.
// Scenario `text` and option `text`/`correct` are REQUIRED and always emitted.

window.MISSION_CONFIG_BUILDERS['decision'] = function (c, cfg) {
    c.innerHTML = `
      <div class="ds-scenarios space-y-4"></div>
      <button type="button" class="ds-add mt-2 text-xs text-primary font-semibold">+ Tambah Skenario</button>`;
    const wrap = c.querySelector('.ds-scenarios');
    const addScenario = (s = {}) => {
        const el = document.createElement('div');
        el.className = 'ds-scenario rounded-lg border border-gray-100 p-3 bg-gray-50/50';
        el.innerHTML = `
          ${bilingualInput('ds-text', s.text || {en:'',id:''}, 'Pertanyaan skenario')}
          <div class="flex items-center gap-4 mt-2 text-xs">
            <div class="flex items-center gap-1">
              <span class="font-semibold text-gray-500">Gambar:</span>
              <input type="hidden" class="ds-image" value="${escapeHtml(s.image)}">
              <input type="file" accept="image/*" class="text-xs" onchange="uploadMissionAsset(this, '.ds-image')">
              ${s.image ? `<img class="mc-image-preview h-8 w-8 rounded object-cover border border-gray-200" src="${escapeHtml(s.image)}">` : ''}
            </div>
            <div class="flex items-center gap-1">
              <span class="font-semibold text-gray-500">Setelah benar:</span>
              <input type="hidden" class="ds-image-after" value="${escapeHtml(s.image_after)}">
              <input type="file" accept="image/*" class="text-xs" onchange="uploadMissionAsset(this, '.ds-image-after')">
              ${s.image_after ? `<img class="mc-image-preview h-8 w-8 rounded object-cover border border-gray-200" src="${escapeHtml(s.image_after)}">` : ''}
            </div>
          </div>
          <div class="ds-options space-y-2 mt-3 pl-4 border-l-2 border-gray-200"></div>
          <div class="mt-2 pl-4 flex items-center justify-between">
            <button type="button" class="ds-add-opt text-xs text-primary font-semibold">+ Opsi</button>
            <button type="button" onclick="this.closest('.ds-scenario').remove(); markMissionDirty()" class="inline-flex items-center gap-1 text-red-400 hover:text-red-600 text-xs font-semibold transition-colors">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
              <span>Hapus Skenario</span>
            </button>
          </div>`;
        const opts = el.querySelector('.ds-options');
        const addOpt = (o = {}) => {
            const oe = document.createElement('div');
            oe.className = 'ds-option rounded border border-gray-100 p-2 bg-white';
            oe.innerHTML = `
              <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                  ${bilingualInput('ds-opt', o.text || {en:'',id:''}, 'Opsi')}
                  <div class="flex items-center justify-between mt-1.5">
                    <label class="flex items-center gap-1.5 text-xs font-semibold text-gray-600 cursor-pointer">
                      <input type="checkbox" class="ds-correct rounded text-primary focus:ring-primary border-gray-300 w-4 h-4" ${o.correct?'checked':''} onchange="markMissionDirty()">
                      <span>Benar (correct)</span>
                    </label>
                  </div>
                </div>
                <div>
                  ${bilingualInput('ds-exp', o.explanation || {en:'',id:''}, 'Penjelasan (opsional)')}
                </div>
              </div>
              <div class="flex justify-end mt-1">
                <button type="button" onclick="this.closest('.ds-option').remove(); markMissionDirty()" class="p-1 text-gray-400 hover:text-red-500 rounded-lg hover:bg-red-50 transition-colors flex items-center justify-center" title="Hapus Opsi">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
              </div>`;
            opts.appendChild(oe); window.Alpine?.initTree(oe);
        };
        (s.options || []).forEach(addOpt);
        el.querySelector('.ds-add-opt').onclick = () => { addOpt(); markMissionDirty(); };
        wrap.appendChild(el); window.Alpine?.initTree(el);
    };
    (cfg.scenarios || []).forEach(addScenario);
    c.querySelector('.ds-add').onclick = () => { addScenario(); markMissionDirty(); };
};

window.MISSION_CONFIG_READERS['decision'] = function (c) {
    return {
        scenarios: [...c.querySelectorAll('.ds-scenario')].map(s => {
            const out = {
                text: readBilingual(s, 'ds-text'),
                options: [...s.querySelectorAll('.ds-option')].map(o => {
                    const opt = {
                        text: readBilingual(o, 'ds-opt'),
                        correct: o.querySelector('.ds-correct').checked
                    };
                    const exp = readBilingual(o, 'ds-exp');
                    if (exp.id || exp.en) opt.explanation = exp;
                    return opt;
                })
            };
            const img = s.querySelector('.ds-image').value; if (img) out.image = img;
            const imgA = s.querySelector('.ds-image-after').value; if (imgA) out.image_after = imgA;
            return out;
        }),
    };
};

// --- Task 9: riddle config editor ----------------------------------------------------
// Config shape: { riddle:{en,id}, hint?:{en,id}, success_text?:{en,id}, answers:["merajan", ...] }
// Field audit: `hint` and `success_text` are OPTIONAL and must be guarded in the reader.
// `riddle` and `answers` (flat string array) are REQUIRED.

window.MISSION_CONFIG_BUILDERS['riddle'] = function (c, cfg) {
    c.innerHTML = `
      <div class="rd-riddle mb-2">${bilingualInput('rd-riddle', cfg.riddle || {en:'',id:''}, 'Teka-teki')}</div>
      <div class="rd-hint mb-2">${bilingualInput('rd-hint', cfg.hint || {en:'',id:''}, 'Petunjuk (opsional)')}</div>
      <div class="rd-success mb-2">${bilingualInput('rd-success', cfg.success_text || {en:'',id:''}, 'Teks sukses (opsional)')}</div>
      <div class="rd-explanation mb-2">${bilingualInput('rd-explanation', cfg.explanation || {en:'',id:''}, 'Penjelasan setelah jawab (opsional)')}</div>
      <label class="text-xs font-semibold text-gray-600 block mb-1">Jawaban diterima (satu per baris, tidak sensitif huruf besar/kecil)</label>
      <textarea class="rd-answers w-full rounded border border-gray-200 px-2 py-1 text-sm mb-1" rows="3" oninput="markMissionDirty()">${(cfg.answers || []).join('\n')}</textarea>`;
};
window.MISSION_CONFIG_READERS['riddle'] = function (c) {
    const out = {
        riddle: readBilingual(c.querySelector('.rd-riddle'), 'rd-riddle'),
        answers: c.querySelector('.rd-answers').value.split('\n').map(a => a.trim()).filter(Boolean),
    };
    const hint = readBilingual(c.querySelector('.rd-hint'), 'rd-hint'); if (hint.id || hint.en) out.hint = hint;
    const st = readBilingual(c.querySelector('.rd-success'), 'rd-success'); if (st.id || st.en) out.success_text = st;
    const exp = readBilingual(c.querySelector('.rd-explanation'), 'rd-explanation'); if (exp.id || exp.en) out.explanation = exp;
    return out;
};

// --- Task 10: quiz config editor -----------------------------------------------------
// Config shape: { questions:[{ prompt:{en,id}, option_a:{en,id}, option_b, option_c, option_d,
//   correct_option:'A'|'B'|'C'|'D', explanation?:{en,id} }] }
// Field audit: `explanation` is OPTIONAL — guarded in the reader like decision's option
// explanation. `prompt`/`option_*`/`correct_option` are REQUIRED and always emitted.

window.MISSION_CONFIG_BUILDERS['quiz'] = function (c, cfg) {
    c.innerHTML = `
      <div class="qz-questions space-y-4"></div>
      <button type="button" class="qz-add mt-2 text-xs text-primary font-semibold">+ Tambah Soal</button>`;
    const wrap = c.querySelector('.qz-questions');
    const addQuestion = (q = {}) => {
        const el = document.createElement('div');
        el.className = 'qz-question rounded-lg border border-gray-100 p-3 bg-gray-50/50';
        el.innerHTML = `
          ${bilingualInput('qz-prompt', q.prompt || {en:'',id:''}, 'Pertanyaan')}
          <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mt-2">
            ${bilingualInput('qz-option-a', q.option_a || {en:'',id:''}, 'Opsi A')}
            ${bilingualInput('qz-option-b', q.option_b || {en:'',id:''}, 'Opsi B')}
            ${bilingualInput('qz-option-c', q.option_c || {en:'',id:''}, 'Opsi C')}
            ${bilingualInput('qz-option-d', q.option_d || {en:'',id:''}, 'Opsi D')}
          </div>
          <div class="mt-2">
            <label class="text-xs font-semibold text-gray-600">Jawaban benar</label>
            <select class="qz-correct w-24 rounded-lg border border-gray-200 px-2 py-1 text-sm block" onchange="markMissionDirty()">
              ${['A','B','C','D'].map(o => `<option value="${o}" ${(q.correct_option || 'A').toUpperCase() === o ? 'selected' : ''}>${o}</option>`).join('')}
            </select>
          </div>
          <div class="qz-explanation mt-2">${bilingualInput('qz-explanation', q.explanation || {en:'',id:''}, 'Penjelasan (opsional)')}</div>
          <div class="mt-2 flex justify-end">
            <button type="button" onclick="this.closest('.qz-question').remove(); markMissionDirty()" class="inline-flex items-center gap-1 text-red-400 hover:text-red-600 text-xs font-semibold transition-colors">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
              <span>Hapus Soal</span>
            </button>
          </div>`;
        wrap.appendChild(el); window.Alpine?.initTree(el);
    };
    (cfg.questions || []).forEach(addQuestion);
    c.querySelector('.qz-add').onclick = () => { addQuestion(); markMissionDirty(); };
};

window.MISSION_CONFIG_READERS['quiz'] = function (c) {
    return {
        questions: [...c.querySelectorAll('.qz-question')].map(q => {
            const out = {
                prompt: readBilingual(q, 'qz-prompt'),
                option_a: readBilingual(q, 'qz-option-a'),
                option_b: readBilingual(q, 'qz-option-b'),
                option_c: readBilingual(q, 'qz-option-c'),
                option_d: readBilingual(q, 'qz-option-d'),
                correct_option: q.querySelector('.qz-correct').value,
            };
            const exp = readBilingual(q, 'qz-explanation'); if (exp.id || exp.en) out.explanation = exp;
            return out;
        }),
    };
};
</script>
