<?php if(!empty($charts)): ?>
    <div class="row g-3 mb-4">
        <?php $__currentLoopData = $charts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chart): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $chartId = $chart['id'] ?? ('chart_' . \Illuminate\Support\Str::random(8));
                $chartTitle = $chart['title'] ?? null;
                $cardClass = $chart['card_class'] ?? 'col-xl-6 col-lg-6';
                $chartType = $chart['type'] ?? 'bar';
                $chartHeight = $chart['height'] ?? 320;

                $data = [
                    'labels' => $chart['labels'] ?? [],
                    'datasets' => $chart['datasets'] ?? [],
                ];

                $defaultOptions = [
                    'responsive' => true,
                    'maintainAspectRatio' => false,
                    'plugins' => [
                        'legend' => ['position' => 'bottom'],
                        'title' => [
                            'display' => (bool) $chartTitle,
                            'text' => $chartTitle,
                            'font' => ['size' => 14, 'weight' => '600'],
                        ],
                        'tooltip' => ['mode' => 'index', 'intersect' => false],
                    ],
                    'scales' => [
                        'x' => ['display' => true, 'grid' => ['display' => false]],
                        'y' => ['display' => true, 'beginAtZero' => true],
                    ],
                ];

                if (in_array($chartType, ['doughnut', 'pie', 'polarArea'], true)) {
                    unset($defaultOptions['scales']);
                }

                $options = isset($chart['options'])
                    ? array_replace_recursive($defaultOptions, $chart['options'])
                    : $defaultOptions;
            ?>

            <div class="<?php echo e($cardClass); ?>">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div style="height: <?php echo e($chartHeight); ?>px;">
                            <canvas id="<?php echo e($chartId); ?>"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <?php $__env->startPush('scripts'); ?>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const ctx = document.getElementById(<?php echo json_encode($chartId, 15, 512) ?>);
                        if (!ctx) return;
                        const chartConfig = {
                            type: <?php echo json_encode($chartType, 15, 512) ?>,
                            data: <?php echo json_encode($data, 15, 512) ?>,
                            options: <?php echo json_encode($options, 15, 512) ?>,
                        };
                        if (window.Chart) {
                            new Chart(ctx, chartConfig);
                        }
                    });
                </script>
            <?php $__env->stopPush(); ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
<?php endif; ?>
<?php /**PATH C:\Mamp\htdocs\GenLabV1.0\resources\views/superadmin/departments/partials/charts.blade.php ENDPATH**/ ?>