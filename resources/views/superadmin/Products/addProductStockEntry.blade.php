@extends('superadmin.layouts.app')
@section('title', 'Stock Entry')

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
                        <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Inventory - Stock Entry</h5>
                <a href="{{ route('superadmin.viewproduct.viewProduct') }}" class="btn btn-primary">
                    View Product
                </a>
            </div>
            <div class="card-body">
                <form action="{{ route('superadmin.productStockEntry.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <!-- Left Side -->
                        <div class="col-xl-6">
                            <h6 class="mb-3">Stock Details</h6>
                            <div class="row mb-3">
                                <!-- <label class="col-lg-3 col-form-label">Type*</label>
                                <div class="col-lg-9">
                                    <select name="type" class="form-select" required>
                                        <option value="">Select Type</option>
                                        <option value="buy">Buy</option>
                                        <option value="sell">Sell</option>
                                    </select>
                                </div> -->
                            </div>

                            <!-- Product Code -->
                            <div class="row mb-3">
                                <label class="col-lg-3 col-form-label">Product*</label>
                                <div class="col-lg-9">
                                    <select name="product_code" class="form-select" required>
                                        <option value="">Select Product</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->product_code }}">
                                                {{ $product->product_name }} ({{ $product->product_code }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Purchase Price -->
                            <div class="row mb-3">
                                <label class="col-lg-3 col-form-label">Purchase Price*</label>
                                <div class="col-lg-9">
                                    <input type="number" name="purchase_price" id="purchase_price" class="form-control" placeholder="0" required>
                                </div>
                            </div>

                            <!-- Quantity -->
                            <div class="row mb-3">
                                <label class="col-lg-3 col-form-label">Quantity*</label>
                                <div class="col-lg-9">
                                    <input type="number" name="quantity" id="quantity" class="form-control" placeholder="0" required>
                                </div>
                            </div>

                            <!-- Purchase Unit -->
                            <div class="row mb-3">
                                <label class="col-lg-3 col-form-label">Purchase Unit*</label>
                                <div class="col-lg-9">
                                    <select class="form-select" name="purchase_unit" required>
                                        <option value="">Select Unit</option>
                                        <option value="Kg">Piece</option>
                                        <option value="Gram">Box</option>
                                        <option value="Piece">Pack</option>
                                        <option value="Dozen">Set</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Right Side -->
                        <div class="col-xl-6">
                            

                            <!-- Invoice No -->
                            <div class="row mb-3">
                                <label class="col-lg-3 col-form-label">Invoice No*</label>
                                <div class="col-lg-9">
                                    <input type="text" name="invoice_no" class="form-control" required>
                                </div>
                            </div>

                            <!-- Upload Bill -->
                            <div class="row mb-3">
                                <label class="col-lg-3 col-form-label">Upload Bill</label>
                                <div class="col-lg-9">
                                    <input type="file" name="upload_bill" class="form-control">
                                </div>
                            </div>

                            <!-- Total (auto-calculated) -->
                            <div class="row mb-3">
                                <label class="col-lg-3 col-form-label">Total</label>
                                <div class="col-lg-9">
                                    <input type="number" id="total" class="form-control" name="total" placeholder="0" readonly>
                                </div>
                            </div>

                            <!-- Remarks -->
                            <div class="row mb-3">
                                <label class="col-lg-3 col-form-label">Remarks</label>
                                <div class="col-lg-9">
                                    <textarea name="remarks" class="form-control" rows="3" placeholder="Enter remarks..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Submit Entry</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- JS for auto-calculating total --}}
<script>
    const price = document.getElementById('purchase_price');
    const qty = document.getElementById('quantity');
    const total = document.getElementById('total');

    function calcTotal() {
        let p = parseFloat(price.value) || 0;
        let q = parseFloat(qty.value) || 0;
        total.value = (p * q).toFixed(2);
    }

    price.addEventListener('input', calcTotal);
    qty.addEventListener('input', calcTotal);
</script>

@endsection
