<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$hasColumn = \Illuminate\Support\Facades\Schema::hasColumn('marketing_expenses', 'person_name');
var_export(['has_person_name' => $hasColumn]);
