{{-- resources/views/layouts/nosidebaradmin.blade.php --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SMARTBIN</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('uploads/images/logo.png') }}">
    @include('partials.pwa-head')

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
            font-family: 'Source Sans Pro', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #14233d 0%, #1f3a5f 50%, #243b6b 100%);
            color: #f1f5f9;
            min-height: 100vh;
            margin: 0;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Force full width */
        .wrapper,
        .content-wrapper,
        .content {
            margin: 0 !important;
            padding: 0 !important;
            max-width: 100% !important;
            background: transparent;
        }

        .container-fluid {
            padding-left: clamp(12px, 3vw, 28px);
            padding-right: clamp(12px, 3vw, 28px);
        }

        .content-wrapper .content {
            border-radius: 10px;
            padding: 10px;
        }

        /* Content header bar (page title + logout) */
        .content-header {
            padding-top: 14px;
            padding-bottom: 6px;
        }

        .content-header h4 {
            font-size: clamp(1.1rem, 2.2vw, 1.5rem);
            font-weight: 600;
            color: #f8fafc;
            letter-spacing: -0.01em;
        }

        /* === Dashboard Layout === */
        .dashboard-container {
            max-width: 100%;
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
            padding: 18px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 14px;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.18);
        }

        .header h1 {
            font-size: clamp(1.6rem, 4vw, 2.5rem);
            margin-bottom: 8px;
            color: #4cd964;
            font-weight: 600;
            letter-spacing: -0.01em;
        }

        .header p {
            font-size: clamp(0.95rem, 1.3vw, 1.05rem);
            opacity: 0.8;
        }

        .logout-btn {
            color: #f1f5f9;
            border-radius: 8px;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .logout-btn:hover,
        .logout-btn:focus,
        .logout-btn:active {
            background-color: rgba(248, 113, 113, 0.12) !important;
            color: #fca5a5 !important;
        }

        /* Footer polish */
        .main-footer {
            background: transparent;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            color: rgba(241, 245, 249, 0.75);
            font-size: 0.8rem;
            padding: 12px 18px;
        }

        .main-footer strong {
            color: #f8fafc;
        }

        /* Responsive: tighten layout on small screens */
        @media (max-width: 767.98px) {
            .content-header .row > .col-sm-6 {
                width: 100%;
                max-width: 100%;
                flex: 0 0 100%;
                text-align: left;
            }

            .content-header .breadcrumb,
            .content-header .float-sm-right {
                float: none !important;
                margin-top: 8px;
                padding-left: 0;
            }

            .header {
                padding: 14px;
            }

            .main-footer {
                text-align: center;
            }

            .main-footer .float-right {
                float: none !important;
                display: block;
                margin-top: 4px;
            }
        }
    </style>
</head>

<body class="layout-top-nav min-vh-100 d-flex flex-column">
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
                                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit"
                                        class="dropdown-item d-flex align-items-center text-white logout-btn">
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
        <footer class="main-footer">
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
    @include('partials.pwa-scripts')

</body>
</html>
