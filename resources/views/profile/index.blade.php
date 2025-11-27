@extends('layouts.app')
@section('content_title', 'User Profile')
@section('content')

<div class="d-flex flex-wrap justify-content-center">
    <div class="card shadow-lg p-4" style="width: 85%; max-width: 900px;">

        <div class="text-center">

            <!-- Placeholder Image -->
            <img src="https://via.placeholder.com/150"
                 alt="Profile Picture"
                 class="rounded-circle mb-3 shadow"
                 style="width: 150px; height: 150px;">

            <h2 class="mb-0">{{ Auth::user()->name }}</h2>
            <p class="text-muted">{{ Auth::user()->email }}</p>

            <hr>

            <!-- Role Mapping -->
            <p class="mt-2">
                <strong>Role:</strong>
                @php
                    $roles = [
                        1 => 'Admin',
                        2 => 'Staff',
                        3 => 'Guest'
                    ];
                @endphp

                <span class="badge bg-primary" style="font-size: 1rem;">
                    {{ $roles[Auth::user()->role] ?? 'Unknown' }}
                </span>
            </p>

        </div>
    </div>
</div>

@endsection
