@extends('superadmin.layouts.app')

@section('content') 

 <style>
        table, th {
          border: 1px solid black;
          border-collapse: collapse;
          text-align: left;
          word-wrap: break-word;
          font-weight: bold;
          overflow: hidden;
          font-size: 14.5px;
          font-family: 'Times New Roman', Times, serif;
          padding: 2px;
        }
        p {
            text-align: right;
            font-weight: bold;
        }
        td {
          border: 1px solid black;
          border-collapse: collapse;
          text-align: left;
          word-wrap: break-word;
          font-weight: normal;
          overflow: hidden;
          font-size: 14.5px;
          font-family: 'Times New Roman', Times, serif;
          padding: 2px 4px;
        } 
        .bg_green{
            background-color: lightgreen;
        } 
       
</style>


<div class="container mt-3">
    <!-- Top Menu with Icon -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Reports Dashboard</h4>
        <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#reportForm" aria-controls="reportForm">
            <i class="fa-solid fa-folder-open fa-lg"></i> Create Report
        </button>
    </div>

    <p>Welcome to the Reports page. Click the icon to create a new report.</p>

    <!-- Real-time Table Preview -->
    <h5 class="mt-4">Table Preview</h5>
    <div class="">
        <table class="" id="liveTable" style="width: 80%;">
            <thead >
                <tr>
                    <th style="padding: 2px; text-align: center; width:8%;">S.No.</th>
                    <th style="padding: 2px; text-align: center; width:30%; ">Tests</th>
                    <th style="padding: 2px 6px; text-align: center; width:20%; ">Test Methods</th>
                    <th class="bg_green" contenteditable="true" style="padding: 2px 9px; text-align: center; width:18%;" >Requirements as per <br>
                            IS : 2185(P-3)-1984 With Amendment No. 1 <br>
                            Grade - 1
                    </th>
                    <th style="padding: 2px; text-align: center; width:12%;">Results</th>
                    <th style="padding: 2px 5px; text-align: center; width:12%;">Conformity</th>
                </tr>
            </thead>
            <tbody>
                <!-- Live rows inserted here -->
            </tbody>
        </table>
    </div>
</div>

<!-- Offcanvas Form -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="reportForm" aria-labelledby="reportFormLabel"  data-bs-scroll="true">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="reportFormLabel">Create Your Report</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="dynamicForm">
            <div id="testsContainer"></div>

            <div class="d-flex gap-2 mt-3">
                <button type="button" id="addTestBtn" class="btn btn-success rounded-circle shadow" title="Add Test">
                    <i class="fa-solid fa-plus"></i>
                </button>
                <button type="submit" class="btn btn-primary rounded-circle shadow" title="Save Report">
                    <i class="fa-solid fa-file-circle-check"></i>
                </button>
            </div>
        </form>

        <h5 class="mt-4">Live JSON Preview</h5>
        <pre id="jsonOutput" class="bg-light p-3" style="min-height:150px;"></pre>
    </div>
</div>
@endsection 

@push('scripts')
<script>
$(document).ready(function() {
    const testsContainer = $('#testsContainer');
    let testCount = 0;

    // Add new test
    $('#addTestBtn').click(function() {
        testCount++;
        const card = $(`
            <div class="card mb-3" data-test-index="${testCount}">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="align-items-center gap-3 w-75">
                        <strong>${testCount}: Test</strong>
                        <input type="text" class="form-control form-control-sm test-name" 
                               name="tests[${testCount}][name]" placeholder="Enter Test Name" />
                    </div>
                    <div class="btn-group ms-3">
                        <button type="button" class="btn btn-sm btn-secondary add-param"><i class="fa-solid fa-plus-circle"></i></button>
                        <button type="button" class="btn btn-sm btn-danger remove-test"><i class="fa-solid fa-trash"></i></button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="parameters-container"></div>
                </div>
            </div>
        `);
        testsContainer.append(card);
        updatePreview();
        card.find('.test-name').focus();
    });

    // Add a parameter row
    function addParameterRow(container, idx) {
        const paramRow = $(`
            <div class="input-group mb-2">
                <input type="text" class="form-control form-control-sm param-name" 
                       name="tests[${idx}][parameters][][name]" placeholder="Parameter Name" />
                <input type="text" class="form-control form-control-sm param-method" 
                       name="tests[${idx}][parameters][][method]" placeholder="Method" />
                <input type="text" class="form-control form-control-sm param-requirement" 
                       name="tests[${idx}][parameters][][requirement]" placeholder="Requirement" />
                <button type="button" class="btn btn-outline-danger remove-param">x</button>
            </div>
        `);
        container.append(paramRow);
        paramRow.find('input').focus();
    }

    // Dynamic events
    testsContainer.on('click', '.add-param', function() {
        const card = $(this).closest('.card');
        const idx = card.data('test-index');
        addParameterRow(card.find('.parameters-container'), idx);
        updatePreview();
    });

    testsContainer.on('click', '.remove-param', function() {
        $(this).closest('.input-group').remove();
        updatePreview();
    });

    testsContainer.on('click', '.remove-test', function() {
        $(this).closest('.card').remove();
        updatePreview();
    });

    testsContainer.on('input', '.test-name, .param-name, .param-method, .param-requirement', updatePreview);

    // Build JSON
    function buildJSON() {
        const tests = {};
        $('#dynamicForm').find('.card').each(function() {
            const $card = $(this);
            const idx = $card.data('test-index');
            const testName = $card.find('.test-name').val() || '';
            tests[idx] = { name: testName, parameters: [] };

            $card.find('.parameters-container .input-group').each(function() {
                const paramName = $(this).find('.param-name').val() || '';
                const method = $(this).find('.param-method').val() || '';
                const requirement = $(this).find('.param-requirement').val() || '';
                tests[idx].parameters.push({ name: paramName, method: method, requirement: requirement });
            });
        });
        return Object.values(tests);
    }

    // Update live JSON and Table preview

    function updatePreview() {
    const jsonData = buildJSON();
    $('#jsonOutput').text(JSON.stringify(jsonData, null, 2));

    const tbody = $('#liveTable tbody');
    tbody.empty();

    jsonData.forEach((test, tIdx) => {
        // Test row
        tbody.append(`
            <tr>
                <td>${tIdx + 1}</td>
                <td colspan="5"><strong>${test.name}</strong></td>
            </tr>
        `);

        test.parameters.forEach((param, i) => {
            let subIndex = String.fromCharCode(97 + i);

            tbody.append(`
                <tr>
                    <td>${tIdx + 1}.${subIndex}</td>
                    <td>${param.name}</td>
                    <td class="method-cell" data-method="${param.method}" style="padding:2px 6px; text-align:center;">${param.method}</td>
                    <td class="req-cell" data-req="${param.requirement}" style="padding:2px 6px; text-align:center;">${param.requirement}</td>
                    <td contenteditable="true" class="bg_green"></td>
                    <td contenteditable="true" class="bg_green"></td>
                </tr>
            `);
        });
    });

    // --- Independent merging logic ---
    mergeCells(".method-cell", "data-method");
    mergeCells(".req-cell", "data-req");
}

function mergeCells(selector, attr) {
    let cells = $(selector);
    let prev = null, rowspan = 1;

    cells.each(function () {
        if (prev && $(this).attr(attr) === $(prev).attr(attr)) {
            rowspan++;
            $(this).remove(); // remove duplicate cell
            $(prev).attr("rowspan", rowspan);
        } else {
            prev = this;
            rowspan = 1;
        }
    });
}


    // Form submit
    $('#dynamicForm').submit(function(e) {
        e.preventDefault();
        console.log('Submitted JSON:', buildJSON());
        alert('Form submitted — check console!');
    });  
});
</script> 

@endpush 


