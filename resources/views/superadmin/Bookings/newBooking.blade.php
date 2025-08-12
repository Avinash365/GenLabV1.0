@extends('superadmin.layouts.app')
@section('title', 'Create New User')
@section('content')



       
			<div class="content">
				<div class="page-header">
					<div class="add-item d-flex">
						<div class="page-title">
							<h4 class="fw-bold">Create Booking</h4>
							<h6>New Booking</h6>
						</div>
					</div>
					<ul class="table-top-head">
						<li>
							<a data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh"><i class="ti ti-refresh"></i></a>
						</li>
						<li>
							<a data-bs-toggle="tooltip" data-bs-placement="top" title="Collapse" id="collapse-header"><i class="ti ti-chevron-up"></i></a>
						</li>
					</ul>
					<div class="page-btn mt-0">
						<a href="product-list.html" class="btn btn-secondary"><i data-feather="arrow-left" class="me-2"></i>Back to Dashboard</a>
					</div>
				</div>
				<form action="" method="POST" enctype="multipart/form-data" class="add-product-form">
					<div class="add-product">
						<div class="accordions-items-seperate" id="accordionSpacingExample">
							<div class="accordion-item border mb-4">
								<h2 class="accordion-header" id="headingSpacingOne">
									<div class="accordion-button collapsed bg-white" data-bs-toggle="collapse" data-bs-target="#SpacingOne" aria-expanded="true" aria-controls="SpacingOne">
										<div class="d-flex align-items-center justify-content-between flex-fill">
										<h5 class="d-flex align-items-center"><i data-feather="info" class="text-primary me-2"></i><span>Booking Information</span></h5>
										</div>
									</div>
								</h2>
								<div id="SpacingOne" class="accordion-collapse collapse show" aria-labelledby="headingSpacingOne">
									<div class="accordion-body border-top">
										<div class="row">
											<div class="col-sm-6 col-12">
                                             <div class="mb-1 position-relative">
											<div class="mb-3">
  <label class="form-label">Client Name <span class="text-danger ms-1">*</span></label>
  <div class="input-group">
    <span class="input-group-text">👤</span>
    <input type="text" class="form-control" name="client_name" placeholder="Select or add a client">
     <!-- Unicode icon here -->
  </div>
</div>



											<div class="add-newplus position-absolute end-0  ">
											<a href="#" data-bs-toggle="modal" data-bs-target="#add-client-modal">
												<i data-feather="plus-circle" class="plus-down-add"></i>
												<span>Add New</span>
											</a>
											</div>
										</div>
									</div>


<div class="modal fade" id="add-client-modal" tabindex="-1" aria-labelledby="addClientModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content shadow-lg border-0 rounded-4">
      <div class="modal-header bg-primary text-white rounded-top-4">
        <h5 class="modal-title" id="addClientModalLabel">Add New Client</h5>
        <button type="button" class="btn-close btn-close-primary" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form>
        <div class="modal-body p-4">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Client Name<span class="text-danger ms-1">*</span></label>
              <input type="text" class="form-control" placeholder="Enter client name" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Client Email</label>
              <input type="email" class="form-control" placeholder="example@email.com">
            </div>
            <div class="col-md-6">
              <label class="form-label">Phone Number</label>
              <input type="tel" class="form-control" placeholder="Enter phone number">
            </div>
            <div class="col-md-6">
              <label class="form-label">Client Address</label>
              <input type="text" class="form-control" placeholder="Enter address">
            </div>
            <div class="col-12">
              <label class="form-label">Report Issue</label>
              <textarea class="form-control" rows="3" placeholder="Describe the issue..."></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer bg-light rounded-bottom-4">
  <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
  <button type="submit" class="btn btn-primary">Add Client</button>
</div>

      </form>
    </div>
  </div>
</div>


											<div class="col-sm-6 col-12">
  <div class="mb-3">
  <label for="clientAddress" class="form-label">
    Client Address <span class="text-danger ms-1">*</span>
  </label>
  <textarea
    id="clientAddress"
    class="form-control"
    name="client_address"
    rows="3"
    placeholder="📍 Enter client's full address"
    required
  ></textarea>
</div>

</div>

										</div>
										<div class="row">
											<div class="col-sm-6 col-12">
												<div class="mb-3 list position-relative">
													<label class="form-label">Job Order Date<span class="text-danger ms-1">*</span></label>
													<div class="input-groupicon calender-input">
															<i data-feather="calendar" class="info-img" style="stroke-width:2.5;"></i>
															<input type="text" class="datetimepicker form-control" placeholder="dd/mm/yyyy">
														</div>

												</div>
											</div>
											<div class="col-sm-6 col-12">
												<div class="mb-3 custom-select-wrapper">
    <label class="form-label">Report Issue To <span class="text-danger ms-1">*</span></label>
    <select class="form-control custom-select" name="reportIssueTo" required>
        <option value="">Select</option>
        <option value="vendor">Vendor</option>
        <option value="sales">Sales Team</option>
        <option value="marketing">Marketing Team</option>
    </select>
    
</div>
											</div>
										</div>
												
										<div class="row">
													<div class="col-lg-4 col-sm-6 col-12">
														<div class="mb-3">
                                                            
															<label class="form-label">Refrence no<span class="text-danger ms-1">*</span></label>
                                                            <div class="input-group">
                                                            <span class="input-group-text">🔢</span>
															<input type="text" class="form-control">
</div>
														</div>
													</div>
													<div class="col-lg-4 col-sm-6 col-12">
														<div class="mb-3">
															<label class="form-label">Marketing Code<span class="text-danger ms-1">*</span></label>
                                                            <div class="input-group">
                                                                <span class="input-group-text">🏷️</span>
															<input type="text" class="form-control">
</div>
														</div>
													</div>
													
									               <div class="col-lg-4 col-sm-6 col-12">
														<div class="mb-3">
															<label class="form-label">Contact no<span class="text-danger ms-1">*</span></label>
															<input type="text" class="form-control" placeholder="📞">
														</div>
													</div>
													
													<div class="col-lg-4 col-sm-6 col-12">
														<div class="mb-3">
															<label class="form-label">Contact email<span class="text-danger ms-1">*</span></label>
                                                            
                            
															<input class="form-control" type="text" placeholder="📧">
                                                            
														</div>
													</div>
													<div class="col-lg-4 col-sm-6 col-12">
														<div class="mb-3">
															<label class="form-label">Contarator Name<span class="text-danger ms-1">*</span></label>
															<input type="text" class="form-control">
														</div>
													</div>
												</div>
                                        </div>
										<!-- Editor -->
										
										<!-- /Editor -->
									</div>
								</div>
							</div>
							
							<div class="accordion-item border mb-4">
								<h2 class="accordion-header" id="headingSpacingThree">
									<div class="accordion-button collapsed bg-white" data-bs-toggle="collapse" data-bs-target="#SpacingThree" aria-expanded="true" aria-controls="SpacingThree">
										<div class="d-flex align-items-center justify-content-between flex-fill">
										<h5 class="d-flex align-items-center"><i data-feather="image" class="text-primary me-2"></i><span>Upload Latter</span></h5>
										</div>
									</div>
								</h2>
								<div id="SpacingThree" class="accordion-collapse collapse show" aria-labelledby="headingSpacingThree">
									<div class="accordion-body border-top">
										<div class="text-editor add-list add">
											<div class="col-lg-12">
												<div class="add-choosen">
													<div class="mb-3">
													<div class="image-upload image-upload-two">
															<input type="file" name="upload_letter">
															<div class="image-uploads">
																<i data-feather="plus-circle" class="plus-down-add me-0"></i>
																<h4>Add Images</h4>
															</div>
														</div>
													</div>
													<div class="phone-img">
														<img src="assets/img/products/phone-add-2.png" alt="image">
														<a href=""><i data-feather="x" class="x-square-add remove-product"></i></a>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							
<div class="accordion-item border mb-4">
    <h2 class="accordion-header" id="headingSpacingFour">
        <div class="accordion-button collapsed bg-white" data-bs-toggle="collapse" data-bs-target="#SpacingFour" aria-expanded="true" aria-controls="SpacingFour">
            <div class="d-flex align-items-center justify-content-between flex-fill">
                <h5 class="d-flex align-items-center"><i data-feather="list" class="text-primary me-2"></i><span>Data Fields</span></h5>
            </div>
        </div>
    </h2>
    <div id="SpacingFour" class="accordion-collapse collapse show" aria-labelledby="headingSpacingFour">
        <div class="accordion-body border-top">
            <div>
                <div class="p-3 bg-light rounded d-flex align-items-center justify-content-between border mb-3">
                    <div class="d-flex align-items-center">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="warranties" value="option1">
                            <label class="form-check-label" for="warranties">Hold</label>
                        </div>
                    </div>
                    <button type="button" class="btn btn-success" id="addItemBtn">
                        <i class="fas fa-plus me-1"></i> Add Items
                    </button>
                </div>
                
                <div id="itemsContainer">
                    <!-- Initial item -->
                    <div class="item-group border p-3 mb-3 rounded">
                        <div class="row">
                            <div class="col-lg-4 col-sm-6 col-12">
                                <div class="mb-3">
                                    <label class="form-label">Sample Description<span class="text-danger ms-1">*</span></label>
                                    <input type="text" class="form-control" name="sampleDescription[]" required>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6 col-12">
                                <div class="mb-3">
                                    <label class="form-label">Sample Quality<span class="text-danger ms-1">*</span></label>
                                    <input type="text" class="form-control" name="sampleQuality[]" required>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6 col-12">
    <div class="mb-3">
        <label class="form-label">Lab Expected Date
            <span class="text-danger ms-1">*</span>
        </label>
        <input type="date" class="form-control" name="labExpectedData[]" required>
    </div>
</div>

                            <div class="col-lg-4 col-sm-6 col-12">
                                <div class="mb-3">
                                    <label class="form-label">Amount<span class="text-danger ms-1">*</span></label>
                                    <input class="form-control" type="text" name="amount[]" required>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6 col-12">
                                <div class="mb-3 custom-select-wrapper">
  <label class="form-label">Lab Analytics <span class="text-danger ms-1">*</span></label>
  <select class="form-control custom-select" name="labAnalysis[]" required>
    <option value="">Select</option>
    <option value="1">Analytics 1</option>
    <option value="2">Analytics 2</option>
    <option value="3">Analytics 3</option>
    <option value="4">Analytics 4</option>
    <option value="5">Analytics 5</option>
    <option value="6">Analytics 6</option>
    <option value="7">Analytics 7</option>
    <option value="8">Analytics 8</option>
    <option value="9">Analytics 9</option>
    <option value="10">Analytics 10</option>
  </select>
</div>


                            </div>
                            <div class="col-lg-4 col-sm-6 col-12">
                                <div class="mb-3">
                                    <label class="form-label">Job Order No<span class="text-danger ms-1">*</span></label>
                                    <input type="text" class="form-control" name="jobOrderNo[]" required>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-danger btn-sm remove-item" style="display: none;">
                            <i class="fas fa-trash me-1"></i> Remove
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
						</div>
					</div>
					<div class="col-lg-15">
						<div class="d-flex align-items-center justify-content-end mb-4">
							<button type="button" class="btn btn-secondary me-2">Cancel</button>
							<button type="submit" class="btn btn-primary">Submit Data</button>
						</div>
					</div>
				</form>
			</div>
			
		

<script>
    document.addEventListener('DOMContentLoaded', function() {
    const addItemBtn = document.getElementById('addItemBtn');
    const itemsContainer = document.getElementById('itemsContainer');
    
    // Add new item
    addItemBtn.addEventListener('click', function() {
        const firstItemGroup = itemsContainer.querySelector('.item-group');
        const newItemGroup = firstItemGroup.cloneNode(true);
        
        // Clear input values in the new group
        const inputs = newItemGroup.querySelectorAll('input');
        inputs.forEach(input => {
            input.value = '';
        });
        
        // Show remove button for all items except the first one
        const removeButtons = itemsContainer.querySelectorAll('.remove-item');
        removeButtons.forEach(button => {
            button.style.display = 'inline-block';
        });
        
        // Add the new item group to the container
        itemsContainer.appendChild(newItemGroup);
    });
    
    // Remove item (event delegation)
    itemsContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-item') || e.target.closest('.remove-item')) {
            const itemGroup = e.target.closest('.item-group');
            const allItemGroups = itemsContainer.querySelectorAll('.item-group');
            
            if (allItemGroups.length > 1) {
                itemGroup.remove();
                
                // Hide remove button if only one item remains
                if (itemsContainer.querySelectorAll('.item-group').length === 1) {
                    itemsContainer.querySelector('.remove-item').style.display = 'none';
                }
            }
        }
    });
    });
</script>


@endsection