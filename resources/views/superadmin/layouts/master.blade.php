<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>@yield('title', $appSettings['company_name'] ?? 'Admin')</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ $appSettings['site_logo_url'] ?? url('assets/img/favicon.png') }}" />

    <!-- Global Styles -->
    <link rel="stylesheet" href="{{ url('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ url('assets/plugins/feather/feather.css') }}">
    <link rel="stylesheet" href="{{ url('assets/plugins/tabler-icons/tabler-icons.min.css') }}">
    <link rel="stylesheet" href="{{ url('assets/css/style.css') }}">
    <style>
      /* Fallback: always allow sidebar to scroll */
      .sidebar-inner { max-height: 100vh; overflow-y: auto; }
    </style>
    @stack('styles')
</head>
<body>
    <div class="main-wrapper">
        @include('superadmin.layouts.include.sidebar')

        <div class="page-wrapper">
            @yield('content')
        </div>
    </div>

    <!-- Global Scripts -->
    <script src="{{ url('assets/js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ url('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ url('assets/js/feather.min.js') }}"></script>
    <script src="{{ url('assets/plugins/slimscroll/jquery.slimscroll.min.js') }}"></script>
    <script>
        if (window.feather) { window.feather.replace(); }
        (function($){
            $(function(){
                if ($ && $.fn && $.fn.slimScroll) {
                    $('.slimscroll').each(function(){
                        var $el = $(this);
                        if (!$el.parent().hasClass('slimScrollDiv')) {
                            $el.slimScroll({ height: '100vh', size: '6px', color: '#adb5bd', wheelStep: 10, touchScrollStep: 50 });
                        }
                    });
                }
            });
        })(window.jQuery);
    </script>
    <script src="{{ url('assets/js/script.js') }}"></script>
    @stack('scripts')
</body>
</html>