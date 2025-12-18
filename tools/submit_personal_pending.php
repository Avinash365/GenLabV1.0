<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\MarketingExpense;
use App\Traits\HandlesMarketingExpenses;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

$month = (int) now()->format('n');
$year = (int) now()->format('Y');
$period = Carbon::create($year, $month, 1);

$pendingExpenses = MarketingExpense::with(['marketingPerson','approver'])
    ->where('section','personal')
    ->whereYear('created_at',$year)
    ->whereMonth('created_at',$month)
    ->where('status','pending')
    ->orderBy('created_at')
    ->get();

if ($pendingExpenses->isEmpty()){
    echo "No pending personal expenses for {$period->format('F Y')}\n";
    exit(0);
}

$pendingIds = $pendingExpenses->pluck('id');
$existingSummaryPaths = $pendingExpenses->pluck('approval_summary_path')->filter()->unique();
$summaryFilename = sprintf('personal-expenses-%s-%s.pdf', $period->format('Y_m'), Str::lower(Str::random(6)));
$summaryPath = 'marketing_expenses/'.$summaryFilename;

echo "Generating summary and assigning approval_summary_path: {$summaryPath} for {$pendingIds->count()} rows\n";

MarketingExpense::whereIn('id', $pendingIds->all())->update([
    'approval_note' => 'Submitted for approval - '.$period->format('F Y'),
    'approved_by'   => null,
    'approved_at'   => null,
    'approval_summary_path'  => $summaryPath,
]);

$refreshedPending = MarketingExpense::with(['marketingPerson','approver'])
    ->whereIn('id', $pendingIds->all())
    ->orderBy('created_at')
    ->get();

// generate PDF using trait method: instantiate a temporary object using the trait
class Temp { use HandlesMarketingExpenses; }
$helper = new Temp();

// generatePersonalSummaryDocument expects generatePersonalSummaryDocument(Collection, Carbon, string)
try{
    $pdfOutput = $helper->generatePersonalSummaryDocument($refreshedPending, $period, $summaryPath);
    echo "Generated summary PDF (length: " . strlen($pdfOutput) . ")\n";
} catch (\Throwable $th){
    echo "Failed to generate PDF: " . $th->getMessage() . "\n";
}

// cleanup old summary paths if unused
foreach($existingSummaryPaths as $oldPath){
    if(!$oldPath || $oldPath === $summaryPath) continue;
    $stillInUse = MarketingExpense::where('approval_summary_path', $oldPath)
        ->whereNotIn('id', $pendingIds->all())
        ->exists();
    if(!$stillInUse){
        Storage::disk('public')->delete($oldPath);
        echo "Deleted old summary: {$oldPath}\n";
    }
}

echo "Done.\n";
