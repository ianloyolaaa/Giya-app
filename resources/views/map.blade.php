@extends('layouts.app')
@section('title', 'Map')
@section('no-footer', true)

@push('head')
<style>
    body { overflow: hidden; }
    .map-layout { height: calc(100vh - 64px); }
    .map-sidebar-head { padding: 16px; border-bottom: 1px solid var(--border); flex-shrink: 0; }
    .map-cat-pills { display: flex; gap: 6px; flex-wrap: wrap; padding: 10px 16px;
                     border-bottom: 1px solid var(--border); flex-shrink: 0; }
    .map-cat-pill { padding: 5px 12px; border-radius: 999px; font-size: 0.6875rem; font-weight: 600;
                    border: 1.5px solid var(--border); color: var(--text-muted);
                    background: var(--bg); cursor: pointer; white-space: nowrap;
                    transition: all .18s; font-family: var(--font-body); }
    .map-cat-pill.active { background: var(--primary); color: #fff; border-color: var(--primary); }
    .map-church-list { flex: 1; overflow-y: auto; padding: 8px; }
    .map-church-list::-webkit-scrollbar { width: 4px; }
    .map-church-list::-webkit-scrollbar-thumb { background: var(--border); border-radius: 999px; }
    .map-church-item { display: flex; gap: 10px; padding: 11px; border-radius: 12px; cursor: pointer;
                       transition: background .15s; margin-bottom: 4px;
                       border: 1.5px solid transparent; width: 100%; text-align: left;
                       background: transparent; font-family: var(--font-body); }
    .map-church-item:hover { background: var(--bg); }
    .map-church-item.selected { background: rgba(142,59,47,.06); border-color: var(--border); }
    .map-detail { position: absolute; bottom: 0; left: 0; right: 0; z-index: 20; background: #fff;
                  border-radius: 20px 20px 0 0; box-shadow: 0 -4px 24px rgba(0,0,0,.14);
                  padding: 20px; transform: translateY(105%); transition: transform .3s ease; }
    .map-detail.open { transform: translateY(0); }
    .map-legend { position: absolute; bottom: 16px; right: 16px; z-index: 10;
                  background: rgba(255,255,255,.96); border-radius: 12px; padding: 12px 14px;
                  box-shadow: var(--shadow-sm); border: 1px solid var(--border); }
    .map-toolbar { position: absolute; top: 16px; right: 16px; z-index: 10; display: flex; gap: 8px; }
    @media (max-width: 768px) { .map-legend { display: none; } }
</style>
@endpush

@section('content')
<div class="map-layout">

    <aside class="map-sidebar">
        <div class="map-sidebar-head">
            <h1 style="font-family:var(--font-display);font-size: 1.0625rem;margin:0 0 10px">Churches in Metro Cebu</h1>
            <div style="position:relative">
                <img src="{{ asset('images/icons/search.svg') }}" alt="" width="15" height="15"
                     style="position:absolute;left:12px;top:50%;transform:translateY(-50%)">
                <input id="churchSearch" class="giya-input" style="padding-left:38px;padding-block:10px;font-size: 0.8125rem"
                       placeholder="Search churches…" oninput="GiyaMap.search(this.value)" autocomplete="off">
            </div>
        </div>

        <div class="map-cat-pills">
            @foreach ($categories as $cat)
                <button type="button" @class(['map-cat-pill', 'active' => $loop->first])
                        onclick="GiyaMap.filter('{{ $cat }}', this)">{{ $cat }}</button>
            @endforeach
        </div>

        <div class="map-church-list" id="churchList"></div>
    </aside>

    <div class="map-wrap">
        @php
            $points = $churches->map(fn ($c, $i) => [
                'id' => $c->id, 'name' => $c->name,
                'lat' => $c->latitude, 'lng' => $c->longitude,
                'color' => $c->color(), 'label' => '',
            ]);
        @endphp

        <x-offline-map :points="$points" />

        <div class="map-toolbar">
            <button type="button" class="btn btn-primary btn-sm" onclick="GiyaMap.locate()">
                <i class="bi bi-crosshair"></i> My Location
            </button>
        </div>

        <div class="map-detail" id="mapDetail">
            <div style="width:40px;height:4px;border-radius:999px;background:#E0D3C4;margin:0 auto 16px"></div>
            <div class="d-flex align-items-start gap-3 mb-3">
                <span style="width:48px;height:48px;border-radius:14px;background:var(--gold-bg);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <i class="bi bi-building" style="font-size: 1.25rem;color:var(--primary)"></i>
                </span>
                <div style="flex:1;min-width:0">
                    <div id="detailName"   style="font-size: 1rem;font-weight:700;color:var(--text)"></div>
                    <div id="detailMeta"   style="font-size: 0.75rem;color:var(--text-muted);margin-top:2px"></div>
                    <div id="detailRating" style="font-size: 0.75rem;color:var(--gold);font-weight:700;margin-top:4px"></div>
                </div>
                <button type="button" class="input-suffix" style="position:static"
                        onclick="GiyaMap.closeDetail()" aria-label="Close">
                    <i class="bi bi-x" style="font-size: 1.25rem"></i>
                </button>
            </div>
            <p id="detailDesc" style="font-size: 0.75rem;color:var(--text-muted);line-height:1.7;margin:0 0 14px"></p>
            <a href="{{ route('plan.create') }}" class="btn btn-primary btn-sm btn-w-full">
                <i class="bi bi-plus-circle"></i> Add to an Itinerary
            </a>
        </div>

        <div class="map-legend">
            <div style="font-weight:700;font-size: 0.6875rem;margin-bottom:8px">Map Legend</div>
            @foreach ([['#8E3B2F','Basilica / Cathedral'],['#D7A94A','Shrine'],['#4A90D9','Church'],['#9B6B4A','Chapel'],['#6B7280','Heritage'],['#2E86DE','Your location']] as [$color, $label])
                <div class="d-flex align-items-center gap-2 mb-1" style="font-size: 0.6875rem;color:var(--text)">
                    <span style="width:10px;height:10px;border-radius:50%;background:{{ $color }};flex-shrink:0"></span>{{ $label }}
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
/**
 * Map page controller.
 *
 * The map itself is server-rendered SVG (see components/offline-map.blade.php),
 * so this script only handles selection, search, filtering and the detail panel.
 * Nothing here requires a network connection.
 */
const GiyaMap = (function () {
    const churches = @json($churches->map(fn ($c) => [
        'id' => $c->id, 'name' => $c->name, 'location' => $c->location,
        'category' => $c->category, 'rating' => (float) $c->rating,
        'color' => $c->color(), 'visits' => $c->daily_visits,
        'description' => $c->description,
        'open' => $c->opening_time, 'close' => $c->closing_time,
    ])->values());

    let activeCategory = 'All';
    let selectedId = null;

    function visible() {
        return activeCategory === 'All'
            ? churches
            : churches.filter(c => c.category === activeCategory);
    }

    function renderList(list) {
        const box = document.getElementById('churchList');

        if (!list.length) {
            box.innerHTML = '<p style="text-align:center;padding:36px 16px;color:var(--text-muted);font-size: 0.8125rem">' +
                            'No destinations match your search.</p>';
            return;
        }

        box.innerHTML = list.map(function (c) {
            return '<button type="button" class="map-church-item' + (selectedId === c.id ? ' selected' : '') +
                   '" id="ci-' + c.id + '" onclick="GiyaMap.select(' + c.id + ')">' +
                   '<span style="width:42px;height:42px;border-radius:10px;background:var(--gold-bg);display:flex;' +
                   'align-items:center;justify-content:center;flex-shrink:0">' +
                   '<i class="bi bi-building" style="color:' + c.color + '"></i></span>' +
                   '<span style="min-width:0;flex:1">' +
                   '<span style="display:block;font-size: 0.8125rem;font-weight:700;color:var(--text)">' + c.name + '</span>' +
                   '<span style="display:block;font-size: 0.6875rem;color:var(--text-muted);margin-top:2px">' + (c.location || '') + '</span>' +
                   '<span style="display:flex;align-items:center;gap:6px;margin-top:4px">' +
                   '<span style="font-size: 0.6875rem;font-weight:700;color:var(--gold)">★ ' + c.rating.toFixed(1) + '</span>' +
                   '<span class="badge badge-brown">' + c.category + '</span></span></span></button>';
        }).join('');
    }

    function highlightPin(id) {
        document.querySelectorAll('.om-pin').forEach(function (pin) {
            pin.style.filter = (String(pin.dataset.pointId) === String(id))
                ? 'drop-shadow(0 0 6px rgba(215,169,74,.95))' : '';
        });
    }

    function select(id) {
        const c = churches.find(x => x.id === id);
        if (!c) return;

        selectedId = id;
        document.getElementById('detailName').textContent   = c.name;
        document.getElementById('detailMeta').textContent    = (c.location || 'Cebu') + ' · ' + c.category;
        document.getElementById('detailRating').textContent  =
            '★ ' + c.rating.toFixed(1) + (c.visits ? '  ·  ' + c.visits + ' visitors' : '');
        document.getElementById('detailDesc').textContent    =
            (c.open && c.close) ? 'Open ' + c.open + ' – ' + c.close : (c.description || '');
        document.getElementById('mapDetail').classList.add('open');

        document.querySelectorAll('.map-church-item').forEach(el => el.classList.remove('selected'));
        const row = document.getElementById('ci-' + id);
        if (row) { row.classList.add('selected'); row.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); }

        highlightPin(id);
    }

    function closeDetail() {
        document.getElementById('mapDetail').classList.remove('open');
        selectedId = null;
        document.querySelectorAll('.map-church-item').forEach(el => el.classList.remove('selected'));
        highlightPin(null);
    }

    // Clicking a pin in the SVG selects the matching destination.
    document.addEventListener('click', function (event) {
        const pin = event.target.closest('.om-pin');
        if (pin && pin.dataset.pointId) select(Number(pin.dataset.pointId));
    });

    renderList(churches);

    return {
        select: select,
        closeDetail: closeDetail,
        search: function (term) {
            const q = term.toLowerCase().trim();
            renderList(visible().filter(c =>
                c.name.toLowerCase().includes(q) || (c.location || '').toLowerCase().includes(q)));
        },
        filter: function (cat, btn) {
            activeCategory = cat;
            document.querySelectorAll('.map-cat-pill').forEach(p => p.classList.remove('active'));
            btn.classList.add('active');
            this.search(document.getElementById('churchSearch').value);
        },
        locate: function () {
            if (!navigator.geolocation) {
                alert('This browser does not provide location services.');
                return;
            }
            navigator.geolocation.getCurrentPosition(
                function () {
                    // Coordinates are read successfully but the schematic map is not
                    // georeferenced to the device, so we report rather than plot.
                    alert('Location found. Use the destination list to pick your next stop.');
                },
                function () { alert('Unable to determine your location.'); }
            );
        },
    };
})();
</script>
@endpush
