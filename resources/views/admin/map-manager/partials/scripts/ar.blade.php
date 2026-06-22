<script>
// ==========================================
// AUTOMATED AR PATTERN & QR MARKER GENERATOR
// ==========================================
window.generateARMarker = function() {
    const markerInput = document.getElementById('ar_marker_id');
    const downloadContainer = document.getElementById('ar-download-container');
    const pattInput = document.getElementById('ar_marker_patt_content');
    
    if (!markerInput) return;
    
    const markerId = markerInput.value.trim();
    if (!markerId) {
        if (downloadContainer) downloadContainer.style.display = 'none';
        if (pattInput) pattInput.value = '';
        return;
    }
    
    if (downloadContainer) downloadContainer.style.display = 'block';
    
    try {
        // Retrieve dynamic slug from activeMarker details
        let slug = '';
        if (activeMarker && activeMarker.locationData && activeMarker.locationData.locationable) {
            slug = activeMarker.locationData.locationable.slug || '';
        }
        
        // If slug is still empty (creating new), slugify the name input dynamically
        if (!slug) {
            const nameInput = document.querySelector('#form-cultural input[name="name[en]"]');
            if (nameInput && nameInput.value) {
                slug = nameInput.value.toLowerCase()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/(^-|-$)+/g, '');
            }
        }
        
        // Build dynamic tourist destination URL or fallback to scan query
        const qrValue = slug 
            ? `${window.location.origin}/cultural/${slug}` 
            : `${window.location.origin}/explore?marker=${encodeURIComponent(markerId)}`;
        
        // Render QR using QRious
        const qr = new QRious({
            value: qrValue,
            size: 300,
            level: 'H'
        });
        
        // Create high-resolution 500x500 AR.js canvas
        const markerCanvas = document.createElement('canvas');
        markerCanvas.width = 500;
        markerCanvas.height = 500;
        const ctx = markerCanvas.getContext('2d');
        
        // Solid Black border (essential for AR.js tracking)
        ctx.fillStyle = '#000000';
        ctx.fillRect(0, 0, 500, 500);
        
        // White background inside the border
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(100, 100, 300, 300);
        
        // Centered QR Code
        ctx.drawImage(qr.canvas, 100, 100, 300, 300);
        
        window.arMarkerCanvas = markerCanvas;
        
        // Set initial fallback pattern (plain QR)
        const fallbackPattText = generatePattText(markerCanvas, 100, 300);
        if (pattInput) {
            pattInput.value = fallbackPattText;
        }
        
        // Load and render brand logo in center
        const logo = new Image();
        logo.onload = function() {
            // White background overlay for logo to prevent bleeding with QR modules
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(212, 212, 76, 76);
            
            // Render logo
            ctx.drawImage(logo, 217, 217, 66, 66);
            
            window.arMarkerCanvas = markerCanvas;
            
            // Regenerate pattern including the logo
            const pattText = generatePattText(markerCanvas, 100, 300);
            if (pattInput) {
                pattInput.value = pattText;
            }
        };
        logo.src = '/icons/logo-color-notext.png';
        
    } catch (e) {
        console.error('AR Marker generation failed:', e);
    }
};

window.downloadARMarker = function() {
    const markerInput = document.getElementById('ar_marker_id');
    if (!markerInput || !window.arMarkerCanvas) return;
    
    const markerId = markerInput.value.trim();
    
    // Download PNG marker
    const pngUrl = window.arMarkerCanvas.toDataURL('image/png');
    const pngLink = document.createElement('a');
    pngLink.href = pngUrl;
    pngLink.download = `${markerId}.png`;
    document.body.appendChild(pngLink);
    pngLink.click();
    document.body.removeChild(pngLink);
};

function generatePattText(canvas, borderWidth, patternSize) {
    const ctx = canvas.getContext('2d');
    const gridSize = 16;
    const cellW = patternSize / gridSize;
    const cellH = patternSize / gridSize;
    
    const grid = [];
    for (let r = 0; r < gridSize; r++) {
        grid[r] = [];
        for (let c = 0; c < gridSize; c++) {
            const startX = borderWidth + c * cellW;
            const startY = borderWidth + r * cellH;
            
            const imgData = ctx.getImageData(startX, startY, cellW, cellH);
            const data = imgData.data;
            let sumR = 0, sumG = 0, sumB = 0;
            const count = data.length / 4;
            
            for (let i = 0; i < data.length; i += 4) {
                sumR += data[i];
                sumG += data[i + 1];
                sumB += data[i + 2];
            }
            
            const normR = (sumR / count / 255).toFixed(3);
            const normG = (sumG / count / 255).toFixed(3);
            const normB = (sumB / count / 255).toFixed(3);
            
            grid[r][c] = `${normR} ${normG} ${normB}`;
        }
    }
    
    const rotations = [];
    
    function rotate90(arr) {
        const n = arr.length;
        const rotated = Array.from({ length: n }, () => []);
        for (let r = 0; r < n; r++) {
            for (let c = 0; c < n; c++) {
                rotated[c][n - 1 - r] = arr[r][c];
            }
        }
        return rotated;
    }
    
    let currentGrid = grid;
    for (let i = 0; i < 4; i++) {
        const blockLines = [];
        for (let r = 0; r < gridSize; r++) {
            blockLines.push(currentGrid[r].join(' '));
        }
        rotations.push(blockLines.join('\n'));
        currentGrid = rotate90(currentGrid);
    }
    
    return rotations.join('\n\n') + '\n';
}
</script>
