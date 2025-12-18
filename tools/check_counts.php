<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\MarketingExpense;

$marketing = MarketingExpense::where('section','marketing')->where('status','pending')->count();
$personalPending = MarketingExpense::where('section','personal')->where('status','pending')->whereNotNull('approval_summary_path')->count();

echo "marketing_pending_count:$marketing\n";
echo "personal_pending_count:$personalPending\n";

// Also compute personal summaries count by grouping same way controller does
$personalCollection = MarketingExpense::with(['marketingPerson','approver'])
    ->where('section','personal')
    ->where('status','pending')
    ->whereNotNull('approval_summary_path')
    ->get();

if ($personalCollection->isEmpty()) {
    echo "personal_summaries_count:0\n";
    exit(0);
}

// group
$groups = $personalCollection->groupBy(function($expense){
    if (!empty($expense->approval_summary_path)) return 'summary:'.$expense->approval_summary_path;
    return 'period:' . optional($expense->created_at)->format('Y-m');
});

$personalSummariesCount = $groups->count();

echo "personal_summaries_count:$personalSummariesCount\n";

return 0;
