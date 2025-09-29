<div id="placeholder" style="width: 100%; height: 800px;"></div>

<!-- ONLYOFFICE DocumentServer API -->
<script src="<?php echo e($documentServerUrl); ?>/web-apps/apps/api/documents/api.js"></script>

<script>
    // Pass editor config directly (no JWT required)
    const config = <?php echo $config; ?>;

    // Initialize the ONLYOFFICE editor
    const docEditor = new DocsAPI.DocEditor("placeholder", config);
</script>
<?php /**PATH A:\GenTech\htdocs\GenTech_bug\bankTransaction\GenLab\resources\views/Reportfrmt/editor.blade.php ENDPATH**/ ?>