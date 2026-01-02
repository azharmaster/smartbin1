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
    overflow: hidden;
    background: linear-gradient(-45deg, #a1ef7a, #99eb99, #4cdc4c, #99eb99, #61ff81);
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
    box-shadow: 0 0 15px rgba(255,255,255,0.15);
    transition: background 0.3s, box-shadow 0.3s;
    z-index: 1;
}

/* -----------------------------
   Active Icon Pulse
---------------------------------*/
.nav-sidebar .nav-link.active .nav-icon {
    animation: pulse-icon 1.5s infinite ease-in-out;
}

@keyframes pulse-icon {
    0% { transform: scale(1); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
}

/* -----------------------------
   Hover effect
---------------------------------*/
.nav-sidebar .nav-link:hover {
    background: rgba(255,255,255,0.05);
}

/* =====================================================
   🔥 TEXT COLOR & FONT CUSTOMIZATION (NEW)
   ✔ No existing code changed
   ✔ Only text styling added
===================================================== */

/* Sidebar text color */
.nav-sidebar .nav-link p,
.nav-sidebar .nav-treeview .nav-link p {
    color: #414a4c;              /* Change text color */
    font-family: 'Poppins', sans-serif; /* Change font */
    font-weight: 500;            /* Slightly bold */
    letter-spacing: 0.3px;       /* Clean spacing */
}

/* Brand text color & font */
.brand-text {
    color: #ffffff !important;
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
}

/* Active link text color */
.nav-sidebar .nav-link.active p {
    color: #ffffff;
    text-shadow: 0 0 8px rgba(255,255,255,0.7);
}

/* Hover text color */
.nav-sidebar .nav-link:hover p {
    color: #414a4c;
}

/* Dropdown arrow color */
.nav-sidebar .nav-link .fa-angle-left {
    color: #ffffff;
}

/* =====================================================
   🔴 LOGOUT BUTTON CUSTOM COLOR
   Targets ONLY the logout button inside sidebar
===================================================== */

/* Default logout button */
.nav-sidebar form .btn-danger {
    background: linear-gradient #4cdc4c; /* gradient red */
    border: none;
    color: #ffffff;
    font-weight: 600;
    letter-spacing: 0.4px;
    transition: all 0.3s ease;
}

/* Logout icon color */
.nav-sidebar form .btn-danger i {
    color: #ffffff !important;
}

/* Hover effect */
.nav-sidebar form .btn-danger:hover {
    background: linear-gradient(135deg, #4cdc4c, #4cdc4c);
    box-shadow: 0 0 15px #4cdc4c;
    transform: translateY(-2px);
}

/* Click / active effect */
.nav-sidebar form .btn-danger:active {
    transform: scale(0.97);
    box-shadow: 0 0 8px rgba(255, 75, 43, 0.6);
}

/* Remove default bootstrap focus outline */
.nav-sidebar form .btn-danger:focus {
    box-shadow: none;
}


</style>

<!-- /.sidebar -->
</aside>
