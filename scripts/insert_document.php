<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Document;
use Illuminate\Support\Facades\DB;

try {
    $d = Document::create([
        'name' => 'Auto Doc',
        'type' => 'other',
        'description' => 'auto insert',
        'file_path' => null,
        'uploaded_by' => null,
        'status' => 'active',
    ]);
    echo "Inserted id: {$d->id}\n";
} catch (Exception $e) {
    echo "Insert failed: " . $e->getMessage() . "\n";
}
$rows = DB::select('SELECT id,name,type,description,uploaded_by,status,created_at FROM documents ORDER BY id DESC LIMIT 10');
if (empty($rows)) { echo "No documents found.\n"; exit; }
foreach ($rows as $r) echo implode("\t", [$r->id,$r->name,$r->type,$r->description,$r->uploaded_by,$r->status,$r->created_at]) . "\n";
