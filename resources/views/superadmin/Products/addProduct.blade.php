@extends('superadmin.layouts.app')
@section('title', 'Create New Product')
@section('content')


@if (session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if ($errors->any())
  <div class="alert alert-danger">
      <ul class="mb-0">
          @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
          @endforeach
      </ul>
  </div>
@endif

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Inventory - Create Product</h5>
            </div>
            <div class="card-body">
                <form action="{{route('superadmin.products.store')}}" method="POST">
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
                                    <select class="form-select" name="product_category_id" required>
                                        <option value="">Select</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Purchase Unit -->
                            <div class="row mb-3">
                                <label class="col-lg-3 col-form-label">Purchase Unit*</label>
                                <div class="col-lg-9">
                                    <select class="form-select" name="purchase_unit" required>
                                        <option value="">Select Unit</option>
                                        <option value="Kg">Kg</option>
                                        <option value="Gram">Gram</option>
                                        <option value="Piece">Piece</option>
                                        <option value="Dozen">Dozen</option>
                                    </select>
                                </div>
                            </div>

                        </div>

                        <div class="col-xl-6">
                            <a href="{{ route('superadmin.viewproduct.viewProduct') }}" class="btn btn-primary mb-3">
                                View Product List
                            </a>
                            <!-- Unit  -->
                            <div class="row mb-3">
                                <label class="col-lg-3 col-form-label">Unit*</label>
                                <div class="col-lg-9">
                                    <input type="number" class="form-control" name="unit" placeholder="" required>
                                </div>
                            </div>

                            <!-- Sales Price -->
                            <div class="row mb-3">
                                <label class="col-lg-3 col-form-label">Purchase price*</label>
                                <div class="col-lg-9">
                                    <input type="number" class="form-control" name="purchase_price" required>
                                </div>
                            </div> 


                            <!-- Remarks -->
                            <div class="row mb-3">
                                <label class="col-lg-3 col-form-label">Remarks</label>
                                <div class="col-lg-9">
                                    <textarea class="form-control" name="remark" rows="4" placeholder="Enter remarks here..."></textarea>
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
