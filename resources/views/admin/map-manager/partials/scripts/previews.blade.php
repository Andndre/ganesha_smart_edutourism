<script>
    document.addEventListener('DOMContentLoaded', function () {
        initCounts();
        initMap();

        // Audio preview on file selection
        const audioInput = document.querySelector('input[name="audio_narration_file"]');
        const audioPreview = document.getElementById('audio-preview');
        const audioPreviewContainer = document.getElementById('audio-preview-container');
        if (audioInput && audioPreview && audioPreviewContainer) {
            audioInput.addEventListener('change', function () {
                const file = this.files[0];
                if (file) {
                    audioPreview.src = URL.createObjectURL(file);
                    audioPreviewContainer.style.display = 'flex';
                } else {
                    audioPreview.src = '';
                    audioPreviewContainer.style.display = 'none';
                }
            });
        }

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
        const culturalNameInput = document.querySelector('#form-cultural input[name="name"]');
        if (culturalNameInput) {
            culturalNameInput.addEventListener('input', function() {
                if (typeof window.generateARMarker === 'function') {
                    window.generateARMarker();
                }
            });
        }
    });
</script>
