<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo e($title); ?></title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background: #f5f5f5; }
    </style>
</head>
<body>
    <h2 style="text-align:center;"><?php echo e($title); ?></h2>
    <?php
        $totalAmount = $expenses->sum(function($expense){ return (float) ($expense->amount ?? 0); });
        $totalApproved = $expenses->sum(function($expense){ return (float) ($expense->approved_amount ?? 0); });
        $totalDue = $expenses->sum(function($expense){
            $approved = (float) ($expense->approved_amount ?? 0);
            return max(0, (float) ($expense->amount ?? 0) - $approved);
        });
        $isPersonal = ($section === 'personal');
        $personalNames = collect();
        if($isPersonal){
            $personalNames = $expenses->map(function($expense){
                if($expense->relationLoaded('marketingPerson') && $expense->marketingPerson){
                    return $expense->marketingPerson->name;
                }
                return $expense->person_name;
            })->filter()->unique()->values();
        }
    ?>
    <?php if($isPersonal && $personalNames->isNotEmpty()): ?>
        <p><strong>Person:</strong> <?php echo e($personalNames->join(', ')); ?></p>
    <?php endif; ?>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <?php if($isPersonal): ?>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Expense Date</th>
                <?php else: ?>
                    <th>Person</th>
                    <th>Amount</th>
                    <th>Description</th>
                    <th>Expense Date</th>
                    <th>Person Code</th>
                    <th>Section</th>
                    <th>Approved</th>
                    <th>Due</th>
                    <th>To</th>
                    <th>Status</th>
                    <th>Approved By</th>
                    <th>Uploaded At</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $expenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $expense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $approved = (float) ($expense->approved_amount ?? 0);
                    $due = max(0, (float) $expense->amount - $approved);
                    $expenseDate = optional($expense->from_date)->format('d M Y');
                ?>
                <tr>
                    <td><?php echo e($loop->iteration); ?></td>
                    <?php if($isPersonal): ?>
                        <td><?php echo e($expense->description ? \Illuminate\Support\Str::limit($expense->description, 120) : '-'); ?></td>
                        <td><?php echo e(number_format((float) $expense->amount, 2)); ?></td>
                        <td><?php echo e($expenseDate); ?></td>
                    <?php else: ?>
                        <td><?php echo e($expense->marketingPerson->name ?? $expense->person_name ?? '-'); ?></td>
                        <td><?php echo e(number_format((float) $expense->amount, 2)); ?></td>
                        <td><?php echo e($expense->description ? \Illuminate\Support\Str::limit($expense->description, 120) : '-'); ?></td>
                        <td><?php echo e($expenseDate); ?></td>
                        <td><?php echo e($expense->marketing_person_code ?? '-'); ?></td>
                        <td><?php echo e(ucfirst($expense->section ?? 'marketing')); ?></td>
                        <td><?php echo e(number_format($approved, 2)); ?></td>
                        <td><?php echo e(number_format($due, 2)); ?></td>
                        <td><?php echo e(optional($expense->to_date)->format('d M Y')); ?></td>
                        <td><?php echo e(ucfirst($expense->status ?? 'pending')); ?></td>
                        <td><?php echo e($expense->approver->name ?? '-'); ?></td>
                        <td><?php echo e(optional($expense->created_at)->format('d M Y')); ?></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="<?php echo e($isPersonal ? 4 : 13); ?>" style="text-align:center;">No data available.</td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <?php if($isPersonal): ?>
                    <td colspan="2" style="text-align:right;">Total</td>
                    <td><?php echo e(number_format($totalAmount, 2)); ?></td>
                    <td></td>
                <?php else: ?>
                    <td colspan="2" style="text-align:right;">Totals</td>
                    <td><?php echo e(number_format($totalAmount, 2)); ?></td>
                    <td colspan="2"></td>
                    <td></td>
                    <td></td>
                    <td><?php echo e(number_format($totalApproved, 2)); ?></td>
                    <td><?php echo e(number_format($totalDue, 2)); ?></td>
                    <td colspan="4"></td>
                <?php endif; ?>
            </tr>
        </tfoot>
    </table>
</body>
</html>
<?php /**PATH C:\Mamp\htdocs\GenLabV1.0\resources\views/superadmin/marketing/expenses/export_pdf.blade.php ENDPATH**/ ?>