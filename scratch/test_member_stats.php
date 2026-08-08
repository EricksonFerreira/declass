<?php

use App\Actions\Clockify\Dashboard\GetMemberStatsAction;
use Carbon\Carbon;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$action = app(GetMemberStatsAction::class);
$start = Carbon::now()->startOfMonth();
$end = Carbon::now();

$res = $action->handle($start, $end);
echo json_encode($res, JSON_PRETTY_PRINT);
