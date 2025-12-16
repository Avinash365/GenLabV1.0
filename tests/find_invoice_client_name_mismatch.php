<?php
chdir(__DIR__ . '/../');
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Invoice;

$rows = Invoice::with('relatedBooking.client')->get();
$mismatches = [];
foreach ($rows as $inv) {
    $b = $inv->relatedBooking;
    if (!$b) continue;
    $bookingName = $b->client_name;
    $assigned = $b->client?->name ?? null;
    if ($bookingName && $assigned && $bookingName !== $assigned) {
        $mismatches[] = [
            'invoice_id' => $inv->id,
            'invoice_no' => $inv->invoice_no,
            'booking_id' => $b->id,
            'booking_client_name' => $bookingName,
            'client_name' => $assigned,
        ];
        if (count($mismatches) >= 10) break;
    }
}

echo json_encode($mismatches, JSON_PRETTY_PRINT);
