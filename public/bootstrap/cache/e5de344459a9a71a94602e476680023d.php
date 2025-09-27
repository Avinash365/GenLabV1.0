<div id="placeholder" style="width: 100%; height: 800px;"></div>

<script src="<?php echo e($documentServerUrl); ?>/web-apps/apps/api/documents/api.js"></script>

<script>
    // decode the config as JS object and assign the JWT
    const config = <?php echo $config; ?>;
    config.token = <?php echo json_encode($jwtToken, 15, 512) ?>;

    // Initialize ONLYOFFICE editor
    const docEditor = new DocsAPI.DocEditor("placeholder", config);
</script>
<?php /**PATH A:\GenTech\htdocs\GenTech_bug\GenLab\resources\views/Reportfrmt/editor.blade.php ENDPATH**/ ?>