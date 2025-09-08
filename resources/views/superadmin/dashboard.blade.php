@extends('superadmin.layouts.app')

@section('title', 'Superadmin Dashboard')

@section('content')
    <div class="content">

        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-2">
            <div class="mb-3">
                <h1 class="mb-1">Welcome, {{ auth()->user()->name ?? 'Admin' }}</h1>
               
            </div>
            
        </div>

        <div class="row">
            <div class="col-xl-3 col-sm-6 col-12 d-flex">
                <div class="card bg-primary sale-widget flex-fill">
                    <div class="card-body d-flex align-items-center">
                        <span class="employee-icon bg-white text-primary p-2 rounded-circle d-inline-flex align-items-center justify-content-center">
  <i class="fas fa-users fs-24"></i>
</span>
                        <div class="ms-2">
                            <p class="text-white mb-1">Total Employee</p>
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
            <p class="text-white mb-1">Total Student</p>
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
                            <p class="text-white mb-1">Parents</p>
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
            <p class="text-white mb-1">Total Teacher</p>
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
                                <p>Admission</p>
                            </div>
                            <span class="revenue-icon bg-cyan-transparent text-cyan">
                                <i class="fa-solid fa-layer-group fs-16"></i>
                            </span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <p class="mb-0"><span class="fs-13 fw-bold text-success">30 Days</span> Interval</p>
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
                                <p>Vouchers</p>
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
                                <p>Transport</p>
                            </div>
                            <span class="revenue-icon bg-orange-transparent text-orange">
                                <i class="ti ti-lifebuoy fs-16"></i>
                            </span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <p class="mb-0"><span class="fs-13 fw-bold text-success"></span> Total Route</p>
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
                                <p>Hostel</p>
                            </div>
                            <span class="revenue-icon bg-indigo-transparent text-indigo">
                                <i class="ti ti-hash fs-16"></i>
                            </span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <p class="mb-0"><span class="fs-13 fw-bold text-danger"></span> Total Rooms</p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Returns -->

        </div>

    </div>
    @include('components.chatbot')
    <link rel="stylesheet" href="/css/chatbot.css">
    <script src="/js/chatbot.js"></script>
@endsection
