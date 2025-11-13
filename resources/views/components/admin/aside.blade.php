<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="index3.html" class="brand-link text-center">
        <!-- <img src="dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8"> -->
        <span class="brand-text font-weight-light ">{{ env('APP_NAME') }}</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <!-- <img src="dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image"> -->
            </div>
            <div class="info">
                <a href="#" class="d-block">{{ auth()->user()->name }}</a>
            </div>
        </div>



        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->

                <!-- <li class="nav-item">
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
              <i class="nav-icon fas fa-th"></i>
              <p>
                Dashboard
              </p>
            </a>
          </li> -->

                @foreach ($routes as $route)
    @if (!$route['is_dropdown'])
    <!-- Non-dropdown menu items -->
    <li class="nav-item">
        <a href="{{ route($route['route_name']) }}"
            class="nav-link {{ request()->routeIs($route['route_active']) ? 'active' : '' }}">
            <i class="nav-icon {{ $route['icon'] }}"></i>
            <p>{{ $route['label'] }}</p>
        </a>
    </li>
    @else
    <!-- Dropdown menu items -->
    <li class="nav-item {{ request()->routeIs($route['route_active']) ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ request()->routeIs($route['route_active']) ? 'active' : '' }}">
            <i class="nav-icon {{ $route['icon'] }}"></i>
            <p>
                {{ $route['label'] }}
                <i class="right fas fa-angle-left"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            <li class="nav-item">
                <a href="{{ route($route['dropdown']['route_name']) }}"
                    class="nav-link {{ request()->routeIs($route['dropdown']['route_active']) ? 'active' : '' }}">
                    <i class="{{ $route['dropdown']['icon'] ?? 'fas fa-circle' }} nav-icon"></i>
                    <p>{{ $route['dropdown']['label'] }}</p>
                </a>
            </li>
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