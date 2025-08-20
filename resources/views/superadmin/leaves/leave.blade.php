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
								<a data-bs-toggle="tooltip" data-bs-placement="top" title="Pdf"><img src="assets/img/icons/pdf.svg" alt="img"></a>
							</li>
							<li class="me-2">
								<a data-bs-toggle="tooltip" data-bs-placement="top" title="Excel"><img src="assets/img/icons/excel.svg" alt="img"></a>
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
										<input type="text" class="form-control datetimepicker" placeholder="Select Date">
										<span class="cus-icon"><i data-feather="calendar" class="feather-clock"></i></span>
									</div>
								</div>
								<div class="dropdown">
									<a href="javascript:void(0);" class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">
										Select Status
									</a>
									<ul class="dropdown-menu  dropdown-menu-end p-3">
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">Approved</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">Rejected</a>
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
										<tr>
											<td>
												<label class="checkboxs">
													<input type="checkbox">
													<span class="checkmarks"></span>
												</label>
											</td>
											<td>Sick Leave</td>
											<td>
												24 Dec 2024							
											</td>
											<td>24 Dec 2024</td>
											<td>
												01 Day
											</td>
											<td>23 Dec 2024</td>
											<td>
												<span class="badge badge-success d-inline-flex align-items-center badge-xs">
													<i class="ti ti-point-filled me-1"></i>Approved
												</span>
											</td>
											<td class="action-table-data justify-content-end">
												<div class="edit-delete-action">
													<a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-leave">
														<i data-feather="edit" class="feather-edit"></i>
													</a>
													<a class="p-2" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete-modal">
														<i data-feather="trash-2" class="feather-trash-2"></i>
													</a>
												</div>
											</td>
										</tr>						
										<tr>
											<td>
												<label class="checkboxs">
													<input type="checkbox">
													<span class="checkmarks"></span>
												</label>
											</td>
											<td>Casual Leave</td>
											<td>
												10 Dec 2024					
											</td>
											<td>10 Dec 2024</td>
											<td>
												01 Day
											</td>
											<td>09 Dec 2024</td>
											<td>
												<span class="badge badge-success d-inline-flex align-items-center badge-xs">
													<i class="ti ti-point-filled me-1"></i>Approved
												</span>
											</td>
											<td class="action-table-data justify-content-end">
												<div class="edit-delete-action">
													<a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-leave">
														<i data-feather="edit" class="feather-edit"></i>
													</a>
													<a class="p-2" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete-modal">
														<i data-feather="trash-2" class="feather-trash-2"></i>
													</a>
												</div>
											</td>
										</tr>						
											
													
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
					<form action="https://dreamspos.dreamstechnologies.com/html/template/leaves-admin.html">
						<div class="modal-body">
							<div class="row">
								<div class="col-lg-12">
									<div class="mb-3">
										<label class="form-label">Employee <span class="text-danger">*</span></label>
										<select class="select">
											<option>Select</option>
											<option>Carl Evans</option>
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
											<option>Sick Leave</option>
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
													<input type="text" class="form-control datetimepicker" placeholder="dd/mm/yyyy">
													<span class="cus-icon"><i data-feather="calendar" class="feather-clock"></i></span>
												</div>
											</div>
										</div>
										<div class="col-lg-6">
											<div class="mb-3">
												<label class="form-label">To <span class="text-danger"> *</span></label>
												<div class="input-addon-right position-relative">
													<input type="text" class="form-control datetimepicker" placeholder="dd/mm/yyyy">
													<span class="cus-icon"><i data-feather="calendar" class="feather-clock"></i></span>
												</div>
											</div>
										</div>
										<div class="col-lg-6">
											<div class="mb-3">
												<div class="input-addon-right position-relative">
													<input type="text" class="form-control datetimepicker" placeholder="dd/mm/yyyy">
													<span class="cus-icon"><i data-feather="calendar" class="feather-clock"></i></span>
												</div>
											</div>
										</div>
										<div class="col-lg-6">
											<div class="mb-3">
												<select class="select">
													<option>Select</option>
													<option>Full Day</option>
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
													<input type="text" class="form-control bg-light " readonly>
												</div>
											</div>
											<div class="col-lg-6">
												<div class="mb-3">
													<label class="form-label">Remaining Leaves</label>
													<input type="text" class="form-control bg-light " readonly>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="col-lg-12">
									<div class="summer-description-box mb-0">
										<label class="form-label">Reason</label>
										<div id="summernote2"></div>
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
							<p class="text-gray-6 mb-0 fs-16">Are you sure you want to delete leave?</p>
							<div class="modal-footer-btn mt-3 d-flex justify-content-center">
								<button type="button" class="btn me-2 btn-secondary fs-13 fw-medium p-2 px-3 shadow-none" data-bs-dismiss="modal">Cancel</button>
								<button type="submit" class="btn btn-submit fs-13 fw-medium p-2 px-3">Yes Delete</button>
							</div>						
						</div>
					</div>
				</div>
			</div>
		</div>











@endsection