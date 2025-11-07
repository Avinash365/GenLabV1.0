<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\Accounts\MarketingExpenseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

DB::beginTransaction();

$request = Request::create('/test', 'POST', [
    'marketing_person_code' => null,
    'marketing_person_name' => 'CLI Admin',
    'amount' => 1000,
    'from_date' => '2025-11-07',
    'to_date' => '2025-11-08',
    'description' => 'CLI test',
    'section' => 'office',
]);

$controller = app(MarketingExpenseController::class);
$response = $controller->store($request);

echo $response->getContent(), "\n";

DB::rollBack();
