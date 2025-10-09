<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title><?php echo $__env->yieldContent('title', $appSettings['company_name'] ?? 'Admin'); ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo e($appSettings['site_logo_url'] ?? url('assets/img/favicon.png')); ?>" />

    <!-- Global Styles -->
    <link rel="stylesheet" href="<?php echo e(url('assets/css/bootstrap.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(url('assets/css/feather.css')); ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.47.0/tabler-icons.min.css">
    <link rel="stylesheet" href="<?php echo e(url('assets/plugins/fontawesome/css/all.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(url('assets/css/style.css')); ?>">
    <style>
      /* Fallback: always allow sidebar to scroll */
      .sidebar-inner { max-height: 100vh; overflow-y: auto; }
    </style>
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body>
    <div class="main-wrapper">
        <?php echo $__env->make('superadmin.layouts.include.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <?php echo $__env->make('superadmin.layouts.include.header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <div class="page-wrapper">
            <?php echo $__env->yieldContent('content'); ?>

            <?php echo $__env->make('superadmin.layouts.include.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>
    </div>

    <!-- Global Scripts -->
    <script src="<?php echo e(url('assets/js/jquery-3.7.1.min.js')); ?>"></script>
    <script src="<?php echo e(url('assets/js/bootstrap.bundle.min.js')); ?>"></script>
    <script src="<?php echo e(url('assets/js/feather.min.js')); ?>"></script>
    <script src="<?php echo e(url('assets/js/jquery.slimscroll.min.js')); ?>"></script>
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
    <script src="<?php echo e(url('assets/js/script.js')); ?>"></script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html><?php /**PATH C:\Mamp\htdocs\GenLabV1.0\resources\views/superadmin/layouts/master.blade.php ENDPATH**/ ?>