<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Calibration;
use Illuminate\Support\Facades\DB;

try {
    $created = Calibration::create([
        'agency_name' => 'Test Agency Auto',
        'equipment_name' => 'Test Equipment Auto',
        'issue_date' => now()->toDateString(),
        'expire_date' => now()->addDays(30)->toDateString(),
        'created_by' => null,
    ]);
    echo "Inserted id: " . $created->id . "\n";
} catch (Exception $e) {
    echo "Insert failed: " . $e->getMessage() . "\n";
}

$rows = DB::select('SELECT id, agency_name, equipment_name, issue_date, expire_date, created_at FROM calibrations ORDER BY id DESC LIMIT 5');
if (empty($rows)) {
    echo "No rows found in calibrations.\n";
    exit(0);
}
foreach ($rows as $r) {
    echo implode("\t", [
        $r->id,
        $r->agency_name,
        $r->equipment_name,
        $r->issue_date,
        $r->expire_date,
        $r->created_at
    ]) . "\n";
}
