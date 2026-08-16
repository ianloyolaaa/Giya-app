@props(['icon' => 'inbox', 'title' => 'Nothing here yet', 'desc' => null])

<div class="empty-state">
    <div class="empty-icon"><i class="bi bi-{{ $icon }}" style="color:var(--gold)"></i></div>
    <div class="empty-title">{{ $title }}</div>
    @if ($desc)
        <div class="empty-desc">{{ $desc }}</div>
    @endif
    {{ $slot }}
</div>
