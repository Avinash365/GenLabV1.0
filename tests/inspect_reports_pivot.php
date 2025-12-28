<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\BookingItem;
use Illuminate\Support\Facades\Storage;

// Find a booking item with a non-null pivot pdf_path (as web panel requires)
$item = BookingItem::with('booking','reports')
    ->whereHas('reports', function($q){
        $q->whereNotNull('booking_item_report.pdf_path');
    })->first();

if (!$item) {
    echo "No BookingItem found with pivot pdf_path.\n";
    exit(0);
}

$out = [
    'id' => $item->id,
    'job_order_no' => $item->job_order_no,
    'booking_reference' => $item->booking->reference_no ?? null,
    'booking_marketing_id' => $item->booking->marketing_id ?? null,
    'reports' => [],
];

foreach ($item->reports as $r) {
    $pivot = $r->pivot ?? null;
    $p = $pivot ? (method_exists($pivot, 'toArray') ? $pivot->toArray() : (array)$pivot) : null;
    $path = $p['pdf_path'] ?? $p['generated_report_path'] ?? $p['file_path'] ?? $p['report_path'] ?? $p['path'] ?? null;
    $exists = null;
    if ($path) {
        try { $exists = Storage::disk('public')->exists($path); } catch (\Throwable $_) { $exists = null; }
    }
    $out['reports'][] = [
        'report_id' => $r->id,
        'pivot' => $p,
        'detected_path' => $path,
        'storage_exists_public' => $exists,
        'url' => $path ? (preg_match('#^https?://#i', $path) ? $path : (Storage::disk('public')->exists($path) ? Storage::url($path) : $path)) : null,
    ];
}

echo json_encode($out, JSON_PRETTY_PRINT), PHP_EOL;
