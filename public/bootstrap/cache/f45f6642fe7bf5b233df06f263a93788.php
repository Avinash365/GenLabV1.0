<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Product List</title>
    <style>
        body{ font-family: DejaVu Sans, sans-serif; font-size:12px; }
        table{ width:100%; border-collapse: collapse; }
        th, td{ border:1px solid #ddd; padding:6px; text-align:left; }
        th{ background:#f2f2f2; }
    </style>
</head>
<body>
    <h3>Product List</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Code</th>
                <th>Category</th>
                <th>Purchase Unit</th>
                <th>Purchase Price</th>
                <th>Unit</th>
                <th>Invoice No</th>
                <th>Remark</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($i + 1); ?></td>
                    <td><?php echo e($product->product_name); ?></td>
                    <td><?php echo e($product->product_code); ?></td>
                    <td><?php echo e($product->category->name ?? 'N/A'); ?></td>
                    <td><?php echo e($product->purchase_unit); ?></td>
                    <td><?php echo e(number_format($product->purchase_price, 2)); ?></td>
                    <td><?php echo e($product->unit); ?></td>
                    <td><?php echo e($product->invoice_no); ?></td>
                    <td><?php echo e($product->remark); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</body>
</html><?php /**PATH C:\Mamp\htdocs\GenLab\resources\views/superadmin/viewproduct/products_pdf.blade.php ENDPATH**/ ?>