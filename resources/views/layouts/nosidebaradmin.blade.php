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

        /* === Device / Floor / Bin Cards CSS === */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 20px;
        }

        .status-card {
            width: 19%;
            border: 2px solid #ddd;
            border-radius: 12px;
            padding: 18px;
            background: #fff;
            color: #fff;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            margin-left: 10px;
            transition: 0.2s ease-in-out;
        }

        .status-card:hover {
            box-shadow: 0 5px 12px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .status-title {
            font-size: 20px;
            font-weight: 600;
            text-align: center;
            margin-bottom: 15px;
        }

        .status-content {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        .status-icon {
            font-size: 32px;
        }

        .status-number {
            font-size: 32px;
            font-weight: bold;
        }

        .card-total { background-color: rgba(255, 255, 255, 0.1); }
        .card-full { background-color: rgba(255, 255, 255, 0.1); }
        .card-half { background-color: rgba(255, 255, 255, 0.1); }
        .card-empty { background-color: rgba(255, 255, 255, 0.1); }
        .card-undetected { background-color: rgba(255, 255, 255, 0.1); }
        .card-primary { background-color: rgba(255, 255, 255, 0.1); }

        .full-devices-cards {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-left: 5px;
        }

        .full-device-card {
            background-color: #6f060687;
            border: 2px solid #ff4d4d;
            border-radius: 12px;
            box-shadow: 0 0 12px rgba(255, 0, 0, 0.5);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .full-device-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 0 18px rgba(255, 0, 0, 0.7);
        }

        .full-status {
            background-color: #FF0000;
            padding: 0.4em 0.9em;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 700;
            box-shadow: 0 0 8px #FF0000, 0 0 12px #FF4d4d, 0 0 18px #FF6666;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 6px #CC0000, 0 0 10px #D93333, 0 0 14px #E06666; transform: scale(1); }
            50% { box-shadow: 0 0 10px #CC0000, 0 0 14px #D93333, 0 0 20px #E06666; transform: scale(1.05); }
            100% { box-shadow: 0 0 6px #CC0000, 0 0 10px #D93333, 0 0 14px #E06666; transform: scale(1); }
        }

        .half-device-card {
            background-color: #8f6f0587;
            border: 2px solid #f7d24a;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(255, 208, 0, 0.45);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .half-device-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 0 14px rgba(255, 208, 0, 0.6);
        }

        .half-status {
            background-color: #FFD700;
            padding: 0.4em 0.9em;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 700;
            box-shadow: 0 0 8px #FFD70090;
        }

        .full-device-card .fw-bold.fs-4,
        .half-device-card .fw-bold.fs-4 {
            font-size: 1.3rem;
            font-weight: bold;
        }

        .floor-frame {
            margin-top: 40px;
            width: 600px;
            max-width: 100%;
            padding: 20px;
            border-radius: 12px;
            border: 2px solid #ddd;
            box-shadow: 0 2px 12px rgba(0,0,0,0.1);
            background-color: #fff;
        }

        .floor-frame select {
            width: 100%;
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #ccc;
            margin-bottom: 15px;
        }

        .floor-frame img {
            width: 100%;
            height: 350px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid #aaa;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .floor-frame img:hover {
            transform: scale(1.03);
            box-shadow: 0 4px 16px rgba(0,0,0,0.2);
        }

        .bins-container {
            margin-top: 40px;
            width: 100%;
            max-width: 100%;
            border: 2px solid #ddd;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.1);
            background-color: #fff;
            padding: 20px;
        }

        .bins-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .bins-header h2 {
            font-size: 1.4rem;
        }

        .bin-count {
            font-weight: bold;
        }

        .bins-list {
            max-height: 600px;
            overflow-y: auto;
        }

        .bin-card {
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 10px;
            background-color: #f5f5f5;
        }

        .bin-card.warning { background-color: #f8d7da; }
        .bin-card.normal { background-color: #d4edda; }

        .bin-progress {
            width: 100%;
            background-color: #e9ecef;
            height: 10px;
            border-radius: 6px;
            margin-top: 5px;
        }

        .bin-progress-bar {
            height: 10px;
            border-radius: 6px;
        }

        .progress-warning { background-color: #e74c3c; }
        .progress-normal { background-color: #7ccc63; }

        .bin-details {
            display: flex;
            justify-content: space-between;
            margin-top: 5px;
        }

        #mapAndList {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 25px;
            margin-top: 40px;
        }

        .floor-frame { width: 45%; }
        .bins-container { width: 55%; }

        .map-card-body {
            overflow: hidden;
            height: 650px;
            transition: height 0.3s ease;
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
        <footer class="main-footer" style="background-color: rgba(0, 0, 0, 0); color: #ffff" >
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
