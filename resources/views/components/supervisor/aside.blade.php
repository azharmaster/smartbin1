<aside class="main-sidebar sidebar-dark-primary elevation-4">

    {{-- Brand --}}
    <a href="{{ route('supervisor.dashboard') }}" class="brand-link text-center">
        <span class="brand-text font-weight-light">{{ env('APP_NAME') }} Supervisor</span>
    </a>

    {{-- Sidebar --}}
    <div class="sidebar">
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

                {{-- Loop through supervisor-specific routes --}}
                @foreach($routes as $route)
                    @php
                        $isActive = request()->routeIs($route['route_active'] ?? '');
                        $iconColor = $route['color'] ?? '#6c757d';
                    @endphp

                    {{-- LOGOUT BUTTON --}}
                    @if(!empty($route['is_logout']) && $route['is_logout'])
                        <li class="nav-item mt-3">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="nav-link btn btn-link w-100 text-start">
                                    <i class="{{ $route['icon'] }} me-2" style="color: {{ $iconColor }}"></i>
                                    {{ $route['label'] }}
                                </button>
                            </form>
                        </li>

                    {{-- SINGLE LINK --}}
                    @elseif(empty($route['is_dropdown']))
                        <li class="nav-item">
                            <a href="{{ route($route['route_name']) }}"
                               class="nav-link {{ $isActive ? 'active' : '' }}">
                                <i class="nav-icon {{ $route['icon'] }}" style="color: {{ $iconColor }}"></i>
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
                                @foreach($route['dropdown'] as $dropdownItem)
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
    </div>
</aside>
