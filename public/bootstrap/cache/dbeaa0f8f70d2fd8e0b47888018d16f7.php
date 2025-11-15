<?php
    $amount = (float) ($expense->amount ?? 0);
    $approved = (float) ($expense->approved_amount ?? 0);
    $due = max(0, $amount - $approved);
    $receiptUrl = $expense->file_path ? asset('storage/'.$expense->file_path) : null;
    $updateUrl = route('superadmin.personal.expenses.update', $expense);
    $deleteUrl = route('superadmin.personal.expenses.destroy', $expense);
?>
<tr
    data-id="<?php echo e($expense->id); ?>"
    data-amount="<?php echo e($amount); ?>"
    data-approved="<?php echo e($approved); ?>"
    data-due="<?php echo e($due); ?>"
    data-date="<?php echo e(optional($expense->from_date)->format('Y-m-d')); ?>"
    data-description="<?php echo e(e($expense->description ?? '')); ?>"
    data-update-url="<?php echo e($updateUrl); ?>"
    data-delete-url="<?php echo e($deleteUrl); ?>"
    data-receipt-url="<?php echo e($receiptUrl); ?>"
>
    <td><?php echo e($serial ?? '—'); ?></td>
    <td><?php echo e($expense->description ? \Illuminate\Support\Str::limit($expense->description, 60) : '-'); ?></td>
    <td><?php echo e(number_format($amount, 2)); ?></td>
    <td><?php echo e(optional($expense->from_date)->format('d M Y')); ?></td>
    <td>
        <?php if($receiptUrl): ?>
            <a href="<?php echo e($receiptUrl); ?>" target="_blank">View Receipt</a>
        <?php else: ?>
            -
        <?php endif; ?>
    </td>
    <td>
        <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-outline-primary js-edit-expense">Edit</button>
            <button type="button" class="btn btn-outline-danger js-delete-expense">Delete</button>
        </div>
    </td>
</tr>
<?php /**PATH C:\Mamp\htdocs\GenLabV1.0\resources\views/superadmin/personal/expenses/_daily_row.blade.php ENDPATH**/ ?>