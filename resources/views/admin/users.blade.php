@extends('layouts.admin')
@section('title', 'Users')
@section('page-title', 'User Management')
@section('page-subtitle', 'Monitor registered pilgrims and administrators')

@section('content')

<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:20px">
    @foreach ([
        ['people-fill', 'Total Users',  $summary['total']],
        ['shield-fill', 'Administrators', $summary['admins']],
        ['gem',         'Premium Members', $summary['premium']],
    ] as [$icon, $label, $value])
        <div class="card card-body">
            <i class="bi bi-{{ $icon }}" style="font-size:20px;color:var(--gold)"></i>
            <div style="font-family:var(--font-display);font-size:22px;font-weight:700;color:var(--text);margin-top:8px">
                {{ number_format($value) }}
            </div>
            <div style="font-size:12px;color:var(--text-muted)">{{ $label }}</div>
        </div>
    @endforeach
</div>

<form method="GET" action="{{ route('admin.users') }}" class="d-flex gap-3 flex-wrap mb-3">
    <div style="flex:1;min-width:220px;position:relative">
        <img src="{{ asset('images/icons/search.svg') }}" alt="" width="15" height="15"
             style="position:absolute;left:14px;top:50%;transform:translateY(-50%)">
        <input type="search" name="search" value="{{ $search }}" class="giya-input"
               style="padding-left:40px" placeholder="Search name or email…">
    </div>
    <div class="d-flex gap-2">
        @foreach (['All', 'User', 'Admin'] as $r)
            <a href="{{ route('admin.users', ['role' => $r, 'search' => $search]) }}"
               @class(['btn', 'btn-sm', 'btn-primary' => $role === $r, 'btn-outline' => $role !== $r])>{{ $r }}</a>
        @endforeach
    </div>
    <button type="submit" class="btn btn-primary btn-sm">Search</button>
</form>

<div class="card" style="overflow:hidden">
    <div style="overflow-x:auto">
        <table class="giya-table">
            <thead>
                <tr>
                    <th>Name</th><th>Email</th><th>Role</th>
                    <th>Pilgrimages</th><th>Churches</th><th>Joined</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $u)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="nav-avatar" style="width:30px;height:30px;font-size:12px;border-color:var(--primary)">
                                    {{ $u->initials() }}
                                </span>
                                <span style="font-weight:600">{{ $u->name }}</span>
                            </div>
                        </td>
                        <td style="color:var(--text-muted);font-size:13px">{{ $u->email }}</td>
                        <td><span @class(['badge', 'badge-primary' => $u->isAdmin(), 'badge-brown' => ! $u->isAdmin()])>{{ ucfirst($u->role) }}</span></td>
                        <td>{{ $u->total_pilgrimages }}</td>
                        <td>{{ $u->total_churches_visited }}</td>
                        <td style="color:var(--text-muted);font-size:13px">{{ $u->created_at?->format('M j, Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-empty-state icon="people" title="No users found" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3"><x-pagination :paginator="$users" /></div>
@endsection
