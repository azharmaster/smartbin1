<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="" class="brand-link brand-app d-flex align-items-center justify-content-center">
        <img src="{{ asset('uploads/images/logo_white.png') }}"
             alt="Logo" class="brand-logo-img" style="height:22px;">
        <img src="{{ asset('uploads/images/smartbin-tex.png') }}"
             alt="Text" class="brand-text-img ms-2" style="height:22px;">
    </a>

    <!-- Sidebar -->
    <nav class="mt-3 sidebar">
        <ul class="nav nav-pills nav-sidebar flex-column"
            role="menu"
            data-widget="treeview"
            data-accordion="false">

            @foreach ($routes as $route)
                @php
                    $iconColor = $route['color'] ?? '#a78bfa';

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
                    <li class="nav-header">{{ $route['label'] }}</li>

                @elseif(($route['type'] ?? null) === 'divider')
                    <li class="nav-item"><hr class="nav-divider"></li>

                @elseif(!empty($route['is_logout']))
                    <li class="nav-item mt-2">
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
/* ===================================================== */
/* ===== Brand Logo ==================================== */
/* ===================================================== */

.brand-link {
    padding: 0.75rem 1rem !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background: rgba(0, 0, 0, 0.08);
}

.brand-logo-img,
.brand-text-img {
    display: inline-block;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

body.sidebar-collapse .brand-text-img {
    display: none !important;
    opacity: 0;
    transform: translateX(-6px);
}

body.sidebar-collapse .brand-link {
    justify-content: center !important;
    padding: 0.75rem 0.5rem !important;
}

/* ===================================================== */
/* ===== Main Sidebar - Purple ========================= */
/* ===================================================== */

.main-sidebar {
    background: linear-gradient(180deg, #672d84 0%, #5a2675 100%);
    box-shadow: 4px 0 12px rgba(0, 0, 0, 0.15);
    transition: width 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}

/* ===================================================== */
/* ===== Sidebar Navigation - text-sm ================== */
/* ===================================================== */

.nav-sidebar .nav-link {
    margin: 2px 8px;
    padding: 8px 12px;
    border-radius: 6px;
    transition: all 0.25s ease;
    font-weight: 400;
    position: relative;
}

.nav-sidebar .nav-link:hover {
    background: rgba(255, 255, 255, 0.06);
    transform: translateX(3px);
}

.nav-sidebar .nav-link p {
    font-size: 0.813rem; /* text-sm */
    font-weight: 400;
    color: rgba(255, 255, 255, 0.75);
    transition: all 0.2s ease;
    position: relative;
    z-index: 1;
}

.nav-sidebar .nav-link:hover p {
    color: #ffffff;
}

.nav-sidebar .nav-icon {
    font-size: 0.875rem;
    margin-right: 12px;
    width: 18px;
    text-align: center;
    transition: all 0.2s ease;
    position: relative;
    z-index: 1;
}

/* ===================================================== */
/* ===== Section Headers =============================== */
/* ===================================================== */

.nav-header {
    font-size: 0.62rem;
    font-weight: 600;
    letter-spacing: 0.1em;
    padding: 10px 14px 6px;
    margin-top: 8px;
    color: rgba(255, 255, 255, 0.3);
    text-transform: uppercase;
    border-left: 2px solid rgba(167, 139, 250, 0.4);
    margin-left: 8px;
}

/* ===================================================== */
/* ===== Dividers ====================================== */
/* ===================================================== */

.nav-divider {
    border-top: 1px solid rgba(255, 255, 255, 0.05);
    margin: 10px 12px;
}

/* ===================================================== */
/* ===== Active State ================================== */
/* ===================================================== */

.nav-sidebar .nav-link.active {
    background: rgba(167, 139, 250, 0.15) !important;
}

.nav-sidebar .nav-link.active p {
    color: #ffffff !important;
    font-weight: 500;
}

.nav-sidebar .nav-link.active .nav-icon {
    color: #c4b5fd !important;
}

.nav-sidebar .nav-link.active::after {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    height: 60%;
    width: 3px;
    background: #a78bfa;
    border-radius: 0 3px 3px 0;
}

/* ===================================================== */
/* ===== Logout Link =================================== */
/* ===================================================== */

.logout-link {
    background: transparent;
    border: none;
    color: rgba(255, 255, 255, 0.75);
    margin: 3px 8px;
    padding: 8px 12px;
    border-radius: 6px;
    transition: all 0.25s ease;
}

.logout-link:hover {
    background: rgba(239, 68, 68, 0.1);
    transform: translateX(3px);
}

.logout-link:hover p {
    color: #fca5a5 !important;
}

.logout-link i {
    color: rgba(248, 113, 113, 0.85);
    transition: all 0.2s ease;
}

.logout-link:hover i {
    color: #fca5a5 !important;
}

/* ===================================================== */
/* ===== Collapsed Sidebar ============================= */
/* ===================================================== */

body.sidebar-collapse .main-sidebar {
    width: 4.8rem !important;
}

body.sidebar-collapse .nav-sidebar .nav-link p,
body.sidebar-collapse .nav-header,
body.sidebar-collapse .brand-text-img,
body.sidebar-collapse .nav-divider {
    display: none !important;
    opacity: 0;
}

body.sidebar-collapse .nav-sidebar .nav-link {
    justify-content: center;
    padding: 10px 0;
    margin: 2px 6px;
}

body.sidebar-collapse .nav-sidebar .nav-icon {
    margin-right: 0;
    font-size: 1rem;
}

body.sidebar-collapse .nav-header {
    padding: 0;
    margin: 0;
}

/* ===================================================== */
/* ===== Scrollable Sidebar ============================ */
/* ===================================================== */

.main-sidebar {
    height: 100vh;
    overflow: hidden;
}

.main-sidebar .sidebar {
    height: calc(100vh - 56px);
    overflow-y: auto;
    overflow-x: hidden;
    padding-bottom: 20px;
}

/* Custom Scrollbar */
.main-sidebar .sidebar::-webkit-scrollbar {
    width: 4px;
}

.main-sidebar .sidebar::-webkit-scrollbar-track {
    background: transparent;
}

.main-sidebar .sidebar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.15);
    border-radius: 8px;
}

.main-sidebar .sidebar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.3);
}

/* ===================================================== */
/* ===== Hover Auto Open =============================== */
/* ===================================================== */

body.sidebar-hover-open .main-sidebar {
    width: 250px !important;
}

body.sidebar-hover-open .nav-sidebar .nav-link p,
body.sidebar-hover-open .nav-header,
body.sidebar-hover-open .brand-text-img {
    display: inline-block !important;
    opacity: 1;
    transform: translateX(0);
}

body.sidebar-hover-open .nav-sidebar .nav-link {
    justify-content: flex-start;
    padding: 8px 12px;
    margin: 2px 8px;
}

body.sidebar-hover-open .nav-sidebar .nav-icon {
    margin-right: 12px;
}

</style>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const body = document.body;
    const sidebar = document.querySelector('.main-sidebar');
    let hoverTimeout;

    sidebar.addEventListener('mouseenter', function () {
        if (body.classList.contains('sidebar-collapse')) {
            clearTimeout(hoverTimeout);
            body.classList.remove('sidebar-collapse');
            body.classList.add('sidebar-hover-open');
        }
    });

    sidebar.addEventListener('mouseleave', function () {
        if (body.classList.contains('sidebar-hover-open')) {
            hoverTimeout = setTimeout(() => {
                body.classList.remove('sidebar-hover-open');
                body.classList.add('sidebar-collapse');
            }, 100);
        }
    });
});
</script>

