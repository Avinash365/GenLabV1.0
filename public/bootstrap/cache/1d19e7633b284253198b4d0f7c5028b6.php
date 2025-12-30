

<?php $__env->startSection('content'); ?>
<div class="container">
    <h3>Add Vehicle</h3>

    <form action="<?php echo e(route('superadmin.vehicles.store')); ?>" method="post" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>

        <div class="mb-3">
            <label class="form-label">Vehicle Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">RC Copy</label>
            <input type="file" name="rc_copy" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Insurance Details</label>
            <input type="file" name="insurance" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">PUC</label>
            <input type="file" name="puc" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Engine Number</label>
            <input type="text" name="engine_number" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Handed Over Person</label>
            <input type="text" name="handed_over_person" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">RC Expiry Date</label>
            <input type="date" name="rc_expiry_date" class="form-control">
        </div>

        <button class="btn btn-success">Save</button>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Mamp\htdocs\GenLabV2.0\resources\views/superadmin/vehicles/create.blade.php ENDPATH**/ ?>