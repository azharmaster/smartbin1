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
                        $subActive = false;

                        if (!empty($dropdownItem['route_active'])) {
                            if (is_array($dropdownItem['route_active'])) {
                                foreach ($dropdownItem['route_active'] as $pattern) {
                                    if (request()->routeIs($pattern)) {
                                        $subActive = true;
                                        break;
                                    }
                                }
                            } else {
                                $subActive = request()->routeIs($dropdownItem['route_active']);
                            }
                        }

                        $subColor = $dropdownItem['color'] ?? $iconColor;
                    @endphp

                    {{-- 🔹 NESTED DROPDOWN (Collective) --}}
                    @if (!empty($dropdownItem['is_dropdown']) && $dropdownItem['is_dropdown'])
                        <li class="nav-item {{ $subActive ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ $subActive ? 'active' : '' }}">
                                <i class="nav-icon {{ $dropdownItem['icon'] }}" style="color: {{ $subColor }}"></i>
                                <p>
                                    {{ $dropdownItem['label'] }}
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>

                            <ul class="nav nav-treeview">
                                @foreach ($dropdownItem['dropdown'] as $child)
                                    @php
                                        $childActive = request()->routeIs($child['route_active'] ?? '');
                                    @endphp
                                    <li class="nav-item">
                                        <a href="{{ route($child['route_name']) }}"
                                        class="nav-link {{ $childActive ? 'active' : '' }}">
                                            <i class="nav-icon {{ $child['icon'] }}"
                                            style="color: {{ $child['color'] ?? $subColor }}"></i>
                                            <p>{{ $child['label'] }}</p>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>

                    {{-- 🔹 NORMAL DROPDOWN ITEM --}}
                    @else
                        <li class="nav-item">
                            <a href="{{ route($dropdownItem['route_name']) }}"
                            class="nav-link {{ $subActive ? 'active' : '' }}">
                                <i class="nav-icon {{ $dropdownItem['icon'] }}" style="color: {{ $subColor }}"></i>
                                <p>{{ $dropdownItem['label'] }}</p>
                            </a>
                        </li>
                    @endif
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


</style>

<!-- /.sidebar -->
</aside>
