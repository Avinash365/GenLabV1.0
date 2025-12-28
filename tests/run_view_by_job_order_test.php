+<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\MobileControllers\Accounts\MarketingPersonInfo;

$req = Request::create('/', 'GET', []);
$ctrl = new MarketingPersonInfo();
$response = $ctrl->viewByJobOrderApi($req, 'MKT013');

if ($response instanceof \Illuminate\Http\JsonResponse) {
    $data = $response->getData(true);
    echo json_encode($data, JSON_PRETTY_PRINT);
} else {
    var_dump($response);
}
