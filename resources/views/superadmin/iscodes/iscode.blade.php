@extends('superadmin.layouts.app')
@section('title', 'Create New User')
@section('content')


<div class="card">
								<div class="card-header">
									<h5 class="card-title">IS CODE</h5>
									
								</div>
								<div class="card-body">
									<div class="row">
										<div class="col-sm">
											<form class="needs-validation" novalidate>
												<div class="form-row row">
													<div class="col-md-4 mb-3">
														<label class="form-label" for="validationCustom01">IS name</label>
														<input type="text" class="form-control" id="validationCustom01" placeholder="First name"  required>
														
													</div>
													<div class="col-md-4 mb-3">
  <label class="form-label" for="validationCustom02">IS Description</label>
  <textarea 
    class="form-control" 
    id="validationCustom02" 
    placeholder="Enter description" 
    rows="3" 
    required></textarea>
</div>

													
												</div>
												<div class="mb-3">
  <label for="formFile" class="form-label">Upload File</label>
  <input class="form-control" type="file" id="formFile" required>
  <div class="invalid-feedback">
    Please upload a file before submitting.
  </div>
</div>

												<div class="mb-3">
													<div class="form-check">
														<input class="form-check-input" type="checkbox" value="" id="invalidCheck" required>
														<label class="form-check-label" for="invalidCheck">
															Agree to terms and conditions
														</label>
														<div class="invalid-feedback">
															You must agree before submitting.
														</div>
													</div>
												</div>
												<button class="btn btn-primary" type="submit">Add IS Code</button>
											</form>
										</div>
									</div>
								</div>
							</div>



@endsection