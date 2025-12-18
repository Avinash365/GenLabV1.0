<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\MarketingExpense;

// derive submitted status from presence of approval_summary_path
$rows = MarketingExpense::select(['id','person_name','marketing_person_code','status','approval_summary_path','created_at','amount'])
    ->where('section','personal')
    ->orderByDesc('created_at')
    ->limit(50)
    ->get()
    ->map(function($r){
        return [
            'id' => $r->id,
            'person_name' => $r->person_name,
            'marketing_person_code' => $r->marketing_person_code,
            'status' => $r->status,
            'submitted_for_approval' => !empty($r->approval_summary_path),
            'approval_summary_path' => $r->approval_summary_path,
            'created_at' => (string)$r->created_at,
            'amount' => (float)$r->amount,
        ];
    });

echo json_encode($rows->all(), JSON_PRETTY_PRINT) . "\n";
