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
        // White glyphs sit on these, so each needs >= 3:1 against white (WCAG
        // non-text contrast). Cyan-500 (2.4:1) and amber-500 (2.1:1) failed, so
        // both dropped to their 700 shades. Keep any new colour above 3:1.
        const CATEGORY_COLORS = {
            umkm: '#8B5CF6', // Violet — 4.2:1
            facilities: '#3B82F6', // Blue — 3.7:1
            toilets: '#0E7490', // Cyan 700 — 5.4:1
            accessibility: '#B45309', // Amber 700 — 5.0:1 (not #E65100: that means "crowd alert")
            cultural: '#1E5128' // Penglipuran Green — 9.3:1 (default)
        };

        // Solid silhouettes, not strokes: the glyph box is 18px at pin size, where a
        // 2px outline collapses into a smudge. Drawn in a 24x24 box. Shared with the
        // explore filter panel (map-search.blade.php) so legend and markers match.
        const CATEGORY_GLYPHS = {
            // Candi bentar (split gate) — reads as Penglipuran, not "generic building".
            // The stepped profile is what survives the downscale; a plain slope read
            // as two bars.
            cultural: '<path d="M3 21.5V8h2.5V5H8V2.5h1.75v19zM21 21.5V8h-2.5V5H16V2.5h-1.75v19z"/>',
            umkm: '<path d="M7.6 10.5V7.6a4.4 4.4 0 018.8 0v2.9h-3V7.6a1.4 1.4 0 00-2.8 0v2.9z"/><path d="M4.6 10h14.8l.75 10.3a1.2 1.2 0 01-1.2 1.3H5.05a1.2 1.2 0 01-1.2-1.3z"/>',
            // Info mark, not a map pin: a pin inside a pin is a redundant metaphor
            facilities: '<path fill-rule="evenodd" d="M12 3a9 9 0 100 18 9 9 0 000-18zm0 2.8a1.7 1.7 0 110 3.4 1.7 1.7 0 010-3.4zm-1.6 4.8h3.2v7.2h-3.2z"/>',
            toilets: '<circle cx="6.05" cy="3.6" r="2.1"/><rect x="3.5" y="6.9" width="5.1" height="7.6" rx="1.8"/><rect x="4.2" y="13" width="1.7" height="8" rx="0.8"/><rect x="6.2" y="13" width="1.7" height="8" rx="0.8"/><circle cx="16.55" cy="3.6" r="2.1"/><path d="M16.55 6.9 20.5 15.6H12.6z"/><rect x="14.7" y="14.6" width="1.7" height="6.4" rx="0.8"/><rect x="16.7" y="14.6" width="1.7" height="6.4" rx="0.8"/>',
            // Figure sits on the wheel's rim, never over its hole — overlapping the
            // hole filled it in and the whole glyph read as a bold letter "b".
            accessibility: '<circle cx="8.4" cy="3.8" r="2.6"/><path d="M6.6 7.2h3.6v2.4h5.6v2.8H6.6z"/><path fill-rule="evenodd" d="M12.2 11.2a5.4 5.4 0 100 10.8 5.4 5.4 0 000-10.8zm0 2.6a2.8 2.8 0 110 5.6 2.8 2.8 0 010-5.6z"/>',
            check: '<path d="M9.6 18.6 3.9 12.9 6 10.8l3.6 3.6L18 6l2.1 2.1z"/>'
        };

        const PIN_PATH = 'M16 1C8.8 1 3 6.8 3 14c0 9.2 13 27 13 27s13-17.8 13-27C29 6.8 23.2 1 16 1z';

        /**
         * Google-Maps-style teardrop pin, anchored at its tip.
         *
         * @param {string} category  key of CATEGORY_COLORS / CATEGORY_GLYPHS
         * @param {object} [options]
         *   highlight {boolean} the selected pin: enlarged, inverted, Bali Gold halo
         *   number    {number}  render this digit instead of the category glyph (route stops)
         *   color     {string}  override the category colour
         *   dimmed    {boolean} grey out (a completed route stop)
         */
        window.gseMapPin = function(category, options) {
            const opts = options || {};
            const color = opts.dimmed ? '#9CA3AF' : (opts.color || CATEGORY_COLORS[category] || CATEGORY_COLORS.cultural);
            const highlight = !!opts.highlight;
            const scale = highlight ? 1.35 : 1;
            const w = Math.round(32 * scale);
            const h = Math.round(42 * scale);

            // The selected pin inverts — white body, category colour for the ring and
            // glyph. A single accent ring can't work here: gold on the cyan pin was
            // 1.2:1 and on amber 1.0:1, so "selected" was invisible on 4 of 5
            // categories. Inverting reuses the category colour, which is already
            // guaranteed >= 3:1 against white.
            const body = highlight ? '#FFFFFF' : color;
            const ink = highlight ? color : '#FFFFFF';

            let inner;
            if (opts.number != null) {
                inner = `<text x="16" y="14" text-anchor="middle" dominant-baseline="central" fill="${ink}" font-size="15" font-weight="800" font-family="'Plus Jakarta Sans', Inter, system-ui, sans-serif">${opts.number}</text>`;
            } else {
                const glyph = CATEGORY_GLYPHS[category] || CATEGORY_GLYPHS.cultural;
                // 0.75 not 0.6667: the glyph box grows to 18px, which is what the
                // multi-part glyphs (wheelchair, restroom) need to stay legible. Any
                // larger and the widest glyphs clip the pin head.
                //
                // The offsets put the centre of the 24x24 glyph box on the centre of
                // the pin head (16, 14), so they move with the scale:
                //   tx = 16 - 24/2 * 0.75 = 7      ty = 14 - 24/2 * 0.75 = 5
                inner = `<g transform="translate(7 5) scale(0.75)" fill="${ink}">${glyph}</g>`;
            }

            const svg = `<svg width="${w}" height="${h}" viewBox="0 0 32 42" xmlns="http://www.w3.org/2000/svg" style="filter:drop-shadow(0 2px 3px rgba(0,0,0,.45))">
                    <path d="${PIN_PATH}" fill="${body}" stroke="${ink}" stroke-width="${highlight ? 3 : 2}"/>
                    ${inner}
                </svg>`;

            // The halo sits behind the pin head (a third of the way down the pin)
            const html = highlight
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
