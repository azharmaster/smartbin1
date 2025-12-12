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
                $photo = Auth::user()->profile_photo 
                    ? asset('uploads/profile/' . Auth::user()->profile_photo)
                    : 'https://via.placeholder.com/150';
            @endphp

            <!-- Profile Picture Wrapper -->
            <div class="position-relative d-inline-block mb-3">
                <img src="{{ $photo }}"
                    alt="Profile Picture"
                    class="rounded-circle shadow-lg"
                    style="
                        width: 170px; 
                        height: 170px; 
                        object-fit: cover; 
                        border: 5px solid #fff;
                        transition: 0.3s;
                    "
                    onmouseover="this.style.transform='scale(1.05)'"
                    onmouseout="this.style.transform='scale(1)'">
            </div>

            <!-- Upload Form -->
            <form action="{{ route('profile.upload.photo') }}"
                  method="POST"
                  enctype="multipart/form-data"
                  class="mt-3 mb-4">

                @csrf

                <div class="input-group mb-3" style="max-width: 400px; margin: 0 auto;">
                    <input type="file" 
                           name="profile_photo" 
                           class="form-control" 
                           required>&nbsp;
                    <button class="btn btn-primary">
                        Upload
                    </button>
                </div>
            </form>

            <!-- Name + Email -->
            <h2 class="fw-bold mb-1">{{ Auth::user()->name }}</h2>
            <p class="text-muted mb-2">{{ Auth::user()->email }}</p>

            <hr style="opacity: 0.2;">

            <!-- Role -->
            @php
                $roles = [
                    1 => ['Admin', 'bg-primary'],
                    2 => ['Staff', 'bg-success'],
                    3 => ['Guest', 'bg-secondary']
                ];

                $roleName = $roles[Auth::user()->role][0] ?? 'Unknown';
                $roleColor = $roles[Auth::user()->role][1] ?? 'bg-dark';
            @endphp

            <p class="mt-2">
                <strong>Role:</strong>
                <span class="badge {{ $roleColor }}" 
                      style="font-size: 1rem; padding: 8px 15px; border-radius: 12px;">
                    {{ $roleName }}
                </span>
            </p>

            <div class="text-center mt-4">
                <button type="button" class="btn btn-danger btn-sm" onclick="openResetPasswordPopup()">
                    Reset Password
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

<script>
function openResetPasswordPopup() {
    Swal.fire({
        title: 'Reset Password',
        html: `
            <div class="mb-2">
                <input id="currentPassword" type="password" class="form-control" placeholder="Current Password">
            </div>
            <div class="mb-2">
                <input id="newPassword" type="password" class="form-control" placeholder="New Password">
            </div>
            <div class="mb-2">
                <input id="confirmPassword" type="password" class="form-control" placeholder="Confirm Password">
            </div>
        `,
        confirmButtonText: 'Update Password',
        showCancelButton: true,
        focusConfirm: false,
        preConfirm: () => {
            const current = document.getElementById('currentPassword').value;
            const newPass = document.getElementById('newPassword').value;
            const confirmPass = document.getElementById('confirmPassword').value;

            if (!current || !newPass || !confirmPass) {
                Swal.showValidationMessage('All fields are required');
                return false;
            }
            if (newPass !== confirmPass) {
                Swal.showValidationMessage('New password and confirm password do not match');
                return false;
            }

            return { current, newPass };
        },
        didOpen: () => {
            // Add focus to the first input
            document.getElementById('currentPassword').focus();
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Submit via POST using fetch
            fetch("{{ route('profile.updatePassword') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    current_password: result.value.current,
                    password: result.value.newPass,
                    password_confirmation: result.value.newPass
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Success', data.success, 'success');
                } else {
                    Swal.fire('Error', data.error || 'Something went wrong', 'error');
                }
            })
            .catch(() => Swal.fire('Error', 'Something went wrong', 'error'));
        }
    });
}
</script>
