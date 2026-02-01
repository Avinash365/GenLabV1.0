@extends('superadmin.layouts.app')
@section('title', 'Manage Bank Accounts')
@section('content')

<div class="d-flex justify-content-end mt-3 me-3">
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createAccountModal">
        <i class="bi bi-plus-lg"></i> Add Bank Account
    </button>
</div>

<!-- Accounts List -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title">Bank Accounts List</h5>
    </div>

    <div class="card-body">
        <div class="table-responsive">

            <table class="table table-bordered table-hover align-middle table-striped">
                <thead class="table-light">
                    <tr>
                        <th>Bank Name</th>
                        <th>Account Number</th>
                        <th>Account Holder</th>
                        <th>Created By</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($accounts as $account)
                    <tr>
                        <td>{{ $account->bank_name }}</td>
                        <td>{{ $account->account_no }}</td>
                        <td>{{ $account->account_holder_name }}</td>
                        <td>{{ $account->creator->name ?? 'N/A' }}</td>

                        <td class="text-nowrap">

                            <button class="btn btn-sm btn-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#editModal{{ $account->id }}">
                                Edit
                            </button>

                            <button class="btn btn-sm btn-danger"
                                data-bs-toggle="modal"
                                data-bs-target="#deleteModal{{ $account->id }}">
                                Delete
                            </button>

                        </td>
                    </tr>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editModal{{ $account->id }}">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">

                                <form action="{{ route('bankAccounts.update',$account->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')

                                    <div class="modal-header">
                                        <h5>Edit Bank Account</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <div class="modal-body">

                                        <div class="mb-3">
                                            <label>Bank Name</label>
                                            <input type="text" name="bank_name" class="form-control"
                                                value="{{ $account->bank_name }}" required>
                                        </div>

                                        <div class="mb-3">
                                            <label>Account Number</label>
                                            <input type="text" name="account_no" class="form-control"
                                                value="{{ $account->account_no }}" required>
                                        </div>

                                        <div class="mb-3">
                                            <label>Account Holder Name</label>
                                            <input type="text" name="account_holder_name" class="form-control"
                                                value="{{ $account->account_holder_name }}" required>
                                        </div>

                                    </div>

                                    <div class="modal-footer">
                                        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button class="btn btn-primary">Update</button>
                                    </div>

                                </form>

                            </div>
                        </div>
                    </div>

                    <!-- Delete Modal -->
                    <div class="modal fade" id="deleteModal{{ $account->id }}">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">

                                <form action="{{ route('bankAccounts.destroy',$account->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')

                                    <div class="modal-header">
                                        <h5>Delete Account</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <div class="modal-body">
                                        Are you sure you want to delete
                                        <strong>{{ $account->account_no }}</strong> ?
                                    </div>

                                    <div class="modal-footer">
                                        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button class="btn btn-danger">Delete</button>
                                    </div>

                                </form>

                            </div>
                        </div>
                    </div>

                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            No bank accounts found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>

            </table>

        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createAccountModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <form action="{{ route('bankAccounts.store') }}" method="POST">
                @csrf

                <div class="modal-header">
                    <h5>Add Bank Account</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label>Bank Name</label>
                        <input type="text" name="bank_name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Account Number</label>
                        <input type="text" name="account_no" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Account Holder Name</label>
                        <input type="text" name="account_holder_name" class="form-control" required>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary">Save</button>
                </div>

            </form>

        </div>
    </div>
</div>

@endsection
