<?php

return [
    'exports' => [
        'disk' => env('EXCEL_DISK', null),
    ],

    'imports' => [
        'read_only' => true,
        'force_sheets_collection' => false,
        'heading_row' => [
            'formatter' => 'slug',
        ],
    ],

    'temporary_files' => [
        'local_path' => storage_path('framework/laravel-excel'),
        'remote_disk' => null,
        'remote_prefix' => null,
    ],
];
