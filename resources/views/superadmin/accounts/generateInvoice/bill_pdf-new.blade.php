<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice PDF</title>
     <style>
        /* ================= A4 PAGE ================= */
        body{
            padding-top: 90px;
            padding-bottom: 40px;
        }
        .a4-page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 15mm;
            background: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
        }

        /* ================= INVOICE ================= */
        .invoice-preview {
            font-family: 'Noto Sans', Arial, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.2;
        }

        .invoice-preview table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            table-layout: fixed;
        }

        .invoice-preview th,
        .invoice-preview td {
            border: 1px solid #000;
            padding: 6px 10px;
            font-size: 12px;
            word-wrap: break-word;
        }


        /* Remove all borders inside the row */
        tr.item-row td {
            border-top: none !important;
            /* border-bottom: none !important; */
            /* border-left: none !important; */
            border-right: none !important;
        }

        /* Keep ONLY left border on first column */
        tr.item-row td:first-child {
            border-left: 1px solid #000 !important;
        }

        /* Keep ONLY right border on last column */
        tr.item-row td:last-child {
            border-right: 1px solid #000 !important;
        }

        /* Last column right border */
        tr.item-row td:last-child {
            border-right: 1px solid #000 !important;
        }


        .invoice-preview th {
            background: #e9ecef;
            font-weight: bold;
        }

        /* ================= TEXT HELPERS ================= */
        .invoice-preview .text-start {
            text-align: left;
            text-transform: uppercase;
        }

        .invoice-preview .text-uppercase {
            text-transform: uppercase;
        }

        .invoice-preview .text-right {
            text-align: right;
        }

        .invoice-preview .text-centre {
            text-align: center;
            font-weight: bold;
        }

        .invoice-preview .text-bottom {
            text-align: center;
            font-weight: bold;
            vertical-align: bottom;
        }

        .invoice-preview .total-row {
            font-weight: bold;
            background: #f9f9f9;
        }

        /* ================= COLUMN WIDTHS ================= */
        .invoice-preview .col-left {
            width: 30%;
        }

        .invoice-preview .col-wide {
            width: 52%;
        }

        /* ================= EDITABLE FIELDS ================= */
        .invoice-preview [contenteditable="true"] {
            background: #ffffff;
            cursor: text;
        }

        .invoice-preview [contenteditable="true"]:focus {
            outline: 2px solid #ffc107;
            background: #fff3a0;
        }

        .item-row.selected {
            background: #fff3cd !important;
            outline: 2px solid #ffc107;
        }


        .invoice-settings-card {
            top: 90px;
            max-height: calc(100vh - 110px);
            /* header + top gap */
            display: flex;
            flex-direction: column;
        }

        .invoice-settings-body {
            overflow-y: auto;
            flex: 1;
            padding-right: 6px;
            /* avoids scrollbar overlap */
        }

        /* Optional: smooth scrollbar */
        .invoice-settings-body::-webkit-scrollbar {
            width: 6px;
        }

        .invoice-settings-body::-webkit-scrollbar-thumb {
            background-color: rgba(0, 0, 0, 0.2);
            border-radius: 4px;
        }
        .bank-table {
            page-break-inside: avoid;
            font-weight: bold;
        }

        .text-right {
            text-align: right !important;
        }
        .text-left{
            text-align: left !important;
        }
        .text-centre{
            text-align: center !important;
        }
    </style>
</head>
<body>

{!! $html !!}

</body>
</html>
