<aside class="main-sidebar sidebar-dark-primary elevation-4">

    {{-- Brand --}}
    <a href="{{ route('supervisor.dashboard') }}" class="brand-link text-center">
        <span class="brand-text font-weight-light">
            {{ env('APP_NAME') }} Supervisor
        </span>
    </a>

    {{-- Sidebar --}}
    <div class="sidebar">
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column">

                @foreach($routes as $route)
                    <li class="nav-item">
                        <a href="{{ route($route['route_name']) }}"
                           class="nav-link {{ request()->routeIs($route['route_active']) ? 'active' : '' }}">

                            <i class="nav-icon {{ $route['icon'] }}"
                               style="color: {{ $route['color'] }}"></i>

                            <p>{{ $route['label'] }}</p>
                        </a>
                    </li>
                @endforeach

                {{-- LOGOUT --}}
                <li class="nav-item mt-3 px-3">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </button>
                    </form>
                </li>

            </ul>
        </nav>
    </div>
</aside>
