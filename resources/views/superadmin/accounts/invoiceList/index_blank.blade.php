@extends('superadmin.layouts.app')
@section('title', 'Manage Documents')
@section('content')


<div class="d-flex justify-content-end mt-3 me-3">
    <a href="{{ route('superadmin.blank-invoices.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Generate Blank PI
    </a>
</div>  

<!-- Table List -->
<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title">Generated Invoice</h5>
        <!-- Search bar -->
        <form method="GET" action="{{ route('superadmin.invoices.index') }}" class="d-flex" role="search">
            <input class="form-control me-2" type="search" name="search" placeholder="Search Document..." value="{{ request('search') }}">
            <button class="btn btn-outline-primary" type="submit">Search</button>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table  table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Invoice No</th>
                        <th>Client Name</th>     
                        <th>Client Gstin</th>
                        <th>GST Amount</th>
                        <th>Total Amount</th>
                        <th>Letter Date</th>
                        <th>Bill Issue Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $invoice->invoice_no }}</td>
                            <td>{{ $invoice->client_name}}</td>
                            <td>{{ $invoice->client_gstin }}</td>
                            <td>{{ ($invoice->cgst_amount ?? 0) + ($invoice->sgst_amount ?? 0) + ($invoice->igst_amount ?? 0) }}</td>
                            <td>{{$invoice->payable_amount}}</td>
                            <td>{{ \Carbon\Carbon::parse($invoice->letter_date)->format('d-m-Y') }}</td>
                           <td>{{ optional($invoice->created_at)->format('d-m-y') }}</td>
<td class="d-flex">
    <!-- Edit Button as Icon -->
    <!-- <a href="{{ route('superadmin.invoices.edit', $invoice->id) }}" 
       class="me-2 border rounded d-flex align-items-center p-2 text-decoration-none"
       title="Edit">
        <i data-feather="edit" class="feather-edit"></i>
    </a> -->

    <!-- Delete Button as Icon -->
    <button type="button" 
            class="p-2 border rounded d-flex align-items-center btn-delete" 
            data-bs-toggle="modal" 
            data-bs-target="#deleteModal{{ $invoice->id }}"
            title="Delete">
        <i data-feather="trash-2" class="feather-trash-2"></i>
    </button>
</td>
                        </tr>

                        <!-- Delete Modal -->
                        <div class="modal fade" id="deleteModal{{ $invoice->id }}" tabindex="-1" aria-hidden="true">
                          <div class="modal-dialog modal-sm">
                            <div class="modal-content">
                              <form action="{{ route('superadmin.blank-invoices.destroy', $invoice->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <div class="modal-header">
                                  <h5 class="modal-title text-danger">Confirm Delete</h5>
                                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                  Are you sure you want to delete <strong>{{ $invoice->invoice_no }}</strong>?
                                </div>
                                <div class="modal-footer">
                                  <button type="button" class="btn btn-secondary btn-sm ms-2" data-bs-dismiss="modal">Cancel</button>
                                  <button type="submit" class="btn btn-danger btn-sm ms-2">Delete</button>
                                </div>
                              </form>
                            </div>
                          </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No documents found.</td>
                        </tr>
                    @endforelse
                </tbody> 
                 
            </table> 
        </div>
        <!-- Pagination --> 
        <div class="mt-3">
            {{ $invoices->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@endsection
