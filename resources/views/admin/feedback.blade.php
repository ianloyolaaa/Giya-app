@extends('layouts.admin')
@section('title', 'Feedback')
@section('page-title', 'Feedback Management')
@section('page-subtitle', 'Reviews and ratings submitted by pilgrims')

@section('content')

<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:20px">
    @foreach ([
        ['chat-dots-fill', 'Total',    $summary['total']],
        ['hourglass-split','Pending',  $summary['pending']],
        ['check-circle-fill','Approved',$summary['approved']],
        ['flag-fill',      'Flagged',  $summary['flagged']],
    ] as [$icon, $label, $value])
        <div class="card card-body">
            <i class="bi bi-{{ $icon }}" style="font-size:18px;color:var(--gold)"></i>
            <div style="font-family:var(--font-display);font-size:22px;font-weight:700;margin-top:8px">{{ $value }}</div>
            <div style="font-size:12px;color:var(--text-muted)">{{ $label }}</div>
        </div>
    @endforeach
</div>

<div class="d-flex gap-2 mb-3 flex-wrap">
    @foreach (['All', 'Pending', 'Approved', 'Flagged'] as $s)
        <a href="{{ route('admin.feedback', ['status' => $s]) }}"
           @class(['btn', 'btn-sm', 'btn-primary' => $status === $s, 'btn-outline' => $status !== $s])>{{ $s }}</a>
    @endforeach
</div>

@forelse ($feedback as $item)
    <div class="card card-body mb-2">
        <div class="d-flex align-items-start gap-3 flex-wrap">
            <span class="nav-avatar" style="border-color:var(--primary);flex-shrink:0">
                {{ strtoupper(substr($item->user->name ?? 'A', 0, 1)) }}
            </span>
            <div style="flex:1;min-width:200px">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span style="font-size:14px;font-weight:700;color:var(--text)">{{ $item->user->name ?? 'Deleted user' }}</span>
                    <x-stars :rating="$item->rating ?? 0" />
                    <span class="badge status-{{ $item->status === 'Approved' ? 'Completed' : ($item->status === 'Flagged' ? 'Draft' : 'Upcoming') }}">
                        {{ $item->status }}
                    </span>
                </div>
                <div style="font-size:12px;color:var(--text-muted);margin-top:2px">
                    {{ $item->church->name ?? 'Unknown destination' }} · {{ $item->created_at?->diffForHumans() }}
                </div>
                @if ($item->comment)
                    <p style="font-size:13px;color:var(--text);line-height:1.7;margin:8px 0 0">{{ $item->comment }}</p>
                @endif
            </div>
            <form method="POST" action="{{ route('admin.feedback.update', $item) }}" class="d-flex gap-2">
                @csrf @method('PATCH')
                <select name="status" class="giya-input" style="width:auto;padding:8px 12px;font-size:13px">
                    @foreach (['Pending', 'Approved', 'Flagged'] as $s)
                        <option value="{{ $s }}" @selected($item->status === $s)>{{ $s }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary btn-sm">Save</button>
            </form>
        </div>
    </div>
@empty
    <div class="card"><x-empty-state icon="chat-dots" title="No feedback yet" /></div>
@endforelse

<div class="mt-3"><x-pagination :paginator="$feedback" /></div>
@endsection
