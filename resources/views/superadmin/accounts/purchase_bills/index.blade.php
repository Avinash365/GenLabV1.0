@extends('superadmin.layouts.app')

@section('title', 'Purchase Bills')



  

@section('content')
<div class="card mt-3">
    <div class="page-header">
        <div class="add-item d-flex ms-4 mt-4">
            <div class="page-title">
                <h4>Purchase Bills</h4>
                <h6>Upload purchase bills and review uploaded files</h6>
            </div>
        </div>
        
        <ul class="table-top-head list-inline d-flex gap-3 align-items-center" style="padding-right:18px;">
            <li class="list-inline-item">
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#uploadInvoiceModal" title="Upload Purchase Bill">
                    Upload Purchase Bill
                </button>
            </li>
            <li class="list-inline-item">
                <a href="{{ route('superadmin.purchase_bills.export.pdf', request()->query()) }}" target="_blank" data-bs-toggle="tooltip" title="PDF"><div class="fa fa-file-pdf"></div></a>
            </li>
            <li class="list-inline-item">
                <a href="{{ route('superadmin.purchase_bills.export.excel', request()->query()) }}" target="_blank" data-bs-toggle="tooltip" title="Excel">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="24" fill="green" viewBox="0 0 24 24">
                        <path d="M19 2H8c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-8.5 14-2-3 2-3H9l-1.5 2.25L6 10H4l2.5 3L4 16h2l1.5-2.25L9 16h1.5zM19 20H8V4h11v16z"/>
                    </svg>
                </a>
            </li>
            <li><a href="{{ route('superadmin.purchase_bills.index') }}" data-bs-toggle="tooltip" title="Refresh"><i class="ti ti-refresh"></i></a></li>
            <li><a data-bs-toggle="tooltip" title="Collapse" id="collapse-header"><i class="ti ti-chevron-up"></i></a></li>
        </ul>
    </div>

    {{-- Flash messages (moved out of modal so they show after redirect) --}}
    @if(session('success'))
        <div class="m-3">
            <div class="alert alert-success">{{ session('success') }}</div>
        </div>
    @endif
    @if(session('error'))
        <div class="m-3">
            <div class="alert alert-danger">{{ session('error') }}</div>
        </div>
    @endif

    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="search-set d-flex align-items-center gap-2">
            <form method="GET" action="{{ route('superadmin.purchase_bills.index') }}" class="d-flex input-group input-group-sm m-0">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search...">
                <button class="btn btn-outline-secondary" type="submit">🔍</button>
            </form>
        </div>

        <!-- Month & Year Filter Form -->
        <div class="search-set d-flex gap-2">
            <form method="GET" action="{{ route('superadmin.purchase_bills.index') }}" class="d-flex input-group">
                <select name="month" class="form-control">
                    <option value="">Select Month</option>
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                        </option>
                    @endforeach
                </select>

                <select name="year" class="form-control">
                    <option value="">Select Year</option>
                    @foreach(range(date('Y'), date('Y') - 10) as $y)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>

                <select name="financial_year" class="form-control">
                    <option value="">Financial Year</option>
                    @php
                        // show financial year start options up to 2030 (e.g. 2030-31 down to 2020-21)
                        $max = 2030;
                        $min = $max - 10;
                        $startYears = range($max, $min);
                    @endphp
                    @foreach($startYears as $start)
                        @php $label = $start . '-' . substr(($start + 1), -2); @endphp
                        <option value="{{ $start }}" {{ request('financial_year') == $start ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>

                <button class="btn btn-outline-secondary" type="submit">Filter</button>
            </form>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="row g-0">
            <div class="col-12 p-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="purchaseBillsTable">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Person</th>
                                <th class="text-end">Amount</th>
                                <th>Bill Date</th>
                                <th>Description</th>
                                <th>Uploaded At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($purchaseBills as $i => $b)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $b['user_name'] ?? '-' }}</td>
                                    <td class="text-end">{{ isset($b['amount']) ? number_format((float)$b['amount'], 2) : '-' }}</td>
                                    <td>{{ $b['bill_date'] ?? '-' }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($b['description'] ?? '-', 60) }}</td>
                                    <td>{{ $b['uploaded_at'] ?? '-' }}</td>
                                    <td>
                                        <a href="{{ $b['url'] }}" class="btn btn-sm btn-primary" target="_blank">View</a>

                                        <button type="button" 
                                            class="btn btn-sm btn-secondary js-edit-bill" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editBillModal"
                                            data-path="{{ base64_encode($b['path']) }}"
                                            data-user_id="{{ $b['user_id'] ?? '' }}"
                                            data-bill_date="{{ $b['bill_date'] ?? '' }}"
                                            data-amount="{{ $b['amount'] ?? '' }}"
                                            data-description="{{ htmlspecialchars($b['description'] ?? '', ENT_QUOTES) }}"
                                            data-action="{{ route('superadmin.purchase_bills.update', base64_encode($b['path'])) }}">
                                            Edit
                                        </button>

                                        <form method="POST" action="{{ route('superadmin.purchase_bills.destroy', base64_encode($b['path'])) }}" class="d-inline-block js-delete-bill">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">No purchase bills uploaded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <div class="card-footer">
    </div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadInvoiceModal" tabindex="-1" aria-labelledby="uploadInvoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="uploadInvoiceModalLabel">Upload Purchase Bill</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="POST" action="{{ route('superadmin.purchase_bills.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label class="form-label">Person</label>
                <select name="user_id" class="form-control" required>
                    <option value="">-- Select User --</option>
                    @if(!empty($users))
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    @endif
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Bill Date</label>
                <input type="date" name="bill_date" class="form-control" value="{{ old('bill_date') }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control">{{ old('description') }}</textarea>
            </div>



            <div class="mb-3">
                <label class="form-label">Amount (optional)</label>
                <input type="number" step="0.01" name="amount" class="form-control" value="{{ old('amount') }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Purchase Bill (PDF/JPG/PNG)</label>
                <input type="file" name="attachment" class="form-control" required>
            </div>

            <div class="d-grid">
                <button class="btn btn-primary">Upload</button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>
    <!-- Edit Modal -->
    <div class="modal fade" id="editBillModal" tabindex="-1" aria-labelledby="editBillModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="editBillModalLabel">Edit Purchase Bill</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">

            <form method="POST" id="editBillForm">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Assign To User</label>
                    <select name="user_id" id="edit_user_id" class="form-control" required>
                        <option value="">-- Select User --</option>
                        @if(!empty($users))
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Bill Date</label>
                    <input type="date" name="bill_date" id="edit_bill_date" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Amount (optional)</label>
                    <input type="number" step="0.01" name="amount" id="edit_amount" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="edit_description" class="form-control"></textarea>
                </div>

                <div class="d-grid">
                    <button class="btn btn-primary">Save Changes</button>
                </div>
            </form>

          </div>
        </div>
      </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Edit modal population
            document.querySelectorAll('.js-edit-bill').forEach(function(btn){
                btn.addEventListener('click', function(){
                    var action = this.getAttribute('data-action');
                    var userId = this.getAttribute('data-user_id');
                    var billDate = this.getAttribute('data-bill_date');
                    var amount = this.getAttribute('data-amount');
                    var description = this.getAttribute('data-description');

                    var form = document.getElementById('editBillForm');
                    form.action = action;
                    document.getElementById('edit_user_id').value = userId || '';
                    document.getElementById('edit_bill_date').value = billDate || '';
                    document.getElementById('edit_amount').value = amount || '';
                    document.getElementById('edit_description').value = description || '';
                });
            });

            // Delete confirmation using Swal if available
            document.querySelectorAll('.js-delete-bill').forEach(function(frm){
                frm.addEventListener('submit', function(e){
                    e.preventDefault();
                    var form = this;
                    if (window.Swal) {
                        Swal.fire({
                            title: 'Delete purchase bill?',
                            text: 'This action cannot be undone.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, delete',
                            cancelButtonText: 'Cancel'
                        }).then(function(result){
                            if (result.isConfirmed) {
                                form.submit();
                            }
                        });
                    } else {
                        if (confirm('Delete this purchase bill? This action cannot be undone.')) {
                            form.submit();
                        }
                    }
                });
            });
        });
    </script>
@endsection
