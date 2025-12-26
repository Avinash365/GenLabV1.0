<?php
chdir(__DIR__ . '/../');
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\MobileControllers\Accounts\MarketingPersonInfo;

$request = Request::create('/dummy', 'GET', [ 'perPage' => 10 ]);
$controller = new MarketingPersonInfo();
try {
    $response = $controller->personalExpensesListApi($request, 'MKT001');
} catch (\Throwable $e) {
    echo "Exception: " . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
    exit(1);
}

if (is_object($response) && method_exists($response, 'getContent')) {
    $status = method_exists($response, 'getStatusCode') ? $response->getStatusCode() : 'N/A';
    $content = $response->getContent();
    echo "HTTP Status: {$status}\n";
    $decoded = json_decode($content, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    } else {
        echo $content . PHP_EOL;
    }
} else {
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
}
// --- Fetch checked-in (cleared) items via MarketingExpenseController::checkedInApi
use App\Http\Controllers\Accounts\MarketingExpenseController;

$req2 = Request::create('/api/superadmin/personal/checked-in', 'GET', [ 'per_page' => 5, 'page' => 1 ]);
$controller2 = $app->make(\App\Http\Controllers\Accounts\MarketingExpenseController::class);
try {
    $response2 = $controller2->checkedInApi($req2);
} catch (\Throwable $e) {
    echo "Exception (checkedInApi): " . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
    exit(1);
}

if (is_object($response2) && method_exists($response2, 'getContent')) {
    $status = method_exists($response2, 'getStatusCode') ? $response2->getStatusCode() : 'N/A';
    $content = $response2->getContent();
    echo "\nChecked-in HTTP Status: {$status}\n";
    $decoded = json_decode($content, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    } else {
        echo $content . PHP_EOL;
    }
} else {
    echo json_encode($response2, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
}
