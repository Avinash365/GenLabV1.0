<?php
// Bootstrap Laravel and create test auth users for API smoke tests

use Illuminate\Support\Facades\Hash;

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    // Ensure DB connection is up
    \DB::connection()->getPdo();

    // Create/Update test user
    $user = \App\Models\User::updateOrCreate(
        ['user_code' => 'test123'],
        [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => \Hash::make('password123'),
            'user_code' => 'test123',
        ]
    );

    // Create/Update admin (if Admin model exists)
    if (class_exists(\App\Models\Admin::class)) {
        $admin = \App\Models\Admin::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => \Hash::make('admin123'),
            ]
        );
        echo "Admin ready: admin@example.com / admin123\n";
    } else {
        echo "Admin model not found; skipped admin seed.\n";
    }

    echo "User ready: test123 / password123\n";
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, 'Error: ' . $e->getMessage() . "\n");
    exit(1);
}
