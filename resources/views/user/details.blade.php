@extends($layout)
@section('content_title', 'User Profile')
@section('content')

@php
    $photo = $user->profile_photo 
        ? asset('uploads/profile/' . $user->profile_photo)
        : 'https://via.placeholder.com/150';

    $roles = [
        1 => ['Admin', 'bg-primary'],
        4 => ['Supervisor', 'bg-info'],
    ];

    $roleName = $roles[$user->role][0] ?? 'Unknown';
    $roleColor = $roles[$user->role][1] ?? 'bg-dark';
@endphp

<style>
    /* Soft shadow cards */
    .card-shadow {
        background-color: #fff;
        border-radius: 18px;
        box-shadow: 0 6px 16px rgba(0,0,0,0.08);
        transition: 0.3s;
    }

    .profile-row {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        align-items: stretch; /* equal height cards */
    }
    .profile-col {
        display: flex;
        flex-direction: column;
    }
    .profile-col .card-shadow {
        flex: 1; /* fill height */
    }

    /* Left card styling */
    .profile-card .profile-user-img {
        width: 150px;
        height: 150px;
        border: 4px solid #fff;
    }
    .profile-card h2 { font-size: 1.6rem; }
    .profile-card p { font-size: 0.9rem; }
    .profile-card .badge { font-size: 0.85rem; padding: 5px 14px; border-radius: 8px; }

    /* Right card alignment */
    .right-card-wrapper .card-body {
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        align-items: flex-start;
    }

    /* Form read-only styles */
    .right-card-wrapper .form-group {
        width: 100%;
        margin-bottom: 1rem;
    }
    .right-card-wrapper label {
        font-weight: 600;
        margin-bottom: 0.25rem;
    }
    .right-card-wrapper .form-control, 
    .right-card-wrapper select {
        border: none;
        background-color: transparent;
        padding-left: 0;
        font-weight: 500;
        color: #333;
        cursor: default;
        pointer-events: none;
    }
</style>

<div class="container-fluid mt-4">
    <div class="profile-row justify-content-center">

        <!-- LEFT COLUMN -->
        <div class="col-md-3 profile-col">
            <div class="card card-success card-outline card-shadow profile-card text-center p-4">

                <div class="position-relative d-inline-block mb-3">
                    <img class="rounded-circle shadow-lg profile-user-img"
                         src="{{ $photo }}"
                         alt="Profile Picture">
                </div>

                <h2 class="fw-bold mb-1">{{ $user->name }}</h2>
                <p class="text-muted mb-2">{{ $user->email }}</p>

                <hr style="opacity: 0.2;">

                <p class="mt-2">
                    <strong>Role:</strong>
                    <span class="badge {{ $roleColor }}">
                        {{ $roleName }}
                    </span>
                </p>

            </div>
        </div>

        <!-- RIGHT COLUMN -->
        <div class="col-md-6 profile-col right-card-wrapper">
            <div class="card card-success card-outline card-shadow w-100 p-4">
                <div class="card-body">

                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" class="form-control" value="{{ $user->name }}">
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" class="form-control" value="{{ $user->email }}">
                    </div>

                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" class="form-control" value="{{ $user->phone ?? '-' }}">
                    </div>

                    <div class="form-group">
                        <label>Role</label>
                        <input type="text" class="form-control" value="{{ $roleName }}">
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>

@endsection


