<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <!-- <a href="" class="brand-link brand-app text-center">
        <span class="brand-text">{{ env('APP_NAME') }}</span>
    </a> -->
    <a href="" class="brand-link brand-app text-center d-flex align-items-center justify-content-center">
        <img src="{{ asset('uploads/images/logo_white.png') }}"
             alt="Logo" class="brand-logo-img" style="height:20px;">
        <img src="{{ asset('uploads/images/smartbin-tex.png') }}"
             alt="Text" class="brand-text-img ms-2" style="height:20px;">
    </a>

    <!-- Sidebar -->
    <nav class="mt-2 sidebar">
        <ul class="nav nav-pills nav-sidebar flex-column"
            role="menu"
            data-widget="treeview"
            data-accordion="false">

            @foreach ($routes as $route)
                @php
                    $iconColor = $route['color'] ?? '#6c757d';

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
                @endphp

                @if(($route['type'] ?? null) === 'section')
                    <li class="nav-header text-uppercase">{{ $route['label'] }}</li>

                @elseif(($route['type'] ?? null) === 'divider')
                    <li class="nav-item"><hr class="my-2"></li>

                @elseif(!empty($route['is_logout']))
                    <li class="nav-item mt-3">
                        <form method="POST" action="{{ route('logout') }}" class="w-100">
                            @csrf
                            <a href="#"
                               class="nav-link logout-link"
                               onclick="event.preventDefault(); this.closest('form').submit();">
                                <i class="nav-icon {{ $route['icon'] }}"></i>
                                <p>{{ $route['label'] }}</p>
                            </a>
                        </form>
                    </li>

                @else
                    <li class="nav-item">
                        <a href="{{ route($route['route_name']) }}" class="nav-link {{ $isActive ? 'active' : '' }}">
                            <i class="nav-icon {{ $route['icon'] }}" style="color: {{ $iconColor }}"></i>
                            <p>{{ $route['label'] }}</p>
                        </a>
                    </li>
                @endif

            @endforeach
        </ul>
    </nav>
</aside>
<!-- /.sidebar-menu -->

<style>
/* ===== Brand logo behavior ===== */

/* Default: show both */
.brand-logo-img {
    display: inline-block;
}

.brand-text-img {
    display: inline-block;
}

/* Sidebar collapsed: hide text image */
body.sidebar-collapse .brand-text-img {
    display: none !important;
}

/* Center logo when collapsed */
body.sidebar-collapse .brand-link {
    justify-content: center !important;
}

/* Base sidebar */
.main-sidebar {
    background: linear-gradient(135deg, #103913ff, #1f6423ff);
    transition: width 0.25s ease-in-out;
}

/* Links */
.nav-sidebar .nav-link {
    margin: 3px 8px;
    padding: 7px 10px;
}

.nav-sidebar .nav-link p {
    font-size: 0.90rem;
    font-weight: 400;
}

.nav-sidebar .nav-icon {
    font-size: 0.85rem;
    margin-right: 8px;
}

/* Nav header */
.nav-header {
    font-size: 0.7rem;
    font-weight: 500;
    letter-spacing: 0.06em;
    padding: 8px 16px 4px;
    margin-top: 6px;
    color: rgba(255,255,255,0.6);
}

/* Brand text */
.brand-app .brand-text {
    font-size: 0.9rem;
    font-weight: 600;
}

/* Remove active highlight */
.nav-sidebar .nav-link.active,
.nav-sidebar .nav-link.active p {
    background: transparent !important;
}

/* Collapsed sidebar */
body.sidebar-collapse .main-sidebar {
    width: 4.6rem !important;
}

body.sidebar-collapse .nav-sidebar .nav-link p,
body.sidebar-collapse .nav-header,
body.sidebar-collapse .brand-text,
body.sidebar-collapse .sidebar hr {
    display: none !important;
}

body.sidebar-collapse .nav-sidebar .nav-link {
    justify-content: center;
    padding: 10px 0;
}

body.sidebar-collapse .nav-sidebar .nav-icon {
    margin-right: 0;
    font-size: 1rem;
}

/* Page Indicator */
.nav-sidebar .nav-link.active::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
    width: 4px;
    background: #717b75;
    border-radius: 0 4px 4px 0;
    transition: background 0.3s ease, height 0.3s ease;
}

.logout-link {
    background: transparent;
    border: none;
    color: #ffffff;
}

.logout-link i {
    color: #ffffff;
}

.logout-link:hover {
    background: rgba(255, 255, 255, 0.08);
    color: #ffffff;
}

.logout-link,
.logout-link:hover,
.logout-link:focus,
.logout-link:active {
    color: #ffffff !important;
    text-decoration: none;
}

.logout-link i,
.logout-link:hover i {
    color: #ffffff !important;
}

/* ============================= */
/* ✅ SCROLLABLE SIDEBAR (NEW)   */
/* ============================= */

.main-sidebar {
    height: 100vh;
    overflow: hidden;
}

.main-sidebar .sidebar {
    height: calc(100vh - 70px); /* adjust if brand height changes */
    overflow-y: auto;
    overflow-x: hidden;
    padding-bottom: 20px;
}

/* Optional: nice scrollbar */
.main-sidebar .sidebar::-webkit-scrollbar {
    width: 6px;
}

.main-sidebar .sidebar::-webkit-scrollbar-thumb {
    background-color: rgba(255, 255, 255, 0.25);
    border-radius: 10px;
}

.main-sidebar .sidebar::-webkit-scrollbar-track {
    background: transparent;
}
</style>

<!-- /.sidebar -->
