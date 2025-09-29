<?php $__env->startSection('title', 'Superadmin Dashboard'); ?>

<?php $__env->startSection('content'); ?>
    <div class="content">

        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-2">
            <div class="mb-3">
                <h1 class="mb-1">Welcome, <?php echo e(auth()->user()->name ?? 'Admin'); ?></h1>
               
            </div>
            
        </div>

        <!-- Sales & Purchase + Overall Info -->
        <div class="row g-3 mb-4">
            <!-- Sales & Purchase -->
            <div class="col-xl-8">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h5 class="mb-0 d-flex align-items-center gap-2">
                            <i class="ti ti-chart-bar"></i>
                            Sales & Purchase
                        </h5>
                        <div class="range-toggle btn-group" role="group" aria-label="Time Range">
                            <button type="button" class="btn btn-sm btn-outline-secondary active" data-range="1D">1D</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-range="1W">1W</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-range="1M">1M</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-range="3M">3M</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-range="6M">6M</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-range="1Y">1Y</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-3 mb-3 flex-wrap">
                            <div class="d-flex align-items-center gap-2">
                                <span class="legend-dot legend-purchase"></span>
                                <div>
                                    <div class="text-muted small">Total Purchase</div>
                                    <div id="totalPurchase" class="fw-semibold">0</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="legend-dot legend-sales"></span>
                                <div>
                                    <div class="text-muted small">Total Sales</div>
                                    <div id="totalSales" class="fw-semibold">0</div>
                                </div>
                            </div>
                        </div>
                        <div class="chart-container" style="height: 300px;">
                            <canvas id="salesPurchaseChart" height="300" aria-label="Sales and Purchase Chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Sales & Purchase -->

            <!-- Overall Information + Customers Overview -->
            <div class="col-xl-4">
                <div class="d-flex flex-column gap-3 h-100">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h6 class="mb-0 d-flex align-items-center gap-2"><i class="ti ti-info-circle"></i> Overall Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-4">
                                    <div class="text-center p-2 border rounded small">
                                        <div class="text-muted">Suppliers</div>
                                        <div class="fw-bold" id="statSuppliers">6987</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="text-center p-2 border rounded small">
                                        <div class="text-muted">Customer</div>
                                        <div class="fw-bold" id="statCustomers">4896</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="text-center p-2 border rounded small">
                                        <div class="text-muted">Orders</div>
                                        <div class="fw-bold" id="statOrders">487</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card flex-fill">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h6 class="mb-0 d-flex align-items-center gap-2"><i class="ti ti-users"></i> Customers Overview</h6>
                            <div class="small text-muted">Today</div>
                        </div>
                        <div class="card-body d-flex align-items-center gap-3">
                            <div style="width: 120px; height: 120px;">
                                <canvas id="customersDonut" width="120" height="120"></canvas>
                            </div>
                            <div class="d-flex gap-4">
                                <div>
                                    <div class="text-muted small">First Time</div>
                                    <div class="h5 mb-0" id="firstTimeVal">5.5K</div>
                                    <div class="badge bg-success-subtle text-success small">+25%</div>
                                </div>
                                <div>
                                    <div class="text-muted small">Return</div>
                                    <div class="h5 mb-0" id="returnVal">3.5K</div>
                                    <div class="badge bg-success-subtle text-success small">+21%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Overall Information + Customers Overview -->
        </div>
        <!-- /Sales & Purchase + Overall Info -->

        <!-- Booking Trend & Status -->
        <div class="row g-3 mb-4">
            <div class="col-xl-8">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 d-flex align-items-center gap-2"><i class="ti ti-calendar"></i> Booking Trend</h6>
                        <div class="small text-muted">Last 30 days</div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 280px;">
                            <canvas id="bookingTrend"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 d-flex align-items-center gap-2"><i class="ti ti-chart-donut-2"></i> Booking Status</h6>
                        <a href="#" class="small text-decoration-underline">View All</a>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-between gap-3">
                        <div style="width: 140px; height: 140px;">
                            <canvas id="bookingStatusDonut" width="140" height="140"></canvas>
                        </div>
                        <div class="small">
                            <div class="d-flex align-items-center gap-2 mb-2"><span class="legend-dot" style="background:#ff8a26"></span> Pending</div>
                            <div class="d-flex align-items-center gap-2 mb-2"><span class="legend-dot" style="background:#2bb673"></span> Completed</div>
                            <div class="d-flex align-items-center gap-2"><span class="legend-dot" style="background:#ffc107"></span> Processing</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Booking Trend & Status -->

        <!-- Dispatch, Attendance & Accounts -->
        <div class="row g-3 mb-4">
            <div class="col-xl-8">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 d-flex align-items-center gap-2"><i class="ti ti-truck-delivery"></i> Report Dispatch</h6>
                        <div class="small text-muted">This week</div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 260px;">
                            <canvas id="dispatchBar"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 d-flex flex-column gap-3">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h6 class="mb-0"><i class="ti ti-id-badge"></i> Attendance</h6>
                        <a href="#" class="small text-decoration-underline">Today</a>
                    </div>
                    <div class="card-body d-flex align-items-center gap-3">
                        <div style="width:110px;height:110px"><canvas id="attendanceDonut" width="110" height="110"></canvas></div>
                        <div class="small">
                            <div class="mb-1">Present: <span id="attPresent" class="fw-semibold">0</span></div>
                            <div class="mb-1">Absent: <span id="attAbsent" class="fw-semibold">0</span></div>
                            <div>Late: <span id="attLate" class="fw-semibold">0</span></div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h6 class="mb-0"><i class="ti ti-report-money"></i> Accounts - Invoices</h6>
                        <a href="#" class="small text-decoration-underline">This month</a>
                    </div>
                    <div class="card-body d-flex align-items-center gap-3">
                        <div style="width:110px;height:110px"><canvas id="invoiceDonut" width="110" height="110"></canvas></div>
                        <div class="small">
                            <div class="mb-1">Paid: <span id="invPaid" class="fw-semibold">0</span></div>
                            <div class="mb-1">Unpaid: <span id="invUnpaid" class="fw-semibold">0</span></div>
                            <div>Overdue: <span id="invOverdue" class="fw-semibold">0</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Dispatch, Attendance & Accounts -->

        <!-- Analyst Workload & Inventory -->
        <div class="row g-3 mb-4">
            <div class="col-xl-8">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 d-flex align-items-center gap-2"><i class="ti ti-flask"></i> Lab Analysts - Workload</h6>
                        <a href="#" class="small text-decoration-underline">Today</a>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 260px;">
                            <canvas id="analystWorkloadChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 d-flex align-items-center gap-2"><i class="ti ti-packages"></i> Inventory - Low Stock</h6>
                        <a href="#" class="small text-decoration-underline">View All</a>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled low-stock-list mb-0">
                            <li class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                <div class="d-flex align-items-center gap-2"><span class="bullet bg-warning"></span> Reagent A</div>
                                <span class="badge text-bg-warning">08</span>
                            </li>
                            <li class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                <div class="d-flex align-items-center gap-2"><span class="bullet bg-warning"></span> Reagent B</div>
                                <span class="badge text-bg-warning">05</span>
                            </li>
                            <li class="d-flex align-items-center justify-content-between py-2">
                                <div class="d-flex align-items-center gap-2"><span class="bullet bg-warning"></span> Kit C</div>
                                <span class="badge text-bg-warning">03</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Analyst Workload & Inventory -->

        <div class="row">
            <div class="col-xl-3 col-sm-6 col-12 d-flex">
                <div class="card bg-primary sale-widget flex-fill">
                    <div class="card-body d-flex align-items-center">
                        <span class="employee-icon bg-white text-primary p-2 rounded-circle d-inline-flex align-items-center justify-content-center">
  <i class="fas fa-users fs-24"></i>
</span>
                        <div class="ms-2">
                            <p class="text-white mb-1">Total Users</p>
                            <div class="d-inline-flex align-items-center flex-wrap gap-2">
                                <h4 class="text-white">48,988,078</h4>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 col-12 d-flex">
                <div class="card bg-secondary sale-widget flex-fill">
    <div class="card-body d-flex align-items-center">
        <span class="student-icon bg-white text-secondary p-2 rounded-circle d-inline-flex align-items-center justify-content-center">
            <i class="ti ti-school fs-24"></i>
        </span>
        <div class="ms-2">
            <p class="text-white mb-1">Total Invoice</p>
            <div class="d-inline-flex align-items-center flex-wrap gap-2">
                <h4 class="text-white">16,478,145</h4>
            </div>
        </div>
    </div>
</div>
            </div>
            <div class="col-xl-3 col-sm-6 col-12 d-flex">
                <div class="card bg-teal sale-widget flex-fill">
                    <div class="card-body d-flex align-items-center">
                        <span class="sale-icon bg-white text-teal">
                            <i class="ti ti-gift fs-24"></i>
                        </span>
                        <div class="ms-2">
                            <p class="text-white mb-1">Letters</p>
                            <div class="d-inline-flex align-items-center flex-wrap gap-2">
                                <h4 class="text-white">24,145,789</h4>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 col-12 d-flex">
                <div class="card bg-info sale-widget flex-fill">
    <div class="card-body d-flex align-items-center">
        <span class="teacher-icon bg-white text-info p-2 rounded-circle d-inline-flex align-items-center justify-content-center">
            <i class="ti ti-chalkboard fs-24"></i>
        </span>
        <div class="ms-2">
            <p class="text-white mb-1">Total Bookings</p>
            <div class="d-inline-flex align-items-center flex-wrap gap-2">
                <h4 class="text-white">18,458,747</h4>
            </div>
        </div>
    </div>
</div>

            </div>
        </div>


        <div class="row">

            <!-- Profit -->
            <div class="col-xl-3 col-sm-6 col-12 d-flex">
                <div class="card revenue-widget flex-fill">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                            <div>
                                <h4 class="mb-1">798</h4>
                                <p>Active </p>
                            </div>
                            <span class="revenue-icon bg-cyan-transparent text-cyan">
                                <i class="fa-solid fa-layer-group fs-16"></i>
                            </span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <p class="mb-0"><span class="fs-13 fw-bold text-success">Last 30 Days</span> </p>
                            <a href="profit-and-loss.html" class="text-decoration-underline fs-13 fw-medium">View All</a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Profit -->

            <!-- Invoice -->
            <div class="col-xl-3 col-sm-6 col-12 d-flex">
                <div class="card revenue-widget flex-fill">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                            <div>
                                <h4 class="mb-1">48,988,78</h4>
                                <p>Paid</p>
                            </div>
                            <span class="revenue-icon bg-teal-transparent text-teal">
                                <i class="ti ti-chart-pie fs-16"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Invoice -->

            <!-- Expenses -->
            <div class="col-xl-3 col-sm-6 col-12 d-flex">
                <div class="card revenue-widget flex-fill">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                            <div>
                                <h4 class="mb-1">8,980,097</h4>
                                <p>Genreted</p>
                            </div>
                            <span class="revenue-icon bg-orange-transparent text-orange">
                                <i class="ti ti-lifebuoy fs-16"></i>
                            </span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <p class="mb-0"><span class="fs-13 fw-bold text-success"></span> Total Amount</p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Expenses -->

            <!-- Returns -->
            <div class="col-xl-3 col-sm-6 col-12 d-flex">
                <div class="card revenue-widget flex-fill">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                            <div>
                                <h4 class="mb-1">78,458,798</h4>
                                <p>Genreted</p>
                            </div>
                            <span class="revenue-icon bg-indigo-transparent text-indigo">
                                <i class="ti ti-hash fs-16"></i>
                            </span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <p class="mb-0"><span class="fs-13 fw-bold text-danger"></span> Total Amount</p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Returns -->

        </div>

    </div>
    <?php echo $__env->make('components.chatbot', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <link rel="stylesheet" href="/css/superadmin-dashboard.css">
    <link rel="stylesheet" href="/css/chatbot.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="/js/superadmin-dashboard.js" defer></script>
    <script src="/js/chatbot.js"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH A:\GenTech\htdocs\GenTech_bug\GenLab\resources\views/superadmin/dashboard.blade.php ENDPATH**/ ?>