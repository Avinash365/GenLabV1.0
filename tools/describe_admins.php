<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $cols = \DB::select('DESCRIBE admins');
    foreach ($cols as $c) {
        echo ($c->Field ?? $c['Field']) . "\t" . ($c->Type ?? $c['Type']) . "\n";
    }
} catch (Throwable $e) {
    fwrite(STDERR, 'Error: ' . $e->getMessage() . "\n");
    exit(1);
}
