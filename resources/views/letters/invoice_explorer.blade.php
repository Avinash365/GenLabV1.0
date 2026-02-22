<!DOCTYPE html>
<html>
<head>
    <title>Invoice Letters</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        ul {
            list-style-type: none;
            padding-left: 20px;
        }
        .folder {
            font-weight: bold;
            color: #2c3e50;
        }
        .file a {
            text-decoration: none;
            color: #2980b9;
        }
        .file a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <h2>Invoice: {{ $invoice->invoice_no }}</h2>
    <p><strong>Invoice Date:</strong> {{ $invoice->invoice_date }}</p>

    <hr>

    @if(!empty($result['children']))
        <ul>
            @foreach($result['children'] as $folder)
                <li class="folder">
                    📁 {{ $folder['name'] }}

                    @if(!empty($folder['children']))
                        <ul>
                            @foreach($folder['children'] as $file)
                                <li class="file">
                                    📄 
                                    <a href="{{ $file['url'] }}" target="_blank">
                                        {{ $file['name'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <ul>
                            <li>No files found</li>
                        </ul>
                    @endif

                </li>
            @endforeach
        </ul>
    @else
        <p>No letters found for this invoice.</p>
    @endif

</body>
</html>