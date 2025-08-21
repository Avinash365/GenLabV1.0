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
            @foreach($products as $i => $product)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $product->product_name }}</td>
                    <td>{{ $product->product_code }}</td>
                    <td>{{ $product->category->name ?? 'N/A' }}</td>
                    <td>{{ $product->purchase_unit }}</td>
                    <td>{{ number_format($product->purchase_price, 2) }}</td>
                    <td>{{ $product->unit }}</td>
                    <td>{{ $product->invoice_no }}</td>
                    <td>{{ $product->remark }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>