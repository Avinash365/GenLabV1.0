<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;
$rows = DB::select('SELECT id, department_name, client_name, letter_no, letter_data, status, created_at FROM important_letters ORDER BY id DESC LIMIT 20');
if (empty($rows)) { echo "No letters found.\n"; exit; }
foreach ($rows as $r) {
    echo implode("\t", [$r->id, $r->department_name, $r->client_name, $r->letter_no, $r->letter_data, $r->status, $r->created_at]) . "\n";
}
