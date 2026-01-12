@extends($layout)
@section('content_title', 'User Profile')
@section('content')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@php
    $photo = Auth::user()->profile_photo 
        ? asset('uploads/profile/' . Auth::user()->profile_photo)
        : 'https://via.placeholder.com/150';

    $roles = [
        1 => ['Admin', 'bg-primary'],
        4 => ['Supervisor', 'bg-info'],
    ];

    $roleName = $roles[Auth::user()->role][0] ?? 'Unknown';
    $roleColor = $roles[Auth::user()->role][1] ?? 'bg-dark';
@endphp

<style>
    /* Soft shadow cards */
    .card-shadow {
        background-color: #fff;
        border-radius: 18px;
        box-shadow: 0 6px 16px rgba(0,0,0,0.08);
        transition: 0.3s;
    }
    .card-shadow:hover {
        box-shadow: 0 8px 20px rgba(0,0,0,0.12);
    }

    /* Left card scaling */
    .profile-card .profile-user-img {
        width: 150px;
        height: 150px;
        border: 4px solid #fff;
    }
    .profile-card h2 {
        font-size: 1.6rem;
    }
    .profile-card p {
        font-size: 0.9rem;
    }
    .profile-card .badge {
        font-size: 0.85rem;
        padding: 5px 14px;
        border-radius: 8px;
    }
    .profile-card .btn-sm {
        padding: 5px 14px;
        font-size: 0.85rem;
        border-radius: 6px;
    }

    /* Upload button */
    .upload-btn {
        padding: 6px 18px;
        font-size: 0.9rem;
    }

    /* Flex alignment for equal height cards */
    .profile-row {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        align-items: stretch; /* ensures cards stretch equally */
    }
    .profile-col {
        display: flex;
        flex-direction: column;
    }
    .profile-col .card-shadow {
        flex: 1; /* makes the card fill the column height */
    }

    /* Right card alignment to left */
    .right-card-wrapper {
        display: flex;
        justify-content: center; /* center the card horizontally */
        align-items: stretch;    /* stretch to same height as left */
    }

    .right-card-wrapper .card-body {
        display: flex;
        flex-direction: column;
        justify-content: flex-start; /* align content to top */
        align-items: flex-start;     /* align all form items left */
    }

    .profile-card, .right-card-wrapper .card-shadow {
        padding: 2rem; 
    }

    /* Form full width */
    .right-card-wrapper form {
        width: 100%;
    }

    .right-card-wrapper form .form-group {
        width: 100%;
    }

    .right-card-wrapper form label {
        text-align: left;
        width: 100%;
        margin-bottom: 0.25rem;
    }

    .right-card-wrapper form .form-control,
    .right-card-wrapper form select {
        width: 100%;
    }

    /* Center the submit button */
    .right-card-wrapper form .form-group.text-center {
        align-self: center;
    }
</style>

<div class="container-fluid mt-4">
    <div class="profile-row justify-content-center">

        <!-- LEFT COLUMN: PROFILE CARD -->
        <div class="col-md-3 profile-col">
            <div class="card card-success card-outline card-shadow profile-card text-center">

                <!-- Profile Picture -->
                <div class="position-relative d-inline-block mb-2">
                    <img class="rounded-circle shadow-lg profile-user-img"
                         src="{{ $photo }}"
                         alt="Profile Picture"
                         style="transition: transform 0.3s;"
                         onmouseover="this.style.transform='scale(1.05)'"
                         onmouseout="this.style.transform='scale(1)'">
                </div>

                <!-- Upload Form -->
                <form action="{{ route('profile.upload.photo') }}" method="POST" enctype="multipart/form-data" class="mt-3 mb-4">
                    @csrf
                    <div class="input-group mb-3" style="max-width: 400px; margin: 0 auto;">
                        <input type="file" name="profile_photo" class="form-control" required>
                        <button class="btn btn-primary upload-btn">Upload</button>
                    </div>
                </form>

                <!-- Name + Email -->
                <h2 class="fw-bold mb-1">{{ Auth::user()->name }}</h2>
                <p class="text-muted mb-2">{{ Auth::user()->email }}</p>

                <hr style="opacity: 0.2;">

                <!-- Role Badge -->
                <p class="mt-2">
                    <strong>Role:</strong>
                    <span class="badge {{ $roleColor }}">
                        {{ $roleName }}
                    </span>
                </p>

                <!-- Reset Password Button -->
                <div class="d-flex justify-content-center align-items-center mt-4">
                    <a href="{{ route('profile.editPassword') }}" class="btn btn-danger btn-sm">
                        Change Password
                    </a>
                </div>

            </div>
        </div>

        <!-- RIGHT COLUMN: PROFILE FORM -->
        <div class="col-md-6 profile-col right-card-wrapper">
            <div class="card card-success card-outline card-shadow w-100">
                <div class="card-body">

                    <form method="POST" action="{{ route('users.store') }}">
                        @csrf
                        <input type="hidden" name="id" value="{{ Auth::id() }}">

                        <!-- Name -->
                        <div class="form-group mb-3">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control" value="{{ Auth::user()->name }}" required>
                        </div>

                        <!-- Email -->
                        <div class="form-group mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" value="{{ Auth::user()->email }}" required>
                        </div>

                        <!-- Phone -->
                        <div class="form-group mb-3">
                            <label>Phone</label>
                            <input type="text" name="phone" class="form-control" value="{{ Auth::user()->phone ?? '' }}">
                        </div>

                        <!-- Role -->
                        <div class="form-group mb-3">
                            <label>Role</label>
                            <select name="role" class="form-control" required>
                                <option value="1" {{ Auth::user()->role == 1 ? 'selected' : '' }}>Admin</option>
                                <option value="4" {{ Auth::user()->role == 4 ? 'selected' : '' }}>Supervisor</option>
                            </select>
                        </div>

                        <!-- Submit Button -->
                        <div class="form-group mt-4 text-center">
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                        </div>

                    </form>

                </div>
            </div>
        </div>

    </div>
</div>

@endsection
