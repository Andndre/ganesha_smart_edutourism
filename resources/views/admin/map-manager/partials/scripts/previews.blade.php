<script>
    document.addEventListener('DOMContentLoaded', function () {
        initCounts();
        initMap();

        // 3D Model preview on file selection
        const modelInput = document.querySelector('input[name="model_3d_file"]');
        const modelPreview = document.getElementById('model-3d-preview');
        const modelPreviewContainer = document.getElementById('model-3d-preview-container');
        if (modelInput && modelPreview && modelPreviewContainer) {
            modelInput.addEventListener('change', function () {
                const file = this.files[0];
                if (file) {
                    modelPreview.src = URL.createObjectURL(file);
                    modelPreviewContainer.style.display = 'flex';
                } else {
                    modelPreview.src = '';
                    modelPreviewContainer.style.display = 'none';
                }
            });
        }
        
        // Auto-generate AR Marker when name input changes
        const culturalNameInput = document.querySelector('#form-cultural input[name="name[en]"]');
        if (culturalNameInput) {
            culturalNameInput.addEventListener('input', function() {
                if (typeof window.generateARMarker === 'function') {
                    window.generateARMarker();
                }
            });
        }
    });
</script>
