/* ============================================================================
   GIYA — Leaflet integration
   ----------------------------------------------------------------------------
   Leaflet is served from /assets/js/leaflet.js, so the library never needs the
   network. Tiles try the local folder first and fall back to OpenStreetMap when
   a tile is missing, which lets the map work before `php artisan giya:tiles`
   has been run.

   Two entry points:
     GiyaLeaflet.browse(config)   devotee map — locate me, plan a route
     GiyaLeaflet.picker(config)   admin map  — click to pin, click a pin to edit

   Church pins render as the church's own photo inside a teardrop frame.
   ============================================================================ */
window.GiyaLeaflet = (function () {
    'use strict';

    var CEBU = { lat: 10.3157, lng: 123.8854 };

    /* ---------------------------------------------------------------- tiles */

    function tileLayer(cfg) {
        var local = L.tileLayer(cfg.tileUrl || '/tiles/{z}/{x}/{y}.png', {
            minZoom: 9, maxZoom: 18, subdomains: [''],
            attribution: '&copy; OpenStreetMap contributors',
            errorTileUrl: '/tiles/blank.png'
        });

        // Count misses; if the local cache clearly is not there, swap once.
        var misses = 0, swapped = false;
        local.on('tileerror', function (e) {
            if (swapped || ++misses < 4) return;
            swapped = true;
            e.target.setUrl('https://tile.openstreetmap.org/{z}/{x}/{y}.png');
            e.target.options.subdomains = [''];
            if (cfg.onFallback) cfg.onFallback();
        });

        return local;
    }

    /* ---------------------------------------------------------------- pins */

    /**
     * A teardrop frame with the church photo inside. Falls back to a lettered
     * disc when the church has no image, so a pin always renders.
     */
    function churchIcon(church, opts) {
        opts = opts || {};
        var size = opts.size || 46;
        var ring = opts.selected ? 'var(--gold)' : '#fff';
        var glow = opts.selected ? '0 0 0 3px rgba(215,169,74,.55),' : '';

        var inner = church.image
            ? '<img src="' + church.image + '" alt="" ' +
              'style="width:100%;height:100%;object-fit:cover;display:block">'
            : '<span style="display:flex;width:100%;height:100%;align-items:center;' +
              'justify-content:center;background:' + (church.color || '#8E3B2F') +
              ';color:#fff;font-weight:700;font-size:' + Math.round(size * 0.42) + 'px">' +
              (church.name || '?').charAt(0) + '</span>';

        var html =
            '<div class="giya-pin' + (opts.selected ? ' is-selected' : '') + '" ' +
                 'style="width:' + size + 'px;height:' + size + 'px">' +
              '<div class="giya-pin-frame" style="border-color:' + ring + ';' +
                   'box-shadow:' + glow + '0 3px 10px rgba(0,0,0,.4)">' + inner + '</div>' +
              '<span class="giya-pin-tail" style="border-top-color:' + ring + '"></span>' +
            '</div>';

        return L.divIcon({
            html: html,
            className: 'giya-pin-wrap',
            iconSize: [size, size * 1.3],
            iconAnchor: [size / 2, size * 1.3],
            popupAnchor: [0, -size * 1.15]
        });
    }

    function userIcon() {
        return L.divIcon({
            html: '<span class="giya-me"><span class="giya-me-dot"></span></span>',
            className: 'giya-pin-wrap',
            iconSize: [22, 22],
            iconAnchor: [11, 11]
        });
    }

    /* ------------------------------------------------------------- geometry */

    function km(a, b) {
        var R = 6371, dLat = rad(b.lat - a.lat), dLng = rad(b.lng - a.lng);
        var h = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(rad(a.lat)) * Math.cos(rad(b.lat)) *
                Math.sin(dLng / 2) * Math.sin(dLng / 2);
        return R * 2 * Math.atan2(Math.sqrt(h), Math.sqrt(1 - h));
    }

    function rad(d) { return d * Math.PI / 180; }

    /** Nearest-neighbour ordering from a start point. */
    function orderStops(start, stops) {
        var left = stops.slice(), here = start, out = [];
        while (left.length) {
            var best = 0, bestD = Infinity;
            for (var i = 0; i < left.length; i++) {
                var d = km(here, left[i]);
                if (d < bestD) { bestD = d; best = i; }
            }
            here = left[best];
            out.push(left.splice(best, 1)[0]);
        }
        return out;
    }

    function escapeHtml(v) {
        return String(v == null ? '' : v)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    /* =========================================================== DEVOTEE MAP */

    function browse(cfg) {
        var map = L.map(cfg.element, {
            center: [cfg.center ? cfg.center.lat : CEBU.lat, cfg.center ? cfg.center.lng : CEBU.lng],
            zoom: cfg.zoom || 12,
            zoomControl: true
        });

        tileLayer(cfg).addTo(map);

        var markers = {};
        var churches = (cfg.churches || []).filter(function (c) { return c.lat && c.lng; });
        var group = L.layerGroup().addTo(map);
        var routeLine = null;
        var meMarker = null;
        var me = null;
        var selected = [];

        churches.forEach(function (c) {
            var m = L.marker([c.lat, c.lng], { icon: churchIcon(c), title: c.name })
                .bindPopup(popupHtml(c))
                .addTo(group);

            m.on('click', function () { if (cfg.onSelect) cfg.onSelect(c.id); });
            markers[c.id] = m;
        });

        if (churches.length) {
            map.fitBounds(L.latLngBounds(churches.map(function (c) {
                return [c.lat, c.lng];
            })).pad(0.18));
        }

        function popupHtml(c) {
            return '<div class="giya-popup">' +
                (c.image ? '<img src="' + c.image + '" alt="">' : '') +
                '<strong>' + escapeHtml(c.name) + '</strong>' +
                '<span>' + escapeHtml(c.category || '') + ' &middot; ' + escapeHtml(c.location || '') + '</span>' +
                (c.hours ? '<span>' + escapeHtml(c.hours) + '</span>' : '') +
                '<button type="button" onclick="GiyaLeaflet.addStop(' + c.id + ')">Add to route</button>' +
                '</div>';
        }

        /* ---- locate me ---- */
        function locate(onDone) {
            if (!navigator.geolocation) {
                if (cfg.onStatus) cfg.onStatus('This browser cannot share a location.', 'error');
                return;
            }

            if (cfg.onStatus) cfg.onStatus('Finding your location…', 'info');

            navigator.geolocation.getCurrentPosition(
                function (pos) {
                    me = { lat: pos.coords.latitude, lng: pos.coords.longitude };

                    if (meMarker) map.removeLayer(meMarker);
                    meMarker = L.marker([me.lat, me.lng], { icon: userIcon(), zIndexOffset: 900 })
                        .bindPopup('You are here')
                        .addTo(map);

                    L.circle([me.lat, me.lng], {
                        radius: pos.coords.accuracy,
                        color: '#2563EB', weight: 1,
                        fillColor: '#2563EB', fillOpacity: .08
                    }).addTo(map);

                    map.setView([me.lat, me.lng], 14);

                    if (cfg.onLocated) cfg.onLocated(me, nearest(me));
                    if (cfg.onStatus) cfg.onStatus('', 'clear');
                    if (onDone) onDone();
                },
                function (err) {
                    var msg = err.code === 1
                        ? 'Location permission was denied. Allow it in the address bar to use this.'
                        : 'Could not get a location fix. Try again outdoors or check GPS.';
                    if (cfg.onStatus) cfg.onStatus(msg, 'error');
                },
                { enableHighAccuracy: true, timeout: 12000, maximumAge: 60000 }
            );
        }

        function nearest(from) {
            return churches
                .map(function (c) {
                    return { church: c, km: km(from, { lat: c.lat, lng: c.lng }) };
                })
                .sort(function (a, b) { return a.km - b.km; })
                .slice(0, 8);
        }

        /* ---- route ---- */
        function drawRoute() {
            if (routeLine) { map.removeLayer(routeLine); routeLine = null; }

            var stops = selected.map(function (id) {
                var c = churches.filter(function (x) { return x.id === id; })[0];
                return c ? { id: c.id, name: c.name, lat: c.lat, lng: c.lng } : null;
            }).filter(Boolean);

            if (!stops.length) {
                if (cfg.onRoute) cfg.onRoute([], 0);
                return;
            }

            var start = me || { lat: stops[0].lat, lng: stops[0].lng };
            var ordered = orderStops(start, stops);
            var path = (me ? [[me.lat, me.lng]] : []).concat(
                ordered.map(function (s) { return [s.lat, s.lng]; })
            );

            routeLine = L.polyline(path, {
                color: '#8E3B2F', weight: 4, opacity: .85, dashArray: '1 8', lineCap: 'round'
            }).addTo(map);

            L.polyline(path, { color: '#D7A94A', weight: 1.5, opacity: .9 }).addTo(routeLine);

            var total = 0;
            for (var i = 1; i < path.length; i++) {
                total += km({ lat: path[i - 1][0], lng: path[i - 1][1] },
                            { lat: path[i][0],     lng: path[i][1] });
            }

            map.fitBounds(routeLine.getBounds().pad(0.2));
            if (cfg.onRoute) cfg.onRoute(ordered, total);
        }

        function addStop(id) {
            if (selected.indexOf(id) === -1) selected.push(id);
            highlight();
            drawRoute();
        }

        function removeStop(id) {
            selected = selected.filter(function (x) { return x !== id; });
            highlight();
            drawRoute();
        }

        function clearRoute() {
            selected = [];
            highlight();
            drawRoute();
        }

        function highlight() {
            churches.forEach(function (c) {
                var m = markers[c.id];
                if (m) m.setIcon(churchIcon(c, { selected: selected.indexOf(c.id) !== -1 }));
            });
        }

        function focus(id) {
            var m = markers[id];
            if (!m) return;
            map.setView(m.getLatLng(), 16, { animate: true });
            m.openPopup();
        }

        /** Hand the ordered stops to Google Maps for turn-by-turn (needs data). */
        function externalDirections() {
            var stops = selected.map(function (id) {
                return churches.filter(function (c) { return c.id === id; })[0];
            }).filter(Boolean);

            if (!stops.length) return null;

            var ordered = orderStops(me || { lat: stops[0].lat, lng: stops[0].lng }, stops);
            var pts = ordered.map(function (s) { return s.lat + ',' + s.lng; });
            var origin = me ? me.lat + ',' + me.lng : pts.shift();
            var dest = pts.pop();

            return 'https://www.google.com/maps/dir/?api=1' +
                   '&origin=' + origin + '&destination=' + dest +
                   (pts.length ? '&waypoints=' + pts.join('|') : '') +
                   '&travelmode=driving';
        }

        setTimeout(function () { map.invalidateSize(); }, 200);
        window.addEventListener('resize', function () { map.invalidateSize(); });

        return {
            map: map, locate: locate, focus: focus,
            addStop: addStop, removeStop: removeStop, clearRoute: clearRoute,
            externalDirections: externalDirections,
            selected: function () { return selected.slice(); },
            distanceTo: function (c) { return me ? km(me, c) : null; }
        };
    }

    /* ============================================================= ADMIN MAP */

    function picker(cfg) {
        var map = L.map(cfg.element, {
            center: [CEBU.lat, CEBU.lng],
            zoom: cfg.zoom || 12
        });

        tileLayer(cfg).addTo(map);

        var existing = L.layerGroup().addTo(map);
        var pin = null;

        function report(latlng) {
            var lat = Math.round(latlng.lat * 1e8) / 1e8;
            var lng = Math.round(latlng.lng * 1e8) / 1e8;
            if (cfg.latInput) document.querySelector(cfg.latInput).value = lat.toFixed(8);
            if (cfg.lngInput) document.querySelector(cfg.lngInput).value = lng.toFixed(8);
            if (cfg.onPin) cfg.onPin(lat, lng);
        }

        function place(lat, lng, silent) {
            var ll = L.latLng(lat, lng);
            if (!pin) {
                pin = L.marker(ll, {
                    icon: churchIcon({ name: 'New', color: '#D7A94A' }, { selected: true, size: 44 }),
                    draggable: true, zIndexOffset: 1000
                }).addTo(map);
                pin.on('dragend', function (e) { report(e.target.getLatLng()); });
            } else {
                pin.setLatLng(ll);
            }
            if (!silent) report(ll);
        }

        map.on('click', function (e) { place(e.latlng.lat, e.latlng.lng); });

        function draw(churches) {
            existing.clearLayers();
            (churches || []).forEach(function (c) {
                if (!c.lat || !c.lng) return;

                L.marker([c.lat, c.lng], {
                    icon: churchIcon(c, { size: 42 }),
                    opacity: c.active ? 1 : 0.55,
                    title: c.name
                })
                    .on('click', function () { if (cfg.onChurchClick) cfg.onChurchClick(c); })
                    .bindTooltip(c.name, { direction: 'top', offset: [0, -50] })
                    .addTo(existing);
            });
        }

        draw(cfg.churches);

        setTimeout(function () { map.invalidateSize(); }, 200);
        window.addEventListener('resize', function () { map.invalidateSize(); });

        return {
            map: map, place: place, draw: draw,
            clear: function () { if (pin) { map.removeLayer(pin); pin = null; } },
            focus: function (lat, lng) { map.setView([lat, lng], 16); }
        };
    }

    /* Popup buttons call through this; the page assigns the live instance. */
    var current = null;

    return {
        browse: function (cfg) { current = browse(cfg); return current; },
        picker: picker,
        addStop: function (id) { if (current) current.addStop(id); },
        churchIcon: churchIcon,
        km: km
    };
})();
