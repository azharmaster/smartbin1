<aside class="main-sidebar sidebar-dark-primary elevation-4" style="background: linear-gradient(135deg, #103913ff, #1f6423ff)">
    <!-- Brand Logo -->
    <a href="" class="brand-link brand-app text-center">

    <span class="brand-text">{{ env('APP_NAME') }}</span>
</a>

    <!-- Sidebar -->
<div class="sidebar">

    <!-- Sidebar Menu -->
    <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" role="menu">

            @foreach ($routes as $route)

                @php
                    $iconColor = $route['color'] ?? '#6c757d';

                    // Active state 
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
                    <li class="nav-header text-uppercase">
                        {{ $route['label'] }}
                    </li>

                @elseif(($route['type'] ?? null) === 'divider')
                    <li class="nav-item">
                        <hr class="my-2">
                    </li>

                @elseif(!empty($route['is_logout']))
                    <li class="nav-item mt-3">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="nav-link btn btn-danger w-100 text-start">
                                <i class="{{ $route['icon'] }} me-2"></i>
                                {{ $route['label'] }}
                            </button>
                        </form>
                    </li>

                @else
                    <li class="nav-item">
                        <a href="{{ route($route['route_name']) }}"
                           class="nav-link {{ $isActive ? 'active' : '' }}">
                            <i class="nav-icon {{ $route['icon'] }}"
                               style="color: {{ $iconColor }}"></i>
                            <p>{{ $route['label'] }}</p>
                        </a>
                    </li>
                @endif

            @endforeach

        </ul>
    </nav>
    <!-- /.sidebar-menu -->
</div>

<style>
.nav-sidebar .nav-link {
    margin: 3px 8px;          /* tighter spacing */
    padding: 7px 10px;        /* smaller click area */
}

.nav-sidebar .nav-link p {
    font-size: 0.90rem;       /* smaller text */
    font-weight: 400;         /* normal (not bold) */
}

.nav-sidebar .nav-icon {
    font-size: 0.85rem;
    margin-right: 8px;
}

.nav-sidebar .nav-link.active {
    font-weight: 400;
}

.nav-header {
    font-size: 0.7rem;
    font-weight: 500;
    letter-spacing: 0.06em;
    padding: 8px 16px 4px;
    margin-top: 6px;
    color: rgba(255,255,255,0.6);
}

.brand-app .brand-text {
    font-size: 0.9rem;
    font-weight: 600;
    letter-spacing: 0.4px;
    text-transform: none;
}

/* Remove blue highlight for active sidebar item */
.nav-sidebar .nav-link.active {
    background: transparent !important;
    color: rgba(255,255,255,0.85) !important;
    box-shadow: none !important;
}

/* Ensure text is not bold when active */
.nav-sidebar .nav-link.active p {
    font-weight: 400 !important;
}

/* Icons also stay normal */
.nav-sidebar .nav-link.active .nav-icon {
    opacity: 0.9;
}

.nav-sidebar .nav-link.active::before {
    content: '';
    width: 3px;
    height: 100%;
    background: rgba(255,255,255,0.25);
    position: absolute;
    left: 0;
    top: 0;
}
</style>

<!-- /.sidebar -->
</aside>
