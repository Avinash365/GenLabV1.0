<!DOCTYPE html>
<html lang="en" data-layout-mode="light_mode">


<head>

    <!-- Meta Tags -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Dreams POS is a powerful Bootstrap based Inventory Management Admin Template designed for businesses, offering seamless invoicing, project tracking, and estimates.">
    <meta name="keywords"
        content="inventory management, admin dashboard, bootstrap template, invoicing, estimates, business management, responsive admin, POS system">
    <meta name="author" content="Dreams Technologies">
    <meta name="robots" content="index, follow">
    <title>@yield('title')</title>



    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="{{ url('assets/img/favicon.png') }}">

    <!-- Apple Touch Icon -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ url('assets/img/apple-touch-icon.png') }}">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ url('assets/css/bootstrap.min.css') }}">

    <!-- Datetimepicker CSS -->
    <link rel="stylesheet" href="{{ url('assets/css/bootstrap-datetimepicker.min.css') }}">

    <!-- animation CSS -->
    <link rel="stylesheet" href="{{ url('assets/css/animate.css') }}">

    <!-- Select2 CSS -->
    <link rel="stylesheet" href="{{ url('assets/plugins/select2/css/select2.min.css') }}">

    <!-- Daterangepikcer CSS -->
    <link rel="stylesheet" href="{{ url('assets/plugins/daterangepicker/daterangepicker.css') }}">

    <!-- Tabler Icon CSS -->
    <link rel="stylesheet" href="{{ url('assets/plugins/tabler-icons/tabler-icons.css') }}">

    <!-- Fontawesome CSS -->
    <link rel="stylesheet" href="{{ url('assets/plugins/fontawesome/css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ url('assets/plugins/fontawesome/css/all.min.css') }}">

    <!-- Color Picker Css -->
    <link rel="stylesheet" href="{{ url('assets/plugins/%40simonwep/pickr/themes/nano.min.css') }}">

    <!-- Main CSS -->
    <link rel="stylesheet" href="{{ url('assets/css/style.css') }}">

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

    <!-- Add Stock -->
    <div class="modal fade" id="add-stock">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="page-title">
                        <h4>Add Stock</h4>
                    </div>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="https://dreamspos.dreamstechnologies.com/html/template/index.html">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="mb-3">
                                    <label class="form-label">Warehouse <span class="text-danger ms-1">*</span></label>
                                    <select class="select">
                                        <option>Select</option>
                                        <option>Lobar Handy</option>
                                        <option>Quaint Warehouse</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="mb-3">
                                    <label class="form-label">Store <span class="text-danger ms-1">*</span></label>
                                    <select class="select">
                                        <option>Select</option>
                                        <option>Selosy</option>
                                        <option>Logerro</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="mb-3">
                                    <label class="form-label">Responsible Person <span
                                            class="text-danger ms-1">*</span></label>
                                    <select class="select">
                                        <option>Select</option>
                                        <option>Steven</option>
                                        <option>Gravely</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="search-form mb-0">
                                    <label class="form-label">Product <span class="text-danger ms-1">*</span></label>
                                    <input type="text" class="form-control" placeholder="Select Product">
                                    <i data-feather="search" class="feather-search"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-md btn-dark me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-md btn-primary">Add Stock</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- /Add Stock -->

    <!-- jQuery -->
    <script src="{{ url('assets/js/jquery-3.7.1.min.js') }}"></script>

    <!-- Feather Icon JS -->
    <script src="{{ url('assets/js/feather.min.js') }}"></script>

    <!-- Slimscroll JS -->
    <script src="{{ url('assets/js/jquery.slimscroll.min.js') }}"></script>

    <!-- Bootstrap Core JS -->
    <script src="{{ url('assets/js/bootstrap.bundle.min.js') }}"></script>

    <!-- ApexChart JS -->
    <script src="{{ url('assets/plugins/apexchart/apexcharts.min.js') }}"></script>
    <script src="{{ url('assets/plugins/apexchart/chart-data.js') }}"></script>

    <!-- Chart JS -->
    <script src="{{ url('assets/plugins/chartjs/chart.min.js') }}"></script>
    <script src="{{ url('assets/plugins/chartjs/chart-data.js') }}"></script>

    <!-- Daterangepikcer JS -->
    <script src="{{ url('assets/js/moment.min.js') }}"></script>
    <script src="{{ url('assets/plugins/daterangepicker/daterangepicker.js') }}"></script>

    <!-- Select2 JS -->
    <script src="{{ url('assets/plugins/select2/js/select2.min.js') }}"></script>

    <!-- Color Picker JS -->
    <script src="{{ url('assets/plugins/%40simonwep/pickr/pickr.es5.min.js') }}"></script>

    <!-- Custom JS -->
    <script src="{{ url('assets/js/theme-colorpicker.js') }}"></script>
    <script src="{{ url('assets/js/script.js') }}"></script>



</body>


</html>
