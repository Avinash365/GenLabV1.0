@extends('superadmin.layouts.app')

@section('content')
    <div class="page-header">
        <div class="add-item d-flex ms-4 mt-4">
            <div class="page-title">
                <h4>Meter Reading</h4>
                <h6>Upload and view meter readings</h6>
            </div>
        </div>
        
        <ul class="table-top-head list-inline d-flex gap-3" >
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadReadingModal">Upload Reading</button>

            <li class="list-inline-item">
                @php $q = http_build_query(request()->only(['search','month','year','marketing_person'])); @endphp
                <a href="{{ route('superadmin.meter-reading.export.pdf') }}{{ $q ? ('?'.$q) : '' }}" class="no-loader" data-bs-toggle="tooltip" title="PDF"><div class="fa fa-file-pdf"></div></a>
            </li>
            <li class="list-inline-item">
                @php $q = http_build_query(request()->only(['search','month','year','marketing_person'])); @endphp
                <a href="{{ route('superadmin.meter-reading.export.excel') }}{{ $q ? ('?'.$q) : '' }}" class="no-loader" data-bs-toggle="tooltip" title="Excel">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="24" fill="green" viewBox="0 0 24 24">
                        <path d="M19 2H8c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-8.5 14-2-3 2-3H9l-1.5 2.25L6 10H4l2.5 3L4 16h2l1.5-2.25L9 16h1.5zM19 20H8V4h11v16z"/>
                    </svg>
                </a>
            </li>
            <li style="margin-right:22px;"><a data-bs-toggle="tooltip" title="Refresh"><i class="ti ti-refresh" ></i></a></li>
        </ul>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @php
        $isAdmin = false;
        if (auth()->check()) {
            $userRole = auth()->user()->role;
            if (is_object($userRole) && isset($userRole->role_name)) {
                $roleName = $userRole->role_name;
            } elseif (is_string($userRole)) {
                $roleName = $userRole;
            } else {
                $roleName = '';
            }
            $isAdmin = $roleName ? stripos($roleName, 'admin') !== false : false;
        }
    @endphp
 
    <!-- Upload Modal -->
    <div class="modal fade" id="uploadReadingModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-md modal-dialog-scrollable">
        <div class="modal-content">
                    <div class="modal-header align-items-center">
                        <h5 class="modal-title mb-0">@if(!empty($hasOpen)) Upload Ending Reading @else Upload Meter Reading @endif</h5>
                        @if(!empty($hasOpen))
                                <span class="badge bg-warning ms-3">ENDING</span>
                        @else
                                <span class="badge bg-success ms-3">STARTING</span>
                        @endif
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
          <div class="modal-body">
            <form action="{{ route('superadmin.meter-reading.upload') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Current Reading</label>
                    <input type="number" step="any" name="current_reading" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Image (optional)</label>
                    <input type="file" name="image" accept="image/*" class="form-control">
                </div>

                @if(empty($hasOpen))
                    <!-- <div class="alert alert-info small">This will create a <strong>starting</strong> reading. You may provide an optional description and image.</div> -->
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                @else
                    <!-- <div class="alert alert-warning small">This will <strong>close</strong> the currently open reading. Description is optional and will be saved as the ending note.</div> -->
                @endif

                <div class="text-end">
                    <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Close</button>
                    @if(!empty($hasOpen))
                        <button class="btn btn-primary">Close Trip</button>
                    @else
                        <button class="btn btn-success">Start Trip</button>
                    @endif
                </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <div class="card mt-3">
        <div class="card-body p-0">

         <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">

            <!-- Search Form -->
            <div class="search-set">
                 <form method="GET" action="{{ route('superadmin.meter-reading.index') }}" class="d-flex input-group me-3" style="max-width:600px; width:100%">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search...">
                        <button class="btn btn-outline-secondary" type="submit">🔍</button>
                </form>
            </div>

            <!-- Month & Year Filter Form -->
            <div class="search-set">
                <form id="filterForm" method="GET" action="{{ route('superadmin.meter-reading.index') }}" class="d-flex input-group">

                    @if($isAdmin ?? false)
                            <select name="marketing_person" class="form-control me-2">
                                <option value="">All Marketing Persons</option>
                                @foreach($marketingPersons as $mp)
                                    <option value="{{ $mp->id }}" {{ request('marketing_person') == $mp->id ? 'selected' : '' }}>
                                        {{ $mp->name }} ({{ $mp->user_code }})
                                    </option>
                                @endforeach
                            </select>
                        @endif

                    <!-- Month Filter -->
                    <select name="month" class="form-control">
                            <option value="">Select Month</option>
                            @foreach(range(1,12) as $m)
                                <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                </option>
                            @endforeach
                        </select>

                    <!-- Year Filter -->
                    <select name="year" class="form-control">
                            <option value="">Select Year</option>
                            @foreach(range(date('Y'), date('Y') - 10) as $y)
                                <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endforeach
                    </select>

                        <button class="btn btn-outline-secondary" type="button" id="clearFiltersBtn">Clear</button>
                </form>
            </div>

        </div>


            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="readingsTable">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Description</th>
                            {{-- $isAdmin computed above --}}
                            @if($isAdmin)
                                <th>Marketing Person</th>
                            @endif
                            <th>Starting Reading (value &amp; date)</th>
                            <th>Ending Reading (value &amp; date)</th>
                            <th>Total Reading</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($readings as $index => $r)
                            <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $r['description'] ?? ($r['end_description'] ?? '-') }}</td>
                                        @if($isAdmin)
                                            <td>{{ $r['marketing_person']['name'] ?? ($r['marketing_person']['user_code'] ?? '-') }}</td>
                                        @endif
                                        <td>
                                            @if(!empty($r['starting_reading']))
                                                {{ $r['starting_reading'] }}
                                                @if(!empty($r['starting_image']))
                                                    &nbsp;<a href="{{ $r['starting_image'] }}" target="_blank" title="View start image"><div class="fa fa-image"></div></a>
                                                @endif
                                                <br>
                                                <small class="text-muted">{{ $r['starting_at'] ?? '-' }}</small>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if(!empty($r['ending_reading']))
                                                {{ $r['ending_reading'] }}
                                                @if(!empty($r['ending_image']))
                                                    &nbsp;<a href="{{ $r['ending_image'] }}" target="_blank" title="View end image"><div class="fa fa-image"></div></a>
                                                @endif
                                                <br>
                                                <small class="text-muted">{{ $r['ending_at'] ?? '-' }}</small>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ isset($r['total_reading']) && is_numeric($r['total_reading']) ? number_format($r['total_reading'], 2) : '-' }}</td>
                                    </tr>
                        @empty
                            @php $colspan = $isAdmin ? 6 : 5; @endphp
                            <tr><td colspan="{{ $colspan }}" class="text-center">No readings uploaded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @php
            $isEmpty = false;
            if (is_object($readings) && method_exists($readings, 'count')) {
                $isEmpty = $readings->count() === 0;
            } elseif (is_array($readings)) {
                $isEmpty = count($readings) === 0;
            }
        @endphp

        @if($isEmpty)
            <div style="height:72px;"></div>
        @endif

        <div class="card-footer d-flex justify-content-between align-items-center @if($isEmpty) position-fixed start-0 end-0 bottom-0 bg-white border-top shadow-sm @endif" style="@if($isEmpty) z-index:1030; @endif">
            <div class="d-flex align-items-center">
                <form id="perPageForm" method="GET" action="{{ route('superadmin.meter-reading.index') }}" class="d-flex align-items-center">
                    <label class="me-2 mb-0">Show</label>
                    <select name="per_page" id="perPageSelect" class="form-select form-select-sm me-2">
                        <option value="25" {{ request('per_page',25) == 25 ? 'selected' : '' }}>25</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        <option value="250" {{ request('per_page') == 250 ? 'selected' : '' }}>250</option>
                    </select>
                    <span class="me-3">entries</span>

                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="month" value="{{ request('month') }}">
                    <input type="hidden" name="year" value="{{ request('year') }}">
                    <input type="hidden" name="marketing_person" value="{{ request('marketing_person') }}">
                </form>
            </div>

            <div>
                @if(method_exists($readings, 'links'))
                    {{ $readings->links() }}
                @endif
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var sel = document.getElementById('perPageSelect');
                if (sel) {
                    sel.addEventListener('change', function () {
                        document.getElementById('perPageForm').submit();
                    });
                }
                    // auto-submit filter form when marketing person / month / year changes
                    var filterForm = document.getElementById('filterForm');
                    if (filterForm) {
                        var mp = filterForm.querySelector('select[name="marketing_person"]');
                        var month = filterForm.querySelector('select[name="month"]');
                        var year = filterForm.querySelector('select[name="year"]');
                        [mp, month, year].forEach(function(el){
                            if (el) el.addEventListener('change', function(){ filterForm.submit(); });
                        });
                        var clearBtn = document.getElementById('clearFiltersBtn');
                        if (clearBtn) {
                            clearBtn.addEventListener('click', function () {
                                // clear all selects inside the filter form and submit
                                filterForm.querySelectorAll('select').forEach(function(s){ s.value = ''; });
                                filterForm.submit();
                            });
                        }
                    }
            });
        </script>
    </div>

@endsection
