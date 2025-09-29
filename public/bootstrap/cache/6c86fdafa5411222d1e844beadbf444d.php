
<?php $__env->startSection('title','Edit DOCX: '.$reportFormat->format_name); ?>
<?php $__env->startSection('content'); ?>
<div class="content">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <h5 class="mb-0">Editing (LibreOffice Online): <?php echo e($reportFormat->format_name); ?></h5>
    <a href="<?php echo e(route('superadmin.reporting.generate')); ?>" class="btn btn-sm btn-secondary">Back</a>
  </div>
  <div class="border rounded" style="height: calc(100vh - 140px);">
    <iframe name="collaboraEditor" style="width:100%;height:100%;border:0;" allowfullscreen></iframe>
    <form id="collabora-launch" method="post" target="collaboraEditor" action="<?php echo e($serverUrl); ?>/loleaflet/dist/loleaflet.html">
      <input type="hidden" name="WOPISrc" value="<?php echo e($wopiSrc); ?>">
      <input type="hidden" name="access_token" value="<?php echo e($token); ?>">
      <input type="hidden" name="access_token_ttl" value="<?php echo e($ttl); ?>">
    </form>
    <script>
      // Submit after iframe is ready
      window.addEventListener('DOMContentLoaded', function(){
        try { document.getElementById('collabora-launch').submit(); } catch(e) { console.error(e); }
      });
    </script>
  </div>
  <p class="text-muted mt-2 small">Changes are saved back automatically when you click Save inside the editor toolbar.</p>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Mamp\htdocs\GenLab\resources\views/superadmin/reporting/report-formats/collabora-edit.blade.php ENDPATH**/ ?>