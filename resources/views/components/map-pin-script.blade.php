<style>
    .gse-map-pin {
        background: none;
        border: none;
    }

    .gse-pin-halo {
        position: absolute;
        left: 50%;
        top: 33%;
        width: 46px;
        height: 46px;
        margin: -23px 0 0 -23px;
        border-radius: 50%;
        background: rgba(212, 175, 55, 0.45);
        animation: gse-pin-pulse 1.6s ease-out infinite;
        pointer-events: none;
    }

    @keyframes gse-pin-pulse {
        0% {
            transform: scale(0.6);
            opacity: 0.9;
        }

        100% {
            transform: scale(1.3);
            opacity: 0;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .gse-pin-halo {
            animation: none;
            opacity: 0.5;
        }
    }
</style>

<script>
    (function() {
        // Category colours shared by the map pins, the explore filter cards and the
        // category badges in the location sheet.
        const CATEGORY_COLORS = {
            umkm: '#8B5CF6', // Violet
            facilities: '#3B82F6', // Blue
            toilets: '#06B6D4', // Cyan
            accessibility: '#F59E0B', // Amber
            cultural: '#1E5128' // Penglipuran Green (default)
        };

        // Glyphs reused verbatim from the explore filter panel (map-search.blade.php)
        // so the legend and the markers show the same iconography.
        const CATEGORY_GLYPHS = {
            cultural: '<path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>',
            umkm: '<path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>',
            facilities: '<path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>',
            toilets: '<path d="M12 4a1 1 0 100 2 1 1 0 000-2zm-2 8h4v8h-4v-8zm8-2h-3v8h2v-8h1zM5 10h3v8H6v-8H5z"/>',
            accessibility: '<path d="M19 10.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM7.5 13.5h7.5m-7.5-3.5h5a2 2 0 012 2v6m-7-6.5V6a2 2 0 012-2h1.5"/>',
            check: '<path d="M5 13l4 4L19 7"/>'
        };

        const PIN_PATH = 'M16 1C8.8 1 3 6.8 3 14c0 9.2 13 27 13 27s13-17.8 13-27C29 6.8 23.2 1 16 1z';

        /**
         * Google-Maps-style teardrop pin, anchored at its tip.
         *
         * @param {string} category  key of CATEGORY_COLORS / CATEGORY_GLYPHS
         * @param {object} [options]
         *   highlight {boolean} the selected pin: enlarged, Bali Gold outline, pulsing halo
         *   number    {number}  render this digit instead of the category glyph (route stops)
         *   color     {string}  override the category colour
         *   dimmed    {boolean} grey out (a completed route stop)
         */
        window.gseMapPin = function(category, options) {
            const opts = options || {};
            const color = opts.dimmed ? '#9CA3AF' : (opts.color || CATEGORY_COLORS[category] || CATEGORY_COLORS.cultural);
            const scale = opts.highlight ? 1.35 : 1;
            const w = Math.round(32 * scale);
            const h = Math.round(42 * scale);

            let inner;
            if (opts.number != null) {
                inner = `<text x="16" y="14" text-anchor="middle" dominant-baseline="central" fill="#FFFFFF" font-size="15" font-weight="800" font-family="'Plus Jakarta Sans', Inter, system-ui, sans-serif">${opts.number}</text>`;
            } else {
                const glyph = CATEGORY_GLYPHS[category] || CATEGORY_GLYPHS.cultural;
                inner = `<g transform="translate(8 6) scale(0.6667)" fill="none" stroke="#FFFFFF" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">${glyph}</g>`;
            }

            const svg = `<svg width="${w}" height="${h}" viewBox="0 0 32 42" xmlns="http://www.w3.org/2000/svg" style="filter:drop-shadow(0 2px 3px rgba(0,0,0,.45))">
                    <path d="${PIN_PATH}" fill="${color}" stroke="${opts.highlight ? '#D4AF37' : '#FFFFFF'}" stroke-width="${opts.highlight ? 3 : 2}"/>
                    ${inner}
                </svg>`;

            // The halo sits behind the pin head (a third of the way down the pin)
            const html = opts.highlight
                ? `<div style="position:relative;width:${w}px;height:${h}px"><span class="gse-pin-halo"></span>${svg}</div>`
                : svg;

            return L.divIcon({
                className: 'gse-map-pin',
                html: html,
                iconSize: [w, h],
                iconAnchor: [w / 2, h],
                popupAnchor: [0, -h]
            });
        };

        window.GSE_MAP_CATEGORY_COLORS = CATEGORY_COLORS;
    })();
</script>
