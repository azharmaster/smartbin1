<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ route('admin.mainmenu') }}" class="brand-link text-center">
        <span class="brand-text font-weight-light">{{ env('APP_NAME') }}</span>
    </a>

    <!-- Sidebar -->
<div class="sidebar">

    <!-- Sidebar Menu -->
    <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

            @foreach ($routes as $route)
                @php
                    $isActive = request()->routeIs($route['route_active']);
                    $iconColor = $route['color'] ?? '#000';
                @endphp

                @if (!$route['is_dropdown'])
                    <li class="nav-item">
                        <a href="{{ route($route['route_name']) }}"
                           class="nav-link {{ $isActive ? 'active' : '' }}"
                           style="color: {{ $iconColor }};">
                            <i class="nav-icon {{ $route['icon'] }}" style="color: {{ $iconColor }};"></i>
                            <p>{{ $route['label'] }}</p>
                        </a>
                    </li>
                @else
                    @php
                        $menuOpen = $isActive ? 'menu-open' : '';
                    @endphp
                    <li class="nav-item {{ $menuOpen }}">
                        <a href="#" class="nav-link {{ $isActive ? 'active' : '' }}">
                            <i class="nav-icon {{ $route['icon'] }}" style="color: {{ $iconColor }};"></i>
                            <p>
                                {{ $route['label'] }}
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @foreach ($route['dropdown'] as $dropdownItem)
                                @php
                                    $subActive = request()->routeIs($dropdownItem['route_active']);
                                    $subColor = $dropdownItem['color'] ?? $iconColor;
                                @endphp
                                <li class="nav-item">
                                    <a href="{{ route($dropdownItem['route_name']) }}"
                                       class="nav-link {{ $subActive ? 'active' : '' }}"
                                       style="color: {{ $subColor }};">
                                        <i class="{{ $dropdownItem['icon'] ?? 'fas fa-circle' }} nav-icon" 
                                           style="color: {{ $subColor }};"></i>
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
<!-- /.sidebar -->
</aside>
