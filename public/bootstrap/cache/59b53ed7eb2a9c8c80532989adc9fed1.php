<div class="p-2">
    <h5 class="mb-3">Editing: <?php echo e($reportFormat->format_name); ?> <span class="badge bg-secondary">v<?php echo e($reportFormat->version); ?></span></h5>
    <form id="report-format-content-form" data-id="<?php echo e($reportFormat->id); ?>">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <textarea id="rf-body-html" name="body_html"><?php echo $reportFormat->body_html; ?></textarea>
        <div class="mt-3 d-flex gap-2">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save</button>
            <button type="button" class="btn btn-outline-danger" id="preview-pdf-btn">Preview PDF</button>
        </div>
    </form>
</div>
<?php /**PATH C:\Mamp\htdocs\GenLab\resources\views/superadmin/reporting/report-formats/edit-content.blade.php ENDPATH**/ ?>