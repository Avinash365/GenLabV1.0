<?php $__env->startSection('content'); ?>

<?php if($errors->any()): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php endif; ?>


<?php 
     $user = Auth::guard('admin')->user() ?? Auth::guard('web')->user(); 
?>


    <div class="page-header">
        <div class="add-item d-flex ms-4 mt-4">
            <div class="page-title">
                <h4>Meter Reading</h4>
                <h6>Upload and view meter readings</h6>
            </div>
        </div>
        
        <ul class="table-top-head list-inline d-flex gap-3" >
            <?php if($user && ($user instanceof Admin || $user->hasPermission('meter_reading.create'))): ?>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadReadingModal">Upload Reading</button>
            <?php endif; ?>
            
             <?php if($user && ($user instanceof Admin || $user->hasPermission('meter_reading.view'))): ?>
                <li class="list-inline-item">
                    <?php $q = http_build_query(request()->only(['search','month','year','marketing_person'])); ?>
                    <a href="<?php echo e(route('superadmin.meter-reading.export.pdf')); ?><?php echo e($q ? ('?'.$q) : ''); ?>" class="no-loader" data-bs-toggle="tooltip" title="PDF"><div class="fa fa-file-pdf"></div></a>
                </li>
                <li class="list-inline-item">
                    <?php $q = http_build_query(request()->only(['search','month','year','marketing_person'])); ?>
                    <a href="<?php echo e(route('superadmin.meter-reading.export.excel')); ?><?php echo e($q ? ('?'.$q) : ''); ?>" class="no-loader" data-bs-toggle="tooltip" title="Excel">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="24" fill="green" viewBox="0 0 24 24">
                            <path d="M19 2H8c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-8.5 14-2-3 2-3H9l-1.5 2.25L6 10H4l2.5 3L4 16h2l1.5-2.25L9 16h1.5zM19 20H8V4h11v16z"/>
                        </svg>
                    </a>
                </li>
            <?php endif; ?>
            <li style="margin-right:22px;"><a data-bs-toggle="tooltip" title="Refresh"><i class="ti ti-refresh" ></i></a></li>
        </ul>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if(session('error')): ?>
        <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
    <?php endif; ?>

    <?php
        $isAdmin = false;
        if (auth()->check()) {
            $userRole = auth()->user()->role;
            if (is_object($userRole) && isset($userRole->role_name)) {
                $roleName = $userRole->role_name;
            } elseif (is_string($userRole)) {
                $roleName = $userRole;
            } else {
                $roleName = '';
            }
            $isAdmin = $roleName ? stripos($roleName, 'admin') !== false : false;
        }
    ?>
 
    <!-- Upload Modal -->
    <div class="modal fade" id="uploadReadingModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-md modal-dialog-scrollable">
        <div class="modal-content">
                    <div class="modal-header align-items-center">
                        <h5 class="modal-title mb-0"><?php if(!empty($hasOpen)): ?> Upload Ending Reading <?php else: ?> Upload Meter Reading <?php endif; ?></h5>
                        <?php if(!empty($hasOpen)): ?>
                                <span class="badge bg-warning ms-3">ENDING</span>
                        <?php else: ?>
                                <span class="badge bg-success ms-3">STARTING</span>
                        <?php endif; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
          <div class="modal-body">
            <form action="<?php echo e(route('superadmin.meter-reading.upload')); ?>" method="post" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <div class="mb-3">
                    <label class="form-label">Current Reading</label>
                    <input type="number" step="any" name="current_reading" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Image (optional)</label>
                    <input type="file" name="image" accept="image/*" class="form-control">
                </div>

                <?php if(empty($hasOpen)): ?>
                    <!-- <div class="alert alert-info small">This will create a <strong>starting</strong> reading. You may provide an optional description and image.</div> -->
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                <?php else: ?>
                    <!-- <div class="alert alert-warning small">This will <strong>close</strong> the currently open reading. Description is optional and will be saved as the ending note.</div> -->
                <?php endif; ?>

                <div class="text-end">
                    <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Close</button>
                    <?php if(!empty($hasOpen)): ?>
                        <button class="btn btn-primary">Close Trip</button>
                    <?php else: ?>
                        <button class="btn btn-success">Start Trip</button>
                    <?php endif; ?>
                </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <div class="card mt-3">
        <div class="card-body p-0">

         <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">

            <!-- Search Form -->
            <div class="search-set">
                 <form method="GET" action="<?php echo e(route('superadmin.meter-reading.index')); ?>" class="d-flex input-group me-3" style="max-width:600px; width:100%">
                        <input type="text" name="search" value="<?php echo e(request('search')); ?>" class="form-control" placeholder="Search...">
                        <button class="btn btn-outline-secondary" type="submit">🔍</button>
                </form>
            </div>

            <!-- Month & Year Filter Form -->
            <div class="search-set">
                <form id="filterForm" method="GET" action="<?php echo e(route('superadmin.meter-reading.index')); ?>" class="d-flex input-group">

                    <?php if($isAdmin ?? false): ?>
                            <select name="marketing_person" class="form-control me-2">
                                <option value="">All Marketing Persons</option>
                                <?php $__currentLoopData = $marketingPersons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($mp->id); ?>" <?php echo e(request('marketing_person') == $mp->id ? 'selected' : ''); ?>>
                                        <?php echo e($mp->name); ?> (<?php echo e($mp->user_code); ?>)
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        <?php endif; ?>

                    <!-- Month Filter -->
                    <select name="month" class="form-control">
                            <option value="">Select Month</option>
                            <?php $__currentLoopData = range(1,12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($m); ?>" <?php echo e(request('month') == $m ? 'selected' : ''); ?>>
                                    <?php echo e(\Carbon\Carbon::create()->month($m)->format('F')); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>

                    <!-- Year Filter -->
                    <select name="year" class="form-control">
                            <option value="">Select Year</option>
                            <?php $__currentLoopData = range(date('Y'), date('Y') - 10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $y): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($y); ?>" <?php echo e(request('year') == $y ? 'selected' : ''); ?>>
                                    <?php echo e($y); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>

                        <button class="btn btn-outline-secondary" type="button" id="clearFiltersBtn">Clear</button>
                </form>
            </div>

        </div>


            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="readingsTable">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Description</th>
                            
                            <?php if($isAdmin): ?>
                                <th>Marketing Person</th>
                            <?php endif; ?>
                            <th>Starting Reading (value &amp; date)</th>
                            <th>Ending Reading (value &amp; date)</th>
                            <th>Total Reading</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $readings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                        <td><?php echo e($loop->iteration); ?></td>
                                        <td><?php echo e($r['description'] ?? ($r['end_description'] ?? '-')); ?></td>
                                        <?php if($isAdmin): ?>
                                            <td><?php echo e($r['marketing_person']['name'] ?? ($r['marketing_person']['user_code'] ?? '-')); ?></td>
                                        <?php endif; ?>
                                        <td>
                                            <?php if(!empty($r['starting_reading'])): ?>
                                                <?php echo e($r['starting_reading']); ?>

                                                <?php if(!empty($r['starting_image'])): ?>
                                                    &nbsp;<a href="<?php echo e($r['starting_image']); ?>" target="_blank" title="View start image"><div class="fa fa-image"></div></a>
                                                <?php endif; ?>
                                                <br>
                                                <small class="text-muted"><?php echo e($r['starting_at'] ?? '-'); ?></small>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if(!empty($r['ending_reading'])): ?>
                                                <?php echo e($r['ending_reading']); ?>

                                                <?php if(!empty($r['ending_image'])): ?>
                                                    &nbsp;<a href="<?php echo e($r['ending_image']); ?>" target="_blank" title="View end image"><div class="fa fa-image"></div></a>
                                                <?php endif; ?>
                                                <br>
                                                <small class="text-muted"><?php echo e($r['ending_at'] ?? '-'); ?></small>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo e(isset($r['total_reading']) && is_numeric($r['total_reading']) ? number_format($r['total_reading'], 2) : '-'); ?></td>
                                    </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <?php $colspan = $isAdmin ? 6 : 5; ?>
                            <tr><td colspan="<?php echo e($colspan); ?>" class="text-center">No readings uploaded yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php
            $isEmpty = false;
            if (is_object($readings) && method_exists($readings, 'count')) {
                $isEmpty = $readings->count() === 0;
            } elseif (is_array($readings)) {
                $isEmpty = count($readings) === 0;
            }
        ?>

        <?php if($isEmpty): ?>
            <div style="height:72px;"></div>
        <?php endif; ?>

        <div class="card-footer d-flex justify-content-between align-items-center <?php if($isEmpty): ?> position-fixed start-0 end-0 bottom-0 bg-white border-top shadow-sm <?php endif; ?>" style="<?php if($isEmpty): ?> z-index:1030; <?php endif; ?>">
            <div class="d-flex align-items-center">
                <form id="perPageForm" method="GET" action="<?php echo e(route('superadmin.meter-reading.index')); ?>" class="d-flex align-items-center">
                    <label class="me-2 mb-0">Show</label>
                    <select name="per_page" id="perPageSelect" class="form-select form-select-sm me-2">
                        <option value="25" <?php echo e(request('per_page',25) == 25 ? 'selected' : ''); ?>>25</option>
                        <option value="100" <?php echo e(request('per_page') == 100 ? 'selected' : ''); ?>>100</option>
                        <option value="250" <?php echo e(request('per_page') == 250 ? 'selected' : ''); ?>>250</option>
                    </select>
                    <span class="me-3">entries</span>

                    <input type="hidden" name="search" value="<?php echo e(request('search')); ?>">
                    <input type="hidden" name="month" value="<?php echo e(request('month')); ?>">
                    <input type="hidden" name="year" value="<?php echo e(request('year')); ?>">
                    <input type="hidden" name="marketing_person" value="<?php echo e(request('marketing_person')); ?>">
                </form>
            </div>

            <div>
                <?php if(method_exists($readings, 'links')): ?>
                    <?php echo e($readings->links()); ?>

                <?php endif; ?>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var sel = document.getElementById('perPageSelect');
                if (sel) {
                    sel.addEventListener('change', function () {
                        document.getElementById('perPageForm').submit();
                    });
                }
                    // auto-submit filter form when marketing person / month / year changes
                    var filterForm = document.getElementById('filterForm');
                    if (filterForm) {
                        var mp = filterForm.querySelector('select[name="marketing_person"]');
                        var month = filterForm.querySelector('select[name="month"]');
                        var year = filterForm.querySelector('select[name="year"]');
                        [mp, month, year].forEach(function(el){
                            if (el) el.addEventListener('change', function(){ filterForm.submit(); });
                        });
                        var clearBtn = document.getElementById('clearFiltersBtn');
                        if (clearBtn) {
                            clearBtn.addEventListener('click', function () {
                                // clear all selects inside the filter form and submit
                                filterForm.querySelectorAll('select').forEach(function(s){ s.value = ''; });
                                filterForm.submit();
                            });
                        }
                    }
            });
        </script>
    </div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Mamp\htdocs\GenLabV2.0\resources\views/superadmin/meter_reading/index.blade.php ENDPATH**/ ?>