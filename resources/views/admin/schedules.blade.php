@extends('layouts.admin')
@section('title', 'Schedules')
@section('page-title', 'Schedule Management')
@section('page-subtitle', 'Masses, novenas, feast days, and processions')

@section('content')

<div class="d-flex justify-content-end mb-3">
    <button type="button" class="btn btn-primary btn-sm" data-modal-open="addScheduleModal">
        <i class="bi bi-plus-lg"></i> Add Schedule
    </button>
</div>

<div class="card" style="overflow:hidden">
    <div style="overflow-x:auto">
        <table class="giya-table">
            <thead>
                <tr><th>Event</th><th>Destination</th><th>Type</th><th>Date</th><th>Time</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($schedules as $s)
                    <tr>
                        <td style="font-weight:600">{{ $s->event_name }}</td>
                        <td style="color:var(--text-muted);font-size:13px">{{ $s->church->name ?? '—' }}</td>
                        <td><span class="badge badge-brown">{{ $s->event_type }}</span></td>
                        <td style="font-size:13px">
                            {{ $s->schedule_date?->format('M j, Y') ?? ($s->recurrence ?? 'Recurring') }}
                        </td>
                        <td style="font-size:13px;color:var(--text-muted)">
                            {{ $s->start_time ? \Illuminate\Support\Carbon::parse($s->start_time)->format('g:i A') : '—' }}
                        </td>
                        <td>
                            <form method="POST" action="{{ route('admin.schedules.destroy', $s) }}"
                                  onsubmit="return confirm('Remove this schedule?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash3"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-empty-state icon="calendar-event" title="No schedules yet" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3"><x-pagination :paginator="$schedules" /></div>

<div class="modal" id="addScheduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border:none;border-radius:var(--radius-2xl);padding:28px">
            <div class="modal-title"><i class="bi bi-calendar-plus-fill" style="color:var(--primary)"></i> Add Schedule</div>
            <form method="POST" action="{{ route('admin.schedules.store') }}">
                @csrf
                <div class="field">
                    <label class="form-label-sm">Destination</label>
                    <select name="church_id" class="giya-input" required>
                        <option value="">Select a destination…</option>
                        @foreach ($churches as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label class="form-label-sm">Event Name</label>
                    <input type="text" name="event_name" class="giya-input" required maxlength="200">
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <div class="field">
                        <label class="form-label-sm">Type</label>
                        <select name="event_type" class="giya-input" required>
                            @foreach ($types as $t)<option value="{{ $t }}">{{ $t }}</option>@endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label class="form-label-sm">Date</label>
                        <input type="date" name="schedule_date" class="giya-input">
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <div class="field">
                        <label class="form-label-sm">Start Time</label>
                        <input type="time" name="start_time" class="giya-input">
                    </div>
                    <div class="field">
                        <label class="form-label-sm">End Time</label>
                        <input type="time" name="end_time" class="giya-input">
                    </div>
                </div>
                <div class="field">
                    <label class="d-flex align-items-center gap-2" style="font-size:13px;color:var(--text);cursor:pointer">
                        <input type="checkbox" name="is_recurring" value="1"> This event repeats
                    </label>
                </div>
                <div class="modal-actions">
                    <button type="submit" class="btn btn-primary" style="flex:1">Add Schedule</button>
                    <button type="button" class="btn btn-outline" style="flex:1" data-modal-close>Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
