<?php
    $rawApproved = (float) ($expense->approved_amount ?? 0);
    $status = $expense->status ?? null;
    $displayApproved = $status === 'approved' ? (float) $expense->amount : $rawApproved;
    $rawDue = max(0, (float) $expense->amount - $rawApproved);
    $displayDue = max(0, (float) $expense->amount - $displayApproved);
    // When rendering via AJAX from approve/reject endpoints, Request::routeIs won't match the list route.
    // Respect explicit flag when provided, else fall back to route name.
    $isApprovalPage = ($isApprovalPage ?? null);
    if ($isApprovalPage === null) {
        $isApprovalPage = Request::routeIs('superadmin.marketing.expenses.approved');
    }
    $showPerson = $showPerson ?? true;
    $groupIds = $expense->aggregate_ids ?? [];
    $groupAttr = !empty($groupIds) ? implode(',', $groupIds) : null;
    $isGroupedPersonal = (($expense->section ?? null) === 'personal') && !empty($groupIds);
?>
<tr data-amount="<?php echo e($expense->amount); ?>" data-approved="<?php echo e($rawApproved); ?>" data-due="<?php echo e($rawDue); ?>" data-id="<?php echo e($expense->id); ?>" <?php if($groupAttr): ?> data-group="<?php echo e($groupAttr); ?>" <?php endif; ?>>
    <td><?php echo e($serial ?? '—'); ?></td>
    <?php
        $personLabel = $expense->marketingPerson->name ?? ($expense->person_name ?: 'N/A');
        if(($expense->section ?? null) === 'personal' && $groupAttr){
            $periodLabel = $expense->getAttribute('personal_period_label');
            if(!$periodLabel){
                $periodStart = optional($expense->from_date)->format('M Y');
                $periodEnd = optional($expense->to_date)->format('M Y');
                if($periodStart && $periodEnd && $periodStart !== $periodEnd){
                    $periodLabel = optional($expense->from_date)->format('d M Y').' - '.optional($expense->to_date)->format('d M Y');
                } elseif($periodStart) {
                    $periodLabel = $periodStart;
                }
            }

            $personSource = $expense->person_name ?: $personLabel;
            $personLabel = trim(($personSource ?: 'Personal Expenses').' '.($periodLabel ? "({$periodLabel})" : ''));
        }
    ?>
    <?php if($showPerson): ?>
        <td><?php echo e($personLabel); ?></td>
    <?php endif; ?>
    <td><?php echo e(number_format($expense->amount, 2)); ?></td>
    <?php if(!$isApprovalPage): ?>
        <td class="text-success"><?php echo e(number_format($displayApproved, 2)); ?></td>
        <td class="text-danger"><?php echo e(number_format($displayDue, 2)); ?></td>
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
        <?php
            $summaryPath = $expense->approval_summary_path ? asset('storage/'.$expense->approval_summary_path) : null;
            $receiptPaths = collect($expense->receipt_paths ?? []);
            if($expense->file_path){
                $receiptPaths->prepend($expense->file_path);
            }
            $receiptPaths = $receiptPaths->filter()->unique()->values();
        ?>
        <?php if($summaryPath): ?>
            <a href="<?php echo e($summaryPath); ?>" target="_blank">Summary PDF</a>
        <?php endif; ?>
        <?php if(!$isGroupedPersonal && $summaryPath && $receiptPaths->isNotEmpty()): ?>
            <br>
        <?php endif; ?>
        <?php if(!$isGroupedPersonal && $receiptPaths->isNotEmpty()): ?>
            <div class="d-flex flex-column gap-1">
                <?php $__currentLoopData = $receiptPaths; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $path): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $href = \Illuminate\Support\Str::startsWith($path, ['http://','https://']) ? $path : asset('storage/'.$path);
                        $label = $receiptPaths->count() > 1 ? 'Receipt '.($index + 1) : 'Receipt';
                    ?>
                    <a href="<?php echo e($href); ?>" target="_blank"><?php echo e($label); ?></a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
        <?php if(!$summaryPath && ($isGroupedPersonal ? true : $receiptPaths->isEmpty())): ?>
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