<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Documents</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 20px;
            color: #1f2937;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }
        .header {
            background-color: #3b82f6;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
        }
        .header p {
            margin: 5px 0 0;
            opacity: 0.9;
            font-size: 0.875rem;
        }
        .file-list {
            padding: 20px;
            list-style: none;
            margin: 0;
        }
        .file-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 10px;
            transition: all 0.2s;
        }
        .file-item:hover {
            border-color: #3b82f6;
            background-color: #eff6ff;
        }
        .file-info {
            flex: 1;
            margin-right: 15px;
            overflow: hidden;
        }
        .file-name {
            font-weight: 500;
            display: block;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .file-meta {
            font-size: 0.75rem;
            color: #6b7280;
        }
        .download-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: #3b82f6;
            color: white;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            transition: background-color 0.2s;
            white-space: nowrap;
        }
        .download-btn:hover {
            background-color: #2563eb;
        }
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6b7280;
        }
        @media (max-width: 480px) {
            .file-item {
                flex-direction: column;
                align-items: flex-start;
            }
            .file-info {
                margin-right: 0;
                margin-bottom: 12px;
                width: 100%;
            }
            .download-btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Document Repository</h1>
            <p>Reference: {{ $job }}</p>
        </div>
        
        @if(count($files) > 0)
            <ul class="file-list">
                @foreach($files as $file)
                    <li class="file-item">
                        <div class="file-info">
                            <span class="file-name" title="{{ $file['filename'] }}">{{ $file['filename'] }}</span>
                            <span class="file-meta">{{ $file['size'] }} • {{ $file['date'] }}</span>
                        </div>
                        <a href="{{ route('public.reports.download', ['job' => $job, 'filename' => $file['filename']]) }}" class="download-btn" download>
                            Download
                        </a>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="empty-state">
                <p>No documents found for this reference.</p>
            </div>
        @endif
        
        <div style="text-align: center; padding: 20px; color: #9ca3af; font-size: 0.75rem;">
            &copy; {{ date('Y') }} GenLab. All rights reserved.
        </div>
    </div>
</body>
</html>
