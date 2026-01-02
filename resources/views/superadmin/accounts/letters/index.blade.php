@extends('superadmin.layouts.app')
@section('title', 'Show Booking List')
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

@php
    $query = http_build_query(
        array_filter(request()->only([
            'search',
            'department_id',
            'month',
            'year',
            'payment_option',
            'marketing_person',
            'client_id'
        ]))
    );
@endphp

    <div class="content">
        <div class="page-header">
            <div class="add-item d-flex justify-content-between w-100">
                <div class="page-title">
                    <h4>All Letters</h4>
                    <h6>Assign Client</h6>
                </div>

                <!-- 🔹 Register Client Button (opens popup) -->
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#registerClientModal">
                    + Register Client
                </button>
            </div>
        </div>
       
        <div class="card">
            <!-- Filters: Search, Month, Year, Payment Option, Client -->
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                <!-- Search Form -->
                <div class="search-set">
                    <div class="search-set">
                        <form method="GET"
                            action="{{ route('superadmin.accountBookingsLetters.index') }}"
                            class="d-flex input-group">

                            {{-- Preserve ALL other filters --}}
                            @foreach([
                                'department_id',
                                'month',
                                'year',
                                'payment_option',
                                'marketing_person',
                                'client_id'
                            ] as $filter)
                                @if(request($filter))
                                    <input type="hidden" name="{{ $filter }}" value="{{ request($filter) }}">
                                @endif
                            @endforeach

                            {{-- Search --}}
                            <input type="text"
                                name="search"
                                id="autoSearch"
                                value="{{ request('search') }}"
                                class="form-control"
                                placeholder="Search...">

                            <button class="btn btn-outline-secondary" type="submit">🔍</button>
                        </form>
                    </div>
                </div>


                <!-- Month & Year Filter -->
                <div class="search-set">
                    <form method="GET"
                        id="invoiceFilterForm"
                        action="{{ route('superadmin.accountBookingsLetters.index') }}"
                        class="d-flex input-group gap-2">

                        {{-- Preserve department --}}
                        <input type="hidden" name="department_id" value="{{ request('department_id') }}">

                        {{--  FIXED search preservation --}}
                        <input type="hidden" name="search" value="{{ request('search') }}">

                        {{-- Month --}}
                        <select name="month" class="form-control">
                            <option value="">Select Month</option>
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                </option>
                            @endforeach
                        </select>

                        {{-- Year --}}
                        <select name="year" class="form-control">
                            <option value="">Select Year</option>
                            @foreach(range(date('Y'), date('Y') - 10) as $y)
                                <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endforeach
                        </select>

                        {{-- Payment Option --}}
                        <select name="payment_option" class="form-control">
                            <option value="">Payment Option</option>
                            <option value="bill" {{ request('payment_option') == 'bill' ? 'selected' : '' }}>Bill</option>
                            <option value="without_bill" {{ request('payment_option') == 'without_bill' ? 'selected' : '' }}>
                                Without Bill
                            </option>
                            <option value="old_bill" {{ request('payment_option') == 'old_bill' ? 'selected' : '' }}>
                                Old Bill
                            </option>
                        </select>

                        {{-- Marketing person --}}
                        <div class="position-relative" style="min-width:200px;">
                            <input type="text"
                                id="marketing_code_input"
                                class="form-control"
                                autocomplete="off"
                                placeholder="Search marketing person">

                            <input type="hidden" name="marketing_person" id="marketing_code_hidden">

                            <div id="marketingCodeDropdown"
                                class="dropdown-menu w-100"
                                style="display:none; max-height:200px; overflow:auto;">
                            </div>
                        </div>

                        {{-- Client --}}
                        <div class="position-relative" style="width:220px;">
                            <input type="text"
                                class="form-control client-search-input"
                                placeholder="Search client..."
                                autocomplete="off">

                            <input type="hidden" name="client_id"
                                class="client-id-hidden"
                                value="">

                            <div class="dropdown-menu w-100 client-dropdown"
                                style="max-height:500px; overflow:auto;">
                                @foreach($clients as $client)
                                    <button type="button"
                                        class="dropdown-item client-option"
                                        data-id="{{ $client->id }}"
                                        data-name="{{ strtolower($client->name) }}">
                                        {{ $client->name }}
                                    </button>
                                @endforeach
                            </div>
                        </div>


                        <button class="btn btn-secondary" type="submit" title="Apply filters"><i class="fa fa-filter"></i></button>
                        <a href="{{ route('superadmin.accountBookingsLetters.index') }}"
                            class="btn btn-primary"
                            title="Reset filters">
                                <i class="ti ti-refresh"></i>
                        </a>
                    </form>
                </div>



            <!-- Department Filter -->
            <div class="my-3 ms-4">
    
                <div class="d-flex gap-2">
                    {{-- ALL --}}
                    <a href="{{ route('superadmin.accountBookingsLetters.index') }}{{ $query ? '?' . $query : '' }}"
                    class="btn btn-sm {{ !request('department_id') ? 'btn-primary' : 'btn-outline-primary' }}">
                        All
                    </a>
                    {{-- DEPARTMENTS --}}
                    @foreach($departments as $dept)
                        @php
                            $deptQuery = http_build_query(
                                array_merge(
                                    request()->except('department_id'),
                                    ['department_id' => $dept->id]
                                )
                            );
                        @endphp

                        <a href="{{ route('superadmin.accountBookingsLetters.index') }}?{{ $deptQuery }}"
                        class="btn btn-sm {{ request('department_id') == $dept->id ? 'btn-primary' : 'btn-outline-primary' }}">
                            {{ $dept->name }}
                        </a>
                    @endforeach
                </div>
    </div>
</div>

            <!-- Booking Table -->
            <div class="card-body">
                <div class="table-responsive">
                    <div class="search-set btn-sm p-1 mb-2">
                        <input
                            type="text"
                            id="localSearch"
                            class="form-control form-control-sm"
                            placeholder="Search in current page only..."
                        >
                    </div>

                    <table class="table">
                        <thead class="table-light">
                            <tr>
                                <th><input type="checkbox" id="select-all"></th>
                                <th style="width:180px;">Client Name</th>
                                <th style="width:160px;">Reference No</th>
                                <th>Marketing Person</th>
                                <th>Show Letter</th>
                                <th>Items</th>
                                <th>Assign Client</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bookings as $booking)
                                <tr 
                                    class = "table-row" 
                                    data-search="{{ 
                                        strtolower(
                                            $booking->client_name . ' ' . 
                                                    $booking->reference_no

                                        )
                                     }}"
                                    >
                                    <td><input type="checkbox"></td>
                                    <td class="truncate-cell">
                                        <div class="cell-inner" data-bs-toggle="tooltip" title="{{ $booking->client_name }}">
                                            {{ $booking->client_name }}</div>
                                    </td>
                                    <td class="truncate-cell">
                                        <div class="cell-inner" data-bs-toggle="tooltip" title="{{ $booking->reference_no }}">
                                            {{ $booking->reference_no }}</div>
                                    </td>
                                    <td>{{ $booking->marketingPerson->name ?? '-' }}</td>
                                    <td>
                                        @if($booking->upload_letter_path)
                                            <a href="{{ url($booking->upload_letter_path) }}" target="_blank">View</a>
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <!-- Items Modal -->
                                    <td>
                                        {{ $booking->items->count() }}
                                        @if($booking->items->count() > 0)
                                            <a href="javascript:void(0);" data-bs-toggle="modal"
                                                data-bs-target="#itemsModal-{{ $booking->id }}">
                                                <i data-feather="eye"></i>
                                            </a>
                                            <div class="modal fade" id="itemsModal-{{ $booking->id }}" tabindex="-1"
                                                aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5>Booking Items for {{ $booking->client_name }}</h5>
                                                            <button type="button" class="close" data-bs-dismiss="modal"
                                                                aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <table class="table table-bordered">
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
                                                                            <td>{{ $item->sample_description }}</td>
                                                                            <td>{{ $item->sample_quality }}</td>
                                                                            <td>{{ $item->lab_analysis_code }}</td>
                                                                            <td>{{ $item->particulars }}</td>
                                                                            <td>{{ \Carbon\Carbon::parse($item->lab_expected_date)->format('d-m-Y') }}
                                                                            </td>
                                                                            <td>{{ $item->amount }}</td>
                                                                            <td>{{ $item->job_order_no }}</td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Assign Client Dropdown -->
                                    <td>
                                        <form action="{{ route('superadmin.clients.assignBooking', $booking->id) }}"
                                            method="POST" class="d-flex">
                                            @csrf
                                            <select name="client_id" class="form-control me-2" style="width: 180px;">
                                                <option value="">Select Client</option>
                                                @foreach($clients as $client)
                                                    <option value="{{ $client->id }}" {{ $booking->client_id == $client->id ? 'selected' : '' }}>
                                                        {{ $client->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-success">Assign</button>
                                        </form>
                                    </td>

                                    <!-- Actions -->
                                    <td class="d-flex">
                                        <a href="{{ route('superadmin.bookings.edit', $booking->id) }}"
                                            class="me-2 p-2 border rounded">
                                            <i data-feather="edit"></i>
                                        </a>
                                        <button type="button" class="p-2 border rounded btn-delete" data-bs-toggle="modal"
                                            data-bs-target="#deleteModal-{{ $booking->id }}">
                                            <i data-feather="trash-2"></i>
                                        </button>
                                        <div class="modal fade" id="deleteModal-{{ $booking->id }}" tabindex="-1">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-body text-center">
                                                        <div class="icon-success bg-danger-transparent text-danger mb-2">
                                                            <i class="ti ti-trash"></i>
                                                        </div>
                                                        <h5>Are you sure you want to delete this booking?</h5>
                                                        <div class="d-flex justify-content-center gap-2 mt-3">
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
                <div class="p-3">
                    {{ $bookings->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>

    <!-- 🔹 Client Registration Modal -->
    <div class="modal fade" id="registerClientModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('superadmin.clients.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Register New Client</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body row g-3">
                        <div class="col-md-6">
                            <input type="text" name="name" class="form-control" placeholder="Client Name" required>
                        </div>
                        <div class="col-md-6">
                            <input type="email" name="email" class="form-control" placeholder="Email">
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="phone" class="form-control" placeholder="Phone">
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="gstin" class="form-control" placeholder="GSTIN">
                        </div>
                        <div class="col-12">
                            <textarea name="address" class="form-control" placeholder="Address"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Register Client</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* Reuse truncate-cell + cell-inner pattern for letters table */
        .truncate-cell {
            max-width: 180px;
        }

        .truncate-cell .cell-inner {
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: normal;
        }

        @media (max-width: 992px) {
            .truncate-cell {
                max-width: 140px;
            }
        }
    </style>
@endpush

@push('scripts')
<script>
/* ----------------------------------------------------
 | GLOBAL HELPERS
 ---------------------------------------------------- */
let ajaxRequests = {};

function debounce(func, wait) {
    let timeout;
    return function () {
        const context = this, args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(context, args), wait);
    };
}

function abortOldRequest(key) {
    if (ajaxRequests[key]) {
        ajaxRequests[key].abort();
        ajaxRequests[key] = null;
    }
}

/* ----------------------------------------------------
 | MARKETING PERSON AUTOCOMPLETE
 ---------------------------------------------------- */
function attachMarketingSearch($input) {

    const $hidden   = $('#marketing_code_hidden');
    const $dropdown = $('#marketingCodeDropdown');

    // Prevent duplicate bindings
    $input.off('keyup');

    $input.on('keyup', debounce(function () {

        let query = $input.val().trim();

        // Minimum 2 characters
        if (query.length < 2) {
            $dropdown.hide().empty();
            $hidden.val('');
            return;
        }

        // Abort old request
        abortOldRequest('marketing');

        ajaxRequests.marketing = $.ajax({
            url: "{{ route('superadmin.bookings.autocomplete') }}",
            type: "GET",
            dataType: "json",
            data: {
                term: query,
                type: 'marketing'
            },
            success: function (data) {

                let html = '';

                if (data.length > 0) {
                    html = data.map(item => `
                        <button type="button"
                                class="dropdown-item"
                                data-id="${item.user_code}"
                                data-name="${item.name}">
                            ${item.label}
                        </button>
                    `).join('');
                } else {
                    html = `<span class="dropdown-item disabled">No results found</span>`;
                }

                $dropdown.html(html).show();
            },
            error: function (xhr, status) {
                if (status !== 'abort') {
                    console.error('Marketing autocomplete failed');
                }
            }
        });

    }, 400));

    // Click on dropdown item
    $dropdown.off('click').on('click', '.dropdown-item', function () {
        $input.val($(this).data('name'));
        $hidden.val($(this).data('id')); // save user_code
        $dropdown.hide().empty();
    });
}

/* ----------------------------------------------------
 | INIT ON PAGE LOAD (IMPORTANT FIX)
 ---------------------------------------------------- */
$(document).ready(function () {

    //  Attach autocomplete immediately
    attachMarketingSearch($('#marketing_code_input'));

    // Hide dropdown on outside click
    $(document).on('click', function (e) {
        if (!$(e.target).closest('#marketing_code_input, #marketingCodeDropdown').length) {
            $('#marketingCodeDropdown').hide();
        }
    });

});
</script>
@endpush

@push('scripts')

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
                | AUTO SUBMIT ON SEARCH (DEBOUNCE)
                ----------------------------- */
                
            });
        </script>

        <script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.client-search-input').forEach(input => {

        const wrapper   = input.closest('.position-relative');
        const dropdown  = wrapper.querySelector('.client-dropdown');
        const hidden    = wrapper.querySelector('.client-id-hidden');
        const options   = dropdown.querySelectorAll('.client-option');

        input.addEventListener('focus', () => {
            dropdown.classList.add('show');
        });

        input.addEventListener('input', function () {
            const query = this.value.toLowerCase();

            options.forEach(opt => {
                opt.style.display =
                    opt.dataset.name.includes(query)
                        ? 'block'
                        : 'none';
            });
        });

        options.forEach(opt => {
            opt.addEventListener('click', () => {
                input.value = opt.innerText;
                hidden.value = opt.dataset.id;
                dropdown.classList.remove('show');
            });
        });

        document.addEventListener('click', e => {
            if (!wrapper.contains(e.target)) {
                dropdown.classList.remove('show');
            }
        });
    });

});
</script>

    @endpush

