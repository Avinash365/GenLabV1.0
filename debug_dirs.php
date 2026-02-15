<?php

use Illuminate\Support\Facades\Storage;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Scanning directories in public/letters matching '30-01-2026'...\n";

$directories = Storage::disk('public')->directories('letters');

$found = false;
foreach ($directories as $dir) {
    if (str_contains($dir, '30-01-2026')) {
        echo "Found: '" . $dir . "'\n";
        echo "Basename: '" . basename($dir) . "'\n";
        echo "Hex dump: " . bin2hex(basename($dir)) . "\n";
        $found = true;
    }
}

if (!$found) {
    echo "No matching directories found.\n";
}
