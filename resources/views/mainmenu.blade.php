{{-- resources/views/mainmenu.blade.php --}}
@extends('layouts.nosidebar')

@section('content')

<a href="{{ route('admin.main.dashboard') }}" class="text-decoration-none text-dark">
    <h1 class="text-center my-4">Main Menu</h1>
</a>


<div class="container">
    <div class="row">

        @foreach ($routes as $route)
            <div class="col-md-3 mb-4">
                @if (!$route['is_dropdown'])
                    {{-- Single button box --}}
                    <div class="card text-center p-3 shadow-sm h-100">
                        <a class="btn btn-app w-100" href="{{ route($route['route_name']) }}">
                            <i class="{{ $route['icon'] }}"></i>
                            {{ $route['label'] }}
                        </a>
                    </div>
                @else
                    {{-- Dropdown card --}}
                    <div class="card shadow-sm h-100">
                        <div class="card-header text-center">
                            <a class="btn btn-app w-100" data-toggle="collapse" href="#collapse{{ Str::slug($route['label']) }}" role="button" aria-expanded="false" aria-controls="collapse{{ Str::slug($route['label']) }}">
                                <i class="{{ $route['icon'] }}"></i>
                                {{ $route['label'] }}
                            </a>
                        </div>
                        <div class="collapse show-transition" id="collapse{{ Str::slug($route['label']) }}">
                            <div class="card-body text-center">
                                @foreach ($route['dropdown'] as $dropdownItem)
                                    <a class="btn btn-app m-2" href="{{ route($dropdownItem['route_name']) }}">
                                        <i class="{{ $dropdownItem['icon'] ?? 'fas fa-circle' }}"></i>
                                        {{ $dropdownItem['label'] }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endforeach

    </div>
</div>

{{-- CSS for .btn-app --}}
<style>
.btn-app {
    position: relative;
    padding: 10px 15px;
    min-width: 80px;
    height: 80px;
    text-align: center;
    color: #666;
    border-radius: 10px;
    border: 1px solid #ddd;
    background-color: #f4f4f4;
    display: inline-block;
    vertical-align: middle;
    font-size: 14px;
    transition: all 0.2s ease-in-out;
}
.btn-app i {
    display: block;
    font-size: 24px;
    margin-bottom: 5px;
}
.btn-app:hover {
    background-color: #007bff;
    color: #fff;
    border-color: #007bff;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}
.btn-app .badge {
    position: absolute;
    top: 5px;
    right: 10px;
    font-size: 10px;
}

/* Smooth collapse animation */
.show-transition {
    transition: height 0.3s ease;
}
</style>

@endsection

{{-- Make sure Bootstrap JS is included in your layout for collapse to work --}}
