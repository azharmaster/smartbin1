@extends($layout)
@section('content_title', 'Reset Password')
@section('content')

<div class="d-flex justify-content-center mt-4">
    <div class="card shadow-lg border-0 p-4" style="width: 85%; max-width: 500px; border-radius: 20px;">

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('profile.updatePassword') }}">
            @csrf

            <div class="form-group mb-3">
                <label>Current Password</label>
                <input type="password" name="current_password" class="form-control" required>
            </div>

            <div class="form-group mb-3">
                <label>New Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="form-group mb-3">
                <label>Confirm New Password</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-danger w-100">Update Password</button>
        </form>
    </div>
</div>

@endsection
