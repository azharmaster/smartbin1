<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ route('admin.mainmenu') }}" class="brand-link text-center">
        <span class="brand-text font-weight-light">{{ env('APP_NAME') }}</span>
    </a>

    <!-- Sidebar -->
<div class="sidebar">

    <!-- Sidebar Menu -->
    <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column"
            data-widget="treeview"
            role="menu"
            data-accordion="false">

           @foreach ($routes as $route)
    @php
        $isActive = request()->routeIs($route['route_active'] ?? '');
        $iconColor = $route['color'] ?? '#6c757d';
    @endphp

    {{-- LOGOUT BUTTON --}}
    @if (!empty($route['is_logout']) && $route['is_logout'])
        <li class="nav-item mt-3">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-link btn btn-danger w-100 text-start">
                    <i class="{{ $route['icon'] }} me-2" style="color: {{ $iconColor }}"></i>
                    {{ $route['label'] }}
                </button>
            </form>
        </li>

    {{-- SINGLE LINK --}}
    @elseif (!$route['is_dropdown'])
        <li class="nav-item">
            <a href="{{ route($route['route_name']) }}"
               class="nav-link {{ $isActive ? 'active' : '' }}">
                <i class="nav-icon {{ $route['icon'] }}" style="color: {{ $iconColor }};"></i>
                <p>{{ $route['label'] }}</p>
            </a>
        </li>

    {{-- DROPDOWN --}}
    @else
        <li class="nav-item {{ $isActive ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ $isActive ? 'active' : '' }}">
                <i class="nav-icon {{ $route['icon'] }}" style="color: {{ $iconColor }}"></i>
                <p>
                    {{ $route['label'] }}
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                @foreach ($route['dropdown'] as $dropdownItem)
                    @php
                        $subActive = request()->routeIs($dropdownItem['route_active'] ?? '');
                        $subColor = $dropdownItem['color'] ?? $iconColor;
                    @endphp
                    <li class="nav-item">
                        <a href="{{ route($dropdownItem['route_name']) }}"
                           class="nav-link {{ $subActive ? 'active' : '' }}">
                            <i class="nav-icon {{ $dropdownItem['icon'] }}" style="color: {{ $subColor }}"></i>
                            <p>{{ $dropdownItem['label'] }}</p>
                        </a>
                    </li>
                @endforeach
            </ul>
        </li>
    @endif
@endforeach


        </ul>
    </nav>
    <!-- /.sidebar-menu -->
</div>
<style>
/* -----------------------------
   Live Gradient Sidebar
---------------------------------*/
.main-sidebar {
    position: relative;
    overflow: hidden; /* hide overflow for animation */
    background: linear-gradient(-45deg, #414a4c, #3b444b, #8a8583, #232b2b, #414a4c);
    background-size: 600% 600%;
    animation: live-gradient 10s ease infinite;
    transition: background 0.3s;
}

/* Animate the gradient */
@keyframes live-gradient {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* -----------------------------
   Floating overlay shapes
---------------------------------*/
.main-sidebar::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0) 70%);
    animation: flow 12s linear infinite;
    pointer-events: none;
    z-index: 0;
}

/* Flowing diagonal movement */
@keyframes flow {
    0%   { transform: rotate(0deg) translate(0, 0); }
    50%  { transform: rotate(15deg) translate(20%, 20%); }
    100% { transform: rotate(0deg) translate(0, 0); }
}

/* -----------------------------
   Active Link Glow
---------------------------------*/
.nav-sidebar .nav-link.active {
    background: rgba(255,255,255,0.15);
    border-radius: 6px;
    box-shadow: 0 0 15px rgba(255,255,255,0.6);
    transition: background 0.3s, box-shadow 0.3s;
    z-index: 1;
}

.nav-sidebar .nav-link.active:hover {
    background: rgba(255,255,255,0.25);
    box-shadow: 0 0 20px rgba(255,255,255,0.8);
}

/* -----------------------------
   Active Icon Pulse
---------------------------------*/
.nav-sidebar .nav-link.active .nav-icon {
    animation: pulse-icon 1.5s infinite ease-in-out;
}

@keyframes pulse-icon {
    0% { transform: scale(1); color: #ffffff; text-shadow: 0 0 0px rgba(255,255,255,0); }
    50% { transform: scale(1.2); color: #ffffff; text-shadow: 0 0 12px rgba(255,255,255,0.6); }
    100% { transform: scale(1); color: #ffffff; text-shadow: 0 0 0px rgba(255,255,255,0); }
}

/* -----------------------------
   Hover effect for all links
---------------------------------*/
.nav-sidebar .nav-link:hover {
    background: rgba(255,255,255,0.05);
    transition: all 0.3s;
}

.nav-sidebar .nav-link .nav-icon:hover {
    transform: scale(1.15);
    transition: transform 0.3s;
}

</style>





<!-- /.sidebar -->
</aside>
