<script>
    const MAP_STYLE_TILE_URLS = {
        standard: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        satellite: 'https://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}',
    };

    function initMapStyleSwitcher(map) {
        var currentStyle = null;
        var styleLayer = null;
        var initialLayer = null;

        map.eachLayer(function(l) {
            if (l instanceof L.TileLayer) initialLayer = l;
        });

        function switchMapStyle(style) {
            if (!style || style === currentStyle) return;

            if (styleLayer) {
                map.removeLayer(styleLayer);
                styleLayer = null;
            }
            if (initialLayer && map.hasLayer(initialLayer)) {
                map.removeLayer(initialLayer);
            }

            if (style === 'standard') {
                if (initialLayer) map.addLayer(initialLayer);
            } else {
                var opts = { maxZoom: 19, attribution: '&copy; OpenStreetMap contributors' };
                if (style === 'satellite') {
                    opts.subdomains = ['mt0', 'mt1', 'mt2', 'mt3'];
                    opts.attribution = '';
                }
                styleLayer = L.tileLayer(MAP_STYLE_TILE_URLS[style], opts).addTo(map);
            }

            document.querySelectorAll('.map-style-option').forEach(function(btn) {
                btn.classList.toggle('border-primary', btn.dataset.style === style);
                btn.classList.toggle('border-transparent', btn.dataset.style !== style);
            });

            try { localStorage.setItem('mapStyle', style); } catch (e) {}
            currentStyle = style;
        }

        document.getElementById('map-style-thumb-standard').src = 'https://a.tile.openstreetmap.org/12/2048/1365.png';
        document.getElementById('map-style-thumb-satellite').src = 'https://mt1.google.com/vt/lyrs=s&x=2048&y=1365&z=12';

        document.querySelectorAll('.map-style-option').forEach(function(btn) {
            var s = btn.id.replace('map-style-option-', '');
            btn.dataset.style = s;
            btn.addEventListener('click', function() {
                switchMapStyle(s);
                window.dispatchEvent(new CustomEvent('close-map-style-modal'));
            });
        });

        var btn = document.getElementById('btn-map-style');
        if (btn) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                window.dispatchEvent(new CustomEvent('open-map-style-modal'));
            });
        }

        switchMapStyle(localStorage.getItem('mapStyle') || 'standard');
    }
</script>
