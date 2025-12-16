{{-- resources/views/layouts/nosidebaradmin.blade.php --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'POS SYSTEM')</title>

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
            padding: 20px;
        }

        .content-wrapper {
            background: transparent;
        }

        .content-wrapper .content {
            //background: rgba(0, 0, 0, 0.25);
            border-radius: 10px;
            padding: 10px;
            //box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
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

        /* Stats Cards */
        .stats-container {
            grid-column: 1 / -1;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 10px;
            margin-bottom: 10px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 12px;
            text-align: center;
            backdrop-filter: blur(10px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
        }

        .stat-card i {
            font-size: 1.8rem;
            margin-bottom: 10px;
        }

        .stat-card h3 {
            font-size: 1rem;
            margin-bottom: 8px;
            color: #4cd964;
        }

        .stat-card .value {
            font-size: 1.4rem;
            font-weight: bold;
        }

        /* Map Container */
        .map-container {
            grid-column: 1 / -1;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 20px;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            display: flex;
        }

        .map-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .map-header h2 {
            color: #4cd964;
        }

        .map-controls {
            display: flex;
            gap: 10px;
        }

        .map-controls button {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .map-controls button:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        #map {
            position: relative;
            width: 100%;
            height: 400px;
            overflow: scroll;
            border: 1px solid #2d4a5c;
        }

        /* Bins Container */
        .bins-container {
            grid-column: 1 / -1;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            display: none;
            flex-direction: column;
        }

        /* Bin Cards */
        .bins-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 10px;
            width: 100%;
            margin: 10px 0;
        }

        .bin-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 20px;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .bin-card.full {
            background: rgba(255, 77, 77, 0.2);
            border-left: 5px solid #ff4d4d;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(255, 77, 77, 0.7);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(255, 77, 77, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(255, 77, 77, 0);
            }
        }

        .bin-card.warning {
            background: rgba(255, 193, 7, 0.2);
            border-left: 5px solid #ffc107;
        }

        .bin-card.normal {
            background: rgba(76, 217, 100, 0.2);
            border-left: 5px solid #4cd964;
        }

        .bin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .bin-id {
            font-size: 1.2rem;
            font-weight: bold;
        }

        .bin-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .status-full {
            background: #ff4d4d;
        }

        .status-warning {
            background: #ffc107;
            color: #333;
        }

        .status-normal {
            background: #4cd964;
            color: #333;
        }

        .bin-location {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .bin-location i {
            margin-right: 5px;
        }

        .bin-progress {
            height: 20px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 15px;
        }

        .bin-progress-bar {
            height: 100%;
            border-radius: 10px;
            transition: width 0.5s ease;
        }

        .progress-full {
            background: #ff4d4d;
        }

        .progress-warning {
            background: #ffc107;
        }

        .progress-normal {
            background: #4cd964;
        }

        .bin-details {
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
        }

        .bin-percentage {
            font-weight: bold;
        }

        .last-updated {
            opacity: 0.7;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header h1 {
                font-size: 2rem;
            }

            .bins-grid {
                grid-template-columns: 1fr;
            }
        }

    </style>
</head>

<body class="hold-transition layout-top-nav">
    <div class="wrapper">

        <!-- Content Wrapper -->
        <div class="content-wrapper">

            <!-- Content Header -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">@yield('content_title')</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('dashboard') }}">Home</a>
                                </li>
                                <li class="breadcrumb-item active">@yield('content_title')</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main content -->
            <div class="content">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="main-footer">
            <div class="float-right d-none d-sm-inline">
                Anything you want
            </div>
            <strong>
                Copyright &copy; 2014-2025 PSENSEHUB / SmartBin.
            </strong>
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
