<script>
    // ==========================================
    // CONFIG & INITS
    // ==========================================
    const PENGLIPURAN_LAT = {{ config('services.penglipuran.latitude') }};
    const PENGLIPURAN_LNG = {{ config('services.penglipuran.longitude') }};
    const PENGLIPURAN_ZOOM = {{ config('services.penglipuran.zoom') }};

    // Loaded locations from Controller
    const locations = @json($locations);
    const storageUrl = "{{ asset('storage') }}";

    let map = null;
    let markers = []; // List of L.marker instances
    let activeMarker = null; // Currently selected/edited marker
    let tempMarker = null; // Temp marker when creating new location
    let currentMode = 'idle'; // 'idle', 'create', 'edit', 'add-point'
    let hasUnsavedChanges = false;
    let siblingMarkers = []; // Other points belonging to the currently highlighted owner
    let addPointOwner = null; // { type: 'cultural_object'|'facility', id } while armed in 'add-point' mode

    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    }

    function markUnsaved() {
        hasUnsavedChanges = true;
    }

    function resetUnsaved() {
        hasUnsavedChanges = false;
    }

    function checkUnsavedChanges(callback) {
        if (hasUnsavedChanges) {
            Swal.fire({
                title: 'Perubahan Belum Disimpan',
                text: 'Apakah Anda yakin ingin membuang perubahan pada form ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Buang',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    resetUnsaved();
                    callback();
                }
            });
        } else {
            callback();
        }
    }

    function attachChangeListeners() {
        const editorPanel = document.getElementById('panel-editor');
        if (!editorPanel) return;
        const inputs = editorPanel.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.removeEventListener('input', markUnsaved);
            input.removeEventListener('change', markUnsaved);
            input.addEventListener('input', markUnsaved);
            input.addEventListener('change', markUnsaved);
        });
    }

    // Set up category colors
    const categoryColors = {
        umkm: '#8B5CF6',         // Violet
        facility: '#3B82F6',     // Blue
        toilet: '#06B6D4',       // Cyan
        cultural: '#1E5128'      // Green
    };
</script>
