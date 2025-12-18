<?php
chdir(__DIR__ . '/../');
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\PersonalExpense;

$code = $argv[1] ?? 'MKT001';
$user = User::where('user_code', $code)->first();
if (!$user) {
    echo "User not found for code: {$code}\n";
    exit(1);
}

echo "Found user id={$user->id}, user_code={$user->user_code}, name={$user->name}\n";

$count = PersonalExpense::where('user_code', $user->user_code)->count();
echo "PersonalExpense rows for user_code {$user->user_code}: {$count}\n";

$examples = PersonalExpense::where('user_code', $user->user_code)->limit(5)->get();
foreach ($examples as $e) {
    echo "- id={$e->id}, amount={$e->amount}, date=" . ($e->expense_date?->toDateString() ?? 'null') . ", desc=" . substr($e->description ?? '',0,60) . "\n";
}

exit(0);
