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
];

// Per-type config editors are registered by Tasks 5-8 into MISSION_CONFIG_BUILDERS[type].
// Each builder: (container: HTMLElement, config: object) => void  (renders inputs, wires oninput -> markMissionDirty).
window.MISSION_CONFIG_BUILDERS = window.MISSION_CONFIG_BUILDERS || {};
// Each reader: (container: HTMLElement) => object (serializes the type's inputs back to a config object).
window.MISSION_CONFIG_READERS = window.MISSION_CONFIG_READERS || {};

function markMissionDirty() { missionDirty = true; }

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
        icon: 'warning', title: 'Buang perubahan?', text: 'Perubahan misi belum disimpan ke daftar.',
        showCancelButton: true, confirmButtonColor: '#dc2626', cancelButtonColor: '#6b7280',
        confirmButtonText: 'Buang', cancelButtonText: 'Batal',
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
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-bold text-gray-700">Misi ${idx + 1}</span>
            <div class="flex items-center gap-1">
                <button @click="locale='id'" :class="locale==='id'?'bg-primary text-white':'bg-gray-100 text-gray-500'" class="px-2 py-0.5 rounded text-[10px] font-semibold" type="button">ID</button>
                <button @click="locale='en'" :class="locale==='en'?'bg-primary text-white':'bg-gray-100 text-gray-500'" class="px-2 py-0.5 rounded text-[10px] font-semibold" type="button">EN</button>
                <button type="button" onclick="this.closest('.mission-item').remove(); markMissionDirty();" class="p-1 text-gray-400 hover:text-red-500">✕</button>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3 mb-3">
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
        </div>
        <div class="grid grid-cols-2 gap-3 mb-3">
            <div x-show="locale==='id'">
                <label class="mb-1 block text-xs font-semibold text-gray-600">Judul (ID)</label>
                <input type="text" class="m-title-id w-full rounded-lg border border-gray-200 px-3 py-1.5 text-sm" value="${(m.title?.id) || ''}" oninput="markMissionDirty()">
            </div>
            <div x-show="locale==='en'">
                <label class="mb-1 block text-xs font-semibold text-gray-600">Judul (EN)</label>
                <input type="text" class="m-title-en w-full rounded-lg border border-gray-200 px-3 py-1.5 text-sm" value="${(m.title?.en) || ''}" oninput="markMissionDirty()">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold text-gray-600">Batas Waktu (detik, opsional)</label>
                <input type="number" class="m-timelimit w-full rounded-lg border border-gray-200 px-3 py-1.5 text-sm" value="${m.time_limit_seconds ?? ''}" oninput="markMissionDirty()">
            </div>
        </div>
        <div class="m-config border-t border-gray-100 pt-3"></div>
    `;
    list.appendChild(row);
    window.Alpine?.initTree(row);
    renderMissionConfig(row, m.type, m.config || {});
    markMissionDirty();
}

function onMissionTypeChange(select) {
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
          <span class="flex gap-1"><button type="button" @click="l='id'" :class="l==='id'?'bg-primary text-white':'bg-gray-100'" class="px-1.5 rounded text-[10px]">ID</button>
          <button type="button" @click="l='en'" :class="l==='en'?'bg-primary text-white':'bg-gray-100'" class="px-1.5 rounded text-[10px]">EN</button></span></div>` : ''}
        <input type="text" x-show="l==='id'" class="${cls}-id w-full rounded border border-gray-200 px-2 py-1 text-sm mt-1" value="${(value?.id || '').replace(/"/g, '&quot;')}" oninput="markMissionDirty()">
        <input type="text" x-show="l==='en'" class="${cls}-en w-full rounded border border-gray-200 px-2 py-1 text-sm mt-1" value="${(value?.en || '').replace(/"/g, '&quot;')}" oninput="markMissionDirty()">
      </div>`;
}
function readBilingual(scope, cls) {
    return { id: scope.querySelector(`.${cls}-id`)?.value || '', en: scope.querySelector(`.${cls}-en`)?.value || '' };
}

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
      <div class="mc-rows space-y-2"></div>
      <button type="button" class="mc-add mt-2 text-xs text-primary font-semibold">+ Tambah ${mode==='pick'?'Kartu':'Pasangan'}</button>`;
    const rows = c.querySelector('.mc-rows');
    const addRow = (data = {}) => {
        const el = document.createElement('div');
        el.className = 'mc-row rounded-lg border border-gray-100 p-2';
        if (mode === 'pick') {
            el.innerHTML = `
              ${bilingualInput('mc-label', data.label || {en:'',id:''}, 'Label')}
              <div class="flex items-center gap-2 mt-1">
                <input type="text" class="mc-icon w-16 rounded border border-gray-200 px-2 py-1 text-sm" placeholder="🌿" value="${data.icon || ''}" oninput="markMissionDirty()">
                <input type="hidden" class="mc-image" value="${data.image || ''}">
                ${data.image ? `<img src="${data.image}" alt="" class="mc-image-preview h-8 w-8 rounded object-cover border border-gray-200">` : ''}
                <input type="file" accept="image/*" class="text-xs" onchange="uploadMissionAsset(this, '.mc-image')">
                <label class="flex items-center gap-1 text-xs"><input type="checkbox" class="mc-correct" ${data.correct?'checked':''} onchange="markMissionDirty()"> benar</label>
                <button type="button" class="text-red-400 text-xs" onclick="this.closest('.mc-row').remove(); markMissionDirty()">hapus</button>
              </div>`;
        } else {
            el.innerHTML = `
              ${bilingualInput('mc-left', data.left || {en:'',id:''}, 'Kiri')}
              ${bilingualInput('mc-right', data.right || {en:'',id:''}, 'Kanan (jawaban)')}
              <div class="flex items-center gap-2 mt-1">
                <input type="text" class="mc-icon w-16 rounded border border-gray-200 px-2 py-1 text-sm" placeholder="🚪" value="${data.icon || ''}" oninput="markMissionDirty()">
                <button type="button" class="text-red-400 text-xs" onclick="this.closest('.mc-row').remove(); markMissionDirty()">hapus</button>
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
    const out = { mode, prompt: readBilingual(c.querySelector('.mc-prompt'), 'mc-prompt') };
    const penalty = c.querySelector('.mc-penalty')?.value; if (penalty !== '' && penalty != null) out.penalty = Number(penalty);
    const rows = [...c.querySelectorAll('.mc-row')];
    if (mode === 'pick') {
        const pickCount = c.querySelector('.mc-pick-count')?.value; if (pickCount !== '' && pickCount != null) out.pick_count = Number(pickCount);
        out.items = rows.map(r => {
            const item = { label: readBilingual(r, 'mc-label'), correct: r.querySelector('.mc-correct').checked };
            const icon = r.querySelector('.mc-icon').value; if (icon) item.icon = icon;
            const image = r.querySelector('.mc-image').value; if (image) item.image = image;
            return item;
        });
    } else {
        out.pairs = rows.map(r => {
            const pair = { left: readBilingual(r, 'mc-left'), right: readBilingual(r, 'mc-right') };
            const icon = r.querySelector('.mc-icon').value; if (icon) pair.icon = icon;
            return pair;
        });
    }
    return out;
};

// Shared asset uploader: uploads the picked file, stores returned URL into the sibling hidden input.
// Scoped to the nearest `.mc-row` or `.ds-scenario` ancestor so both Task 5 (matching) and
// Task 8 (decision scenarios) can reuse this same helper without rewriting it.
function uploadMissionAsset(fileInput, hiddenSelector) {
    const file = fileInput.files[0];
    if (!file) return;
    const fd = new FormData();
    fd.append('file', file);
    fd.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value);
    fetch('{{ route('admin.route-missions.upload-asset') }}', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            if (d.url) {
                const scope = fileInput.closest('.mc-row, .ds-scenario');
                scope.querySelector(hiddenSelector).value = d.url;
                let preview = scope.querySelector('.mc-image-preview');
                if (!preview) {
                    preview = document.createElement('img');
                    preview.className = 'mc-image-preview h-8 w-8 rounded object-cover border border-gray-200';
                    fileInput.insertAdjacentElement('afterend', preview);
                }
                preview.src = d.url;
                markMissionDirty();
            }
        })
        .catch(() => Swal.fire({ icon: 'error', title: 'Upload gagal', confirmButtonColor: '#1E5128' }));
}
</script>
