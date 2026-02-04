@if(!empty($charts))
    <div class="row g-3 mb-4">
        @foreach($charts as $chart)
            @php
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
            @endphp

            <div class="{{ $cardClass }}">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div style="height: {{ $chartHeight }}px;">
                            <canvas id="{{ $chartId }}"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                (function() {
                    var init = function() {
                        const ctx = document.getElementById(@json($chartId));
                        if (!ctx) return;
                        if (ctx.chart) ctx.chart.destroy(); // Destroy existing chart if any
                        const chartConfig = {
                            type: @json($chartType),
                            data: @json($data),
                            options: @json($options),
                        };
                        if (window.Chart) {
                            ctx.chart = new Chart(ctx, chartConfig);
                        }
                    };

                    if (document.readyState !== 'loading') {
                        init();
                    } else {
                        document.addEventListener('DOMContentLoaded', init);
                    }
                })();
            </script>
        @endforeach
    </div>
@endif
