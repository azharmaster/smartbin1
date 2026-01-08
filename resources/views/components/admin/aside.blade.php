<aside class="main-sidebar sidebar-dark-primary elevation-4" style="background: linear-gradient(135deg, #103913ff, #1f6423ff)">
    <!-- Brand Logo -->
<!-- <a href="{{ route('admin.mainmenu') }}" class="brand-link brand-app text-center"> -->
    <a href="{{ route('admin.dashboard') }}" class="brand-link brand-app text-center">

    <span class="brand-text">{{ env('APP_NAME') }}</span>
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
        // ✅ FIX: Support both string and array for route_active
        $isActive = false;
        if (!empty($route['route_active'])) {
            if (is_array($route['route_active'])) {
                foreach ($route['route_active'] as $pattern) {
                    if (request()->routeIs($pattern)) {
                        $isActive = true;
                        break;
                    }
                }
            } else {
                $isActive = request()->routeIs($route['route_active']);
            }
        }

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

.main-sidebar {
    background-color: #0f3d1f; /* dark green */
    box-shadow: inset -1px 0 0 rgba(255,255,255,0.08);
}

/* Fix AdminLTE stacking issues */
.main-sidebar,
.main-sidebar * {
    box-sizing: border-box;
}

.brand-link {
    background-color: rgba(0,0,0,0.15);
    border-bottom: 1px solid rgba(255,255,255,0.1);
    padding: 1rem;
}

.brand-text {
    color: #ffffff !important;
    font-weight: 700;
    letter-spacing: 0.4px;
    font-size: 1.05rem;
}

.nav-sidebar .nav-link {
    color: rgba(255,255,255,0.85);
    border-radius: 8px;
    margin: 4px 10px;
    padding: 10px 14px;
    transition: background 0.2s ease, transform 0.15s ease;
}

.nav-sidebar .nav-link p {
    margin: 0;
    font-size: 1.0rem;
    font-weight: 500;
}

.nav-sidebar .nav-icon {
    opacity: 0.9;
    margin-right: 10px;
    transition: opacity 0.2s ease;
}

.nav-sidebar .nav-link:hover .nav-icon {
    opacity: 1;
}

/* =====================================================
   HOVER
===================================================== */

.nav-sidebar .nav-link:hover {
    background: rgba(255,255,255,0.08);
    transform: translateX(3px);
}

/* =====================================================
   ACTIVE STATE
===================================================== */

.nav-sidebar .nav-link.active {
    background: rgba(255,255,255,0.15);
    font-weight: 600;
    box-shadow: inset 3px 0 0 #4ade80; /* green indicator */
}

.nav-sidebar .nav-link.active p {
    color: #ffffff;
}

/* =====================================================
   DROPDOWN
===================================================== */

.nav-treeview {
    padding-left: 10px;
}

.nav-treeview .nav-link {
    font-size: 0.88rem;
    opacity: 0.8;
}

.nav-treeview .nav-link.active {
    opacity: 1;
}

/* Dropdown arrow */
.nav-sidebar .nav-link .fa-angle-left {
    transition: transform 0.2s ease;
    opacity: 0.7;
}

.menu-open > .nav-link .fa-angle-left {
    transform: rotate(-90deg);
    opacity: 1;
}


//sidebar title
.brand-app {
    text-decoration: none !important;
    display: flex;
    align-items: center;
    justify-content: center;

    padding: 16px 12px;
    background: rgba(0,0,0,0.15);
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.brand-app:hover {
    background: rgba(255,255,255,0.06);
}

/* App name text */
.brand-app .brand-text {
    color: #ffffff;
    font-size: 1.15rem;
    font-weight: 700;
    letter-spacing: 0.8px;
    text-transform: uppercase; /* optional, looks clean */
    text-decoration: none !important;
}

/* Remove AdminLTE underline edge cases */
.brand-link,
.brand-link:hover,
.brand-link:focus {
    text-decoration: none !important;
}

/* Neutralize ALL active states */
.nav-sidebar .nav-link.active,
.nav-sidebar .menu-open > .nav-link,
.nav-sidebar .nav-treeview .nav-link.active {
    background: transparent !important;
    box-shadow: none !important;
}

/* Active text = same as normal text */
.nav-sidebar .nav-link.active p,
.nav-sidebar .nav-treeview .nav-link.active p {
    color: rgba(255,255,255,0.85);
    font-weight: 500;
}

/* =====================================================
   HOVER EFFECT ONLY (DARKEN)
===================================================== */

.nav-sidebar .nav-link:hover {
    background: rgba(0,0,0,0.25);
}

/* Child hover slightly lighter */
.nav-sidebar .nav-treeview .nav-link:hover {
    background: rgba(0,0,0,0.35);
}

/* Icons follow text */
.nav-sidebar .nav-link:hover .nav-icon {
    opacity: 1;
}

/* =====================================================
   KEEP DROPDOWN OPEN WITHOUT STYLING
===================================================== */

.menu-open > .nav-link {
    background: transparent !important;
}
</style>

<!-- /.sidebar -->
</aside>
