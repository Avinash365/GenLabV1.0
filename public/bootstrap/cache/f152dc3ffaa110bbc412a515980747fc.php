<table class="table table-hover mb-2">
    <thead>
        <tr>
            <th>#</th>
            <th><?php echo e($isClient ? 'Marketing Person' : 'Client'); ?></th>
            <th>Reference No</th>
            <th>Booking Date</th>
            <th>Amount</th>
            <th>Items</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($i+1); ?></td>
                <td> 
                 <?php if($isClient): ?>
                        <?php echo e($booking->marketingPerson->name ?? 'N/A'); ?>

                    <?php else: ?>
                         <?php echo e($booking->client->name ?? 'N/A'); ?>

                    <?php endif; ?>
                </td>
                <td><?php echo e($booking->reference_no); ?></td>
                <td><?php echo e($booking->created_at->format('d-M-Y')); ?></td>
                <td>₹<?php echo e(number_format($booking->total_amount, 2)); ?></td>
                
                <td>
                    <?php echo e($booking->items->count()); ?>

                    <?php if($booking->items->count() > 0): ?>
                        <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#itemsModal-<?php echo e($booking->id); ?>">
                            <i class="fa fa-eye ms-2 text-primary"></i>
                        </a>

                        <!-- Modal -->
                        <div class="modal fade" id="itemsModal-<?php echo e($booking->id); ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Booking Items for <?php echo e($isClient ? ($booking->marketingPerson?->name ?? 'N/A') : ($booking->client?->name ?? 'N/A')); ?></h5>
                                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span> 
                                            </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Sample Description</th>
                                                        <th>Sample Quality</th>
                                                        <th>Lab Analyst</th>
                                                        <th>Particulars</th>
                                                        <th>Expected Date</th>
                                                        <th>Amount</th>
                                                        <th>Job Order No</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $__currentLoopData = $booking->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <tr>
                                                        <td><?php echo e($item->sample_description); ?></td>
                                                        <td><?php echo e($item->sample_quality); ?></td>
                                                        <td><?php echo e($item->lab_analysis_code); ?></td>
                                                        <td><?php echo e($item->particulars); ?></td>
                                                        <td><?php echo e(\Carbon\Carbon::parse($item->lab_expected_date)->format('d-m-Y')); ?></td>
                                                        <td>₹<?php echo e(number_format($item->amount, 2)); ?></td>
                                                        <td><?php echo e($item->job_order_no); ?></td>
                                                    </tr>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </td>

                <td>
                    
                    <span class="badge <?php echo e($booking->generatedInvoice?->status ? 'bg-success' : 'bg-warning'); ?>">
                        <?php echo e($booking->generatedInvoice?->status ? 'Completed' : 'Pending'); ?>

                    </span> 
                        <?php if(!$booking->generatedInvoice?->status): ?>
                        <a href="<?php echo e(route('superadmin.bookingInvoiceStatuses.edit', $booking->id)); ?>" 
                        class="btn btn-sm btn-outline-primary ms-2">
                            <i class="bi bi-pencil"></i> Update
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>

<?php echo $bookings->links('pagination::bootstrap-5'); ?>

<?php /**PATH A:\GenTech\htdocs\GenTech_bug\bankTransaction\GenLab\resources\views/superadmin/accounts/marketingPerson/partials_bookings.blade.php ENDPATH**/ ?>