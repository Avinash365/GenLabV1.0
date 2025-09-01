@extends('superadmin.layouts.app')
@section('title', 'Manage Quotations')
@section('content')




<div class="d-flex justify-content-end mt-3 me-3">
    @can('create', App\Models\Profile::class)
        <a href="{{ route('superadmin.quotations.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Generate Quotation
        </a>
    @endcan
</div>


<!-- Table List -->
<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title">Generated Quotations</h5>
        <!-- Search bar -->
        <form method="GET" action="{{ route('superadmin.quotations.index') }}" class="d-flex" role="search">
            <input class="form-control me-2" type="search" name="search" placeholder="Search Quotation..." value="{{ request('search') }}">
            <button class="btn btn-outline-primary" type="submit">Search</button>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Quotation No</th>
                        <th>Client Name</th>
                        <th>Marketing Person</th>      
                        <th>Client Gstin</th>
                        <th>Total Amount</th>
                        <th>Quotation Date</th>
                        <th>Bill Issue To</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($quotations as $quotation)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $quotation->quotation_no }}</td>
                            <td>{{ $quotation->client_name ?? 'N/A' }}</td>
                            <td>{{ $quotation->generatedBy->name ?? 'N/A' }}</td>
                            <td>{{ $quotation->client_gstin }}</td>
                            <td>{{ $quotation->payable_amount }}</td>
                            <td>{{ \Carbon\Carbon::parse($quotation->quotation_date)->format('d-m-Y') }}</td>
                            <td>{{ $quotation->bill_issue_to }}</td>
                           <td class="d-flex">
    <!-- Edit Button -->
    <a href="{{ route('superadmin.quotations.edit', $quotation->id) }}" 
       class="me-2 border rounded d-flex align-items-center p-2 text-decoration-none">
        <i data-feather="edit" class="feather-edit"></i>
    </a>

    <!-- Delete Button -->
    <button type="button" class="p-2 border rounded d-flex align-items-center btn-delete" 
            data-bs-toggle="modal" data-bs-target="#deleteModal{{ $quotation->id }}">
        <i data-feather="trash-2" class="feather-trash-2"></i>
    </button>
</td>
                        </tr>

                        <!-- Delete Modal -->
                        <div class="modal fade" id="deleteModal{{ $quotation->id }}" tabindex="-1" aria-hidden="true">
                          <div class="modal-dialog modal-sm">
                            <div class="modal-content">
                              <form action="{{ route('superadmin.quotations.destroy', $quotation->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <div class="modal-header">
                                  <h5 class="modal-title text-danger">Confirm Delete</h5>
                                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                  Are you sure you want to delete <strong>{{ $quotation->quotation_no }}</strong>?
                                </div>
                                <div class="modal-footer">
                                  <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                  <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </div>
                              </form>
                            </div>
                          </div>
                        </div>

                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">No quotations found.</td>
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
@endsection
