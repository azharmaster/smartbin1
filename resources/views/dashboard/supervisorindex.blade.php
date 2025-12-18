@extends('layouts.supervisorapp')
@section('content_title', 'Dashboard')

@section('content')

<div class="container-fluid mt-4">

    <!-- STAFF USERS LIST -->
    <div class="card p-3">
        <h5 class="mb-3"><i class="fas fa-users"></i> Staff Users</h5>

        @if($staffUsers->isEmpty())
            <p class="text-center">No staff users found.</p>
        @else
            <ul class="list-group">
                @foreach($staffUsers as $user)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        {{ $user->name }}
                        <span class="badge bg-primary">Staff</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

</div>

@endsection
