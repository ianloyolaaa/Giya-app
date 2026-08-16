@extends('layouts.admin')
@section('title', 'Destinations')
@section('page-title', 'Destination Management')
@section('page-subtitle', 'Churches, shrines, and heritage sites in Metro Cebu')

@section('content')

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
    <form method="GET" action="{{ route('admin.destinations') }}" class="d-flex gap-2 flex-wrap" style="flex:1">
        <div style="flex:1;min-width:200px;position:relative">
            <img src="{{ asset('images/icons/search.svg') }}" alt="" width="15" height="15"
                 style="position:absolute;left:14px;top:50%;transform:translateY(-50%)">
            <input type="search" name="search" value="{{ $search }}" class="giya-input"
                   style="padding-left:40px" placeholder="Search destinations…">
        </div>
        <select name="category" class="giya-input" style="max-width:180px" onchange="this.form.submit()">
            @foreach ($categories as $cat)
                <option value="{{ $cat }}" @selected($category === $cat)>{{ $cat }}</option>
            @endforeach
        </select>
    </form>
    <button type="button" class="btn btn-primary btn-sm" data-modal-open="addChurchModal">
        <i class="bi bi-plus-lg"></i> Add Destination
    </button>
</div>

<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px" class="dest-grid">
    @forelse ($churches as $church)
        <article class="card" style="overflow:hidden">
            <div style="height:5px;background:{{ $church->color() }}"></div>
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <span class="badge badge-brown">{{ $church->category }}</span>
                    <span @class(['badge', 'badge-green' => $church->is_active, 'badge-muted' => ! $church->is_active])>
                        {{ $church->is_active ? 'Active' : 'Hidden' }}
                    </span>
                </div>

                <h3 style="font-size: 0.9375rem;font-weight:700;color:var(--text);margin:0 0 4px">{{ $church->name }}</h3>
                <p style="font-size: 0.75rem;color:var(--text-muted);margin:0 0 10px">
                    <i class="bi bi-geo-alt-fill" style="font-size: 0.625rem"></i> {{ $church->location }}
                </p>

                <div class="d-flex align-items-center gap-3 mb-3" style="font-size: 0.75rem;color:var(--text-muted)">
                    <span><i class="bi bi-star-fill" style="color:var(--gold)"></i> {{ number_format($church->rating, 1) }}</span>
                    @if ($church->is_featured)<span class="badge badge-amber">Featured</span>@endif
                </div>

                <form method="POST" action="{{ route('admin.destinations.toggle', $church) }}"
                      style="padding-top:12px;border-top:1px solid var(--border-light)">
                    @csrf @method('PATCH')
                    <button type="submit" @class(['btn', 'btn-sm', 'btn-w-full', 'btn-outline' => $church->is_active, 'btn-primary' => ! $church->is_active])>
                        {{ $church->is_active ? 'Hide from app' : 'Make visible' }}
                    </button>
                </form>
            </div>
        </article>
    @empty
        <div style="grid-column:1/-1">
            <div class="card"><x-empty-state icon="building" title="No destinations found" /></div>
        </div>
    @endforelse
</div>

<div class="mt-3"><x-pagination :paginator="$churches" /></div>

{{-- Add destination --}}
<div class="modal" id="addChurchModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border:none;border-radius:var(--radius-2xl);padding:28px">
            <div class="modal-title"><i class="bi bi-plus-circle-fill" style="color:var(--primary)"></i> Add Destination</div>
            <form method="POST" action="{{ route('admin.destinations.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="field">
                    <label class="form-label-sm">Name</label>
                    <input type="text" name="name" class="giya-input" required maxlength="200">
                    @error('name')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <div class="field">
                        <label class="form-label-sm">Location</label>
                        <input type="text" name="location" class="giya-input" required placeholder="Cebu City">
                    </div>
                    <div class="field">
                        <label class="form-label-sm">Category</label>
                        <select name="category" class="giya-input" required>
                            @foreach (array_slice($categories, 1) as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <div class="field">
                        <label class="form-label-sm">Latitude</label>
                        <input type="number" step="0.00000001" name="latitude" class="giya-input" placeholder="10.2939">
                    </div>
                    <div class="field">
                        <label class="form-label-sm">Longitude</label>
                        <input type="number" step="0.00000001" name="longitude" class="giya-input" placeholder="123.9019">
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <div class="field">
                        <label class="form-label-sm">Opens</label>
                        <input type="time" name="opening_time" class="giya-input">
                    </div>
                    <div class="field">
                        <label class="form-label-sm">Closes</label>
                        <input type="time" name="closing_time" class="giya-input">
                    </div>
                </div>
                <div class="field">
                    <label class="form-label-sm">Description</label>
                    <textarea name="description" class="giya-input" rows="2"></textarea>
                </div>
                <div class="modal-actions">
                    <button type="submit" class="btn btn-primary" style="flex:1">Add Destination</button>
                    <button type="button" class="btn btn-outline" style="flex:1" data-modal-close>Cancel</button>
                </div>
            <div class="field">
                    <label class="form-label-sm">Church Photo</label>
                    <input type="file" name="photo" class="giya-input" accept="image/jpeg,image/png,image/webp">
                    <input type="text" name="caption" class="giya-input" style="margin-top:8px"
                           placeholder="Photo caption (optional)" maxlength="255">
                    @error('photo')<span class="field-error">{{ $message }}</span>@enderror
                </div>
            </form>
        </div>
    </div>
</div>

@push('head')
<style>
    @media (max-width: 1100px) { .dest-grid { grid-template-columns: repeat(2, 1fr) !important; } }
    @media (max-width: 700px)  { .dest-grid { grid-template-columns: 1fr !important; } }
</style>
@endpush

@push('scripts')
@if ($errors->any())
<script>GiyaUI.Modal.open('addChurchModal');</script>
@endif
@endpush
@endsection
