<?php
// Quick test runner for invoice list (marketing) API (invokes controller directly)
chdir(__DIR__ . '/../');
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\MobileControllers\Accounts\MarketingPersonInfo;

// Example request: fetch first page, 10 per page
$request = Request::create('/dummy', 'GET', [
    'perPage' => 10,
    // 'department_id' => 1,
    // 'client_id' => 123,
    // 'payment_status' => '1',
    // 'month' => 12,
    // 'year' => 2025,
]);

$controller = new MarketingPersonInfo();
$response = $controller->invoiceListApi($request, 'MKT001');

if ($response instanceof Illuminate\Http\JsonResponse) {
    echo $response->getContent();
} else {
    echo json_encode($response);
}
