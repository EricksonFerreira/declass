<?php

use App\Models\ClockifyTimeTracker;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$trackers = ClockifyTimeTracker::all();
foreach ($trackers as $t) {
    echo "ID: {$t->id}, User: {$t->user_id}, Start: {$t->inicio}, End: {$t->fim}, Duration: {$t->duration_in_seconds}\n";
}
