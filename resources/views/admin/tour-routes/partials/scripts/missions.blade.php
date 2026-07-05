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
        missions.push({
            id: row.dataset.missionId ? Number(row.dataset.missionId) : undefined,
            type,
            title: { id: row.querySelector('.m-title-id').value, en: row.querySelector('.m-title-en').value },
            points: Number(row.querySelector('.m-points').value) || 0,
            time_limit_seconds: row.querySelector('.m-timelimit').value ? Number(row.querySelector('.m-timelimit').value) : null,
            config: reader ? reader(row.querySelector('.m-config')) : {},
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
</script>
