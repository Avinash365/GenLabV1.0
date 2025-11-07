<?php
    $approved = (float) ($expense->approved_amount ?? 0);
    $due = max(0, (float) $expense->amount - $approved);
    // When rendering via AJAX from approve/reject endpoints, Request::routeIs won't match the list route.
    // Respect explicit flag when provided, else fall back to route name.
    $isApprovalPage = ($isApprovalPage ?? null);
    if ($isApprovalPage === null) {
        $isApprovalPage = Request::routeIs('superadmin.marketing.expenses.approved');
    }
?>
<tr data-amount="<?php echo e($expense->amount); ?>" data-approved="<?php echo e($approved); ?>" data-due="<?php echo e($due); ?>" data-id="<?php echo e($expense->id); ?>">
    <td><?php echo e($serial ?? '—'); ?></td>
    <?php
        $personLabel = $expense->marketingPerson->name ?? ($expense->person_name ?: 'N/A');
    ?>
    <td><?php echo e($personLabel); ?></td>
    <td><?php echo e(number_format($expense->amount, 2)); ?></td>
    <?php if(!$isApprovalPage): ?>
        <td class="text-success"><?php echo e(number_format($approved, 2)); ?></td>
        <td class="text-danger"><?php echo e(number_format($due, 2)); ?></td>
    <?php endif; ?>
    <td><?php echo e(optional($expense->created_at)->format('d M Y')); ?></td>
    <td>
        <?php echo e(optional($expense->from_date)->format('d M Y')); ?>

        -
        <?php echo e(optional($expense->to_date)->format('d M Y')); ?>

    </td>
    <?php if(!$isApprovalPage): ?>
        <td><?php echo e($expense->approver->name ?? '-'); ?></td>
    <?php endif; ?>
    <td>
        <?php if($expense->file_path): ?>
            <a href="<?php echo e(asset('storage/'.$expense->file_path)); ?>" target="_blank">PDF</a>
        <?php else: ?>
            -
        <?php endif; ?>
    </td>
    <td>
        <?php if($isApprovalPage): ?>
            <?php if($expense->status === 'pending'): ?>
                <div class="btn-group btn-group-sm" role="group">
                    <button class="btn btn-success js-approve-expense">Approve</button>
                    <button class="btn btn-outline-danger js-reject-expense">Reject</button>
                </div>
            <?php else: ?>
                <?php if($expense->status === 'approved'): ?>
                    <span class="badge bg-success">Approved</span>
                <?php elseif($expense->status === 'rejected'): ?>
                    <span class="badge bg-danger">Rejected</span>
                <?php else: ?>
                    <span class="badge bg-secondary">Pending</span>
                <?php endif; ?>
            <?php endif; ?>
        <?php else: ?>
            <?php if($expense->status === 'approved'): ?>
                <span class="badge bg-success">Approved</span>
            <?php elseif($expense->status === 'rejected'): ?>
                <span class="badge bg-danger">Rejected</span>
            <?php else: ?>
                <span class="badge bg-secondary">Pending</span>
            <?php endif; ?>
        <?php endif; ?>
    </td>
</tr>
<?php /**PATH C:\Mamp\htdocs\GenLabV1.0\resources\views/superadmin/marketing/expenses/_row.blade.php ENDPATH**/ ?>