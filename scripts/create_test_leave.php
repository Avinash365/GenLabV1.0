<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Leave;

$user = User::find(6);
if (!$user) {
    echo "User with id 6 not found\n";
    exit(1);
}

$leave = Leave::create([
    'user_id' => $user->id,
    'employee_name' => $user->name,
    'leave_type' => 'Casual Leave',
    'from_date' => '2026-01-10',
    'to_date' => '2026-01-12',
    'days_hours' => 3,
    'day_type' => 'Full Day',
    'reason' => 'Automated test leave',
    'status' => 'Applied'
]);

if ($leave) {
    echo "Created leave id: {$leave->id}\n";
} else {
    echo "Failed to create leave\n";
}
