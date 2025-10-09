<?php $__env->startSection('content'); ?>
<div class="page-header mt-3" style="margin-left: 10px; margin-right: 10px;">
    <h4 class="mb-2">Upload Report Format</h4>
    <button class="btn btn-primary" id="btnNewFormat">New Upload</button>
</div>

<?php if(session('success')): ?>
    <div class="alert alert-success mt-3"><?php echo e(session('success')); ?></div>
<?php endif; ?>
<?php if($errors->any()): ?>
    <div class="alert alert-danger mt-3">
        <ul class="mb-0">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card mt-3" style="margin-left: 10px; margin-right: 10px;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>U.N.</th>
                        <th>Format Name</th>
                        <th>IS Code</th>
                        <th>Sample</th>
                        <th>File Name</th>
                        <th>Uploaded At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $formats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $fmt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($formats->firstItem() + $i); ?></td>
                            <td><?php echo e($fmt->format_name); ?></td>
                            <td><?php echo e($fmt->is_code); ?></td>
                            <td><?php echo e($fmt->sample); ?></td>
                            <td><?php echo e($fmt->original_file_name); ?></td>
                            <td><?php echo e($fmt->created_at->format('d M Y')); ?></td>
                            <td>
                                <a href="<?php echo e(route('superadmin.reporting.report-formats.show', $fmt)); ?>" target="_blank" class="btn btn-sm btn-outline-primary">View</a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">No report formats uploaded yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if($formats->hasPages()): ?>
        <div class="card-footer"><?php echo e($formats->links()); ?></div>
    <?php endif; ?>
</div>

<!-- Modal -->
<div class="modal fade" id="newFormatModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">New Format</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="<?php echo e(route('superadmin.reporting.report-formats.store')); ?>" method="POST" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <div class="modal-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Format Name <span class="text-danger">*</span></label>
                    <input type="text" name="format_name" class="form-control" required value="<?php echo e(old('format_name')); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">IS Code</label>
                    <input type="text" name="is_code" class="form-control" value="<?php echo e(old('is_code')); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Sample</label>
                    <input type="text" name="sample" class="form-control" value="<?php echo e(old('sample')); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">File <span class="text-danger">*</span></label>
                    <input type="file" name="file" class="form-control" required>
                </div>
            </div>
        </div>
        <div class="modal-footer" style="gap: 10px;">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Upload</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function(){
        const btn = document.getElementById('btnNewFormat');
        btn.addEventListener('click', function(){
            const modal = new bootstrap.Modal(document.getElementById('newFormatModal'));
            modal.show();
        });
    });
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Mamp\htdocs\GenLabV1.0\resources\views/superadmin/reporting/report-formats/index.blade.php ENDPATH**/ ?>