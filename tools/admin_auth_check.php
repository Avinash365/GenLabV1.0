<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

$email = 'admin@example.com';
$pass = 'admin123';

$admin = \App\Models\Admin::where('email',$email)->first();
if (!$admin) {
    echo "Admin not found\n";
    exit(1);
}

echo "Admin exists: {$admin->email}\n";
$hashOk = Hash::check($pass, $admin->password);
echo 'Hash check: ' . ($hashOk ? 'OK' : 'FAIL') . "\n";

$attempt = Auth::guard('api_admin')->attempt(['email'=>$email,'password'=>$pass]);
echo 'Guard attempt: ' . ($attempt ? 'OK' : 'FAIL') . "\n";
if ($attempt) {
    echo 'Token: ' . $attempt . "\n";
}
