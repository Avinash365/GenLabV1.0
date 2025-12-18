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
