<div id="placeholder" style="width: 100%; height: 800px;"></div>

<!-- ONLYOFFICE DocumentServer API -->
<script src="{{ $documentServerUrl }}/web-apps/apps/api/documents/api.js"></script>

<script>
    // Pass editor config directly (no JWT required)
    const config = {!! $config !!};

    // Initialize the ONLYOFFICE editor
    const docEditor = new DocsAPI.DocEditor("placeholder", config);
</script>
