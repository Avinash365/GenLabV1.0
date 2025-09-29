<div id="placeholder" style="width: 100%; height: 800px;"></div>

<script src="{{ $documentServerUrl }}/web-apps/apps/api/documents/api.js"></script>

<script>
    // decode the config as JS object and assign the JWT
    const config = {!! $config !!};
    config.token = @json($jwtToken);

    // Initialize ONLYOFFICE editor
    const docEditor = new DocsAPI.DocEditor("placeholder", config);
</script>
