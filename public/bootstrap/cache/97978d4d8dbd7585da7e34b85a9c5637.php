<?php $__env->startSection('title', 'Create New User'); ?>
<?php $__env->startSection('content'); ?>

<?php if(session('success')): ?>
  <div class="alert alert-success"><?php echo e(session('success')); ?></div>
<?php endif; ?>

<?php if($errors->any()): ?>
  <div class="alert alert-danger">
      <ul class="mb-0">
          <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <li><?php echo e($error); ?></li>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </ul>
  </div>
<?php endif; ?>

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
                    <a href="<?php echo e(route('superadmin.viewproduct.pdf', $categoryId ?? null)); ?>?search=<?php echo e(request('search')); ?>" target="_blank" data-bs-toggle="tooltip" data-bs-placement="top" title="PDF">
                        <div class="fa fa-file-pdf"></div>
                    </a>
                </li>
                <li class="list-inline-item">
                    <a href="<?php echo e(route('superadmin.viewproduct.excel', $categoryId ?? null)); ?>?search=<?php echo e(request('search')); ?>" target="_blank" data-bs-toggle="tooltip" data-bs-placement="top" title="Excel">
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
            <a href="<?php echo e(route('superadmin.productStockEntry.create')); ?>" class="btn btn-primary">
                <i class="ti ti-circle-plus me-1"></i>Add Stock
            </a>
        </div>
    </div>

    <!-- /product list -->
    <div class="card shadow-sm border-0">
            <!-- Header -->
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                <!-- Search -->
                <form method="GET" action="<?php echo e(route('superadmin.viewproduct.viewProduct', $categoryId ?? null)); ?>" class="d-flex flex-grow-1" style="max-width: 400px;">
                    <div class="input-group">
                        <input type="text" 
                            name="search" 
                            value="<?php echo e(request('search')); ?>" 
                            class="form-control" 
                            placeholder="Search by name or code...">
                        <button class="btn btn-primary" type="submit">
                            <i class="fa fa-search"></i>
                        </button>
                    </div>
                </form> 
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Product::class)): ?>
                    <div class="d-flex justify-content-end mt-3 me-3 mb-4">
                            <a href="<?php echo e(route('superadmin.products.addProduct')); ?>" class="btn btn-primary">
                                <i class="bi bi-plus-lg"></i> Add New
                            </a>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-body p-3">
                <!-- Category Filter -->
                <div class="mb-4">
                    <div class="d-flex flex-wrap gap-2">
                        
                        <a href="<?php echo e(route('superadmin.viewproduct.viewProduct')); ?>"
                        class="btn btn-sm <?php echo e(empty($categoryId) ? 'btn-primary' : 'btn-outline-primary'); ?>">
                            All
                        </a>

                        
                        <?php $__currentLoopData = $productCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a href="<?php echo e(route('superadmin.viewproduct.viewProduct', $category->id)); ?>?search=<?php echo e(request('search')); ?>"
                            class="btn btn-sm <?php echo e($categoryId == $category->id ? 'btn-primary' : 'btn-outline-primary'); ?>">
                                <?php echo e($category->name); ?>

                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>
                                    <label class="checkboxs">
                                        <input type="checkbox" id="select-all">
                                        <span class="checkmarks"></span>
                                    </label>
                                </th>
                                <th>#</th>
                                <th>Name</th>
                                <th>Product Code</th>
                                <th>Category</th>
                                <th>Purchase Unit</th>
                                <th>Purchase Price</th>
                                <th>Unit</th>
                                <!-- <th>Invoice No</th> -->
                                <th>Remark</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td>
                                        <label class="checkboxs">
                                            <input type="checkbox">
                                            <span class="checkmarks"></span>
                                        </label>
                                    </td>
                                    <td><?php echo e($products->firstItem() + $index); ?></td>
                                    <td><?php echo e($product->product_name); ?></td>
                                    <td><?php echo e($product->product_code); ?></td>
                                    <td><?php echo e($product->category->name ?? 'N/A'); ?></td>
                                    <td><?php echo e($product->purchase_unit); ?></td>
                                    <td><?php echo e(number_format($product->purchase_price, 2)); ?></td>
                                    <td><?php echo e($product->unit); ?></td>
                                    <!-- <td><?php echo e($product->invoice_no); ?></td> -->
                                    <td><?php echo e($product->remark); ?></td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $product)): ?>
                                                <a href="javascript:void(0);" 
                                                class="btn btn-sm  edit-product-btn"
                                                data-id="<?php echo e($product->id); ?>"
                                                data-name="<?php echo e($product->product_name); ?>"
                                                data-code="<?php echo e($product->product_code); ?>"
                                                data-category="<?php echo e($product->product_category_id); ?>"
                                                data-purchase-unit="<?php echo e($product->purchase_unit); ?>"
                                                data-unit="<?php echo e($product->unit); ?>"
                                                data-price="<?php echo e($product->purchase_price); ?>"
                                                data-remarks="<?php echo e($product->remark); ?>"> 
                                                    <i data-feather="edit"></i>
                                                </a> 
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $product)): ?>
                                                <a href="javascript:void(0);" 
                                                class="btn btn-sm btn-outline-danger delete-product-btn"
                                                data-id="<?php echo e($product->id); ?>"
                                                data-bs-toggle="modal"
                                                data-bs-target="#delete">
                                                    <i data-feather="trash-2"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="11" class="text-center text-muted">
                                        No products found.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-end mt-3">
                    <?php echo e($products->appends(['search' => request('search')])->links('pagination::bootstrap-5')); ?>

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
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
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
                                <?php $__currentLoopData = $productCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($category->id); ?>">
                                        <?php echo e($category->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
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

            document.getElementById("editProductForm").action = "<?php echo e(url('superadmin/products')); ?>/" + this.dataset.id;

            new bootstrap.Modal(document.getElementById('edit-product')).show();
        });
    });

    // Delete functionality
    document.querySelectorAll(".delete-product-btn").forEach(function(button) {
        button.addEventListener("click", function() {
            let productId = this.dataset.id;
            document.getElementById("deleteProductForm").action = "<?php echo e(url('superadmin/products')); ?>/" + productId;
        });
    });
});
</script>

<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Mamp\htdocs\GenLab\resources\views/superadmin/viewproduct/viewProduct.blade.php ENDPATH**/ ?>