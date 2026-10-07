
    (function () {
        'use strict';

        const CFG = {};

        const LEVEL_LABEL = { provinsi: 'Provinsi', kabupaten: 'Kabupaten/Kota', kecamatan: 'Kecamatan', desa: 'Desa/Kelurahan' };
        const STYLE = {
            provinsi: { color: '#C17F52', weight: 2.5, fillOpacity: 0.05, fillColor: '#C17F52' },
            kabupaten: { color: '#241C16', weight: 1.6, fillOpacity: 0.04, fillColor: '#241C16' },
            kecamatan: { color: '#6B5D4F', weight: 1.1, dashArray: '4 4', fillOpacity: 0.03, fillColor: '#6B5D4F' },
            desa: { color: '#A89985', weight: 0.8, dashArray: '2 3', fillOpacity: 0.03, fillColor: '#A89985' },
        };
        const MAX_ZOOM = { provinsi: 9, kabupaten: 11, kecamatan: 13, desa: 14 };
        // Fitur raster dengan bentang bbox lebih besar dari ini dianggap poligon
        // latar (artefak dissolve menutupi hampir seluruh provinsi) dan tidak digambar.
        const RASTER_MAX_SPAN_DEG = 0.5;

        let map = null;
        const baseLayers = {};
        let currentBase = 'osm';
        const overlays = { permukiman: null, provinsi: null, kabupaten: null, kecamatan: null, desa: null };
        const boundaries = {};                 // level -> FeatureCollection
        const featureIndex = new Map();        // id -> { level, feature }
        const layerIndex = new Map();          // 'level:id' -> L.Layer
        const fetchCache = new Map();          // url -> Promise(json)
        const rasterCache = new Map();         // year -> { features, summary }
        const regionCache = new Map();         // 'year:areaId' -> { sel, count, areaKm2 }
        let rasterLayer = null;
        let loadingCount = 0;
        let rasterBoundsFitted = false;

        const state = {
            level: 'provinsi',
            areaId: CFG.province ? CFG.province.id : null,
            regencyId: null, districtId: null, villageId: null,
            year: null,
        };

        const els = {};
        ['filter-kabupaten', 'filter-kecamatan', 'filter-desa', 'filter-tahun',
            'btn-reset-wilayah', 'btn-reset-stats', 'basemap-select', 'denah-map-loading',
            'stats-level', 'stats-name', 'stats-path', 'stat-total-lokasi', 'stat-total-records',
            'stat-total-luas', 'stat-year-label', 'stat-change', 'raster-file', 'raster-stats',
            'stats-by-year']
            .forEach(function (id) {
                const key = id.replace(/-([a-z])/g, function (m, c) { return c.toUpperCase(); });
                els[key] = document.getElementById(id);
            });

        // ---------- util umum ----------
        const fmtInt = new Intl.NumberFormat('id-ID');
        const fmtDec = new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        function escapeHtml(s) {
            return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
            });
        }

        // Perbaiki encoding teks (mojibake) pada nama wilayah
// (data UTF-8 terbaca sebagai Latin-1/Windows-1252).
        // Aman untuk teks ASCII/unicode normal: hanya berubah bila menemukan pola mojibake.
        function fixEncoding(str) {
            if (str == null) return str;
            const s = String(str);
            // Sudah berisi karakter unicode asli (> U+00FF) -> jangan ubah.
            for (let i = 0; i < s.length; i++) {
                if (s.charCodeAt(i) > 0xFF) return s;
            }
            // Deteksi byte khas mojibake (tiga byte UTF-8, diawali C2..EF).
            let suspicious = false;
            for (let i = 0; i < s.length; i++) {
                const c = s.charCodeAt(i);
                if (c >= 0xC0 && c <= 0xEF && i + 1 < s.length && (s.charCodeAt(i + 1) & 0xC0) === 0x80) {
                    suspicious = true;
                    break;
                }
            }
            if (!suspicious) return s;
            const bytes = new Uint8Array(s.length);
            for (let i = 0; i < s.length; i++) bytes[i] = s.charCodeAt(i) & 0xFF;
            try {
                return new TextDecoder('utf-8').decode(bytes);
            } catch (e) {
                return s;
            }
        }

        // Nama tampilan dari sebuah feature GeoJSON dengan perbaikan encoding.
        function displayName(f) {
            const p = f && f.properties ? f.properties : null;
            return p && p.name != null ? fixEncoding(p.name) : '';
        }

        function numOrNull(v) { const n = parseInt(v, 10); return isNaN(n) ? null : n; }

        function fetchJSON(url) {
            if (!fetchCache.has(url)) {
                fetchCache.set(url, fetch(url, { headers: { Accept: 'application/json' } })
                    .then(function (r) { if (!r.ok) { throw new Error('HTTP ' + r.status); } return r.json(); })
                    .catch(function (e) { fetchCache.delete(url); throw e; }));
            }
            return fetchCache.get(url);
        }

        function loading(on) {
            loadingCount = Math.max(0, loadingCount + (on ? 1 : -1));
            if (els.denahMapLoading) els.denahMapLoading.classList.toggle('hidden', loadingCount === 0);
            if (els.denahMapLoading) els.denahMapLoading.classList.toggle('flex', loadingCount > 0);
        }


        // ---------- matematika geo (shoelace equirectangular + point-in-polygon) ----------
        function walkCoords(coords, cb) {
            if (!Array.isArray(coords)) return;
            if (typeof coords[0] === 'number' && typeof coords[1] === 'number') { cb(coords[0], coords[1]); return; }
            coords.forEach(function (c) { walkCoords(c, cb); });
        }

        function geomBbox(g) {
            const bb = { minLng: Infinity, minLat: Infinity, maxLng: -Infinity, maxLat: -Infinity };
            if (!g) return bb;
            walkCoords(g.coordinates, function (lng, lat) {
                if (lng < bb.minLng) bb.minLng = lng;
                if (lng > bb.maxLng) bb.maxLng = lng;
                if (lat < bb.minLat) bb.minLat = lat;
                if (lat > bb.maxLat) bb.maxLat = lat;
            });
            return bb;
        }

        function ringAreaKm2(ring) {
            const n = ring.length;
            if (n < 4) return 0;
            let sumLat = 0;
            for (let i = 0; i < n - 1; i++) sumLat += ring[i][1];
            const cos0 = Math.cos((sumLat / (n - 1)) * Math.PI / 180);
            const R = 6378137;
            let total = 0;
            for (let i = 0; i < n - 1; i++) {
                const x1 = R * cos0 * ring[i][0] * Math.PI / 180, y1 = R * ring[i][1] * Math.PI / 180;
                const x2 = R * cos0 * ring[i + 1][0] * Math.PI / 180, y2 = R * ring[i + 1][1] * Math.PI / 180;
                total += (x1 * y2 - x2 * y1);
            }
            return Math.abs(total) / 2 / 1e6;
        }

        function polyAreaKm2(poly) {
            if (!poly || !poly.length) return 0;
            let area = ringAreaKm2(poly[0]);
            for (let i = 1; i < poly.length; i++) area -= ringAreaKm2(poly[i]);
            return Math.abs(area);
        }

        function geomAreaKm2(g) {
            if (!g) return 0;
            const polys = g.type === 'Polygon' ? [g.coordinates] : (g.type === 'MultiPolygon' ? g.coordinates : []);
            let total = 0;
            polys.forEach(function (p) { total += polyAreaKm2(p); });
            return total;
        }

        function ringCentroid(ring) {
            let sx = 0, sy = 0;
            const n = Math.max(1, ring.length - 1);
            for (let i = 0; i < n; i++) { sx += ring[i][0]; sy += ring[i][1]; }
            return [sx / n, sy / n];
        }

        function eachSubPolygon(g, cb) {
            if (!g) return;
            if (g.type === 'Polygon') { cb(g.coordinates); return; }
            if (g.type === 'MultiPolygon') g.coordinates.forEach(function (p) { cb(p); });
        }

        function pointInRing(lng, lat, ring) {
            let inside = false;
            for (let i = 0, j = ring.length - 1; i < ring.length; j = i++) {
                const xi = ring[i][0], yi = ring[i][1], xj = ring[j][0], yj = ring[j][1];
                if (((yi > lat) !== (yj > lat)) && (lng < (xj - xi) * (lat - yi) / (yj - yi) + xi)) inside = !inside;
            }
            return inside;
        }

        function geomContains(g, lng, lat) {
            let hit = false;
            eachSubPolygon(g, function (poly) {
                if (hit) return;
                if (!pointInRing(lng, lat, poly[0])) return;
                let inHole = false;
                for (let i = 1; i < poly.length; i++) {
                    if (pointInRing(lng, lat, poly[i])) { inHole = true; break; }
                }
                if (!inHole) hit = true;
            });
            return hit;
        }


        // ---------- normalisasi koordinat GeoJSON (deteksi & flip terbalik) ----------
        // GeoJSON standar WGS84 (OGC:CRS84) memakai urutan [longitude, latitude].
        // Namun sebagian data permukiman tersimpan dengan urutan [lat, lng] terbalik,
        // sehingga poligong terlempar jauh ke laut / luar pulau. Deteksi heuristik
        // bergantung pada rentang sah WGS84: longitude [-180, 180], latitude [-90, 90].
        function coordsLookFlipped(lng, lat) {
            if (Math.abs(lng) > 180) return true;   // nilai pertama bukan longitude -> urutan [lat, lng]
            if (Math.abs(lat) > 90)  return false;  // nilai kedua bukan latitude -> urutan [lng, lat] benar
            if (Math.abs(lng) > 90)  return false;  // nilai pertama > 90 hanya bisa jadi longitude
            if (Math.abs(lat) > 90)  return true;   // nilai kedua > 90 hanya bisa jadi longitude -> [lat,lng]
            return false;                           // keduanya |x|,|y| <= 90: ambigu, anggap sudah [lng,lat]
        }

        function deepFlipCoords(coords) {
            if (!Array.isArray(coords)) return coords;
            if (typeof coords[0] === 'number' && typeof coords[1] === 'number') {
                return [coords[1], coords[0]];  // swap [a,b] -> [b,a]
            }
            return coords.map(deepFlipCoords);
        }

        function normalizeGeometry(geom) {
            if (!geom || !geom.type || !geom.coordinates) return geom;
            const flat = [];
            walkCoords(geom.coordinates, function (lng, lat) { flat.push([lng, lat]); });

            let flipped = false;
            // Periksa sampel (sampai 200 titik pertama) untuk efisiensi.
            for (let i = 0; i < Math.min(flat.length, 200); i++) {
                if (coordsLookFlipped(flat[i][0], flat[i][1])) { flipped = true; break; }
            }
            if (!flipped) return geom;           // sudah [lng, lat], tidak perlu di-flip
            return { type: geom.type, coordinates: deepFlipCoords(geom.coordinates) };
        }


        // ---------- peta Leaflet ----------
        function ensureMap() {
            const el = document.getElementById('denah-map');
            if (!el || typeof L === 'undefined') return null;
            if (map) return map;
            const center = CFG.province && CFG.province.lat ? [CFG.province.lat, CFG.province.lng] : [-3.78, 102.26];
            map = L.map(el, { zoomControl: true, scrollWheelZoom: true, preferCanvas: true }).setView(center, 8);
            baseLayers.osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>', maxZoom: 19 });
            baseLayers.satellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', { attribution: '&copy; Esri', maxZoom: 19 });
            baseLayers.terrain = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Shaded_Relief/MapServer/tile/{z}/{y}/{x}', { attribution: '&copy; Esri', maxZoom: 16 });
            baseLayers.osm.addTo(map);
            overlays.permukiman = L.layerGroup().addTo(map);
            overlays.provinsi = L.layerGroup().addTo(map);
            overlays.kabupaten = L.layerGroup().addTo(map);
            overlays.kecamatan = L.layerGroup();
            overlays.desa = L.layerGroup();
            return map;
        }

        function onEachBoundary(level) {
            return function (feature, layer) {
                const p = feature.properties || {};
                if (p.id) {
                    featureIndex.set(p.id, { level: level, feature: feature });
                    layerIndex.set(level + ':' + p.id, layer);
                    // Klik wilayah di peta -> pilih wilayah + auto zoom-in.
                    layer.on('click', function () { applySelection(level, p.id); });
                }
                if (p.name) layer.bindTooltip(escapeHtml(fixEncoding(p.name)), { sticky: true });
            };
        }

                function normalizeFeatureCollection(fc) {
            if (!fc || !fc.features) return fc;
            return {
                type: 'FeatureCollection',
                features: fc.features.map(function (f) {
                    return f && f.geometry ? {
                        type: 'Feature',
                        properties: f.properties || {},
                        geometry: normalizeGeometry(f.geometry)
                    } : f;
                })
            };
        }

        function addBoundaryLayers(level, fc) {
            const group = overlays[level];
            if (!group || !fc) return;
            const normalized = normalizeFeatureCollection(fc);
            L.geoJSON(normalized, { style: STYLE[level] || {}, onEachFeature: onEachBoundary(level) })
                .eachLayer(function (l) { group.addLayer(l); });
        }

        function loadLevel(level) {
            return fetchJSON(CFG.endpoints.areas + '/' + level).then(function (fc) {
                const normalized = normalizeFeatureCollection(fc);
                boundaries[level] = normalized;
                (normalized.features || []).forEach(function (f) {
                    if (f.properties && f.properties.id) featureIndex.set(f.properties.id, { level: level, feature: f });
                });
                return normalized;
            });
        }


        // ---------- dropdown wilayah (cascading dari properties GeoJSON) ----------
        function fillOptions(sel, features, placeholder) {
            sel.innerHTML = '';
            const opt0 = document.createElement('option');
            opt0.value = '';
            opt0.textContent = placeholder;
            sel.appendChild(opt0);
            features.slice().sort(function (a, b) {
                return displayName(a).localeCompare(displayName(b), 'id');
            }).forEach(function (f) {
                const opt = document.createElement('option');
                opt.value = f.properties.id;
                opt.textContent = displayName(f);
                sel.appendChild(opt);
            });
        }

        function rebuildKabupatenOptions() {
            fillOptions(els.filterKabupaten, (boundaries.kabupaten && boundaries.kabupaten.features) || [], 'Semua Kabupaten/Kota');
        }

        function rebuildKecamatanOptions() {
            const sel = els.filterKecamatan;
            if (!state.regencyId) {
                fillOptions(sel, [], 'Semua Kecamatan');
                sel.disabled = true;
                return;
            }
            const items = ((boundaries.kecamatan && boundaries.kecamatan.features) || [])
                .filter(function (f) { return f.properties.parent_id === state.regencyId; });
            fillOptions(sel, items, 'Semua Kecamatan');
            sel.disabled = false;
        }

        function rebuildDesaOptions() {
            const sel = els.filterDesa;
            if (!state.districtId) {
                fillOptions(sel, [], 'Semua Desa/Kelurahan');
                sel.disabled = true;
                return Promise.resolve();
            }
            sel.disabled = true;
            fillOptions(sel, [], 'Memuat desa…');
            return loadDesaOfDistrict(state.districtId).then(function (fc) {
                fillOptions(sel, (fc && fc.features) || [], 'Semua Desa/Kelurahan');
                sel.disabled = false;
                sel.value = state.villageId ? String(state.villageId) : '';
            });
        }

        function loadDesaOfDistrict(districtId) {
            const url = CFG.endpoints.areas + '/desa?parent_id=' + districtId;
            return fetchJSON(url).then(function (fc) {
                (fc.features || []).forEach(function (f) {
                    if (f.properties && f.properties.id) featureIndex.set(f.properties.id, { level: 'desa', feature: f });
                });
                addDesaLayers(fc);
                return fc;
            });
        }

        // Tambahkan layer desa (dedupe via id yang sudah tergambar).
        function addDesaLayers(fc) {
            const group = overlays.desa;
            if (!group || !fc) return;
            if (!group._denahIds) group._denahIds = {};
            L.geoJSON(fc, { style: STYLE.desa, onEachFeature: onEachBoundary('desa') })
                .eachLayer(function (l) {
                    const f = l.feature;
                    const key = f && f.properties ? f.properties.id : null;
                    if (key === null || group._denahIds[key]) return;
                    group._denahIds[key] = true;
                    group.addLayer(l);
                });
        }

        // Muat ulang layer desa sesuai konteks filter saat ini.
        function refreshDesaOverlay() {
            if (!map || !map.hasLayer(overlays.desa)) return;
            overlays.desa.clearLayers();
            overlays.desa._denahIds = {};
            let url;
            if (state.districtId) url = CFG.endpoints.areas + '/desa?parent_id=' + state.districtId;
            else if (state.regencyId) url = CFG.endpoints.areas + '/desa?regency_id=' + state.regencyId;
            else url = CFG.endpoints.areas + '/desa';
            loading(true);
            fetchJSON(url).then(function (fc) {
                (fc.features || []).forEach(function (f) {
                    if (f.properties && f.properties.id) featureIndex.set(f.properties.id, { level: 'desa', feature: f });
                });
                addDesaLayers(fc);
            }).finally(function () { loading(false); });
        }


        // ---------- seleksi wilayah + zoom ----------
        function applySelection(level, id) {
            const entry = featureIndex.get(id);
            if (level !== 'provinsi' && !entry) return;
            state.level = level;
            state.areaId = id;
            const p = entry ? (entry.feature.properties || {}) : {};
            if (level === 'kabupaten') {
                state.regencyId = id; state.districtId = null; state.villageId = null;
            } else if (level === 'kecamatan') {
                state.regencyId = p.parent_id || null; state.districtId = id; state.villageId = null;
            } else if (level === 'desa') {
                state.districtId = p.parent_id || null;
                const d = state.districtId ? featureIndex.get(state.districtId) : null;
                state.regencyId = d ? (d.feature.properties.parent_id || null) : null;
                state.villageId = id;
            } else {
                state.level = 'provinsi';
                state.areaId = CFG.province ? CFG.province.id : null;
                state.regencyId = state.districtId = state.villageId = null;
            }
            syncDropdowns();
            zoomToSelection();
            refreshDesaOverlay();
            updateStats();
        }

        function syncDropdowns() {
            els.filterKabupaten.value = state.regencyId ? String(state.regencyId) : '';
            rebuildKecamatanOptions();
            els.filterKecamatan.value = state.districtId ? String(state.districtId) : '';
            rebuildDesaOptions();
        }

        function zoomToSelection() {
            if (!map) return;
            const layer = state.areaId ? layerIndex.get(state.level + ':' + state.areaId) : null;
            if (layer && layer.getBounds && layer.getBounds().isValid()) {
                map.fitBounds(layer.getBounds(), { padding: [40, 40], maxZoom: MAX_ZOOM[state.level] || 10 });
                return;
            }
            const entry = state.areaId ? featureIndex.get(state.areaId) : null;
            const p = entry ? entry.feature.properties : CFG.province;
            if (p && p.lat != null && p.lng != null) {
                map.setView([p.lat, p.lng], MAX_ZOOM[state.level] || 10);
            }
        }


        // ---------- layer raster permukiman dari public/data/data_{tahun}.geojson ----------
        function currentRasterYear() { return state.year || CFG.latestFileYear; }

        let rasterMaxCount = 1;
        function prepareRaster(fc) {
            const features = [];
            let totalCount = 0, totalArea = 0, maxCount = 0;
            (fc.features || []).forEach(function (f) {
                const g = normalizeGeometry(f.geometry);
                if (!g || (g.type !== 'Polygon' && g.type !== 'MultiPolygon')) return;
                const bbox = geomBbox(g);
                // lewati poligon latar raksasa (artefak dissolve)
                if ((bbox.maxLng - bbox.minLng) > RASTER_MAX_SPAN_DEG || (bbox.maxLat - bbox.minLat) > RASTER_MAX_SPAN_DEG) return;
                const area = geomAreaKm2(g);
                const props = f.properties || {};
                const count = Number(props.count != null ? props.count : props.classifica) || 0;
                if (count > maxCount) maxCount = count;
                features.push({ geom: g, bbox: bbox, area: area, count: count });
                totalCount += count;
                totalArea += area;
            });
            rasterMaxCount = Math.max(1, maxCount);
            return { features: features, summary: { sel: features.length, count: totalCount, areaKm2: totalArea } };
        }

        function rasterColor(count) {
            const t = Math.min(1, Math.sqrt((count || 0) / rasterMaxCount));
            const c0 = [234, 214, 200], c1 = [163, 78, 42];
            const r = Math.round(c0[0] + (c1[0] - c0[0]) * t);
            const g = Math.round(c0[1] + (c1[1] - c0[1]) * t);
            const b = Math.round(c0[2] + (c1[2] - c0[2]) * t);
            return 'rgb(' + r + ',' + g + ',' + b + ')';
        }

        function rasterPopup(p) {
            return '<strong>Sel Permukiman</strong><br>'
                + 'Count: ' + fmtInt.format(p.count)
                + '<br>Luas: ' + fmtDec.format(p.area) + ' km²';
        }

        function rebuildRasterLayer() {
            overlays.permukiman.clearLayers();
            rasterLayer = null;
            const year = currentRasterYear();
            const r = year !== null ? rasterCache.get(year) : null;
            if (!r) { renderRasterCard(); return; }
            const feats = r.features.map(function (f) {
                return { type: 'Feature', properties: { count: f.count, area: f.area }, geometry: f.geom };
            });
            rasterLayer = L.geoJSON({ type: 'FeatureCollection', features: feats }, {
                style: function (f) {
                    const col = rasterColor(f.properties.count);
                    return { color: col, weight: 0, fillColor: col, fillOpacity: 0.72 };
                },
                onEachFeature: function (f, l) { l.bindPopup(rasterPopup(f.properties)); },
            });
            rasterLayer.addTo(overlays.permukiman);
            renderRasterCard();
        }

        function fitRasterBounds() {
            if (!map || rasterBoundsFitted) return;
            const year = currentRasterYear();
            const r = year !== null ? rasterCache.get(year) : null;
            if (!r || !r.features.length) return;
            const bounds = L.latLngBounds([]);
            r.features.forEach(function (f) {
                if (!f.bbox) return;
                bounds.extend([f.bbox.minLat, f.bbox.minLng]);
                bounds.extend([f.bbox.maxLat, f.bbox.maxLng]);
            });
            if (bounds.isValid()) {
                rasterBoundsFitted = true;
                map.fitBounds(bounds, { padding: [30, 30], maxZoom: 12 });
            }
        }

                function loadRaster() {
            const year = currentRasterYear();
            // Reset flag fitBounds agar tahun/raster baru dapat di-fit ulang.
            rasterBoundsFitted = false;
            overlays.permukiman.clearLayers();
            rasterLayer = null;
            if (year === null || CFG.fileYears.indexOf(year) === -1) { renderRasterCard(); return; }
            if (rasterCache.has(year)) { rebuildRasterLayer(); fitRasterBounds(); return; }
            loading(true);
            renderRasterCard();
            fetchJSON(CFG.endpoints.raster.replace('{year}', year))
                .then(function (fc) {
                    // Selalu normalisasi GeoJSON (deteksi & flip koordinat terbalik) sebelum diproses.
                    const features = (fc && fc.type === 'FeatureCollection' ? fc.features : (fc && fc.type === 'Feature' ? [fc] : []));
                    const normalized = { type: 'FeatureCollection', features: features.map(function (f) {
                        return f && f.geometry ? { type: 'Feature', properties: f.properties || {}, geometry: normalizeGeometry(f.geometry) } : f;
                    })};
                    rasterCache.set(year, prepareRaster(normalized));
                    rebuildRasterLayer();
                    fitRasterBounds();
                })
                .catch(function () {
                    if (els.rasterStats) els.rasterStats.innerHTML = '<p class="text-xs text-red-500">Gagal memuat file raster.</p>';
                })
                .finally(function () { loading(false); });
        }


        // ---------- statistik raster per wilayah (dihitung dari file GeoJSON) ----------
        function regionRasterStats() {
            const year = currentRasterYear();
            if (year === null || CFG.fileYears.indexOf(year) === -1) return null;
            const r = rasterCache.get(year);
            if (!r || !state.areaId) return null;
            // Tingkat provinsi = seluruh file (menghindari PIP pada geometri provinsi yang besar).
            if (state.level === 'provinsi') return r.summary;
            const cacheKey = year + ':' + state.areaId;
            if (regionCache.has(cacheKey)) return regionCache.get(cacheKey);
            const entry = featureIndex.get(state.areaId);
            const bg = entry ? entry.feature.geometry : null;
            if (!bg) return r.summary;
            const bb = geomBbox(bg);
            let sel = 0, count = 0, area = 0;
            r.features.forEach(function (f) {
                if (f.bbox.maxLng < bb.minLng || f.bbox.minLng > bb.maxLng || f.bbox.maxLat < bb.minLat || f.bbox.minLat > bb.maxLat) return;
                eachSubPolygon(f.geom, function (poly) {
                    const c = ringCentroid(poly[0]);
                    if (!geomContains(bg, c[0], c[1])) return;
                    const a = polyAreaKm2(poly);
                    area += a;
                    sel++;
                    count += f.count * (f.area > 0 ? a / f.area : 1);
                });
            });
            const res = { sel: sel, count: Math.round(count), areaKm2: area };
            regionCache.set(cacheKey, res);
            return res;
        }

        function renderRasterCard() {
            if (!els.rasterFile || !els.rasterStats) return;
            const year = currentRasterYear();
            if (year === null) {
                els.rasterFile.textContent = '—';
                els.rasterStats.innerHTML = '<p>Tidak ada file raster.</p>';
                return;
            }
            els.rasterFile.textContent = 'data_' + year + '.geojson';
            if (CFG.fileYears.indexOf(year) === -1) {
                els.rasterStats.innerHTML = '<p>File raster untuk tahun ' + year + ' tidak tersedia.</p>';
                return;
            }
            if (!rasterCache.has(year)) {
                els.rasterStats.innerHTML = '<p>Memuat raster…</p>';
                return;
            }
            const reg = regionRasterStats();
            const r = rasterCache.get(year);
            const rows = [];
            rows.push('<div class="flex items-center justify-between"><span>Sel permukiman (wilayah ini)</span><strong class="text-denah-ink">' + fmtInt.format(reg ? reg.sel : 0) + '</strong></div>');
            rows.push('<div class="flex items-center justify-between"><span>Total count (wilayah ini)</span><strong class="text-denah-ink">' + fmtInt.format(reg ? reg.count : 0) + '</strong></div>');
            rows.push('<div class="flex items-center justify-between"><span>Luas raster (wilayah ini)</span><strong class="text-denah-ink">' + fmtDec.format(reg ? reg.areaKm2 : 0) + ' km²</strong></div>');
            rows.push('<p class="pt-1 border-t border-denah-terracotta/15 text-[11px]">Seluruh file: ' + fmtInt.format(r.summary.sel) + ' sel · ' + fmtDec.format(r.summary.areaKm2) + ' km²</p>');
            els.rasterStats.innerHTML = rows.join('');
        }


        // ---------- panel statistik kanan (real-time per wilayah terpilih) ----------
        function statsParams() {
            const p = new URLSearchParams();
            const nameOf = function (id) {
                const e = featureIndex.get(id);
                return e ? displayName(e.feature) : null;
            };
            if (state.villageId) {
                const rn = nameOf(state.regencyId), dn = nameOf(state.districtId), vn = nameOf(state.villageId);
                if (rn) p.set('regency', rn);
                if (dn) p.set('district', dn);
                if (vn) p.set('village', vn);
            } else if (state.districtId) {
                const rn = nameOf(state.regencyId), dn = nameOf(state.districtId);
                if (rn) p.set('regency', rn);
                if (dn) p.set('district', dn);
            } else if (state.regencyId) {
                const rn = nameOf(state.regencyId);
                if (rn) p.set('regency', rn);
            }
            return p;
        }

        function updateStats() {
            renderStatsHeader();
            const base = statsParams();
            const jobs = [fetchJSON(CFG.endpoints.stats + '?' + base.toString())];
            if (state.year) {
                const py = new URLSearchParams(base);
                py.set('year', state.year);
                jobs.push(fetchJSON(CFG.endpoints.stats + '?' + py.toString()));
            }
            Promise.all(jobs)
                .then(function (res) { renderStats(res[0], res.length > 1 ? res[1] : null); })
                .catch(function () {
                    if (els.statsByYear) els.statsByYear.innerHTML = '<p class="text-xs text-red-500">Gagal memuat statistik.</p>';
                });
        }

        function renderStatsHeader() {
            const entry = state.areaId ? featureIndex.get(state.areaId) : null;
            els.statsLevel.textContent = LEVEL_LABEL[state.level] || 'Provinsi';
            if (!entry) {
                els.statsName.textContent = CFG.province ? fixEncoding(CFG.province.name) : 'Provinsi';
                els.statsPath.textContent = 'Seluruh wilayah';
                return;
            }
            els.statsName.textContent = entry.feature.properties.name ? displayName(entry.feature) : 'Provinsi';
            const parts = [];
            let e = entry, guard = 0;
            while (e && guard < 4) {
                parts.unshift(e.feature.properties.name ? displayName(e.feature) : '');
                e = e.feature.properties.parent_id ? featureIndex.get(e.feature.properties.parent_id) : null;
                guard++;
            }
            els.statsPath.textContent = parts.join(', ');
        }

        function renderStats(all, yr) {
            const t = (yr || all).totals || {};
            els.statTotalLokasi.textContent = fmtInt.format(t.total_locations || 0);
            els.statTotalRecords.textContent = fmtInt.format(t.total_records || 0) + ' record';
            els.statTotalLuas.textContent = fmtDec.format(t.total_area_km2 || 0);
            els.statYearLabel.textContent = state.year ? String(state.year) : 'Semua Tahun';
            renderChange(all.by_year || []);
            renderByYear(all.by_year || []);
            renderRasterCard();
        }

        function renderChange(byYear) {
            if (!byYear.length) {
                els.statChange.innerHTML = '<p class="text-xs text-denah-ink-muted">Belum ada data tahunan.</p>';
                return;
            }
            const years = byYear.map(function (r) { return r.year; }).sort(function (a, b) { return a - b; });
            const cur = state.year || years[years.length - 1];
            const prev = years.slice().reverse().find(function (y) { return y < cur; });
            const curRow = byYear.find(function (r) { return r.year === cur; });
            const prevRow = prev != null ? byYear.find(function (r) { return r.year === prev; }) : null;
            if (!curRow || !prevRow) {
                els.statChange.innerHTML = '<p class="text-xs text-denah-ink-muted">Belum ada data pembanding tahun sebelumnya.</p>';
                return;
            }
            const delta = (curRow.total_locations || 0) - (prevRow.total_locations || 0);
            const pct = prevRow.total_locations ? (delta / prevRow.total_locations) * 100 : 0;
            const up = delta >= 0;
            els.statChange.innerHTML =
                '<div class="flex items-center justify-between rounded-lg bg-denah-cream-bg px-3 py-2">'
                + '<span class="text-xs text-denah-ink-muted">vs ' + prevRow.year + '</span>'
                + '<span class="text-sm font-bold ' + (up ? '▲ ' : '▼ ') + '">'
                + (up ? '▲ ' : '▼ ') + fmtInt.format(delta)
                + ' <span class="font-medium">(' + (pct >= 0 ? '+' : '') + fmtDec.format(pct) + '%)</span>'
                + '</span></div>';
        }


        function renderByYear(rows) {
            if (!rows.length) {
                els.statsByYear.innerHTML = '<p class="text-xs text-denah-ink-muted">Belum ada data.</p>';
                return;
            }
            const max = rows.reduce(function (m, r) { return Math.max(m, r.total_locations || 0); }, 0) || 1;
            const active = state.year || null;
            els.statsByYear.innerHTML = rows.map(function (r) {
                const w = Math.round(((r.total_locations || 0) / max) * 100);
                const barCls = active && r.year === active ? 'bg-denah-terracotta' : 'bg-denah-terracotta/60';
                return '<div class="flex items-center gap-2 text-xs">'
                    + '<span class="w-10 shrink-0 font-semibold text-denah-ink">' + r.year + '</span>'
                    + '<div class="h-4 flex-1 overflow-hidden rounded bg-denah-cream-bg">'
                    + '<div class="h-full rounded ' + barCls + '" style="width: ' + w + '%"></div>'
                    + '</div>'
                    + '<span class="w-14 shrink-0 text-right font-medium text-denah-ink-muted">' + fmtInt.format(r.total_locations || 0) + '</span>'
                    + '</div>';
            }).join('');
        }



        // ---------- binding UI ----------
        function bindUi() {
            els.filterKabupaten.addEventListener('change', function () {
                const id = numOrNull(els.filterKabupaten.value);
                if (id) applySelection('kabupaten', id);
                else applySelection('provinsi', CFG.province ? CFG.province.id : null);
            });
            els.filterKecamatan.addEventListener('change', function () {
                const id = numOrNull(els.filterKecamatan.value);
                if (id) applySelection('kecamatan', id);
                else if (state.regencyId) applySelection('kabupaten', state.regencyId);
                else applySelection('provinsi', CFG.province ? CFG.province.id : null);
            });
            els.filterDesa.addEventListener('change', function () {
                const id = numOrNull(els.filterDesa.value);
                if (id) applySelection('desa', id);
                else if (state.districtId) applySelection('kecamatan', state.districtId);
                else if (state.regencyId) applySelection('kabupaten', state.regencyId);
                else applySelection('provinsi', CFG.province ? CFG.province.id : null);
            });
            els.filterTahun.addEventListener('change', function () {
                state.year = numOrNull(els.filterTahun.value);
                loadRaster();
                updateStats();
            });

            const reset = function () { applySelection('provinsi', CFG.province ? CFG.province.id : null); };
            if (els.btnResetWilayah) els.btnResetWilayah.addEventListener('click', reset);
            if (els.btnResetStats) els.btnResetStats.addEventListener('click', reset);
            document.querySelectorAll('[data-overlay]').forEach(function (cb) {
                cb.addEventListener('change', function () {
                    const group = overlays[cb.dataset.overlay];
                    if (!map || !group) return;
                    if (cb.checked) {
                        group.addTo(map);
                        if (cb.dataset.overlay === 'desa') refreshDesaOverlay();
                    } else {
                        map.removeLayer(group);
                    }
                });
            });
            if (els.basemapSelect) {
                els.basemapSelect.addEventListener('change', function () {
                    const name = els.basemapSelect.value;
                    if (!map || !baseLayers[name]) return;
                    if (currentBase && baseLayers[currentBase]) map.removeLayer(baseLayers[currentBase]);
                    baseLayers[name].addTo(map);
                    currentBase = name;
                });
            }
        }

        // ---------- init ----------
        function init() {
            if (!ensureMap()) return;
            bindUi();
            loading(true);
            let initFailed = false;
            Promise.all([loadLevel('provinsi'), loadLevel('kabupaten'), loadLevel('kecamatan')])
                .then(function () {
                    rebuildKabupatenOptions();
                    addBoundaryLayers('provinsi', boundaries.provinsi);
                    addBoundaryLayers('kabupaten', boundaries.kabupaten);
                    addBoundaryLayers('kecamatan', boundaries.kecamatan);
                    // Fit bounds otomatis ke wilayah Bengkulu setelah layer dimuat.
                    if (map && boundaries.provinsi && boundaries.provinsi.features) {
                        const allBounds = L.latLngBounds([]);
                        boundaries.provinsi.features.forEach(function (f) {
                            const g = f.geometry;
                            if (!g) return;
                            const bb = geomBbox(g);
                            if (isFinite(bb.minLat) && isFinite(bb.minLng) && isFinite(bb.maxLat) && isFinite(bb.maxLng)) {
                                allBounds.extend([bb.minLat, bb.minLng]);
                                allBounds.extend([bb.maxLat, bb.maxLng]);
                            }
                        });
                        if (allBounds.isValid()) {
                            map.fitBounds(allBounds, { padding: [50, 50], maxZoom: 10 });
                        }
                    }
                    zoomToSelection();
                    updateStats();
                })
                .catch(function () { initFailed = true; })
                .finally(function () {
                    loading(false);
                    if (initFailed && els.denahMapLoading) {
                        els.denahMapLoading.classList.add('flex');
                        els.denahMapLoading.classList.remove('hidden');
                        els.denahMapLoading.innerHTML = '<span class="rounded-full bg-red-600/90 px-4 py-1.5 text-xs font-medium text-white shadow-lg">Gagal memuat batas wilayah dari server.</span>';
                    }
                });
            loadRaster();
            setTimeout(function () { if (map) map.invalidateSize(); }, 120);
        }

        document.addEventListener('livewire:navigated', function () {
            if (map) setTimeout(function () { map.invalidateSize(); }, 120);
        });

        init();
    })();
