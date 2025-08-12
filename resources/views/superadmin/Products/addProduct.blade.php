@extends('superadmin.layouts.app')
@section('title', 'Create New Product')
@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Inventory - Create Product</h5>
            </div>
            <div class="card-body">
                <form action="#" method="POST">
                    {{-- CSRF Token --}}
                    @csrf

                    <div class="row">
                        <div class="col-xl-6">
                            <h6 class="mb-3">Create Product</h6>

                            <!-- Product Name -->
                            <div class="row mb-3">
                                <label class="col-lg-3 col-form-label">Product Name*</label>
                                <div class="col-lg-9">
                                    <input type="text" class="form-control" name="product_name" required>
                                </div>
                            </div>

                            <!-- Product Code -->
                            <div class="row mb-3">
                                <label class="col-lg-3 col-form-label">Product Code*</label>
                                <div class="col-lg-9">
                                    <input type="text" class="form-control" name="product_code" required>
                                </div>
                            </div>

                            <!-- Product Category -->
                            <div class="row mb-3">
                                <label class="col-lg-3 col-form-label">Purchase Category*</label>
                                <div class="col-lg-9">
                                    <select class="form-select" name="purchase_unit" required>
                                        <option value="">Select</option>
                                        <option value="1">California</option>
                                        <option value="2">Texas</option>
                                        <option value="3">Florida</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Purchase Unit -->
                            <div class="row mb-3">
                                <label class="col-lg-3 col-form-label">Purchase Unit*</label>
                                <div class="col-lg-9">
                                    <select class="form-select" name="purchase_unit" required>
                                        <option value="">Select Unit</option>
                                        <option value="1">California</option>
                                        <option value="2">Texas</option>
                                        <option value="3">Florida</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Sales Unit -->
                            <div class="row mb-3">
                                <label class="col-lg-3 col-form-label">Sales Unit*</label>
                                <div class="col-lg-9">
                                    <select class="form-select" name="sales_unit" required> 
                                        <option value="">Select Unit</option>
                                        <option value="1">California</option>
                                        <option value="2">Texas</option>
                                        <option value="3">Florida</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-6">
                            <a href="{{ route('superadmin.viewproduct.viewProduct') }}" class="btn btn-primary mb-3">
                                View Product List
                            </a>



                            <!-- Unit Ratio -->
                            <div class="row mb-3">
                                <label class="col-lg-3 col-form-label">Unit Ratio*</label>
                                <div class="col-lg-9">
                                    <input type="number" class="form-control" name="sales_price" placeholder="Eg. Purchase Unit : KG & Sales Unit : Gram = Ratio : 1000" required>
                                </div>
                            </div>

                            <!-- Sales Price -->
                            <div class="row mb-3">
                                <label class="col-lg-3 col-form-label">Sales Price*</label>
                                <div class="col-lg-9">
                                    <input type="number" class="form-control" name="sales_price" required>
                                </div>
                            </div>

                            <!-- Remarks -->
                            <div class="row mb-3">
                                <label class="col-lg-3 col-form-label">Remarks</label>
                                <div class="col-lg-9">
                                    <textarea class="form-control" name="remarks" rows="4" placeholder="Enter remarks here..."></textarea>
                                </div>
                            </div>

                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
