@extends('superadmin.layouts.app')
@section('title', 'Booking By Letter')
@section('content')

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif 

<div class="content">
    <div class="page-header">
        <div class="add-item d-flex">
            <div class="page-title">
                <h4>Booking</h4>
                <h6>Booking By Letter</h6>
            </div>                            
        </div>
        <ul class="table-top-head list-inline d-flex gap-3">
            <li class="list-inline-item">
                <a href="{{ route('superadmin.showbooking.exportPdf', array_filter(['department' => $department?->id, 'search' => request('search'), 'month' => request('month'), 'year' => request('year'), 'marketing' => request('marketing'), 'use_created_at' => request('use_created_at')], fn($v) => filled($v))) }}" data-bs-toggle="tooltip" title="PDF"><div class="fa fa-file-pdf"></div></a>
            </li>
            <li class="list-inline-item">
                <a href="{{ route('superadmin.showbooking.exportExcel', array_filter(['department' => $department?->id, 'search' => request('search'), 'month' => request('month'), 'year' => request('year'), 'marketing' => request('marketing'), 'use_created_at' => request('use_created_at')], fn($v) => filled($v))) }}" data-bs-toggle="tooltip" title="Excel">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="24" fill="green" viewBox="0 0 24 24">
                        <path d="M19 2H8c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-8.5 14-2-3 2-3H9l-1.5 2.25L6 10H4l2.5 3L4 16h2l1.5-2.25L9 16h1.5zM19 20H8V4h11v16z"/>
                    </svg>
                </a>
            </li>
            <li><a data-bs-toggle="tooltip" title="Refresh"><i class="ti ti-refresh"></i></a></li>
            <li><a data-bs-toggle="tooltip" title="Collapse" id="collapse-header"><i class="ti ti-chevron-up"></i></a></li>
        </ul>
    </div>

    <div class="card">
    

    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">

    <!-- Search Form -->
    <div class="search-set">
        <form method="GET" action="{{ route('superadmin.showbooking.showBooking', $department?->id) }}" class="d-flex input-group">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search...">
            <button class="btn btn-outline-secondary" type="submit">🔍</button>
        </form>
    </div>

    <!-- Month & Year Filter Form -->
    <div class="search-set">
        <form method="GET" action="{{ route('superadmin.showbooking.showBooking', $department?->id) }}" class="d-flex align-items-center gap-2">
            
            <!-- Month Filter -->
            <select name="month" class="form-control" style="width:auto">
                <option value="">Select Month</option>
                @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                    </option>
                @endforeach
            </select>

            <!-- Year Filter -->
            <select name="year" class="form-control" style="width:auto">
                <option value="">Select Year</option>
                @foreach(range(date('Y'), date('Y') - 10) as $y)
                    <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>
                        {{ $y }}
                    </option>
                @endforeach
            </select>

            <button class="btn btn-outline-secondary" type="submit">Filter</button>
            
            <!-- Filter by Created Date Checkbox -->
            <div class="form-check d-flex align-items-center ms-2 mb-0">
                <input class="form-check-input" type="checkbox" name="use_created_at" value="1" id="use_created_at" {{ request('use_created_at') ? 'checked' : '' }}>
                <label class="form-check-label ms-1" for="use_created_at" style="white-space: nowrap;">
                     
                </label>
            </div>

            @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
            @if(request('marketing'))<input type="hidden" name="marketing" value="{{ request('marketing') }}">@endif
        </form>
    </div>
 

</div>

       
    
        <!--  Department filter buttons -->
        <div class="mb-4 mt-4 ms-3">
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <a href="{{ route('superadmin.showbooking.showBooking', array_filter(['search' => request('search'), 'month' => request('month'), 'year' => request('year'), 'marketing' => request('marketing'), 'use_created_at' => request('use_created_at')], fn($v) => filled($v))) }}"
                   class="btn btn-sm {{ !$department ? 'btn-primary' : 'btn-outline-primary' }}">
                    All
                </a>

                @foreach($departments as $dept)
                    <a href="{{ route('superadmin.showbooking.showBooking', array_filter(['department' => $dept->id, 'search' => request('search'), 'month' => request('month'), 'year' => request('year'), 'marketing' => request('marketing'), 'use_created_at' => request('use_created_at')], fn($v) => filled($v))) }}"
                       class="btn btn-sm {{ $department && $department->id == $dept->id ? 'btn-primary' : 'btn-outline-primary' }}">
                        {{ $dept->name }}
                    </a>
                @endforeach

                @if(isset($marketingPersons) && $marketingPersons->count())
                    <form method="GET" action="{{ route('superadmin.showbooking.showBooking', $department?->id) }}" class="ms-auto me-3 d-flex align-items-center">
                        @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
                        @if(request('month'))<input type="hidden" name="month" value="{{ request('month') }}">@endif
                        @if(request('year'))<input type="hidden" name="year" value="{{ request('year') }}">@endif
                        @if(request('use_created_at'))<input type="hidden" name="use_created_at" value="{{ request('use_created_at') }}">@endif
                        <select name="marketing" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width:220px;">
                            <option value="">Select Marketing Person</option>
                            @foreach($marketingPersons as $mp)
                                <option value="{{ $mp->user_code }}" {{ request('marketing') == $mp->user_code ? 'selected' : '' }}>{{ $mp->user_code }} - {{ $mp->name }}</option>
                            @endforeach
                        </select>
                    </form>
                @endif
            </div>
        </div>
        

        <div class="card-body p-0">

        <div class="search-set mb-0 p-3">
                <input
                    type="text"
                    id="localSearch"
                    class="form-control"
                    placeholder="Search in current page only..."
                >
            </div>
            
            <div class="table-responsive">
                <table class="table">
                    <thead class="table-light">
                        <tr>
                            <th class="checkbox-col"><label class="checkboxs"><input type="checkbox" id="select-all"><span class="checkmarks"></span></label></th>
                            <th class="client-col">Client Name</th>
                            <th class="reference-col">Reference No</th>
                            <th class="marketing-col">Marketing Person</th>
                            <th class="show-letter-col">Show Letter</th>
                            <th class="items-col">Items</th>
                            <th class="action-col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $booking)
                        <tr class="table-row">
                            <td class="checkbox-col"><label class="checkboxs"><input type="checkbox"><span class="checkmarks"></span></label></td>
                            <td class="truncate-cell client-col">
                                <div class="cell-inner" data-bs-toggle="tooltip" title="{{ $booking->client_name }}">{{ $booking->client_name }}</div>
                            </td>
                            <td class="truncate-cell reference-col">
                                <div class="cell-inner" data-bs-toggle="tooltip" title="{{ $booking->reference_no }}">{{ $booking->reference_no }}</div>
                            </td>
                            <td class="marketing-col">{{ $booking->marketingPerson->name }}</td>
                            <td class="show-letter-col">
                                @if($booking->upload_letter_path)
                                    <a href="{{url($booking->upload_letter_path)}}" target="_blank">View</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="items-col">
                                {{ $booking->items->count() }}
                                @if($booking->items->count() > 0)
                                    <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#itemsModal-{{ $booking->id }}">
                                        <i data-feather="eye" class="feather-eye ms-1"></i>
                                    </a>
                                    <!-- Modal -->
                                    <div class="modal fade" id="itemsModal-{{ $booking->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Booking Items for {{ $booking->client_name }}</h5>
                                                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span> 
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>Job Order No</th>
                                                                    <th>Sample Description</th>
                                                                    <th>Sample Quality</th>
                                                                    <th>Lab Analyst</th>
                                                                    <th>Particulars</th>
                                                                    <th>Expected Date</th>
                                                                    <th>Amount</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($booking->items as $item)
                                                                <tr>
                                                                    <td>{{ $item->job_order_no }}</td>
                                                                    <td>{{ $item->sample_description }}</td>
                                                                    <td>{{ $item->sample_quality }}</td>
                                                                    <td>{{ $item->lab_analysis_code }}</td>
                                                                    <td>{{ $item->particulars }}</td>
                                                                    <td>{{ \Carbon\Carbon::parse($item->lab_expected_date)->format('d-m-Y') }}</td>
                                                                    <td>{{ $item->amount }}</td>
                                                                </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </td>
                            <td class="action-col">
                                <div class="d-flex justify-content-end align-items-center">
                                    <a href="{{ route('superadmin.bookings.cards.all', [$booking->id]) }}"
                                        target="_blank"
                                        class="border rounded d-flex align-items-center p-2 text-decoration-none me-2"
                                        aria-label="View booking">
                                            <i data-feather="eye" class="feather-eye"></i>
                                    </a>

                                    <a href="{{ route('superadmin.bookings.edit', $booking->id) }}" 
                                       class="border rounded d-flex align-items-center p-2 text-decoration-none me-2"
                                       aria-label="Edit booking">
                                        <i data-feather="edit" class="feather-edit"></i>
                                    </a>

                                    <!-- Delete Button -->
                                    <button type="button" class="border rounded d-flex align-items-center p-2 btn-delete"
                                            data-bs-toggle="modal" data-bs-target="#deleteModal-{{ $booking->id }}"
                                            aria-label="Delete booking">
                                        <i data-feather="trash-2" class="feather-trash-2"></i>
                                    </button>

                                    <!-- Delete Modal -->
                                    <div class="modal fade" id="deleteModal-{{ $booking->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-body text-center p-4">
                                                    <div class="icon-success bg-danger-transparent text-danger mb-2">
                                                        <i class="ti ti-trash"></i>
                                                    </div>
                                                    <h5 class="mb-3">Are you sure you want to delete this booking?</h5>
                                                    <div class="d-flex justify-content-center gap-2">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <form action="{{ route('superadmin.bookings.destroy', $booking->id) }}" method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger">Delete</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="14" class="text-center">No bookings found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @push('styles')
            <style>
                /* Allow full content to display: remove two-line clamp and enable wrapping */
                .truncate-cell { max-width: none; }
                .truncate-cell .cell-inner {
                    display: block;
                    white-space: normal;
                    word-break: break-word;
                    overflow-wrap: anywhere;
                }
                @media (max-width: 768px){ .truncate-cell { max-width: none; } }

                /* Column sizing and responsive behavior */
                table.table { width: 100%; table-layout: auto; }

                .checkbox-col { width: 44px; padding-left: 6px; padding-right: 6px; }

                .client-col { min-width: 220px; max-width: 380px; }
                .reference-col { min-width: 160px; max-width: 260px; }
                .marketing-col { min-width: 140px; max-width: 220px; }
                .show-letter-col { min-width: 90px; max-width: 120px; text-align: center; }
                .items-col { min-width: 90px; max-width: 120px; text-align: center; }
                .action-col { min-width: 140px; text-align: right; }

                /* Ensure cells wrap within their max widths */
                td.client-col, th.client-col,
                td.reference-col, th.reference-col,
                td.marketing-col, th.marketing-col,
                td.show-letter-col, th.show-letter-col,
                td.items-col, th.items-col {
                    white-space: normal; word-break: break-word; overflow-wrap: anywhere;
                }

                @media (max-width: 992px) {
                    .client-col { min-width: 160px; }
                    .reference-col { min-width: 120px; }
                    .marketing-col { min-width: 120px; }
                }
            </style>
            @endpush

            <script>
    const localSearchInput = document.getElementById('localSearch');

    if (localSearchInput) {
        localSearchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('tbody .table-row');

            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                // exclude the Action column (last cell)
                const searchableText = Array.from(cells)
                    .slice(0, Math.max(0, cells.length - 1))
                    .map(td => (td.innerText || '').toLowerCase())
                    .join(' ');

                if (!query || searchableText.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
</script>


            <!-- Pagination -->
            <div class="p-3">
                <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
                    <form method="GET" action="{{ route('superadmin.showbooking.showBooking', $department?->id) }}" class="d-flex align-items-center gap-2">
                        @foreach(request()->except(['perPage','page']) as $key => $val)
                            <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                        @endforeach
                        <label for="perPageSelect" class="me-1 mb-0 small">Rows per page:</label>
                        <select name="perPage" id="perPageSelect" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                            @foreach([25,50,100] as $size)
                                <option value="{{ $size }}" {{ request('perPage',25)==$size ? 'selected' : '' }}>{{ $size }}</option>
                            @endforeach
                        </select>
                    </form>
                    <div>
                        {{ $bookings->appends(request()->all())->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection