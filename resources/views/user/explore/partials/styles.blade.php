<style>
    /* Sembunyikan atribusi leaflet yang terlalu besar di HP */
    .leaflet-control-attribution {
        display: none !important;
    }

    /* Hilangkan efek outline saat klik marker */
    .leaflet-container:focus {
        outline: none;
    }

    /* Animasi Bottom Sheet */
    .bottom-sheet-enter {
        transform: translateY(100%);
    }

    .bottom-sheet-active {
        transform: translateY(0);
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Heatmap Gradient Overlay */
    .heatmap-overlay {
        position: absolute;
        inset: 0;
        pointer-events: none;
        z-index: 1;
        opacity: 0.4;
        mix-blend-mode: multiply;
    }

    .heatmap-cell {
        position: absolute;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(239, 68, 68, 0.8) 0%, rgba(249, 115, 22, 0.6) 30%, rgba(234, 179, 8, 0.4) 60%, rgba(34, 197, 94, 0.2) 80%, transparent 100%);
    }

    /* My Location Arrow */
    .location-arrow {
        position: absolute;
        width: 24px;
        height: 24px;
        pointer-events: none;
        z-index: 500;
        filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
    }

    .location-pulse {
        position: absolute;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(30, 81, 40, 0.3);
        animation: pulse 2s infinite;
        pointer-events: none;
        z-index: 499;
    }

    @keyframes pulse {
        0% {
            transform: scale(0.5);
            opacity: 1;
        }

        100% {
            transform: scale(2);
            opacity: 0;
        }
    }

    /* FAB Active State */
    .fab-btn-active {
        background: #1E5128 !important;
        color: white !important;
    }
</style>
