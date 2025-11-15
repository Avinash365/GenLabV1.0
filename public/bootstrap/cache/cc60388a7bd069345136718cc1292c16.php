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

    
    <form id="editorForm" method="POST">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="editing_report_id" id="editing_report_id" value="<?php echo e(old('editing_report_id', $assignedReport->id ?? '')); ?>">
        <input type="hidden" name="booking_item_id" value="<?php echo e($item->id); ?>">
        <input type="hidden" name="booking_id" value="<?php echo e($booking->id ?? ''); ?>">
        <input type="hidden" name="m_s" value="<?php echo e($booking->m_s ?? ''); ?>">

        
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
            <div class="col-md-3">
                <label for="issued_to" class="form-label">Issued To:</label>
                <textarea name="issued_to" id="issued_to" class="form-control" rows="2" required><?php echo e(old('issued_to', $booking->report_issue_to ?? '')); ?></textarea>
            </div> 
            <div class="col-md-3">
                <label for="name_of_work" class="form-label">Name of Work:</label>
                <textarea name="name_of_work" id="name_of_work" class="form-control" rows="2" required><?php echo e(old('name_of_work', $booking->name_of_work ?? '')); ?></textarea>
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
            <div class="col-md-9">
                <label for="sample_description" class="form-label">Sample Description:</label>
                <textarea name="sample_description" id="sample_description" class="form-control" rows="1" required><?php echo e(old('sample_description', $item->sample_description ?? '')); ?></textarea>
            </div>
            <div class="col-md-3">
                <label for="date_of_issue" class="form-label">Date of Issue:</label>
                <input type="date" name="date_of_issue" id="date_of_issue" class="form-control" 
                       value="<?php echo e(old('date_of_issue', isset($pivotRecord) && $pivotRecord->issue_to_date ? \Carbon\Carbon::parse($pivotRecord->issue_to_date)->format('Y-m-d') : '')); ?>" required>
            </div>
        </div>

        
        <div class="row">
            <div class="col-md-12">
                <textarea id="jodit-editor" name="content" class="form-control" style="height: 400px;">
                    <?php echo e(old('content', 
                        (isset($pivotRecord) && $pivotRecord->generated_report_path) 
                            ? Storage::disk('public')->get($pivotRecord->generated_report_path) 
                            : (isset($assignedReport) && $assignedReport->file_path ? Storage::disk('public')->get($assignedReport->file_path) : '') 
                    )); ?>

                </textarea>  
            </div>
        </div>

        
        
            <div class="d-flex justify-content-end align-items-center gap-3 mt-3 mb-2"> 
                
                
                <div class="form-check me-auto">
                    <input class="form-check-input" type="checkbox" name="include_header" id="include_header" value="1" checked>
                    <label class="form-check-label" for="include_header">
                        Include Header in Report
                    </label>
                </div> 


                
                <?php if(isset($type) && $type === '28day'): ?>
                    <button type="submit" class="btn btn-primary" formaction="<?php echo e(route('generateReportPDF.generatePdf28Days')); ?>">
                        Save (28Days)
                    </button>
                <?php else: ?>
                    <button type="submit" class="btn btn-primary" formaction="<?php echo e(route('generateReportPDF.generatePdf')); ?>">
                        Save
                    </button>
                <?php endif; ?> 
                <button type="submit" class="btn btn-primary" formaction="<?php echo e(route('download.qr')); ?>">QR</button> 

                <button type="submit" class="btn btn-primary" formaction="<?php echo e(route('generateReportPDF.generateReportWord')); ?>">Word Doc</button> 

                <?php if(isset($pivotRecord) && !empty($pivotRecord->pdf_path)): ?>
                    <a href="<?php echo e(route('viewPdf', basename($pivotRecord->pdf_path))); ?>" 
                    target="_blank" 
                    class="btn btn-sm btn-info pt-2">
                        View
                    </a>
                <?php endif; ?>
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


<div id="pdfIcon" 
     style="position: fixed; bottom: 100px; right: 30px; background-color: #007bff; color: #fff; 
            width: 55px; height: 55px; border-radius: 50%; display: flex; align-items: center; 
            justify-content: center; cursor: grab; z-index: 1050; box-shadow: 0 4px 8px rgba(0,0,0,0.2);">
    <i class="fa-solid fa-file-pdf fa-lg"></i>
</div>


<div id="floatingPdfView" 
     style="display:none; position: fixed; top: 80px; left: 100px; width: 600px; height: 650px; 
            background: #fff; border: 2px solid #007bff; border-radius: 8px; z-index: 1051; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.3); overflow: hidden;">
    <div id="pdfHeader" 
         style="background-color: #007bff; color: #fff; padding: 8px 12px; 
                cursor: grab; display: flex; justify-content: space-between; align-items: center;">
        <span><i class="fa-solid fa-eye"></i> Live PDF Preview</span>
        <button id="closePdf" class="btn btn-sm btn-light text-dark">×</button>
    </div>
    <iframe id="floatingPdfFrame" src="" style="width:100%; height:590px; border: none;"></iframe>
</div>


<link href="https://cdn.jsdelivr.net/npm/jodit@latest/es2021/jodit.fat.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/jodit@latest/es2021/jodit.fat.min.js"></script>

<style>
.active-report { background-color: #d4edda; border-color: #28a745; }
#pdfIcon:hover { background-color: #0056b3; transform: scale(1.1); transition: all 0.2s ease-in-out; } 
#floatingPdfView {
    resize: none;
    user-select: none;
}
#resizeHandle:hover {
    background-color: #0056b3;
    transition: 0.2s;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {

    // Initialize Jodit Editor
    const editor = Jodit.make('#jodit-editor', { height: 400 });
    let typingTimer;
    const delay = 500; // Delay in ms before generating live preview

    // --- Live PDF Preview Function ---
    function updatePdfPreview() {
        const content = editor.value;
        if (!content.trim()) return;

        // Collect header form data
        const headerData = {
            content: content,
            booking_item_id: document.querySelector('[name="booking_item_id"]').value,
            report_no: document.getElementById('report_no').value,
            ulr_no: document.getElementById('ulr_no').value,
            issued_to: document.getElementById('issued_to').value,
            date_of_receipt: document.getElementById('date_of_receipt').value,
            date_of_start_analysis: document.getElementById('another_date').value,
            letter_ref_no: document.getElementById('letter_ref_no').value,
            letter_ref_date: document.getElementById('letter_ref_date').value,
            completion_date: document.getElementById('completion_date').value,
            sample_description: document.getElementById('sample_description').value,
            date_of_issue: document.getElementById('date_of_issue').value,
            name_of_work: document.getElementById('name_of_work').value,
            m_s: '<?php echo e($booking->m_s ?? ""); ?>',
            include_header: document.getElementById('include_header').checked ? 1 : 0, 
        };

        fetch("<?php echo e(route('reports.livePreview')); ?>", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "<?php echo e(csrf_token()); ?>"
            },
            body: JSON.stringify(headerData)
        })
        .then(res => res.json())
        .then(data => {
            if (data.pdf_url) {
                document.getElementById('floatingPdfFrame').src = data.pdf_url + '#toolbar=0';
            }
        })
        .catch(console.error);
    }

    // --- Auto update preview after typing stops ---
    function scheduleUpdate() {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(updatePdfPreview, delay);
    }

    // Editor changes
    editor.events.on('change', scheduleUpdate);

    // Any input or textarea changes
    const formFields = document.querySelectorAll('#editorForm input, #editorForm textarea');
    formFields.forEach(field => field.addEventListener('input', scheduleUpdate));

    // Initial preview
    updatePdfPreview();

    // --- Floating PDF modal and icon ---
    const pdfIcon = document.getElementById('pdfIcon');
    const floatingPdfView = document.getElementById('floatingPdfView');
    const closePdf = document.getElementById('closePdf');
    const pdfHeader = document.getElementById('pdfHeader');

    // Show preview
    pdfIcon.addEventListener('click', () => {
        floatingPdfView.style.display = 'block';
        updatePdfPreview();
    });

    // Close preview
    closePdf.addEventListener('click', () => {
        floatingPdfView.style.display = 'none';
    });

    // Make modal draggable
    let isDragging = false, offsetX, offsetY;
    pdfHeader.addEventListener('mousedown', e => {
        isDragging = true;
        offsetX = e.clientX - floatingPdfView.offsetLeft;
        offsetY = e.clientY - floatingPdfView.offsetTop;
        pdfHeader.style.cursor = 'grabbing';
    });
    document.addEventListener('mousemove', e => {
        if (isDragging) {
            floatingPdfView.style.left = `${e.clientX - offsetX}px`;
            floatingPdfView.style.top = `${e.clientY - offsetY}px`;
        }
    });
    document.addEventListener('mouseup', () => {
        isDragging = false;
        pdfHeader.style.cursor = 'grab';
    });

    // Make floating PDF icon movable
    let isIconDragging = false, iconOffsetX, iconOffsetY;
    pdfIcon.addEventListener('mousedown', e => {
        isIconDragging = true;
        iconOffsetX = e.clientX - pdfIcon.offsetLeft;
        iconOffsetY = e.clientY - pdfIcon.offsetTop;
        pdfIcon.style.cursor = 'grabbing';
    });
    document.addEventListener('mousemove', e => {
        if (isIconDragging) {
            pdfIcon.style.left = `${e.clientX - iconOffsetX}px`;
            pdfIcon.style.top = `${e.clientY - iconOffsetY}px`;
            pdfIcon.style.right = 'auto';
            pdfIcon.style.bottom = 'auto';
        }
    });
    document.addEventListener('mouseup', () => {
        isIconDragging = false;
        pdfIcon.style.cursor = 'grab';
    });

    // Make floating PDF modal resizable
    const resizeHandle = document.createElement('div');
    resizeHandle.id = 'resizeHandle';
    resizeHandle.style.cssText = `
        width: 15px; height: 15px; background: #007bff;
        position: absolute; right: 0; bottom: 0; cursor: se-resize;
    `;
    floatingPdfView.appendChild(resizeHandle);

    let isResizing = false, startX, startY, startWidth, startHeight;
    resizeHandle.addEventListener('mousedown', e => {
        isResizing = true;
        startX = e.clientX;
        startY = e.clientY;
        startWidth = floatingPdfView.offsetWidth;
        startHeight = floatingPdfView.offsetHeight;
        e.preventDefault();
    });
    document.addEventListener('mousemove', e => {
        if (isResizing) {
            floatingPdfView.style.width = `${startWidth + e.clientX - startX}px`;
            floatingPdfView.style.height = `${startHeight + e.clientY - startY}px`;
        }
    });
    document.addEventListener('mouseup', () => {
        isResizing = false;
    });

});
</script>


<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH A:\GenTech\htdocs\GenlabV1.0\GenLabV1.0\resources\views/Reportfrmt/generate.blade.php ENDPATH**/ ?>