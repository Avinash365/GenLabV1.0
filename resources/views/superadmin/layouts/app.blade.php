<!DOCTYPE html>
<html lang="en" data-layout-mode="light_mode">

<head>
    <!-- Meta Tags -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="ITL is a powerful Bootstrap based Inventory Management Admin Template designed for businesses, offering seamless invoicing, project tracking, and estimates.">
    <meta name="keywords"
        content="inventory management, admin dashboard, bootstrap template, invoicing, estimates, business management, responsive admin, POS system">
    <meta name="author" content="Dreams Technologies">
    <meta name="robots" content="index, follow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php(
        $__appSetting = isset($setting) ? $setting : (View::shared('setting') ?? \App\Models\SiteSetting::first())
    )
    @php(
        $___faviconBase = optional($__appSetting)->site_favicon ? asset('storage/' . optional($__appSetting)->site_favicon) : url('assets/img/favicon.png')
    )
    @php(
        $___favVersion = optional($__appSetting)->updated_at ? ('?v=' . optional($__appSetting)->updated_at->timestamp) : ''
    )
    @php($__pageTitle = trim($__env->yieldContent('title')))
    <title>{{ $__pageTitle !== '' ? ($__pageTitle . ' • ' . (optional($__appSetting)->project_title ?? config('app.name', 'Dream POS'))) : (optional($__appSetting)->project_title ?? config('app.name', 'Dream POS')) }}</title>
    <link id="app-favicon" rel="icon" type="image/png" sizes="32x32" href="{{ $___faviconBase . $___favVersion }}">
    <link rel="shortcut icon" href="{{ $___faviconBase . $___favVersion }}" type="image/x-icon">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ url('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{url('assets/plugins/summernote/summernote-bs4.min.css')}}">  
    <link rel="stylesheet" href="{{ url('assets/css/bootstrap-datetimepicker.min.css') }}">
    <link rel="stylesheet" href="{{ url('assets/css/animate.css') }}">
    <link rel="stylesheet" href="assets/plugins/bootstrap-tagsinput/bootstrap-tagsinput.css">
    <link rel="stylesheet" href="{{ url('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="assets/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="{{ url('assets/plugins/daterangepicker/daterangepicker.css') }}">
    <link rel="stylesheet" href="{{ url('assets/plugins/tabler-icons/tabler-icons.css') }}">
    <link rel="stylesheet" href="{{ url('assets/plugins/fontawesome/css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ url('assets/plugins/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ url('assets/plugins/%40simonwep/pickr/themes/nano.min.css') }}">
    <link rel="stylesheet" href="{{ url('assets/css/style.css') }}">
    
    @stack('styles')

    <!-- FORCE Bootstrap table colors fix -->
    <style>
        .table-success { background-color: #d4edda !important; }
        .table-danger { background-color: #f8d7da !important; }
    </style>

</head>

<body>
    <!-- Main Wrapper -->
    <div class="main-wrapper">

        <!-- Header -->
        @include('superadmin.layouts.include.header')
        <!-- /Header -->

        <!-- Sidebar -->
        @include('superadmin.layouts.include.sidebar')
        <!-- /Sidebar -->

        <div class="page-wrapper">
            @yield('content')
            @include('superadmin.layouts.include.footer')
        </div>

    </div>
    <!-- /Main Wrapper -->

    <!-- Scripts -->
    <script src="{{ url('assets/js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ url('assets/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ url('assets/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ url('assets/js/feather.min.js') }}"></script>
    <script src="{{ url('assets/js/jquery.slimscroll.min.js') }}"></script>
    <script src="{{ url('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ url('assets/plugins/apexchart/apexcharts.min.js') }}"></script>
    <script src="{{ url('assets/plugins/apexchart/chart-data.js') }}"></script>
    <script src="{{ url('assets/plugins/chartjs/chart.min.js') }}"></script>
    <script src="{{ url('assets/plugins/chartjs/chart-data.js') }}"></script>
    <script src="{{ url('assets/js/moment.min.js') }}"></script>
    <script src="{{ url('assets/js/bootstrap-datetimepicker.min.js') }}"></script>
    <script src="{{ url('assets/plugins/daterangepicker/daterangepicker.js') }}"></script>
    <script src="{{ url('assets/plugins/select2/js/select2.min.js') }}"></script>
    <script src="{{ url('assets/plugins/%40simonwep/pickr/pickr.es5.min.js') }}"></script>
    <script src="{{ url('assets/js/theme-colorpicker.js') }}"></script>
    <script src="{{ url('assets/js/script.js') }}"></script>
    <script src="{{url('assets/plugins/summernote/summernote-bs4.min.js')}}" ></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @stack('scripts')
</body>
</html>
