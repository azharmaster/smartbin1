<div class="sidebar-wrapper">
    <ul class="nav">
        @foreach ($routes as $item)
            <li class="nav-item">
                <a href="{{ route($item['route_name']) }}"
                    class="nav-link {{ request()->routeIs($item['route_active']) ? 'active' : '' }}">
                    <i class="{{ $item['icon'] }}"></i>
                    <p>{{ $item['label'] }}</p>
                </a>
            </li>
        @endforeach
    </ul>
</div>
