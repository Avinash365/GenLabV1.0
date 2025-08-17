@extends('superadmin.layouts.app')
@section('title', 'Create New User')
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

<div class="content">
    <div class="page-header">
        <div class="add-item d-flex">
            <div class="page-title">
                <h4>Inventory</h4>
                <h6>Product List</h6>
            </div>							
        </div>
        <ul class="table-top-head">
            <ul class="list-inline d-flex gap-3">
                <li class="list-inline-item">
                    <a href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="PDF">
                        <div class="fa fa-file-pdf"></div>
                    </a>
                </li>
                <li class="list-inline-item">
                    <a href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="Excel">
                        <div>
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
            <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-stock"><i class="ti ti-circle-plus me-1"></i>Add Stock</a>
        </div>
    </div>

    <!-- /product list -->
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
            <div class="search-set">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search...">
                    <button class="btn btn-outline-secondary" type="button">🔍</button>
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
                            <th>Name</th>
                            <th>Product Code</th>
                            <th>Purchase Category</th>
                            <th>Purchase Unit</th>
                            <th>Purchase Price</th>
                            <th>Unit</th>
                            <th>Invoice no</th>
                            <th>Remark</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $index => $product)
                            <tr>
                                <td>
                                    <label class="checkboxs">
                                        <input type="checkbox">
                                        <span class="checkmarks"></span>
                                    </label>
                                </td>
                                <td>{{ $products->firstItem() + $index }}</td>
                                <td>{{ $product->product_name }}</td>
                                <td>{{ $product->product_code }}</td>
                                <td>{{ $product->product_category_id ?? 'N/A' }}</td>
                                <td>{{ $product->purchase_unit }}</td>
                                <td>{{ $product->purchase_price }}</td>
                                <td>{{ $product->unit }}</td>
                                <td>{{ $product->invoice_no }}</td>
                                <td>{{ $product->remark }}</td>
                                <td class="d-flex">
                                    <div class="d-flex align-items-center edit-delete-action">
                                        @can('update', $product)
                                            <a href="javascript:void(0);" 
                                            class="me-2 border rounded d-flex align-items-center p-2 edit-product-btn"
                                            data-id="{{ $product->id }}"
                                            data-name="{{ $product->product_name }}"
                                            data-code="{{ $product->product_code }}"
                                            data-category="{{ $product->product_category_id }}"
                                            data-purchase-unit="{{ $product->purchase_unit }}"
                                            data-unit="{{ $product->unit }}"
                                            data-price="{{ $product->purchase_price }}"
                                            data-remarks="{{ $product->remark }}"> 
                                                <i data-feather="edit" class="feather-edit"></i>
                                            </a> 
                                        @endcan
                                        @can('delete', $product)
                                        <a class="p-2 border rounded d-flex align-items-center delete-product-btn"
                                            href="javascript:void(0);"
                                            data-id="{{ $product->id }}"
                                            data-bs-toggle="modal"
                                            data-bs-target="#delete">
                                                <i data-feather="trash-2" class="feather-trash-2"></i>
                                        </a> 
                                        @endcan                    
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-3">
                {{ $products->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
    <!-- /product list -->
</div>

<!-- Edit Product -->
<div class="modal fade" id="edit-product">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="page-title">
                    <h4>Edit Product</h4>
                </div>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span> 
                </button>
            </div>
            <form method="POST" id="editProductForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Product Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="product_name" id="edit_product_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Product Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="product_code" id="edit_product_code" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Purchase Category</label>
                            <select class="form-select" name="product_category_id" id="edit_product_category_id" required>
                                <option value="">Select</option>
                                <option value="Electronics">Electronics</option>
                                <option value="Groceries">Groceries</option>
                                <option value="Clothing">Clothing</option>
                                <option value="Stationery">Stationery</option>
                                <option value="Furniture">Furniture</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Purchase Unit</label>
                            <select class="form-select" name="purchase_unit" id="edit_purchase_unit" required>
                                <option value="">Select Unit</option>
                                <option value="Kg">Kg</option>
                                <option value="Gram">Gram</option>
                                <option value="Piece">Piece</option>
                                <option value="Dozen">Dozen</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Unit </label>
                            <input type="text" class="form-control" name="unit" id="edit_unit">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Purchase Price</label>
                            <input type="number" step="0.01" class="form-control" name="purchase_price" id="edit_purchase_price">
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label">Remarks</label>
                        <textarea class="form-control" name="remark" id="edit_remark" rows="3"></textarea>
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
<!-- /Edit Product -->

<!-- Delete -->
<div class="modal fade modal-default" id="delete">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body p-0">
                <div class="success-wrap text-center">
                    <form id="deleteProductForm" method="POST">
                        @csrf
                        @method('DELETE')
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

@endsection

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Edit functionality
    document.querySelectorAll(".edit-product-btn").forEach(function(button) {
        button.addEventListener("click", function() {
            document.getElementById("edit_product_name").value = this.dataset.name;
            document.getElementById("edit_product_code").value = this.dataset.code;
            document.getElementById("edit_product_category_id").value = this.dataset.category;
            document.getElementById("edit_purchase_unit").value = this.dataset.purchaseUnit;
            document.getElementById("edit_unit").value = this.dataset.unit;
            document.getElementById("edit_purchase_price").value = this.dataset.price;
            document.getElementById("edit_remark").value = this.dataset.remarks;

            document.getElementById("editProductForm").action = "{{ url('superadmin/products') }}/" + this.dataset.id;

            new bootstrap.Modal(document.getElementById('edit-product')).show();
        });
    });

    // Delete functionality
    document.querySelectorAll(".delete-product-btn").forEach(function(button) {
        button.addEventListener("click", function() {
            let productId = this.dataset.id;
            document.getElementById("deleteProductForm").action = "{{ url('superadmin/products') }}/" + productId;
        });
    });
});
</script>
