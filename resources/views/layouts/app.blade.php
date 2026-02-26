<!DOCTYPE html>
<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ env('APP_NAME') }}</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="{{ asset('adminlte') }}/plugins/fontawesome-free/css/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('adminlte') }}/dist/css/adminlte.min.css">
    @vite('resources/js/app.css')
    <link rel="stylesheet" href="{{ asset('adminlte') }}/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet"
        href="{{ asset('adminlte') }}/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="{{ asset('adminlte') }}/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
    
    <!-- PWA -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#007bff">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="POS">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">

    <style>
        /* Global Loading Spinner */
        #global-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.7);
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }
        .spinner-container {
            border: 8px solid #f3f3f3;
            border-top: 8px solid #3498db;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .loading-text {
            margin-top: 15px;
            font-weight: bold;
            color: #333;
            font-size: 1.1rem;
        }
    </style>

    @yield('styles')
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div id="global-loader">
        <div class="spinner-container"></div>
        <div class="loading-text">Mohon Tunggu...</div>
    </div>
    @include('sweetalert::alert')
    <div class="wrapper">

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i
                            class="fas fa-bars"></i></a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="/dashboard" class="nav-link">Home</a>
                </li>

            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <div class="dropdown">
                    <button class="btn dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false">
                        {{ ucwords(auth()->user()->name) }}
                    </button>
                    <div class="dropdown-menu">
                        <button type="button" class="btn S" data-toggle="modal" data-target="#formGantiPassword">
                            Ganti Password
                        </button>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn text-danger">Logout</button>
                        </form>
                    </div>
                </div>

            </ul>
        </nav>
        <!-- /.navbar -->
        <x-user.form-ganti-password />

        <!-- Main Sidebar Container -->
        <x-admin.aside />

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">@yield('content_title')</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                                <li class="breadcrumb-item active">@yield('content_title')</li>
                            </ol>
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                </div><!-- /.container-fluid -->
            </div>
            <!-- /.content-header -->

            <!-- Main content -->
            <div class="content">
                <div class="container-fluid">
                    @yield('content')
                    <!-- /.row -->
                </div><!-- /.container-fluid -->
            </div>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->


        <!-- Main Footer -->
        <footer class="main-footer">
            <!-- To the right -->
            <div class="float-right d-none d-sm-inline">
                Anything you want
            </div>
            <!-- Default to the left -->
            <strong>Copyright &copy; 2026 <a href="https://rayhn.my.id">rayhn.my.id</a>.</strong> All rights
            reserved.
        </footer>
    </div>
    <!-- ./wrapper -->

    <!-- REQUIRED SCRIPTS -->

    <!-- jQuery -->
    <script src="{{ asset('adminlte') }}/plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="{{ asset('adminlte') }}/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="{{ asset('adminlte') }}/dist/js/adminlte.min.js"></script>
    <script src="{{ asset('adminlte') }}/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="{{ asset('adminlte') }}/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="{{ asset('adminlte') }}/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
    <script src="{{ asset('adminlte') }}/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
    <script src="{{ asset('adminlte') }}/plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
    <script src="{{ asset('adminlte') }}/plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
    <script src="{{ asset('adminlte') }}/plugins/jszip/jszip.min.js"></script>
    <script src="{{ asset('adminlte') }}/plugins/pdfmake/pdfmake.min.js"></script>
    <script src="{{ asset('adminlte') }}/plugins/pdfmake/vfs_fonts.js"></script>
    <script src="{{ asset('adminlte') }}/plugins/datatables-buttons/js/buttons.html5.min.js"></script>
    <script src="{{ asset('adminlte') }}/plugins/datatables-buttons/js/buttons.print.min.js"></script>
    <script src="{{ asset('adminlte') }}/plugins/datatables-buttons/js/buttons.colVis.min.js"></script>
    <script>
        $(function() {
            // Show loader on form submission
            $('form').on('submit', function() {
                // Check if the form is valid (for simple validations)
                if (this.checkValidity()) {
                    $('#global-loader').css('display', 'flex');
                }
            });

            // Show loader on menu clicks/navigation
            $('a.nav-link, a.btn').on('click', function(e) {
                let href = $(this).attr('href');
                let target = $(this).attr('target');
                
                // Only show if it's a real navigation to another internal page
                if (href && href !== '#' && !href.startsWith('javascript:') && !target) {
                    $('#global-loader').css('display', 'flex');
                }
            });

            // Global AJAX Loader
            $(document).ajaxStart(function() {
                $('#global-loader').css('display', 'flex');
            }).ajaxStop(function() {
                $('#global-loader').hide();
            });

            $("#table1").DataTable({
                "responsive": true,
                "lengthChange": true,
                "autoWidth": false,
                "buttons": ["csv", "excel", "pdf", "print"]
            }).buttons().container().appendTo('#table1_wrapper .col-md-6:eq(0)');
            
            $('#table2').DataTable({
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true,
                "buttons": ["csv", "excel", "pdf", "print"]
            }).buttons().container().appendTo('#table2_wrapper .col-md-6:eq(0)');

            // Global Currency Formatter
            $('body').on('input', '.currency-input', function(e) {
                let value = $(this).val().replace(/[^0-9]/g, '');
                if (value !== '') {
                    value = new Intl.NumberFormat('id-ID').format(value);
                }
                $(this).val(value);
            });
        });

        /**
         * Smart Print Helper
         * Detects Android/Tablet to use RawBT app, 
         * or falls back to standard browser print/local bridge.
         */
        function smartPrint(printUrl) {
            let isAndroid = /android/i.test(navigator.userAgent);
            
            if (isAndroid) {
                // If the URL is relative, make it absolute for RawBT
                let absoluteUrl = new URL(printUrl, window.location.origin).href;
                // Encode the URL and construct the RawBT intent
                let encodedUrl = encodeURIComponent(absoluteUrl);
                let rawbtUrl = "rawbt:" + encodedUrl;
                
                // Directly redirect to RawBT app
                window.location.href = rawbtUrl;
            } else {
                // For desktop/laptop, open a new tab/popup for print preview
                // Alternatively, here could be an AJAX call to the Local Bridge
                let printWindow = window.open(printUrl, '_blank', 'width=400,height=600');
                if (printWindow) {
                    printWindow.focus();
                } else {
                    Swal.fire('Pop-up Diblokir!', 'Mohon izinkan pop-up untuk situs ini agar bisa mencetak struk.', 'warning');
                }
            }
        }
    </script>
    @yield('scripts')
    <!-- PWA Service Worker -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('Service Worker registered', reg))
                    .catch(err => console.log('Service Worker registration failed', err));
            });
        }
    </script>
</body>

</html>
