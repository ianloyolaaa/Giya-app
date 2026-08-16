@extends('layouts.app')
@section('title', 'Giya AI')

@section('content')
<div class="page-wrap" style="max-width:900px">

    <div class="d-flex align-items-center gap-3 mb-4">
        <span style="width:52px;height:52px;border-radius:16px;background:var(--primary);display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <i class="bi bi-chat-dots-fill" style="font-size:22px;color:var(--gold)"></i>
        </span>
        <div>
            <h1 style="font-family:var(--font-display);font-size:24px;margin:0">Giya AI Assistant</h1>
            <div class="d-flex align-items-center gap-2" style="margin-top:2px">
                <span style="width:8px;height:8px;border-radius:50%;background:var(--gold);display:inline-block"></span>
                <span style="font-size:13px;color:var(--text-muted)">Pilgrimage guide for Metro Cebu</span>
            </div>
        </div>
    </div>

    <div class="card" style="overflow:hidden">
        {{-- Preview conversation, rendered statically until the assistant ships --}}
        <div style="padding:24px;display:flex;flex-direction:column;gap:16px;background:#fff">
            <div class="d-flex gap-3">
                <span style="width:36px;height:36px;border-radius:50%;background:var(--gold-bg);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <i class="bi bi-stars" style="color:var(--primary);font-size:15px"></i>
                </span>
                <div class="chat-bubble chat-bubble-bot">Maayong buntag! I am Giya AI, your pilgrimage companion for Metro Cebu. Soon you will be able to ask me about churches, mass schedules, and pilgrimage routes right here.</div>
            </div>

            <div class="d-flex gap-3 flex-row-reverse">
                <span class="nav-avatar" style="flex-shrink:0">{{ auth()->user()->initials() }}</span>
                <div class="chat-bubble chat-bubble-user">What churches are near me?</div>
            </div>

            <div class="d-flex gap-3">
                <span style="width:36px;height:36px;border-radius:50%;background:var(--gold-bg);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <i class="bi bi-stars" style="color:var(--primary);font-size:15px"></i>
                </span>
                <div class="chat-bubble chat-bubble-bot" style="opacity:.55">
                    <span class="d-inline-flex align-items-center gap-2">
                        <i class="bi bi-hourglass-split"></i> This capability is still being built…
                    </span>
                </div>
            </div>
        </div>

        {{-- Coming-soon notice replaces the composer --}}
        <div style="padding:24px;border-top:1px solid var(--border);background:var(--bg);text-align:center">
            <span class="badge badge-amber mb-3" style="padding:5px 14px">
                <i class="bi bi-cone-striped"></i> Coming Soon
            </span>
            <p style="font-size:14px;color:var(--text-muted);line-height:1.75;max-width:520px;margin:0 auto 20px">
                The conversational assistant is under development. In the meantime you can
                browse every destination on the map or build a route in the Plan Hub —
                both work fully offline.
            </p>
            <div class="d-flex gap-2 justify-content-center flex-wrap">
                <a href="{{ route('map') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-map-fill"></i> Browse the Map
                </a>
                <a href="{{ route('plan.hub') }}" class="btn btn-outline btn-sm">
                    <i class="bi bi-journal-text"></i> Open Plan Hub
                </a>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <h2 class="section-title" style="font-size:18px">What Giya AI will help with</h2>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px">
            @foreach ([
                ['clock-fill',      'Church Hours',    'Opening and closing times for every destination.'],
                ['calendar-check',  'Mass Schedules',  'Weekday, Saturday, and Sunday mass times.'],
                ['signpost-2-fill', 'Route Guidance',  'Suggested order and travel time between stops.'],
                ['universal-access','Accessibility',   'Wheelchair access and visitor guidelines.'],
            ] as [$icon, $title, $desc])
                <div class="card card-body" style="padding:16px">
                    <i class="bi bi-{{ $icon }}" style="font-size:20px;color:var(--gold)"></i>
                    <div style="font-size:14px;font-weight:700;color:var(--text);margin-top:8px">{{ $title }}</div>
                    <div style="font-size:12px;color:var(--text-muted);line-height:1.6;margin-top:4px">{{ $desc }}</div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
