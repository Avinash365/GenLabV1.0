<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

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
