<?php $__env->startSection('title', 'Create New User'); ?>
<?php $__env->startSection('content'); ?>





				<div class="content">
					<div class="page-header">
						<div class="add-item d-flex">
							<div class="page-title">
								<h4>Invertory</h4>
								<h6>Supplier List</h6>
							</div>							
						</div>
						<ul class="table-top-head">
							<ul class="list-inline d-flex gap-3">
  <li class="list-inline-item ">
    <a href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="PDF">
      <div class="fa fa-file-pdf"></div>
    </a>
  </li>
  <li class="list-inline-item">
    <a href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="Excel">
      <!-- Inline Excel Icon -->
		<div data-bs-toggle="tooltip" data-bs-placement="top" title="Export to Excel">
			<svg xmlns="http://www.w3.org/2000/svg" width="20" height="24" fill="green" viewBox="0 0 24 24">
				<path d="M19 2H8c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-8.5 14-2-3 2-3H9l-1.5 2.25L6 10H4l2.5 3L4 16h2l1.5-2.25L9 16h1.5zM19 20H8V4h11v16z"/>
			</svg>
		</div>


    </a>
  </li>
</ul>

							<li>
								<a data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh"><i class="ti ti-refresh"></i></a>
							</li>
							<li>
								<a data-bs-toggle="tooltip" data-bs-placement="top" title="Collapse" id="collapse-header"><i class="ti ti-chevron-up"></i></a>
							</li>
						</ul>
						<div class="page-btn">
							<a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-stock"><i class="ti ti-circle-plus me-1"></i>Add Supplier</a>
						</div>
					</div>
					<!-- /product list -->
					<div class="card">
						<div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
							<div class="search-set">
								<div class="input-group">
									<input type="text" class="form-control" placeholder="Search...">
									<button class="btn btn-outline-secondary" type="button">🔍</button> <!-- or use any local icon class -->
									</button>
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
											<th>SL</th>
											<th>Supplier Name</th>
											<th>Address</th>
											<th>Mobile No</th>
											<th>Email</th>
											<th>Company Name</th>
                                            <th>Product List</th>
											<th>Action</th>
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
											<td>Lavish Warehouse </td>
											<td>Electro Mart </td>
											<td>
												<div class="d-flex align-items-center">
													
													<a href="javascript:void(0);">Lenovo IdeaPad 3</a>
												</div>												
											</td>
											<td>24 Dec 2024</td>
											<td>
												<div class="d-flex align-items-center">
													
													<a href="javascript:void(0);">James Kirwin</a>
												</div>
											</td>
											<td>100</td>
                                            <td>100</td>
										
											
											<td class="d-flex">
												<div class="d-flex align-items-center edit-delete-action">
													<a class="me-2 border rounded d-flex align-items-center p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-stock">
														<i data-feather="edit" class="feather-edit"></i>
													</a>
													<a class="p-2 border rounded d-flex align-items-center" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
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
				
			</div>
        </div>
		<!-- /Main Wrapper -->

		<!-- Add Stock -->
		<div class="modal fade" id="add-stock">
			<div class="modal-dialog modal-dialog-centered stock-adjust-modal">
				<div class="modal-content">
					<div class="modal-header">
						<div class="page-title">
							<h4>Add Supplier</h4>
						</div>
						<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<form action="">
						<div class="modal-body">
							<div class="row">
								<div class="col-lg-12">
									<div class="search-form mb-0">
										<label class="form-label">Supplier Name * <span class="text-danger ms-1">*</span></label>
										<div class="position-relative">
											<input type="text" class="form-control" placeholder="">
											
										</div>
									</div>
								</div>
								<div class="col-lg-12">
									<div class="search-form mb-0">
										<label class="form-label">Address * <span class="text-danger ms-1">*</span></label>
										<div class="position-relative">
											<input type="text" class="form-control" placeholder="">
											
										</div>
									</div>
								</div>
								<div class="col-lg-12">
									<div class="search-form mb-0">
										<label class="form-label">Mobile No * <span class="text-danger ms-1">*</span></label>
										<div class="position-relative">
											<input type="text" class="form-control" placeholder="">
											
										</div>
									</div>
								</div>
								<div class="col-lg-12">
									<div class="search-form mb-0">
										<label class="form-label">Email <span class="text-danger ms-1">*</span></label>
										<div class="position-relative">
											<input type="text" class="form-control" placeholder="">
											
										</div>
									</div>
								</div>
                                <div class="col-lg-12">
									<div class="search-form mb-0">
										<label class="form-label">Comapany Name <span class="text-danger ms-1">*</span></label>
										<div class="position-relative">
											<input type="text" class="form-control" placeholder="">
											
										</div>
									</div>
								</div>
                                <div class="col-lg-12">
									<div class="search-form mb-0">
										<label class="form-label">Product List<span class="text-danger ms-1">*</span></label>
										<div class="position-relative">
											<input type="text" class="form-control" placeholder="">
											
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
							<button type="submit" class="btn btn-primary">Add Stock</button>
						</div>
					</form>
				</div>
			</div>
		</div>
		<!-- /Add Stock -->

		<!-- Edit Stock -->
		<div class="modal fade" id="edit-stock">
			<div class="modal-dialog modal-dialog-centered stock-adjust-modal">
				<div class="modal-content">
					<div class="modal-header">
						<div class="page-title">
							<h4>Edit Stock</h4>
						</div>
						<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<form action="">
						<div class="modal-body">
							<div class="row">
								<div class="col-lg-12">
									<div class="mb-3">
										<label class="form-label">Warehouse <span class="text-danger ms-1">*</span></label>
										<select class="select">
											<option>Select</option>
											<option selected>Lavish Warehouse</option>
											<option>Quaint Warehouse</option>
											<option>Traditional Warehouse</option>
										</select>
									</div>
								</div>
								<div class="col-lg-12">
									<div class="mb-3">
										<label class="form-label">Store <span class="text-danger ms-1">*</span></label>
										<select class="select">
											<option>Select</option>
											<option selected>Electro Mart</option>
											<option>Quantum Gadgets</option>
											<option>Prime Bazaar</option>
										</select>
									</div>
								</div>
								<div class="col-lg-12">
									<div class="mb-3">
										<label class="form-label">Responsible Person <span class="text-danger ms-1">*</span></label>
										<select class="select">
											<option>Select</option>
											<option selected>James Kirwin</option>
											<option>Francis Chang</option>
											<option>Antonio Engle</option>
										</select>
									</div>
								</div>
								<div class="col-lg-12">
									<div class="search-form mb-3">
										<label class="form-label">Product<span class="text-danger ms-1">*</span></label>
										<div class="position-relative">
											<input type="text" class="form-control" placeholder="Select Product" value="Nike Jordan">
											<i data-feather="search" class="feather-search"></i>
										</div>
									</div>
								</div>
								<div class="col-lg-12">
									<div class="modal-body-table">
										<div class="table-responsive">
											<table class="table  datanew">
												<thead>
													<tr>
														<th>Product</th>
														<th>SKU</th>
														<th>Category</th>
														<th>Qty</th>
														<th class="no-sort"></th>
													</tr>
												</thead>
												<tbody>
													<tr>
														<td>
															<div class="d-flex align-items-center">
																<a href="javascript:void(0);" class="avatar avatar-md me-2">
																	<img src="assets/img/products/stock-img-02.png" alt="product">
																</a>
																<a href="javascript:void(0);">Nike Jordan</a>
															</div>												
														</td>
														<td>PT002</td>
														<td>Nike</td>
														<td>
															<div class="product-quantity bg-gray-transparent border-0">
																<span class="quantity-btn"><i data-feather="minus-circle" class="feather-search"></i></span>
																<input type="text" class="quntity-input bg-transparent" value="2">
																<span class="quantity-btn">+<i data-feather="plus-circle" class="plus-circle"></i></span>
															</div>
														</td>
														<td>
															<div class="d-flex align-items-center justify-content-between edit-delete-action">
																<a class="d-flex align-items-center border rounded p-2" href="javascript:void(0);">
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
		<!-- /Edit Stock -->

		<!-- Delete -->
		<div class="modal fade modal-default" id="delete">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-body p-0">
						<div class="success-wrap text-center">
							<form action="">
								<div class="icon-success bg-danger-transparent text-danger mb-2">
									<i class="ti ti-trash"></i>
								</div>
								<h3 class="mb-2">Delete Stock</h3>
								<p class="fs-16 mb-3">Are you sure you want to delete product from stock?</p>
								<div class="d-flex align-items-center justify-content-center gap-2 flex-wrap">
									<button type="button" class="btn btn-md btn-secondary" data-bs-dismiss="modal">No, Cancel</button>
									<button type="submit" class="btn btn-md btn-primary">Yes, Delete</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Delete -->








<?php $__env->stopSection(); ?>
<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Mamp\htdocs\GenLab\resources\views/superadmin/supplier/Supplier.blade.php ENDPATH**/ ?>