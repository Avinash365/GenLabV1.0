<?php $__env->startSection('content'); ?>
<div class="container mt-4">

    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Generate A Report</h4>
        <button class="btn btn-success" type="button" data-bs-toggle="offcanvas" data-bs-target="#reportList" aria-controls="reportList">
            <i class="fa fa-plus"></i> Select A Report Format
        </button>
    </div>

    
    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?> 

    <?php if(session('error')): ?>
        <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

   <form id="editorForm" method="POST" action="<?php echo e(route('generateReportPDF.generatePdf')); ?>">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="editing_report_id" value="<?php echo e(old('editing_report_id', $assignedReport->id ?? '')); ?>">
    <input type="hidden" name="booking_item_id" value="<?php echo e($item->id); ?>">
    <input type="hidden" name="booking_id" value="<?php echo e($booking->id ?? ''); ?>">

    
    <div class="row mb-3">
        <div class="col-md-6">
            <label for="report_no" class="form-label">REPORT NO:</label>
            <input type="text" name="report_no" id="report_no" class="form-control" 
                   value="<?php echo e(old('report_no', $item->job_order_no)); ?>" required>
        </div>
        <div class="col-md-6">
            <label for="ulr_no" class="form-label">ULR No:</label>
            <input type="text" name="ulr_no" id="ulr_no" class="form-control" 
                   value="<?php echo e(old('ulr_no', $pivotRecord->ult_r_no ?? '')); ?>" required>
        </div>
    </div>

    
    <div class="row mb-3">
        <div class="col-md-6">
            <label for="issued_to" class="form-label">Issued To:</label>
            <textarea name="issued_to" id="issued_to" class="form-control" rows="3" required><?php echo e(old('issued_to', $booking->report_issue_to ?? '')); ?></textarea>
        </div>
        <div class="col-md-3">
            <label for="date_of_receipt" class="form-label">Date of Receipt:</label>
            <input type="date" name="date_of_receipt" id="date_of_receipt" class="form-control" 
                value="<?php echo e(old('date_of_receipt', isset($pivotRecord) && $pivotRecord->date_of_receipt ? \Carbon\Carbon::parse($pivotRecord->date_of_receipt)->format('Y-m-d') : '')); ?>" 
                required>
        </div>
        <div class="col-md-3">
            <label for="another_date" class="form-label">Date of Start of Analysis:</label>
            <input type="date" name="date_of_start_analysis" id="another_date" class="form-control" 
                value="<?php echo e(old('date_of_start_analysis', isset($pivotRecord) && $pivotRecord->date_of_start_of_analysis ? \Carbon\Carbon::parse($pivotRecord->date_of_start_of_analysis)->format('Y-m-d') : '')); ?>" 
                required>
        </div>
    </div>

    
    <div class="row mb-3">
        <div class="col-md-6">
            <label for="letter_ref_no" class="form-label">Letter Ref. No.:</label>
            <input type="text" name="letter_ref_no" id="letter_ref_no" class="form-control" 
                   value="<?php echo e(old('letter_ref_no', $booking->reference_no ?? '')); ?>" required>
        </div>
        <div class="col-md-3">
            <label for="letter_ref_date" class="form-label">Letter Ref Date:</label>
            <input type="date" name="letter_ref_date" id="letter_ref_date" class="form-control" 
                   value="<?php echo e(old('letter_ref_date', $booking->letter_date ?? '')); ?>" required>
        </div>
        <div class="col-md-3">
            <label for="completion_date" class="form-label">Date of Completion of Analysis:</label>
            <input type="date" name="completion_date" id="completion_date" class="form-control" 
                value="<?php echo e(old('completion_date', isset($pivotRecord) && $pivotRecord->date_of_completion_of_analysis ? \Carbon\Carbon::parse($pivotRecord->date_of_completion_of_analysis)->format('Y-m-d') : '')); ?>" 
                required>
        </div>
    </div>

    
    <div class="row mb-3">
        <div class="col-md-6">
            <label for="sample_description" class="form-label">Sample Description:</label>
            <textarea name="sample_description" id="sample_description" class="form-control" rows="3" required><?php echo e(old('sample_description', $item->sample_description ?? '')); ?></textarea>
        </div>
        <div class="col-md-3">
            <label for="date_of_issue" class="form-label">Date of Issue:</label>
            <input type="date" name="date_of_issue" id="date_of_issue" class="form-control" 
                value="<?php echo e(old('date_of_issue', isset($pivotRecord) && $pivotRecord->issue_to_date ? \Carbon\Carbon::parse($pivotRecord->issue_to_date)->format('Y-m-d') : '')); ?>" required>
        </div>
        <div class="col-md-3">
            <label for="name_of_work" class="form-label">Name of Work:</label>
            <textarea name="name_of_work" id="name_of_work" class="form-control" rows="2" required><?php echo e(old('name_of_work', $booking->name_of_work ?? '')); ?></textarea>
        </div>
    </div>

    
    <textarea id="jodit-editor" name="content" class="form-control" style="height: 400px;">
        <?php echo e(old('content', 
            (isset($pivotRecord) && $pivotRecord->generated_report_path) 
                ? Storage::disk('public')->get($pivotRecord->generated_report_path) 
                : (isset($assignedReport) && $assignedReport->file_path ? Storage::disk('public')->get($assignedReport->file_path) : '') 
        )); ?>

    </textarea>

    
    <div class="d-flex justify-content-end gap-2 mt-3 mb-2"> 
        <button type="submit" class="btn btn-primary">Save</button> 
        <a href="<?php echo e(route('viewPdf', basename($pivotRecord->pdf_path))); ?>" target="_blank" class="btn btn-sm btn-info pt-2">
            View
        </a>
    </div>   
</form>

</div>


<div class="offcanvas offcanvas-end" tabindex="-1" id="reportList" aria-labelledby="reportListLabel">
    <div class="offcanvas-header">
        <h5 id="reportListLabel">Saved Reports</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">

        
        <div class="mb-3">
            <input type="text" id="searchReports" class="form-control" placeholder="Search report by name...">
        </div>

        <div id="reportListContainer">
            <?php $__empty_1 = true; $__currentLoopData = $reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="card mb-2 report-card <?php echo e((isset($assignedReport) && $assignedReport->id == $report->id) ? 'active-report' : ''); ?>">
                    <div class="card-body d-flex justify-content-between align-items-center gap-2">
                        <span class="report-title"><strong><?php echo e($report->report_no); ?></strong></span>

                        <div class="d-flex gap-1">
                            
                            <button type="button" class="btn btn-sm btn-outline-primary load-report" 
                                data-id="<?php echo e($report->id); ?>"
                                data-content="<?php echo e(Storage::disk('public')->get($report->file_path)); ?>"
                                data-name="<?php echo e($report->report_no); ?>"
                                data-description="<?php echo e($report->report_description); ?>"
                                title="Load Report">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p>No reports saved yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>


<link href="https://cdn.jsdelivr.net/npm/jodit@latest/es2021/jodit.fat.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/jodit@latest/es2021/jodit.fat.min.js"></script>

<style>
    .active-report {
        background-color: #d4edda; /* light green */
        border-color: #28a745; /* green border */
    }
</style>

<script> 

function ensureTableHeader(html) {
    // Create a temporary DOM element
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = html;

    // Find all tables
    tempDiv.querySelectorAll('table').forEach(table => {
        const firstRow = table.querySelector('tr');
        if (firstRow && !table.querySelector('thead')) {
            const thead = document.createElement('thead');
            thead.appendChild(firstRow.cloneNode(true)); // Move the first row to thead
            table.insertBefore(thead, table.firstChild);

            // Remove the original first row from tbody if exists
            firstRow.remove();
        }
    });

    return tempDiv.innerHTML;
}

// Initialize Jodit with table border override
const editor = Jodit.make('#jodit-editor', { 
    height: 400,
    iframe: true,
    iframeStyle: `
        table, th, td {
            border: 1px solid #000;
            border-collapse: collapse;
        }
    `
});

const editingIdInput = document.getElementById('editing_report_id');

// Load report into editor
document.querySelectorAll('.load-report').forEach(btn => {
    btn.addEventListener('click', function() {
        const content = this.dataset.content;
        const name = this.dataset.name;
        const description = this.dataset.description;
        const id = this.dataset.id;

        // ✅ Ensure first row goes under <thead>
        editor.value = ensureTableHeader(content);

        document.getElementById('report_no').value = name;
        document.getElementById('report_disc').value = description;
        editingIdInput.value = id;

        // Remove previous highlight
        document.querySelectorAll('.report-card').forEach(card => card.classList.remove('active-report'));

        // Highlight the currently loaded report
        this.closest('.report-card').classList.add('active-report');

        bootstrap.Offcanvas.getInstance(document.getElementById('reportList')).hide();
    });
});

// 🔍 Search filter
document.getElementById('searchReports').addEventListener('keyup', function() {
    const query = this.value.toLowerCase();
    document.querySelectorAll('.report-card').forEach(card => {
        const title = card.querySelector('.report-title').textContent.toLowerCase();
        card.style.display = title.includes(query) ? '' : 'none';
    });
});
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH A:\GenTech\htdocs\GenTech_bug\bankTransaction\GenLab\resources\views/Reportfrmt/generate.blade.php ENDPATH**/ ?>