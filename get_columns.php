<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$cols = Illuminate\Support\Facades\Schema::getColumnListing('veiculo_danos');
foreach ($cols as $col) {
    echo $col . ': ' . Illuminate\Support\Facades\Schema::getColumnType('veiculo_danos', $col) . PHP_EOL;
}
