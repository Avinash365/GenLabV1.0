@extends('superadmin.layouts.app')
@section('title', 'Create New User')
@section('content')


   
				<div class="content">
					<div class="page-header">
						<div class="add-item d-flex">
							<div class="page-title">
								<h4>Leaves</h4>
								<h6>Manage your Leaves</h6>
							</div>
						</div>
						<ul class="table-top-head">
							<li class="me-2">
								<a id="leave-export-pdf" data-base-url="{{ route('superadmin.leave.export.pdf') }}" href="{{ route('superadmin.leave.export.pdf') }}" target="_blank" rel="noopener" data-bs-toggle="tooltip" data-bs-placement="top" title="Pdf">
									<img src="{{ asset('assets/img/icons/pdf.svg') }}" alt="PDF export">
								</a>
							</li>
							<li class="me-2">
								<a id="leave-export-excel" data-base-url="{{ route('superadmin.leave.export.excel') }}" href="{{ route('superadmin.leave.export.excel') }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Excel">
									<img src="{{ asset('assets/img/icons/excel.svg') }}" alt="Excel export">
								</a>
							</li>
							<li class="me-2">
								<a data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh"><i class="ti ti-refresh"></i></a>
							</li>
							<li class="me-2">
								<a data-bs-toggle="tooltip" data-bs-placement="top" title="Collapse" id="collapse-header"><i class="ti ti-chevron-up"></i></a>
							</li>
						</ul>
						<div class="page-btn">
							<a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-leave">Apply Leave</a>
						</div>
					</div>
					<!-- /product list -->
					<div class="card">
						<div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
							<div class="search-set">
								<div class="search-input">
									<span class="btn-searchset"><i class="ti ti-search fs-14 feather-search"></i></span>
								</div>
							</div>
							<div class="d-flex table-dropdown my-xl-auto right-content align-items-center flex-wrap row-gap-3">
								<div class="me-2 date-select-small">
									<div class="input-addon-left position-relative">
										<input type="text" class="form-control datetimepicker" placeholder="Select Date" id="leave-filter-date" value="{{ request('date') }}">
										<span class="cus-icon"><i data-feather="calendar" class="feather-clock"></i></span>
									</div>
								</div>
								<div class="dropdown">
									<input type="hidden" id="leave-status-filter" value="{{ request('status', '') }}">
									<a href="javascript:void(0);" id="leave-status-trigger" class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">
										Select Status
									</a>
									<ul class="dropdown-menu  dropdown-menu-end p-3">
										<li>
											<button type="button" class="dropdown-item rounded-1" data-status="">All Statuses</button>
										</li>
										<li>
											<button type="button" class="dropdown-item rounded-1" data-status="Approved">Approved</button>
										</li>
										<li>
											<button type="button" class="dropdown-item rounded-1" data-status="Rejected">Rejected</button>
										</li>
									</ul>
								</div>
							</div>
						</div>
						<div class="card-body p-0">
							<div class="table-responsive">
								<table class="table datatable">
									<thead class="thead-light">
										<tr>
											<th class="no-sort">
												<label class="checkboxs">
													<input type="checkbox" id="select-all">
													<span class="checkmarks"></span>
												</label>
											</th>
											<th>Type</th>
											<th>From Date</th>
											<th>To Date</th>
											<th>Days/Hours</th>
											<th>Applied On</th>
											<th>Status</th>
											<th class="no-sort"></th>
										</tr>
									</thead>
									<tbody>
										@forelse($leaves ?? [] as $leave)
										<tr>
											<td>
												<label class="checkboxs">
													<input type="checkbox">
													<span class="checkmarks"></span>
												</label>
											</td>
											<td>{{ $leave->leave_type ?? 'N/A' }}</td>
											<td>{{ $leave->from_date ? \Carbon\Carbon::parse($leave->from_date)->format('d M Y') : 'N/A' }}</td>
											<td>{{ $leave->to_date ? \Carbon\Carbon::parse($leave->to_date)->format('d M Y') : 'N/A' }}</td>
											<td>{{ $leave->days_hours_formatted ?? ($leave->days_hours . ' Days') }}</td>
											<td>{{ $leave->created_at ? $leave->created_at->format('d M Y') : 'N/A' }}</td>
											<td>
												<span class="badge {{ $leave->status_badge_class ?? 'badge-secondary' }} d-inline-flex align-items-center badge-xs">
													<i class="ti ti-point-filled me-1"></i>{{ $leave->status ?? 'Unknown' }}
												</span>
											</td>
											<td class="action-table-data justify-content-end">
												<div class="edit-delete-action">
													@if(($leave->status ?? '') === 'Applied')
													<button class="btn btn-sm btn-success me-1" onclick="approveLeave({{ $leave->id }}, 'Approved')">
														<i class="ti ti-check"></i> Approve
													</button>
													<button class="btn btn-sm btn-danger me-1" onclick="approveLeave({{ $leave->id }}, 'Rejected')">
														<i class="ti ti-x"></i> Reject
													</button>
													@endif
													<a class="me-2 p-2" href="#" onclick="editLeave({{ $leave->id }})" data-bs-toggle="modal" data-bs-target="#edit-leave">
														<i data-feather="edit" class="feather-edit"></i>
													</a>
													<a class="p-2" href="javascript:void(0);" onclick="deleteLeave({{ $leave->id }})" data-bs-toggle="modal" data-bs-target="#delete-modal">
														<i data-feather="trash-2" class="feather-trash-2"></i>
													</a>
												</div>
											</td>
										</tr>
										@empty
										<tr>
											<td colspan="8" class="text-center py-4">
												<div class="text-muted">
													<i class="ti ti-inbox fs-48"></i>
													<h5 class="mt-2">No leave applications found</h5>
													<p>Click "Apply Leave" to create your first leave application</p>
												</div>
											</td>
										</tr>
										@endforelse
									</tbody>
								</table>
							</div>
						</div>
					</div>
					<!-- /product list -->
				</div>
				
			

<!-- Add Leave -->
		<div class="modal fade" id="add-leave">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-header">
						<div class="page-title">
							<h4>Apply Leave</h4>
						</div>
						<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<form action="{{ route('superadmin.leave.store') }}" method="POST">
						@csrf
						<div class="modal-body">
							<div class="row">
								<div class="col-lg-12">
									<div class="mb-3">
										<label class="form-label">Employee <span class="text-danger">*</span></label>
										<select class="form-select" name="user_id" required>
											<option value="">Select Employee</option>
											@forelse($users ?? [] as $user)
												<option value="{{ $user->id }}">{{ $user->name ?? 'Unknown User' }}</option>
											@empty
												<option value="" disabled>No employees found</option>
											@endforelse
										</select>
									</div>
								</div>
								<div class="col-lg-12">
									<div class="mb-3">
										<label class="form-label">Leave Type <span class="text-danger">*</span></label>
										<select class="form-select" name="leave_type" required>
											<option value="">Select Leave Type</option>
											<option value="Sick Leave">Sick Leave</option>
											<option value="Casual Leave">Casual Leave</option>
											<option value="Emergency Leave">Emergency Leave</option>
											<option value="Annual Leave">Annual Leave</option>
											<option value="Maternity Leave">Maternity Leave</option>
											<option value="Paternity Leave">Paternity Leave</option>
										</select>
									</div>
								</div>
								<div class="col-lg-12">
									<div class="row">
										<div class="col-lg-6">
											<div class="mb-3">
												<label class="form-label">From <span class="text-danger"> *</span></label>
												<input type="date" class="form-control" name="from_date" id="from_date" required>
											</div>
										</div>
										<div class="col-lg-6">
											<div class="mb-3">
												<label class="form-label">To <span class="text-danger"> *</span></label>
												<input type="date" class="form-control" name="to_date" id="to_date" required>
											</div>
										</div>
										<div class="col-lg-6">
											<div class="mb-3">
												<label class="form-label">Days/Hours <span class="text-danger"> *</span></label>
												<input type="number" class="form-control" name="days_hours" id="days_hours" min="1" required>
											</div>
										</div>
										<div class="col-lg-6">
											<div class="mb-3">
												<label class="form-label">Day Type <span class="text-danger"> *</span></label>
												<select class="form-select" name="day_type" required>
													<option value="">Select</option>
													<option value="Full Day">Full Day</option>
													<option value="Half Day">Half Day</option>
													<option value="Hours">Hours</option>
												</select>
											</div>
										</div>
									</div>
								</div>
								<div class="col-lg-12">
									<div class="bg-light rounded p-3 pb-0">
										<div class="row">
											<div class="col-lg-6">
												<div class="mb-3">
													<label class="form-label">No of Days</label>
													<input type="text" class="form-control bg-light" id="calculated_days" readonly>
												</div>
											</div>
											<div class="col-lg-6">
												<div class="mb-3">
													<label class="form-label">Remaining Leaves</label>
													<input type="text" class="form-control bg-light" value="30" readonly>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="col-lg-12">
									<div class="mb-3">
										<label class="form-label">Reason <span class="text-danger">*</span></label>
										<textarea class="form-control" name="reason" rows="4" placeholder="Enter reason for leave" required></textarea>
									</div>
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
							<button type="submit" class="btn btn-primary">Submit</button>
						</div>
					</form>
				</div>
			</div>
		</div>
		<!-- /Add Leave -->

		<!-- Edit Leave -->
		<div class="modal fade" id="edit-leave">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-header">
						<div class="page-title">
							<h4>Edit Leave</h4>
						</div>
						<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<form action="https://dreamspos.dreamstechnologies.com/html/template/leaves-admin.html">
						<div class="modal-body">
							<div class="row">
								<div class="col-lg-12">
									<div class="mb-3">
										<label class="form-label">Employee <span class="text-danger">*</span></label>
										<select class="select">
											<option>Select</option>
											<option selected>Carl Evans</option>
											<option>Minerva Rameriz</option>
											<option>Robert Lamon</option>
										</select>
									</div>
								</div>
								<div class="col-lg-12">
									<div class="mb-3">
										<label class="form-label">Leave Type <span class="text-danger">*</span></label>
										<select class="select">
											<option>Select</option>
											<option selected>Sick Leave</option>
											<option>Casual Leave</option>
										</select>
									</div>
								</div>
								<div class="col-lg-12">
									<div class="row">
										<div class="col-lg-6">
											<div class="mb-3">
												<label class="form-label">From <span class="text-danger"> *</span></label>
												<div class="input-addon-right position-relative">
													<input type="text" class="form-control datetimepicker" value="24 Dec 2024">
													<span class="cus-icon"><i data-feather="calendar" class="feather-clock"></i></span>
												</div>
											</div>
										</div>
										<div class="col-lg-6">
											<div class="mb-3">
												<label class="form-label">To <span class="text-danger"> *</span></label>
												<div class="input-addon-right position-relative">
													<input type="text" class="form-control datetimepicker" value="24 Dec 2024">
													<span class="cus-icon"><i data-feather="calendar" class="feather-clock"></i></span>
												</div>
											</div>
										</div>
										<div class="col-lg-6">
											<div class="mb-3">
												<div class="input-addon-right position-relative">
													<input type="text" class="form-control datetimepicker" value="24 Dec 2024">
													<span class="cus-icon"><i data-feather="calendar" class="feather-clock"></i></span>
												</div>
											</div>
										</div>
										<div class="col-lg-6">
											<div class="mb-3">
												<select class="select">
													<option>Select</option>
													<option selected>Full Day</option>
													<option>Half Day</option>
												</select>
											</div>
										</div>
									</div>
								</div>
								<div class="col-lg-12">
									<div class="bg-light rounded p-3 pb-0">
										<div class="row">
											<div class="col-lg-6">
												<div class="mb-3">
													<label class="form-label">No of Days</label>
													<input type="text" class="form-control bg-light " value="01" readonly>
												</div>
											</div>
											<div class="col-lg-6">
												<div class="mb-3">
													<label class="form-label">Remaining Leaves</label>
													<input type="text" class="form-control bg-light " value="08" readonly>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="col-lg-12">
									<div class="summer-description-box mb-0">
										<label class="form-label">Reason</label>
										<div id="summernote"></div>
									</div>
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
							<button type="submit" class="btn btn-primary">Save Changes</button>
						</div>
					</form>
				</div>
			</div>
		</div>
		<!-- /Edit Leave -->

		<!-- delete modal -->
		<div class="modal fade" id="delete-modal">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content p-5 px-3 text-center">
							<span class="rounded-circle d-inline-flex p-2 bg-danger-transparent mb-2"><i class="ti ti-trash fs-24 text-danger"></i></span>
							<h4 class="fs-20 text-gray-9 fw-bold mb-2 mt-1">Delete Leave</h4>
							<p class="text-gray-6 mb-0 fs-16">Are you sure you want to delete this leave application?</p>
							<div class="modal-footer-btn mt-3 d-flex justify-content-center">
								<button type="button" class="btn me-2 btn-secondary fs-13 fw-medium p-2 px-3 shadow-none" data-bs-dismiss="modal">Cancel</button>
								<form id="delete-form" method="POST" style="display:inline;">
									@csrf
									@method('DELETE')
									<button type="submit" class="btn btn-submit fs-13 fw-medium p-2 px-3">Yes Delete</button>
								</form>
							</div>						
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Approve/Reject Modal -->
		<div class="modal fade" id="approve-modal">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-header">
						<div class="page-title">
							<h4 id="approve-title">Approve Leave</h4>
						</div>
						<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<form id="approve-form" method="POST">
						@csrf
						@method('PUT')
						<div class="modal-body">
							<div class="row">
								<div class="col-lg-12">
									<div class="mb-3">
										<label class="form-label">Status <span class="text-danger">*</span></label>
										<select class="form-select" name="status" id="approve-status" required>
											<option value="Approved">Approve</option>
											<option value="Rejected">Reject</option>
										</select>
									</div>
								</div>
								<div class="col-lg-12">
									<div class="mb-3">
										<label class="form-label">Comments</label>
										<textarea class="form-control" name="admin_comments" rows="3" placeholder="Enter any comments (optional)"></textarea>
									</div>
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
							<button type="submit" class="btn btn-primary">Submit</button>
						</div>
					</form>
				</div>
			</div>
		</div>

@push('styles')
<style>
.dataTables_wrapper {
	padding: 0 1.25rem 1.25rem;
}

.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter,
.dataTables_wrapper .dataTables_info,
.dataTables_wrapper .dataTables_paginate {
	margin-top: 1rem;
}

.dataTables_wrapper .dataTables_length {
	display: flex;
	align-items: center;
	gap: 0.5rem;
}

.dataTables_wrapper .dataTables_length label {
	display: flex;
	align-items: center;
	gap: 0.75rem;
	margin-bottom: 0;
	font-weight: 500;
	color: #4b5563;
	white-space: nowrap;
}

.dataTables_wrapper .dataTables_length select {
	border-radius: 0.5rem;
	padding: 0.3rem 2rem 0.3rem 0.75rem;
	border: 1px solid #d1d5db;
	min-width: 80px;
}

.dataTables_wrapper .dataTables_paginate {
	display: flex;
	align-items: center;
	justify-content: flex-end;
	gap: 0.25rem;
}

.dataTables_wrapper .dataTables_paginate .paginate_button {
	border-radius: 999px !important;
	border: 1px solid transparent !important;
	padding: 0.4rem 0.75rem;
	margin: 0 0.125rem;
	color: #4b5563 !important;
	background: #f3f4f6 !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.current,
.dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
	background: #ffedd5 !important;
	color: #b45309 !important;
	border-color: #fdba74 !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.current) {
	background: #e5e7eb !important;
	color: #1f2937 !important;
}

.dataTables_wrapper .dataTables_info {
	margin-top: 1rem;
	color: #6b7280;
}

@media (min-width: 992px) {
	.dataTables_wrapper .row:last-child {
		display: flex;
		align-items: center;
	}

	.dataTables_wrapper .dataTables_length {
		order: 0;
	}

	.dataTables_wrapper .dataTables_info {
		order: 1;
		margin-left: 1.5rem;
	}

	.dataTables_wrapper .dataTables_paginate {
		order: 2;
		margin-left: auto;
	}
}
</style>
@endpush

<script>
// Calculate days between dates
document.addEventListener('DOMContentLoaded', function() {
    const fromDate = document.getElementById('from_date');
    const toDate = document.getElementById('to_date');
    const calculatedDays = document.getElementById('calculated_days');
    const daysHours = document.getElementById('days_hours');
	const statusHidden = document.getElementById('leave-status-filter');
	const statusTrigger = document.getElementById('leave-status-trigger');
	const statusMenu = statusTrigger ? statusTrigger.nextElementSibling : null;
	if (statusTrigger && statusHidden) {
		statusTrigger.textContent = statusHidden.value ? statusHidden.value : 'All Statuses';
	}
	const approveTemplate = @json(route('superadmin.leave.approve', ['leave' => '__leave__']));
	const deleteTemplate = @json(route('superadmin.leave.destroy', ['leave' => '__leave__']));
	const approveForm = document.getElementById('approve-form');
	const deleteForm = document.getElementById('delete-form');

    function calculateDays() {
        if (fromDate.value && toDate.value) {
            const from = new Date(fromDate.value);
            const to = new Date(toDate.value);
            
            if (to >= from) {
                const timeDiff = to.getTime() - from.getTime();
                const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1;
                calculatedDays.value = daysDiff + (daysDiff === 1 ? ' Day' : ' Days');
                daysHours.value = daysDiff;
            }
        }
    }

    fromDate.addEventListener('change', calculateDays);
    toDate.addEventListener('change', calculateDays);

	if (statusMenu) {
		statusMenu.querySelectorAll('[data-status]').forEach(function(button) {
			button.addEventListener('click', function() {
				const value = button.getAttribute('data-status') || '';
				if (statusHidden) {
					statusHidden.value = value;
				}
				if (statusTrigger) {
					statusTrigger.textContent = value ? value : 'All Statuses';
				}
			});
		});
	}

	function buildExportUrl(baseUrl) {
		try {
			const url = new URL(baseUrl, window.location.origin);
			const dateInput = document.getElementById('leave-filter-date');
			const searchInput = document.querySelector('.search-input input[type="search"]');

			if (statusHidden && statusHidden.value) {
				url.searchParams.set('status', statusHidden.value);
			}
			if (dateInput && dateInput.value) {
				url.searchParams.set('date', dateInput.value);
			}
			if (searchInput && searchInput.value) {
				url.searchParams.set('search', searchInput.value);
			}

			return url.toString();
		} catch (error) {
			return baseUrl;
		}
	}

	['leave-export-pdf', 'leave-export-excel'].forEach(function(id) {
		const link = document.getElementById(id);
		if (!link) {
			return;
		}

		link.addEventListener('click', function(event) {
			const baseUrl = link.getAttribute('data-base-url');
			if (!baseUrl) {
				return;
			}

			event.preventDefault();
			const exportUrl = buildExportUrl(baseUrl);

			if (link.target === '_blank') {
				window.open(exportUrl, link.target, 'noopener');
			} else {
				window.location.href = exportUrl;
			}
		});
	});

	window.approveLeave = function(leaveId, status) {
		if (!approveForm) {
			return;
		}

		const title = document.getElementById('approve-title');
		const statusSelect = document.getElementById('approve-status');

		approveForm.action = approveTemplate.replace('__leave__', leaveId);

		if (title) {
			title.textContent = status === 'Approved' ? 'Approve Leave' : 'Reject Leave';
		}

		if (statusSelect) {
			statusSelect.value = status;
		}

		const modalElement = document.getElementById('approve-modal');
		if (modalElement) {
			if (window.bootstrap && window.bootstrap.Modal) {
				window.bootstrap.Modal.getOrCreateInstance(modalElement).show();
			} else if (window.jQuery) {
				window.jQuery(modalElement).modal('show');
			} else {
				modalElement.classList.add('show');
				modalElement.style.display = 'block';
				modalElement.removeAttribute('aria-hidden');
			}
		}
	};

	window.deleteLeave = function(leaveId) {
		if (!deleteForm) {
			return;
		}

		deleteForm.action = deleteTemplate.replace('__leave__', leaveId);
	};
});

function editLeave(leaveId) {
    // You can add edit functionality here by pre-filling the edit modal
    console.log('Edit leave:', leaveId);
}

// Success/Error alerts
@if(session('success'))
    alert('{{ session('success') }}');
@endif

@if(session('error'))
	alert('{{ session('error') }}');
@endif

@if($errors->any())
    alert('{{ $errors->first() }}');
@endif
</script>

@endsection