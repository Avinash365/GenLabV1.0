@extends('layouts.app') <!-- or your layout file -->

@section('content')
<div class="container">
    <h3 class="mb-3">Dynamic Test Form</h3>

    <form id="dynamicForm">
        <div id="testsContainer"></div>

        <div class="d-flex gap-2 mt-3">
            <button type="button" id="addTestBtn" class="btn btn-success">+ Add Test (Menu)</button>
            <button type="submit" class="btn btn-primary">Submit</button>
        </div>
    </form>

    <h5 class="mt-4">Live JSON preview</h5>
    <pre id="jsonOutput" class="bg-light p-3" style="min-height:120px;"></pre>
</div>
@endsection


@push('scripts')
<script>
$(document).ready(function() {
    const testsContainer = $('#testsContainer');
    let testCount = 0;

    $('#addTestBtn').click(function() {
        testCount++;
        const card = $(`
            <div class="card mb-3" data-test-index="${testCount}">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2 w-75">
                        <strong>Menu ${testCount}:</strong>
                        <input type="text" class="form-control form-control-sm test-name" 
                               name="tests[${testCount}][name]" 
                               placeholder="Enter Test (Menu)" />
                    </div>
                    <div>
                        <button type="button" class="btn btn-sm btn-secondary add-param">+ Add Submenu</button>
                        <button type="button" class="btn btn-sm btn-danger remove-test">Remove Menu</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="parameters-container"></div>
                </div>
            </div>
        `);
        testsContainer.append(card);

        addSubmenuInput(card.find('.parameters-container'), testCount);
        card.find('.test-name').focus();
        updatePreview();
    });

    function addSubmenuInput(container, idx) {
        const paramRow = $(`
            <div class="input-group mb-2">
                <input type="text" class="form-control form-control-sm param-input" 
                       name="tests[${idx}][parameters][]" 
                       placeholder="Enter Submenu Item">
                <button type="button" class="btn btn-outline-danger remove-param">x</button>
            </div>
        `);
        container.append(paramRow);
        paramRow.find('input').focus();
    }

    testsContainer.on('click', '.add-param', function() {
        const card = $(this).closest('.card');
        const idx = card.data('test-index');
        addSubmenuInput(card.find('.parameters-container'), idx);
        updatePreview();
    });

    testsContainer.on('click', '.remove-param', function() {
        $(this).closest('.input-group').remove();
        updatePreview();
    });

    testsContainer.on('click', '.remove-test', function() {
        if (confirm('Remove this menu and its submenus?')) {
            $(this).closest('.card').remove();
            updatePreview();
        }
    });

    testsContainer.on('input', '.test-name, .param-input', updatePreview);

    function buildJSON() {
        const formData = $('#dynamicForm').serializeArray();
        const tests = {};
        formData.forEach(f => {
            const match = f.name.match(/^tests\[(\d+)\]\[(\w+)\](?:\[\])?$/);
            if (!match) return;
            const idx = match[1], field = match[2];
            if (!tests[idx]) tests[idx] = { name: '', parameters: [] };
            if (field === 'name') tests[idx].name = f.value;
            else if (field === 'parameters') tests[idx].parameters.push(f.value);
        });
        return Object.values(tests);
    }

    function updatePreview() {
        $('#jsonOutput').text(JSON.stringify(buildJSON(), null, 2));
    }

    $('#dynamicForm').submit(function(e) {
        e.preventDefault();
        console.log('Submitted JSON:', buildJSON());
        alert('Form submitted — check console.');
    });
});
</script>
@endpush


