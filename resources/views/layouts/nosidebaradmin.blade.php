{{-- resources/views/layouts/nosidebaradmin.blade.php --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SMARTBIN</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('uploads/images/logo.png') }}">

    <!-- Google Font -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/fontawesome-free/css/all.min.css') }}">

    <!-- AdminLTE Theme style -->
    <link rel="stylesheet" href="{{ asset('adminlte/dist/css/adminlte.min.css') }}">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet"
        href="{{ asset('adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet"
        href="{{ asset('adminlte/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <!-- Custom Dashboard CSS -->
    <style>
        /* === Reset and Base === */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #1a2f3f 0%, #2d4a5c 100%);
            color: #fff;
            min-height: 100vh;
            margin-left: 50px;
            margin-right: 50px;
        }

        .content-wrapper {
            background: transparent;
        }

        .content-wrapper .content {
            border-radius: 10px;
            padding: 10px;
        }

        /* === Dashboard Layout === */
        .dashboard-container {
            max-width: 1400px;
            margin-left: 0;
            margin-right: auto;
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
            transition: margin-left 0.3s ease, grid-template-columns 0.3s ease;
        }

        /* Header */
        .header {
            grid-column: 1 / -1;
            text-align: center;
            margin-bottom: 5px;
            padding: 10px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            color: #4cd964;
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.8;
        }

        .logout-btn:hover,
        .logout-btn:focus,
        .logout-btn:active {
            background-color: transparent !important;
            color: inherit !important;
        }

    </style>
</head>

<body class="min-vh-100 d-flex flex-column">
    <div class="wrapper">

        <!-- Content Wrapper -->
        <div class="content-wrapper">

            <!-- Content Header -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h4 class="m-0">@yield('content_title')</h4>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                {{-- <li class="breadcrumb-item">
                                    <a href="{{ route('dashboard') }}">Home</a>
                                </li> --}}
                                {{-- <li class="breadcrumb-item active">@yield('content_title')</li> --}}
                                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="dropdown-item d-flex align-items-center text-white logout-btn">
                                        <i class="fas fa-sign-out-alt me-2" style="padding-right: 5px;"></i>
                                        Log Out
                                    </button>
                                </form>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main content -->
            <div class="content">
            <main class="flex-grow-1">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </main>
            </div>
        </div>

        <!-- Footer -->
        <footer class="main-footer" style="background-color: rgba(0, 0, 0, 0); color: #ffff" ></footer>
            <div class="float-right d-none d-sm-inline"><b>Version</b> 0.1</div>
            <strong>Copyright &copy; 2026-2027 SmartBin.</strong>
            All rights reserved.
        </footer>

    </div>

    <!-- Scripts -->
    <script src="{{ asset('adminlte/plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('adminlte/dist/js/adminlte.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/jszip/jszip.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/pdfmake/pdfmake.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/pdfmake/vfs_fonts.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>

</body>

</html>
