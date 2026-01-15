@extends('superadmin.layouts.app')
@section('title', 'Manage Quotations')
@section('content')

{{-- Generate Quotation Button Removed --}}

    <div class="page-header">
        <div class="add-item d-flex ms-4 mt-4">
            <div class="page-title">
                <h4>Quotation</h4>
                <h6>Your Generated Quotation Will Be Display Here.</h6>
            </div>
        </div>
        
        <ul class="table-top-head list-inline d-flex gap-3" >
 
            <li class="list-inline-item">
                <a href="#" data-bs-toggle="tooltip" title="PDF"><div class="fa fa-file-pdf"></div></a>
            </li>
            <li class="list-inline-item">
                <a href="#" data-bs-toggle="tooltip" title="Excel">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="24" fill="green" viewBox="0 0 24 24">
                        <path d="M19 2H8c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-8.5 14-2-3 2-3H9l-1.5 2.25L6 10H4l2.5 3L4 16h2l1.5-2.25L9 16h1.5zM19 20H8V4h11v16z"/>
                    </svg>
                </a>
            </li>
            <li style="margin-right:22px;"><a data-bs-toggle="tooltip" title="Refresh"><i class="ti ti-refresh" ></i></a></li>
        </ul>
    </div>



<!-- <h5 class="card-title">Generated Quotations</h5>  -->
<!-- Table List -->
<div class="card mt-4">
    
    <div class="card-header d-flex justify-content-between align-items-center">
    
        <!-- Search bar -->
         <form method="GET" id="invoiceFilterForm" action="{{ route('superadmin.marketing.quotations.index') }}"
                class="d-flex align-items-center justify-content-between w-100 gap-3 flex-wrap">
                <input type="hidden" name="type" value="{{ request('type', $type ?? '') }}">
                <input type="hidden" name="department_id" value="{{ request('department_id', $department_id ?? '')}}">
                {{-- LEFT : Search --}}
                <div class="d-flex align-items-center gap-2">
                    <input type="text" name="search" id="autoSearch" value="{{ request('search') }}" class="form-control"
                        style="width:220px" placeholder="Search...">
                </div>

                {{-- RIGHT : Filters --}}
                <div class="d-flex align-items-center gap-2 flex-wrap">

                    {{-- Month --}}
                    <select name="month" class="form-select" style="width:140px">
                        <option value="">Month</option>
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                            </option>
                        @endforeach
                    </select>

                    {{-- Year --}}
                    <select name="year" class="form-select" style="width:120px">
                        <option value="">Year</option>
                        @foreach(range(date('Y'), date('Y') - 10) as $y)
                            <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endforeach
                    </select>

                    {{-- Client --}}
                    
                    {{-- Status --}}
                    <!-- <select name="payment_status" class="form-select" style="width:140px">
                        <option value="">Status</option>
                        <option value="1" {{ request('payment_status') == '1' ? 'selected' : '' }}>Paid</option>
                        <option value="0" {{ request('payment_status') == '0' ? 'selected' : '' }}>Unpaid</option>
                        <option value="2" {{ request('payment_status') == '2' ? 'selected' : '' }}>Cancelled</option>
                        <option value="3" {{ request('payment_status') == '3' ? 'selected' : '' }}>Partial</option>
                        <option value="4" {{ request('payment_status') == '4' ? 'selected' : '' }}>Settled</option>
                    </select> -->

                    {{-- Apply --}}
                    <button class="btn btn-outline-secondary" type="submit" title="Apply filters">
                        <i class="ti ti-filter"></i>
                    </button>

                    {{-- Reset --}}
                    <a href="{{ route('superadmin.marketing.quotations.index', ['type' => request('type', $type ?? '')]) }}"
                        class="btn btn-outline-secondary" title="Reset filters">
                        <i class="ti ti-refresh"></i>
                    </a>
                </div>
            </form>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <div class="search-set btn-sm me-4 mb-4">
                        <input
                            type="text"
                            id="localSearch"
                            class="form-control"
                            placeholder="Search in current page only..."
                        >
                </div>
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Quotation No</th>
                        <th>Client Name</th>
                        <th>Client Gstin</th>
                        <th>Total Amount</th>
                        <th>Quotation Date</th>
                        <th>Bill Issue To</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($quotations as $quotation)
                        <tr 
                            class = "table-row" 
                                data-search="{{ 
                                    strtolower(
                                         $quotation->quotation_no . ' ' . 
                                         ($quotation->marketingPerson->name ?? '') . ' ' . 
                                         ($quotation->client_name ?? '')
                        
                                    )
                                 }}"

                        >
                            
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $quotation->quotation_no }}</td>
                            <td>{{ $quotation->client_name ?? 'N/A' }}</td>
                            <td>{{ $quotation->client_gstin }}</td>
                            <td>{{ $quotation->payable_amount }}</td>
                            <td>{{ \Carbon\Carbon::parse($quotation->quotation_date)->format('d-m-Y') }}</td>
                            <td>{{ $quotation->bill_issue_to }}</td>
                            
                        </tr>

                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No quotations found.</td>
                        </tr>
                    @endforelse
                </tbody> 
            </table> 
        </div>
        <!-- Pagination --> 
        <div class="mt-3">
            {{ $quotations->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

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
                let typingTimer;
                const delay = 400;      // ms
                const minLength = 2;    // submit after 2 chars

                const searchInput = document.getElementById('autoSearch');

                if (searchInput) {
                    searchInput.addEventListener('keyup', function () {
                        clearTimeout(typingTimer);

                        typingTimer = setTimeout(() => {
                            const value = this.value.trim();

                            // submit if enough chars OR cleared
                            if (value.length >= minLength || value.length === 0) {
                                form.submit();
                            }
                        }, delay);
                    });
                }
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
