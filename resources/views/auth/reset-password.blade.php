{{-- resources/views/auth/reset-password.blade.php --}}
@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')
<style>
/* ---------- Page background image ---------- */
body.login-page {
  background: linear-gradient(
      rgba(0, 0, 0, 0.55),
      rgba(0, 0, 0, 0.55)
    ),
    url("{{ asset('uploads/images/mrt-plaza-entrance-of.jpg') }}") no-repeat center center fixed;
  background-size: cover;
}

/* ---------- Transparent / glass login card ---------- */
.login-card-body {
  background: rgba(255, 255, 255, 0.15);   /* transparency */
  backdrop-filter: blur(100px);            /* glass blur */
  -webkit-backdrop-filter: blur(10px);
  border-radius: 12px;
  box-shadow: 0 8px 25px rgba(0,0,0,0.35);
  color: #fff;
}

/* ---------- Login title text ---------- */
.login-box-msg {
  color: #030202ff;
  font-weight: 600;
}

/* ---------- Input fields ---------- */
.login-card-body .form-control {
  background: rgba(214, 211, 211, 0.95);
  border: 1px solid rgba(244, 244, 244, 1);
  color: #151313ff;
}

.login-card-body .form-control::placeholder {
  color: rgba(29, 18, 18, 0.75);
}

.login-card-body .form-control:focus {
  background: rgba(211, 209, 209, 0.3);
  border-color: #fff;
  color: #1c1616ff;
  box-shadow: none;
}

/* ---------- Input icons ---------- */
.login-card-body .input-group-text {
  background: rgba(255, 255, 255, 0.25);
  border: 1px solid rgba(255, 255, 255, 0.4);
  color: #230c0cff;
}

/* ---------- Sign In / Reset button ---------- */
.btn-primary {
  background-color: #007bff;
  border: none;
  font-weight: 600;
}

.btn-primary:hover {
  background-color: #0056b3;
}

/* ---------- Links ---------- */
.login-card-body a {
  color: #e0e0e0;
}

.login-card-body a:hover {
  color: #007bff;
  text-decoration: underline;
}
</style>

<div class="login-box">
  <div class="login-logo">
    <a href="{{ asset('adminlte') }}/index2.html">
      <img src="{{ asset('uploads/images/logo_white.png') }}" alt="SMARTBIN Logo" style="height:50px; margin-bottom:10px;"><br>
      <b>SMARTBIN</b>TRXSYSTEM
    </a>
  </div>
  <!-- /.login-logo -->
  <div class="card">
    <div class="card-body login-card-body">

      <p class="login-box-msg">Reset Your Password</p>

      <form method="POST" action="{{ route('password.update') }}">
          @csrf

          <input type="hidden" name="token" value="{{ $token }}">

          <div class="input-group mb-3">
              <input type="email"
                  name="email"
                  value="{{ $email }}"
                  class="form-control"
                  required>
          </div>

          <div class="input-group mb-3">
              <input type="password"
                  name="password"
                  class="form-control"
                  placeholder="New Password"
                  required>
          </div>

          <div class="input-group mb-3">
              <input type="password"
                  name="password_confirmation"
                  class="form-control"
                  placeholder="Confirm Password"
                  required>
          </div>

          <button type="submit"
                  class="btn btn-primary btn-block">
              Reset Password
          </button>
      </form>

      <br>
      <a href="{{ route('login') }}">Back to login</a>

    </div>
  </div>
</div>
@endsection