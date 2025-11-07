<?php $__env->startSection('title', 'Create New User'); ?>
<?php $__env->startSection('content'); ?>



<div class="row">
                        
                        <div class="col-xl-6">
                            <div class="card">
                                <div class="card-header justify-content-between">
                                    <div class="card-title">
                                        Add Unit
                                    </div>
                                    
                                </div>
                                <div class="card-body">
                                    <form>
                                        <div class="row mb-3">
                                            <label for="inputEmail1" class="col-sm-2 col-form-label">Unit Name*</label>
                                            <div class="col-sm-10">
                                                <div class="input-group">
                                                    
                                                    <input type="email" class="form-control" id="inputEmail1">
                                                    
                                                </div>
                                            </div>
                                        </div>
                                        
                                        
                                        <button type="submit" class="btn btn-primary">Save</button>
                                    </form>
                                </div>
                                
                            </div>
                        </div>
						<div class="col-xl-6">
    <div class="card">
        <div class="card-header justify-content-between d-flex">
            <div class="card-title mb-0">
                Unit List
            </div>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-hover table-sm">
                <thead class="table-light">
                    <tr>
                        <th>SL</th>
                        <th>Branch</th>
                        <th>Name</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Electronics</td>
                        <td>Electronics</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-primary" title="Edit">
                                ✏️
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" title="Delete">
                                🗑️
                            </button>
                        </td>
                    </tr>
                    
                </tbody>
            </table>
        </div>
    </div>  
</div>

                    </div>

                    <?php $__env->stopSection(); ?>
<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Mamp\htdocs\GenLabV1.0\resources\views/superadmin/unit/Unit.blade.php ENDPATH**/ ?>