<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SMARTBIN</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('uploads/images/bin2.png') }}">
    
    <!-- Google Font -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/fontawesome-free/css/all.min.css') }}">

    <!-- AdminLTE Theme style -->
    <link rel="stylesheet" href="{{ asset('adminlte/dist/css/adminlte.min.css') }}">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap JS (with Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>


    <!-- jQuery -->
    <script src="{{ asset('adminlte/plugins/jquery/jquery.min.js') }}"></script>

    <!-- Bootstrap 4 -->
    <script src="{{ asset('adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <!-- AdminLTE App -->
    <script src="{{ asset('adminlte/dist/js/adminlte.min.js') }}"></script>

    <!-- DataTables -->
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

    <!-- LIVEWIRE STYLES -->
    @livewireStyles

    @stack('styles')
   <style>
/* Top container: left = entries + buttons, right = search */
.dataTables_wrapper .top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    margin-bottom: 8px;
}

/* Left block: entries + buttons */
.dataTables_wrapper .top .left-block {
    display: flex;
    flex-direction: column; /* buttons below entries */
}

/* Show entries dropdown wider */
.dataTables_length select {
    width: 150px;
    display: inline-block;
    height: auto;          /* optional: lets it scale with font-size */
    padding: 0.3rem 1.2rem; /* optional: more spacing inside */
    font-size: 0.9rem;     /* optional: bigger font */
}

/* Buttons styling */
.dt-buttons .btn-custom {
    background-color: #ffffff;  /* white background */
    border: none;
    box-shadow: 0 4px 12px rgba(0,0,0,0.25); /* heavy shadow */
    font-weight: 600;
    margin-top: 6px;    /* spacing below entries */
    margin-right: 6px;
    transition: all 0.2s ease; /* smooth hover */
    color: #000000;     /* default text color */
}

/* Icon default colors (only when NOT hovered) */
.dt-buttons .btn-custom.copy i { color: #f39c12; }   /* orange */
.dt-buttons .btn-custom.csv i { color: #3498db; }    /* blue */
.dt-buttons .btn-custom.excel i { color: #27ae60; }  /* green */
.dt-buttons .btn-custom.pdf i { color: #e74c3c; }    /* red */
.dt-buttons .btn-custom.print i { color: #8e44ad; }  /* purple */
.dt-buttons .btn-custom.filter i { color: #2c3e50; } /* dark accent */

/* Hover: button background = icon color, icon + text white */
.dt-buttons .btn-custom.copy:hover {
    background-color: #f39c12;
    color: #ffffff;
}
.dt-buttons .btn-custom.csv:hover {
    background-color: #3498db;
    color: #ffffff;
}
.dt-buttons .btn-custom.excel:hover {
    background-color: #27ae60;
    color: #ffffff;
}
.dt-buttons .btn-custom.pdf:hover {
    background-color: #e74c3c;
    color: #ffffff;
}
.dt-buttons .btn-custom.print:hover {
    background-color: #8e44ad;
    color: #ffffff;
}
.dt-buttons .btn-custom.filter:hover {
    background-color: #2c3e50;
    color: #ffffff;
}

/* Force icon white on hover */
.dt-buttons .btn-custom:hover i {
    color: #ffffff;
}

/* Spacing between buttons */
.dt-buttons .btn-custom + .btn-custom {
    margin-left: 6px;
}

/* Right block: search bar */
.dataTables_wrapper .right-block {
    margin-top: 4px;
}

/* Optional: ensure search input width looks good */
.dataTables_wrapper .dataTables_filter input {
    width: 180px;
    display: inline-block;
}

    </style>

</head>

<body class="hold-transition sidebar-mini text-sm">
    @include('sweetalert::alert')

    <div class="wrapper">

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                        <i class="fas fa-bars"></i>
                    </a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="/dashboard" class="nav-link">SMARTBIN</a>
                </li>
            </ul>

            @php
                $photo = Auth::user()->profile_photo 
                    ? asset('uploads/profile/' . Auth::user()->profile_photo)
                    : 'https://via.placeholder.com/150';
            @endphp

            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="{{ $photo }}" 
                            class="rounded-circle" 
                            alt="Profile" 
                            width="30" 
                            height="30">
                        <span class="ms-2">{{ Auth::user()->name }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="{{ route('profile.index') }}">
                                <i class="fas fa-user me-2"></i> Profile
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="{{ route('profile.editPassword') }}">
                                <i class="fas fa-key me-2"></i> Reset Password
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="dropdown-item d-flex align-items-center">
                                    <i class="fas fa-sign-out-alt me-2"></i> Log Out
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>

        <!-- Sidebar -->
        <x-admin.aside />

        <!-- Content Wrapper -->
        <div class="content-wrapper">

            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">@yield('content_title')</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                                <li class="breadcrumb-item active">@yield('content_title')</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </div>
        </div>

        <footer class="main-footer">
            <div class="float-right d-none d-sm-inline">Anything you want</div>
            <strong>Copyright &copy; 2014-2021 SmartBin.</strong>
            All rights reserved.
        </footer>
    </div>
    <!-- Bootstrap 4 -->
    <script src="{{ asset('adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <script>

    document.addEventListener("DOMContentLoaded", function () {
    // Remove AdminLTE's custom scrollbar
    if ($('.sidebar').data('overlayScrollbars')) {
        $('.sidebar').overlayScrollbars().destroy();
    }
});
    document.addEventListener("DOMContentLoaded", function () {
    // Destroy AdminLTE’s OverlayScrollbars so we can control scrolling
    if ($('.sidebar').data('overlayScrollbars')) {
        $('.sidebar').overlayScrollbars().destroy();
    }

    // Apply normal scrolling
    $('.main-sidebar').css({
        'position': 'fixed',
        'height': '100vh',
        'overflow': 'hidden'
    });

    $('.sidebar').css({
        'height': '100%',
        'overflow-y': 'auto',
        'overflow-x': 'hidden'
    });
});
        /*$(function () {
            $("#table1").DataTable({
                responsive: true,
                lengthChange: false,
                autoWidth: true,
                buttons: ["copy", "csv", "excel", "pdf", "print", "colvis"]
            }).buttons().container().appendTo('#table1_wrapper .col-md-6:eq(0)');
*/

$(document).ready(function () { 
    $('.datatable').each(function() { 
    if (!$.fn.DataTable.isDataTable(this)) { 
    const hasButtons = $(this).hasClass('datatable-buttons'); 

     $(this).DataTable({ 
        responsive: true, 
        autoWidth: false, 
        lengthChange: true, 
        dom: hasButtons ? '<"top"<"left-block"lB><"right-block"f>>rtip' : 'lfrtip', 

        buttons: hasButtons ? [ 
            { extend: 'copyHtml5',
         className: 'btn btn-custom copy',
          text: '<i class="fas fa-copy"></i> Copy' },
           { extend: 'csvHtml5', className: 'btn btn-custom csv', text: '<i class="fas fa-file-csv"></i> CSV' }, 
           { extend: 'excelHtml5', className: 'btn btn-custom excel', text: '<i class="far fa-file-excel"></i> Excel' },
            { extend: 'pdfHtml5', className: 'btn btn-custom pdf', text: '<i class="far fa-file-pdf"></i> PDF' },
             { extend: 'print', className: 'btn btn-custom print', text: '<i class="fas fa-print"></i> Print' },
              { extend: 'colvis', className: 'btn btn-custom filter', text: '<i class="fas fa-sort"></i> Filter' } 
            ] : [] 
        }); 
        if (hasButtons) { 
            $(this).DataTable().buttons().container().appendTo($(this).closest('.left-block').find('.dt-buttons')); 

            } 
        } 
    });

            $('#table2').DataTable({
                paging: true,
                lengthChange: false,
                searching: false,
                ordering: true,
                info: true,
                autoWidth: true,
                responsive: true,
            });
        });
    </script>

    <!-- AlpineJS (REQUIRED for Livewire x-data) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- LIVEWIRE SCRIPTS -->
    @livewireScripts

     @stack('scripts')

</body>

</html>
