<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

use Illuminate\Support\Facades\Storage;

echo "Listing directories starting with '30-01-2026':\n";
$dirs = Storage::directories('public/letters');
foreach ($dirs as $d) {
    if (str_contains($d, '30-01-2026')) {
        echo " - " . $d . "\n";
        echo "   Basename: '" . basename($d) . "'\n";
        echo "   Length: " . strlen(basename($d)) . "\n";
    }
}

$target = '30-01-2026--NALCO--DAMANJODI-SITE--NBCC-ODISHA-';
echo "\nChecking exact match for: '$target'\n";
if (Storage::exists("public/letters/$target")) {
    echo "EXISTS!\n";
} else {
    echo "DOES NOT EXIST via Storage::exists()\n";
}

// Check with raw PHP file functions
$rawPath = storage_path("app/public/letters/$target");
echo "Raw Path: $rawPath\n";
if (file_exists($rawPath)) {
     echo "Raw file_exists: YES\n";
} else {
     echo "Raw file_exists: NO\n";
}
