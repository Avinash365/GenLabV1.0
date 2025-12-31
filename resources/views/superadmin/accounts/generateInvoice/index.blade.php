@extends('superadmin.layouts.app')
@section('title', 'Generate Invoice')
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
                    <h4>Generate Invoice</h4>
                    <h6>Generate Invoice By Letter</h6>
                </div>
            </div>

            <ul class="table-top-head list-inline d-flex gap-3">
                <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#missingInvoiceModal">
                    <i class="fa fa-exclamation text-danger"></i>
                </button>
                <li class="list-inline-item">
                    <a href="#" data-bs-toggle="tooltip" title="PDF">
                        <div class="fa fa-file-pdf"></div>
                    </a>
                </li>
                <li class="list-inline-item">
                    <a href="#" data-bs-toggle="tooltip" title="Excel">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="24" fill="green" viewBox="0 0 24 24">
                            <path
                                d="M19 2H8c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-8.5 14-2-3 2-3H9l-1.5 2.25L6 10H4l2.5 3L4 16h2l1.5-2.25L9 16h1.5zM19 20H8V4h11v16z" />
                        </svg>
                    </a>
                </li>
                <li><a data-bs-toggle="tooltip" title="Refresh"><i class="ti ti-refresh"></i></a></li>
                <li><a data-bs-toggle="tooltip" title="Collapse" id="collapse-header"><i class="ti ti-chevron-up"></i></a>
                </li>
            </ul>
        </div>

        {{-- Missing invoice Number --}}

        <div class="modal fade" id="missingInvoiceModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">Missing Invoices</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        <!-- YOUR CARD START -->
                        <div class="card p-3">
                            <div class="row g-2 mb-3">
                                <div class="col">
                                    <input type="date" id="from_date" class="form-control">
                                </div>
                                <div class="col">
                                    <input type="date" id="to_date" class="form-control">
                                </div>
                                <div class="col">
                                    <button class="btn btn-primary w-100" onclick="loadMissing(1)">
                                        Search
                                    </button>
                                </div>
                            </div>

                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Blank Invoice No</th>
                                    </tr>
                                </thead>
                                <tbody id="missingTable"></tbody>
                            </table>

                            <div class="d-flex justify-content-between">
                                <button class="btn btn-secondary btn-sm mt-3" onclick="prevPage()">Prev</button>
                                <span id="pageInfo"></span>
                                <button class="btn btn-secondary btn-sm mt-3" onclick="nextPage()">Next</button>
                            </div>
                        </div>
                        <!-- YOUR CARD END -->

                    </div>

                </div>
            </div>
        </div>


        <!-- Bulk Generate Invoice Form START -->
    
            <!-- Bulk Generate Invoice Button -->
            

            <div class="card">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">

                    <form method="GET" id="invoiceFilterForm"
                        action="{{ route('superadmin.bookingInvoiceStatuses.index') }}"
                        class="d-flex align-items-center justify-content-between w-100 gap-3 flex-wrap">

                        {{-- SEARCH --}}
                         <input type="hidden" name="department" value="{{ request('department', $department ?? '')}}">

                        <div class="d-flex align-items-center gap-2">
                            <input type="text" name="search" id="autoSearch" value="{{ request('search') }}"
                                class="form-control" style="width:220px" placeholder="Search...">
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            {{-- MARKETING PERSON --}}
                            <select name="marketing_person" class="form-control" style="width:180px">
                                <option value="">Marketing Person</option>
                                @foreach($marketingPersons as $mp)
                                    <option value="{{ $mp->user_code }}" {{ request('marketing_person') == $mp->user_code ? 'selected' : '' }}>
                                        {{ $mp->label }}
                                    </option>
                                @endforeach
                            </select>

                            {{-- PAYMENT OPTION --}}
                            <select name="payment_option" class="form-control" style="width:140px">
                                <option value="">Payment Option</option>
                                <option value="bill" {{ request('payment_option') == 'bill' ? 'selected' : '' }}>Bill</option>
                                <option value="old_bill" {{ request('payment_option') == 'old_bill' ? 'selected' : '' }}>Old
                                    Bill
                                </option>
                            </select>

                            {{-- CLIENT --}}
                            <select name="client_id" class="form-control" style="width:180px">
                                <option value="">Client</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>
                                        {{ $client->name }}
                                    </option>
                                @endforeach
                            </select>

                            {{-- MONTH --}}
                            <select name="month" class="form-control" style="width:140px">
                                <option value="">Month</option>
                                @foreach(range(1, 12) as $m)
                                    <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                    </option>
                                @endforeach
                            </select>

                            {{-- YEAR --}}
                            <select name="year" class="form-control" style="width:120px">
                                <option value="">Year</option>
                                @foreach(range(date('Y'), date('Y') - 10) as $y)
                                    <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endforeach
                            </select>

                            {{-- APPLY --}}
                            <button class="btn btn-outline-secondary" type="submit" title="Apply filters">
                                <i class="ti ti-filter"></i>
                            </button>

                            {{-- RESET (keeps department) --}}
                            <a href="{{ route('superadmin.bookingInvoiceStatuses.index', $department?->id) }}"
                                class="btn btn-outline-secondary" title="Reset filters">
                                <i class="ti ti-refresh"></i>
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Department filter buttons -->
    <div class="mb-4 mt-4 ms-3">
                <div class="d-flex justify-content-between">
                    <div class="d-flex flex-wrap gap-2">

                        {{-- ALL (remove department only, keep others) --}}
                        <a href="{{ route(
                                'superadmin.bookingInvoiceStatuses.index',
                                request()->except('department')
                            ) }}"
                        class="btn btn-sm {{ !request('department') ? 'btn-primary' : 'btn-outline-primary' }}">
                            All
                        </a>

                        {{-- DEPARTMENTS --}}
                        @foreach($departments as $dept)
                            <a href="{{ route(
                                    'superadmin.bookingInvoiceStatuses.index',
                                    array_merge(
                                        request()->except('department'),
                                        ['department' => $dept->id]
                                    )
                                ) }}"
                            class="btn btn-sm {{ request('department') == $dept->id ? 'btn-primary' : 'btn-outline-primary' }}">
                                {{ $dept->name }}
                            </a>
                        @endforeach

                    </div>
                     <div class="search-set btn-sm me-4">
                        <input
                            type="text"
                            id="localSearch"
                            class="form-control form-control-sm"
                            placeholder="Search in current page only..."
                        >
                    </div>
                </div>
</div>

                <form id="bulkInvoiceForm"   action="{{ route('superadmin.bookingInvoiceStatuses.bulkGenerate') }}" method="GET"></form>
                    <div class="card-body p-0">
                        <div class="table-responsive">
        
                            <table class="table table-hover"> <!-- Added table-hover -->
                                <thead class="table-light">
                                    <tr>
                                        <th>
                                            <label class="checkboxs">
                                                <input type="checkbox" id="select-all">
                                                <span class="checkmarks"></span>
                                            </label>
                                        </th>
                                        <th>Assigned Client</th>
                                        <th>Reference No</th>
                                        <th>Marketing Person</th>
                                        <th>Booking Date</th>
                                        <th>Items</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($bookings as $booking)
                                        <tr  class = "table-row" 
                                data-search="{{ 
                                    strtolower(
                                         $booking->client->name . ' ' . 
                                         $booking->reference_no . ' ' . 
                                         ($booking->marketingPerson->name ?? ' ')
                        
                                    )
                                 }}">
                                            <td>
                                                <label class="checkboxs">
                                                    <input type="checkbox" name="booking_ids[]" form="bulkInvoiceForm" value="{{ $booking->id }}">
                                                    <span class="checkmarks"></span>
                                                </label>
                                            </td>
                                            <td>{{ $booking->client->name ?? ''}}</td>
                                            <td>{{ $booking->reference_no ?? ''}}</td>
                                            <td>{{ $booking->marketingPerson->name ?? '' }}</td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($booking->job_order_date)->format('d-m-Y') }}
                                            </td>
                                            <td>
                                                {{ $booking->items->count() }}
                                                @if($booking->items->count() > 0)
                                                    <a href="javascript:void(0);" data-bs-toggle="modal"
                                                        data-bs-target="#itemsModal-{{ $booking->id }}">
                                                        <i data-feather="eye" class="feather-eye ms-1"></i>
                                                    </a>

                                                    <!-- Modal -->
                                                    <div class="modal fade" id="itemsModal-{{ $booking->id }}" tabindex="-1"
                                                        aria-hidden="true">
                                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Booking Items for
                                                                        {{ $booking->client_name ?? '' }}
                                                                    </h5>
                                                                    <button type="button" class="close" data-bs-dismiss="modal"
                                                                        aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <div class="table-responsive">
                                                                        <table class="table ">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>Sample Description</th>
                                                                                    <th>Sample Quality</th>
                                                                                    <th>Lab Analyst</th>
                                                                                    <th>Particulars</th>
                                                                                    <th>Expected Date</th>
                                                                                    <th>Amount</th>
                                                                                    <th>Job Order No</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                @foreach($booking->items as $item)
                                                                                    <tr>
                                                                                        <td>{{ $item->sample_description ?? '' }}</td>
                                                                                        <td>{{ $item->sample_quality ?? '' }}</td>
                                                                                        <td>{{ $item->lab_analysis_code ?? '' }}</td>
                                                                                        <td>{{ $item->particulars ?? '' }}</td>
                                                                                        <td>{{ \Carbon\Carbon::parse($item->lab_expected_date)->format('d-m-Y') }}
                                                                                        </td>
                                                                                        <td>{{ $item->amount ?? '' }}</td>
                                                                                        <td>{{ $item->job_order_no ?? ''}}</td>
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

                                            <td class="d-flex align-items-center gap-2">
                                                <a href="{{ route('superadmin.bookingInvoiceStatuses.edit', $booking->id) }}"
                                                    class="btn btn-success d-flex align-items-center p-2" title="Generate Invoice">
                                                    <i data-feather="file-text"></i>
                                                </a>

                                                <!-- <a href="{{ route('superadmin.bookings.edit', $booking->id) }}" 
                                                                class="btn btn-outline-primary d-flex align-items-center p-2">
                                                                    <i data-feather="edit"></i>
                                                                </a> -->

                                                <button type="button" class="btn btn-outline-danger d-flex align-items-center p-2"
                                                    data-bs-toggle="modal" data-bs-target="#deleteModal-{{ $booking->id }}">
                                                    <i data-feather="trash-2"></i>
                                                </button>

                                                <!-- Move / Transfer -->
                                                <a href="#" class="btn btn-warning d-flex align-items-center p-2"
                                                    title="Without Bill">
                                                    <i data-feather="corner-up-right"></i>
                                                </a>

                                                <!-- Delete Modal -->
                                                <div class="modal fade" id="deleteModal-{{ $booking->id }}" tabindex="-1"
                                                    aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-body text-center p-4">
                                                                <div class="icon-success bg-danger-transparent text-danger mb-2">
                                                                    <i class="ti ti-trash"></i>
                                                                </div>
                                                                <h5 class="mb-3">Are you sure you want to delete this booking?</h5>
                                                                <div class="d-flex justify-content-center gap-2">
                                                                    <button type="button" class="btn btn-secondary"
                                                                        data-bs-dismiss="modal">Cancel</button>
                                                                    <form
                                                                        action="{{ route('superadmin.bookings.destroy', $booking->id) }}"
                                                                        method="POST">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="btn btn-danger">Delete</button>
                                                                    </form>
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
                        <!-- Pagination -->
                        <div class="p-3">
                            {{ $bookings->appends(request()->all())->links('pagination::bootstrap-5') }}
                        </div>
                    </div> 
                </form>
                <div class="mb-3 ms-2 d-flex justify-content-between">
                    <button type="submit" form="bulkInvoiceForm" class="btn btn-primary">
                        Generate Invoice for Selected
                    </button>
                </div>
            </div>
      
        <!-- Bulk Generate Invoice Form END -->
    </div>

    <!-- Row hover CSS -->
    @push('styles')
        <style>
            .table-hover tbody tr:hover {
                background-color: #f0f8ff;
                cursor: pointer;
                transition: background-color 0.3s;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            // Select/Deselect All
            document.getElementById('select-all').addEventListener('change', function () {
                let checkboxes = document.querySelectorAll('input[name="booking_ids[]"]');
                checkboxes.forEach(cb => cb.checked = this.checked);
            });
        </script>
    @endpush


    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let currentPage = 1;
        let totalRecords = 0;
        let perPage = 10;

        function loadMissing(page = 1) {
            currentPage = page;

            $.ajax({
                url: "{{ route('invoices.missing') }}",
                data: {
                    from_date: $('#from_date').val(),
                    to_date: $('#to_date').val(),
                    page: page
                },
                success: function (res) {
                    let rows = '';
                    let start = (page - 1) * perPage;

                    if (res.data.length === 0) {
                        rows = `<tr><td colspan="2" class="text-center">No missing invoices</td></tr>`;
                    }

                    res.data.forEach((inv, i) => {
                        rows += `
                                        <tr>
                                            <td>${start + i + 1}</td>
                                            <td>${inv}</td>
                                        </tr>
                                    `;
                    });

                    $('#missingTable').html(rows);

                    totalRecords = res.total;
                    $('#pageInfo').text(
                        `Page ${res.page} of ${Math.ceil(res.total / perPage)}`
                    );
                }
            });
        }

        function nextPage() {
            if (currentPage * perPage < totalRecords) {
                loadMissing(currentPage + 1);
            }
        }

        function prevPage() {
            if (currentPage > 1) {
                loadMissing(currentPage - 1);
            }
        }

        // Initial load
        loadMissing();
    </script>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('invoiceFilterForm');
    if (!form) return;

    /* -----------------------------
     | AUTO SUBMIT ON SELECT CHANGE
     ----------------------------- */
    form.querySelectorAll('select').forEach(select => {
        select.addEventListener('change', () => {
            form.submit();
        });
    });

    /* -----------------------------
     | AUTO SUBMIT ON SEARCH (NO LAG)
     ----------------------------- */
    const searchInput = document.getElementById('autoSearch');
    if (!searchInput) return;

    let typingTimer;
    let lastValue = searchInput.value.trim();
    const delay = 400;
    const minLength = 2;

    searchInput.addEventListener('input', function () {
        clearTimeout(typingTimer);

        typingTimer = setTimeout(() => {
            const currentValue = this.value.trim();

            //  Ignore if value didn't actually change
            if (currentValue === lastValue) return;

            //  Ignore only-spaces typing
            if (currentValue.length === 0 && lastValue.length === 0) return;

            //  Submit if valid length OR cleared
            if (currentValue.length >= minLength || currentValue.length === 0) {
                lastValue = currentValue;
                form.submit();
            }

        }, delay);
    });
});
</script>
  <script>
            const localSearchInput = document.getElementById('localSearch');

            if (localSearchInput) {
                localSearchInput.addEventListener('input', function () {
                    const query = this.value.toLowerCase().trim();
                    const rows = document.querySelectorAll('.table-row');

                    rows.forEach(row => {
                        const text = row.getAttribute('data-search');

                        if (!query || text.includes(query)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            }
        </script>
@endpush

@endsection