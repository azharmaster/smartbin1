<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SMARTBIN</title>
  <link rel="icon" type="image/x-icon" href="{{ asset('uploads/images/bin2.png') }}">

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="{{ asset('adminlte') }}/plugins/fontawesome-free/css/all.min.css">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="{{ asset('adminlte') }}/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{ asset('adminlte') }}/dist/css/adminlte.min.css">

  <!-- ===================== BACKGROUND & LOGIN BOX STYLES ===================== -->
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

    /* ---------- Remember me text ---------- */
    .icheck-primary label {
      color: #030303ff;
    }

    /* ---------- Sign In button ---------- */
    .btn-primary {
      background-color: #007bff;
      border: none;
      font-weight: 600;
    }

    .btn-primary:hover {
      background-color: #0056b3;
    }

    /* ---------- Forgot password link ---------- */
    .login-card-body a {
      color: #e0e0e0;
    }

    .login-card-body a:hover {
      color: #007bff;
      text-decoration: underline;
    }
  </style>
</head>

<body class="hold-transition login-page">
<div class="login-box">
  <div class="login-logo">
    <a href="{{ asset('adminlte') }}/index2.html"><b>POS</b>SYSTEM</a>
  </div>
  <!-- /.login-logo -->
  <div class="card">
    <div class="card-body login-card-body">
      <p class="login-box-msg">Sign in to start your session</p>
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
      <form action="{{ route('login') }}" method="post">
        @csrf
        <div class="input-group mb-3">
          <input type="email" name="email" class="form-control" placeholder="Email">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-envelope"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="password" name="password" class="form-control" placeholder="Password">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-8">
            <div class="icheck-primary">
              <input type="checkbox" id="remember">
              <label for="remember">
                Remember Me
              </label>
            </div>
          </div>
          <!-- /.col -->
          <div class="col-4">
            <button type="submit" class="btn btn-primary btn-block">Sign In</button>
          </div>
          <!-- /.col -->
        </div>
      </form>

      <!-- /.social-auth-links -->

      <p class="mb-1">
        <a href="forgot-password.html">I forgot my password</a>
      </p>
    </div>
    <!-- /.login-card-body -->
  </div>
</div>
<!-- /.login-box -->
<div class="text-center mt-3">
    <a href="{{ route('complaint.guest') }}" class="btn btn-outline-secondary btn-block">
        Continue as Guest
    </a>
</div>

<!-- jQuery -->
<script src="{{ asset('adminlte') }}/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="{{ asset('adminlte') }}/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="{{ asset('adminlte') }}/dist/js/adminlte.min.js"></script>
</body>
</html>
