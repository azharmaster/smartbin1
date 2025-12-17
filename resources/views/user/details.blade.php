@extends('layouts.app')
@section('content_title', 'User Profile')
@section('content')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="d-flex justify-content-center mt-4">
    <div class="card shadow-lg border-0 p-4"
         style="width: 85%; max-width: 850px; border-radius: 20px;">

        <div class="text-center">

            @php
                $photo = $user->profile_photo
                    ? asset('uploads/profile/' . $user->profile_photo)
                    : 'https://via.placeholder.com/150';
            @endphp

            <!-- Profile Picture -->
            <div class="position-relative d-inline-block mb-3">
                <img src="{{ $photo }}"
                     alt="Profile Picture"
                     class="rounded-circle shadow-lg"
                     style="width:170px;height:170px;object-fit:cover;border:5px solid #fff;">
            </div>

            <!-- Name + Email -->
            <h2 class="fw-bold mb-1">{{ $user->name }}</h2>
            <p class="text-muted mb-2">{{ $user->email }}</p>

            <hr style="opacity: 0.2;">

            <!-- Role -->
            @php
                $roles = [
                    1 => ['Admin', 'bg-primary'],
                    2 => ['Staff', 'bg-success'],
                    3 => ['Guest', 'bg-secondary']
                ];

                $roleName = $roles[$user->role][0] ?? 'Unknown';
                $roleColor = $roles[$user->role][1] ?? 'bg-dark';
            @endphp

            <p class="mt-2">
                <strong>Role:</strong>
                <span class="badge {{ $roleColor }}"
                      style="font-size: 1rem; padding: 8px 15px; border-radius: 12px;">
                    {{ $roleName }}
                </span>
            </p>

            <!-- ADMIN ACTION -->
            @if(auth()->user()->role === 1)
                <div class="mt-4">
                    <button class="btn btn-danger btn-sm"
                            onclick="confirmReset()">
                        Reset Password to Default
                    </button>
                </div>
            @endif

        </div>
    </div>
</div>

<script>
function confirmReset() {
    Swal.fire({
        title: 'Reset Password?',
        text: 'Password will be reset to default: 12345678',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, reset',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('reset-password-form').submit();
        }
    });
}
</script>

<form id="reset-password-form"
      action="{{ route('users.reset-password', $user->id) }}"
      method="POST" style="display:none;">
    @csrf
</form>

@endsection
