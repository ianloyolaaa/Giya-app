@extends('layouts.app')

@section('title', 'Map')

@push('head')
    <link rel="stylesheet" href="{{ asset('assets/css/leaflet.css') }}?v={{ filemtime(public_path('assets/css/leaflet.css')) }}">
@endpush

@section('content')
<div style="max-width:1280px;margin:0 auto;padding:24px 20px 48px">

    <div class="d-flex align-items-end justify-content-between flex-wrap gap-3 mb-3">
        <div>
            <span class="eyebrow">EXPLORE</span>
            <h1 style="font-family:var(--font-display);font-size: 1.625rem;margin:4px 0 2px">Map of Metro Cebu</h1>
            <p style="color:var(--text-muted);font-size: 0.8125rem;margin:0">
                Find churches near you, then build a route through the ones you want to visit.
            </p>
        </div>

        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline" id="btnLocate">
                <i class="bi bi-geo-alt-fill"></i> Find my location
            </button>
            <button type="button" class="btn btn-primary" id="btnClearRoute" style="display:none">
                <i class="bi bi-x-lg"></i> Clear route
            </button>
        </div>
    </div>

    <div id="mapNote" class="map-note" style="display:none"></div>

    <div style="display:grid;grid-template-columns:340px 1fr;gap:18px" class="map-grid">

        <aside class="map-sidebar card" style="padding:16px;max-height:640px;overflow-y:auto">

            <input type="search" id="mapSearch" class="giya-input" placeholder="Search churches..."
                   style="margin-bottom:10px" aria-label="Search churches">

            <div class="d-flex gap-1 flex-wrap" style="margin-bottom:14px">
                @foreach ($categories as $category)
                    <button type="button"
                            @class(['cat-chip', 'is-active' => $loop->first])
                            data-cat="{{ $category }}">{{ $category }}</button>
                @endforeach
            </div>

            <div id="routeBox" style="display:none;margin-bottom:14px">
                <div class="form-label-sm d-flex justify-content-between align-items-center">
                    <span>Your route</span>
                    <span id="routeDistance" style="color:var(--primary);font-weight:700"></span>
                </div>
                <div id="routeStops"></div>
                <a href="#" id="btnDirections" class="btn btn-outline btn-sm" style="width:100%;margin-top:6px">
                    <i class="bi bi-signpost-fill"></i> Open turn-by-turn
                </a>
                <p style="font-size: 0.6875rem;color:var(--text-muted);margin:6px 0 0">
                    Turn-by-turn opens Google Maps and needs a data connection.
                </p>
            </div>

            <div class="form-label-sm" id="listHeading">{{ count($markers) }} destinations</div>
            <div id="churchList"></div>
        </aside>

        <div class="giya-map-canvas" id="giyaMap"></div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/leaflet.js') }}?v={{ filemtime(public_path('assets/js/leaflet.js')) }}"></script>
<script src="{{ asset('assets/js/giya-leaflet.js') }}?v={{ filemtime(public_path('assets/js/giya-leaflet.js')) }}"></script>
<script>
(function () {
    const churches = @json($markers);
    const note     = document.getElementById('mapNote');
    const listBox  = document.getElementById('churchList');
    const routeBox = document.getElementById('routeBox');

    let category = 'All';
    let query    = '';
    let distances = {};

    function showNote(message, kind) {
        if (!message || kind === 'clear') { note.style.display = 'none'; return; }
        note.textContent = message;
        note.className = 'map-note' + (kind === 'error' ? ' is-error' : '');
        note.style.display = 'block';
    }

    const map = GiyaLeaflet.browse({
        element: 'giyaMap',
        churches: churches,
        onStatus: showNote,
        onFallback: function () {
            showNote('Local map tiles are not downloaded yet, so tiles are loading from OpenStreetMap. Run "php artisan giya:tiles" to work fully offline.', 'info');
        },
        onLocated: function (me, nearest) {
            distances = {};
            nearest.forEach(function (n) { distances[n.church.id] = n.km; });
            renderList();
            showNote('Showing your location. The nearest destinations are listed first.', 'info');
        },
        onSelect: function (id) {
            const row = document.querySelector('[data-church="' + id + '"]');
            if (row) row.scrollIntoView({ behavior: 'smooth', block: 'center' });
        },
        onRoute: function (stops, totalKm) {
            const wrap = document.getElementById('routeStops');
            document.getElementById('btnClearRoute').style.display = stops.length ? '' : 'none';

            if (!stops.length) { routeBox.style.display = 'none'; return; }

            routeBox.style.display = 'block';
            document.getElementById('routeDistance').textContent = totalKm.toFixed(1) + ' km';
            wrap.innerHTML = stops.map(function (s, i) {
                return '<div class="route-stop">' +
                    '<span class="n">' + (i + 1) + '</span>' +
                    '<span style="flex:1">' + s.name + '</span>' +
                    '<button type="button" class="btn btn-ghost btn-sm" data-drop="' + s.id + '" aria-label="Remove stop">&times;</button>' +
                '</div>';
            }).join('');

            const url = map.externalDirections();
            document.getElementById('btnDirections').href = url || '#';
        }
    });

    function filtered() {
        return churches
            .filter(function (c) { return category === 'All' || c.category === category; })
            .filter(function (c) { return !query || (c.name + ' ' + c.location).toLowerCase().indexOf(query) !== -1; })
            .sort(function (a, b) {
                const da = distances[a.id], db = distances[b.id];
                if (da != null && db != null) return da - db;
                if (da != null) return -1;
                if (db != null) return 1;
                return a.name.localeCompare(b.name);
            });
    }

    function renderList() {
        const list = filtered();
        document.getElementById('listHeading').textContent =
            list.length + ' destination' + (list.length === 1 ? '' : 's');

        if (!list.length) {
            listBox.innerHTML = '<p style="text-align:center;padding:28px 12px;color:var(--text-muted);font-size: 0.8125rem">No destinations match.</p>';
            return;
        }

        listBox.innerHTML = list.map(function (c) {
            return '<div class="history-item" data-church="' + c.id + '" style="cursor:pointer">' +
                '<img src="' + c.image + '" alt="" loading="lazy" ' +
                     'onerror="this.style.background=\'var(--gold-bg)\';this.removeAttribute(\'src\')" ' +
                     'style="width:42px;height:42px;border-radius:10px;object-fit:cover;flex-shrink:0;background:var(--gold-bg)">' +
                '<div style="flex:1;min-width:0">' +
                    '<div style="font-size: 0.8125rem;font-weight:700;color:var(--text)">' + c.name + '</div>' +
                    '<div style="font-size: 0.6875rem;color:var(--text-muted)">' +
                        c.category + ' &middot; ' + c.location +
                        (distances[c.id] != null ? ' &middot; ' + distances[c.id].toFixed(1) + ' km away' : '') +
                    '</div>' +
                '</div>' +
                '<button type="button" class="btn btn-ghost btn-sm" data-add="' + c.id + '" aria-label="Add to route">+</button>' +
            '</div>';
        }).join('');
    }

    document.getElementById('btnLocate').addEventListener('click', function () { map.locate(); });
    document.getElementById('btnClearRoute').addEventListener('click', function () { map.clearRoute(); });

    document.getElementById('mapSearch').addEventListener('input', function () {
        query = this.value.trim().toLowerCase();
        renderList();
    });

    document.querySelectorAll('.cat-chip').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.cat-chip').forEach(function (b) {
                b.classList.remove('is-active');
            });
            this.classList.add('is-active');
            category = this.dataset.cat;
            renderList();
        });
    });

    document.addEventListener('click', function (e) {
        const add = e.target.closest('[data-add]');
        if (add) { e.stopPropagation(); map.addStop(Number(add.dataset.add)); return; }

        const drop = e.target.closest('[data-drop]');
        if (drop) { map.removeStop(Number(drop.dataset.drop)); return; }

        const row = e.target.closest('[data-church]');
        if (row) map.focus(Number(row.dataset.church));
    });

    renderList();
})();
</script>
<style>
    @media (max-width: 900px) {
        .map-grid { grid-template-columns: 1fr !important; }
        .map-sidebar { max-height: none !important; }
        .giya-map-canvas { height: 420px; }
    }
</style>
@endpush
