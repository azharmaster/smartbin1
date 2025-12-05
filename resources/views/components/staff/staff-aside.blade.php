<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ route('staff.mainmenu') }}" class="brand-link text-center">
        <span class="brand-text font-weight-light">{{ env('APP_NAME') }}</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <!-- Optional user image -->
            </div>
            <div class="info">
                <a href="{{ route('profile.staffindex') }}" class="d-block">
                    {{ auth()->check() ? auth()->user()->name : 'Guest' }}
                </a>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                @foreach ($routes as $route)
                    @if (!$route['is_dropdown'])
                        <li class="nav-item">
                            <a href="{{ route($route['route_name']) }}"
                               class="nav-link {{ request()->routeIs($route['route_active']) ? 'active' : '' }}">
                                <i class="nav-icon {{ $route['icon'] }}"></i>
                                <p>{{ $route['label'] }}</p>
                            </a>
                        </li>
                    @else
                        <li class="nav-item {{ request()->routeIs($route['route_active']) ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs($route['route_active']) ? 'active' : '' }}">
                                <i class="nav-icon {{ $route['icon'] }}"></i>
                                <p>
                                    {{ $route['label'] }}
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                @foreach ($route['dropdown'] as $dropdownItem)
                                    <li class="nav-item">
                                        <a href="{{ route($dropdownItem['route_name']) }}"
                                           class="nav-link {{ request()->routeIs($dropdownItem['route_active']) ? 'active' : '' }}">
                                            <i class="{{ $dropdownItem['icon'] ?? 'fas fa-circle' }} nav-icon"></i>
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
