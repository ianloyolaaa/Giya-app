@php
    $user = auth()->user();
    $links = [
        ['route' => 'home',    'label' => 'Home',    'icon' => 'house-fill',      'match' => 'home'],
        ['route' => 'map',     'label' => 'Map',     'icon' => 'map-fill',        'match' => 'map'],
        ['route' => 'plan.hub','label' => 'Plan',    'icon' => 'journal-text',    'match' => 'plan.*'],
        ['route' => 'chatbot', 'label' => 'Chatbot', 'icon' => 'chat-dots-fill',  'match' => 'chatbot'],
        ['route' => 'profile', 'label' => 'Profile', 'icon' => 'person-fill',     'match' => 'profile'],
    ];
@endphp

<nav class="giya-nav">
    <div class="nav-inner">

        <a href="{{ route('home') }}" class="nav-logo">
            <span class="nav-logo-icon">
                <img src="{{ asset('images/logo/giya-icon.svg') }}" alt="" width="32" height="32">
            </span>
            <span class="nav-logo-name">Giya</span>
            <span class="nav-logo-badge d-none d-sm-inline">Metro Cebu</span>
        </a>

        <div class="nav-links">
            @foreach ($links as $link)
                <a href="{{ route($link['route']) }}"
                   @class(['nav-link', 'active' => request()->routeIs($link['match'])])>
                    {{ $link['label'] }}
                </a>
            @endforeach
        </div>

        <div class="nav-right">
            @auth
                <span class="nav-bell" title="Notifications">
                    <img src="{{ asset('images/icons/bell.svg') }}" alt="Notifications" width="16" height="16">
                </span>

                <a href="{{ route('profile') }}" class="nav-avatar-wrap">
                    <span class="nav-avatar">{{ $user->initials() }}</span>
                    <span class="nav-username">{{ $user->firstName() }}</span>
                </a>

                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="btn-signout">Sign Out</button>
                </form>
            @endauth

            <button type="button" class="hamburger-btn" id="navHamburger" aria-label="Toggle navigation">
                <i class="bi bi-list" style="font-size:20px;color:var(--gold)"></i>
            </button>
        </div>
    </div>

    <div class="mobile-menu" id="navMobileMenu">
        <div class="mobile-nav-links">
            @foreach ($links as $link)
                <a href="{{ route($link['route']) }}"
                   @class(['mobile-nav-link', 'active' => request()->routeIs($link['match'])])>
                    <i class="bi bi-{{ $link['icon'] }} me-2"></i>{{ $link['label'] }}
                </a>
            @endforeach

            @auth
                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="mobile-nav-link w-100 text-start bg-transparent border-0">
                        <i class="bi bi-box-arrow-right me-2"></i>Sign Out
                    </button>
                </form>
            @endauth
        </div>
    </div>
</nav>
