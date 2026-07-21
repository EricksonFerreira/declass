<?php

$sourceFile = 'app/Http/Controllers/ReservaController.php';

// Extract checkin methods
$methodsCheckin = [
    'checkin',
    'veiculosDisponiveisCheckin',
    'veiculosModalCheckin',
    'diagnosticoDisponibilidadeCheckin',
    'criarContratoCheckIn',
    'checkinStatusUpgrade',
    'excluirContratoCheckin',
];

$methodsCheckout = [
    'checkoutVistoria',
    'finalizarCheckoutVistoria',
    'adicionarDanoVeiculo',
];

$lines = file($sourceFile);
if (!$lines) die("Cannot read source file");

function extractMethods($lines, $methodNames) {
    $ranges = [];
    $current = null;
    for ($i = 0; $i < count($lines); $i++) {
        if (preg_match('/^\s*(public|private|protected)\s+function\s+(\w+)\s*\(/', $lines[$i], $m)) {
            if ($current !== null) $ranges[$current]['end'] = $i - 1;
            $name = $m[2];
            if (in_array($name, $methodNames)) {
                $ranges[$name] = ['start' => $i, 'end' => null];
            }
            $current = in_array($name, $methodNames) ? $name : null;
        }
    }
    if ($current !== null) $ranges[$current]['end'] = count($lines) - 1;
    uasort($ranges, fn($a, $b) => $a['start'] <=> $b['start']);
    return $ranges;
}

function writeController($targetFile, $ranges, $lines, $uses, $constructor) {
    $h = "<?php\n\nnamespace App\Http\Controllers;\n\n";
    foreach ($uses as $u) $h .= "use $u;\n";
    $h .= "\nclass $targetFile extends Controller\n{\n    public function __construct(\n";
    foreach ($constructor as $c) $h .= "        protected $c,\n";
    $h = rtrim($h, ",\n") . "\n    ) {}\n\n";
    $h .= "    private function dadosCodigoReservaPorIdentificador(string|int \$id): ?object\n";
    $h .= "    {\n        return app(\\App\\Services\\Reserva\\ReservaIdentificadorService::class)\n            ->dadosCodigoReservaPorIdentificador(\$id);\n    }\n\n";
    file_put_contents($targetFile, $h);

    foreach ($ranges as $name => $r) {
        file_put_contents($targetFile, "\n", FILE_APPEND);
        for ($i = $r['start']; $i <= $r['end']; $i++) {
            file_put_contents($targetFile, rtrim($lines[$i]) . "\n", FILE_APPEND);
        }
    }
    file_put_contents($targetFile, "}\n", FILE_APPEND);
    echo "Written $targetFile\n";
}

// Checkin
$rangesCheckin = extractMethods($lines, $methodsCheckin);
$checkinUses = [
    'App\\DTO\\BreadCrumbItem',
    'App\\Http\\Requests\\Reserva\\CriarContratoCheckInRequest',
    'App\\Models\\ContratoCheckin',
    'App\\Models\\Reserva',
    'App\\Services\\BreadcrumbService',
    'App\\Services\\ContratoCheckinService',
    'App\\Services\\MovimentacaoService',
    'App\\Services\\ReservaService',
    'App\\Services\\UpgradeVeiculoService',
    'App\\Services\\VeiculoService',
    'App\\Services\\Reserva\\ReservaIdentificadorService',
    'Carbon\\Carbon',
    'Illuminate\\Http\\Request',
    'Illuminate\\Support\\Facades\\Auth',
    'Illuminate\\Support\\Facades\\DB',
    'Illuminate\\Support\\Facades\\Storage',
    'App\\Services\\CalcaoService',
];
$checkinConstructor = [
    '\\App\\Services\\ReservaService \$reservaService',
    '\\App\\Services\\BreadcrumbService \$breadcrumbService',
    '\\App\\Services\\ContratoCheckinService \$contratoCheckinService',
    '\\App\\Services\\MovimentacaoService \$movimentacaoService',
    '\\App\\Services\\VeiculoService \$veiculoService',
    '\\App\\Services\\UpgradeVeiculoService \$upgradeVeiculoService',
    '\\App\\Services\\CalcaoService \$calcaoService',
    '\\App\\Services\\Reserva\\ReservaIdentificadorService \$identificadorService',
];
writeController('app/Http/Controllers/ReservaCheckinController.php', $rangesCheckin, $lines, $checkinUses, $checkinConstructor);

// Checkout
$rangesCheckout = extractMethods($lines, $methodsCheckout);
$checkoutUses = [
    'App\\Http\\Requests\\Reserva\\AdicionarDanoVeiculoRequest',
    'App\\Http\\Requests\\Reserva\\FinalizarCheckoutVistoriaRequest',
    'App\\Models\\ContratoCheckout',
    'App\\Services\\BreadcrumbService',
    'App\\Services\\ReservaService',
    'App\\Services\\VeiculoDanoService',
    'App\\Services\\Reserva\\ReservaIdentificadorService',
    'Illuminate\\Http\\Request',
    'Illuminate\\Support\\Facades\\Auth',
    'Illuminate\\Support\\Facades\\DB',
    'Illuminate\\Support\\Facades\\Storage',
    'App\\Services\\CalcaoService',
    'App\\Services\\MovimentacaoService',
];
$checkoutConstructor = [
    '\\App\\Services\\ReservaService \$reservaService',
    '\\App\\Services\\BreadcrumbService \$breadcrumbService',
    '\\App\\Services\\VeiculoDanoService \$veiculoDanoService',
    '\\App\\Services\\CalcaoService \$calcaoService',
    '\\App\\Services\\MovimentacaoService \$movimentacaoService',
    '\\App\\Services\\Reserva\\ReservaIdentificadorService \$identificadorService',
];
writeController('app/Http/Controllers/ReservaCheckoutController.php', $rangesCheckout, $lines, $checkoutUses, $checkoutConstructor);

// Update routes
$routeFile = 'routes/grupos/reserva.php';
$routeContent = file_get_contents($routeFile);

// Change checkin route controller references
$routeContent = preg_replace(
    "/Route::get\('\{codigo\}\/checkin',\s*\[ReservaController::class,\s*'checkin'\]\)/",
    "Route::get('{codigo}/checkin', [ReservaCheckinController::class, 'checkin'])",
    $routeContent
);

$routeContent = preg_replace(
    "/Route::get\('\{codigo\}\/checkin\/veiculos-disponiveis',\s*\[ReservaController::class,\s*'veiculosDisponiveisCheckin'\]\)/",
    "Route::get('{codigo}/checkin/veiculos-disponiveis', [ReservaCheckinController::class, 'veiculosDisponiveisCheckin'])",
    $routeContent
);

$routeContent = preg_replace(
    "/Route::get\('\{codigo\}\/checkin\/veiculos-modal',\s*\[ReservaController::class,\s*'veiculosModalCheckin'\]\)/",
    "Route::get('{codigo}/checkin/veiculos-modal', [ReservaCheckinController::class, 'veiculosModalCheckin'])",
    $routeContent
);

$routeContent = preg_replace(
    "/Route::get\('\{codigo\}\/checkin\/diagnostico',\s*\[ReservaController::class,\s*'diagnosticoDisponibilidadeCheckin'\]\)/",
    "Route::get('{codigo}/checkin/diagnostico', [ReservaCheckinController::class, 'diagnosticoDisponibilidadeCheckin'])",
    $routeContent
);

$routeContent = preg_replace(
    "/Route::post\('\{codigo\}\/criarContratoCheckin',\s*\[ReservaController::class,\s*'criarContratoCheckin'\]\)/",
    "Route::post('{codigo}/criarContratoCheckin', [ReservaCheckinController::class, 'criarContratoCheckIn'])",
    $routeContent
);

$routeContent = preg_replace(
    "/Route::post\('\{id\}\/adicionarDanoVeiculo',\s*\[ReservaController::class,\s*'adicionarDanoVeiculo'\]\)/",
    "Route::post('{id}/adicionarDanoVeiculo', [ReservaCheckoutController::class, 'adicionarDanoVeiculo'])",
    $routeContent
);

$routeContent = preg_replace(
    "/Route::get\('\{id\}\/checkout',\s*\[ReservaController::class,\s*'checkoutVistoria'\]\)/",
    "Route::get('{id}/checkout', [ReservaCheckoutController::class, 'checkoutVistoria'])",
    $routeContent
);

$routeContent = preg_replace(
    "/Route::post\('\{id\}\/checkout\/finalizar',\s*\[ReservaController::class,\s*'finalizarCheckoutVistoria'\]\)/",
    "Route::post('{id}/checkout/finalizar', [ReservaCheckoutController::class, 'finalizarCheckoutVistoria'])",
    $routeContent
);

$routeContent = preg_replace(
    "/Route::get\('\{id\}\/checkin\/status-upgrade',\s*\[ReservaController::class,\s*'checkinStatusUpgrade'\]\)/",
    "Route::get('{id}/checkin/status-upgrade', [ReservaCheckinController::class, 'checkinStatusUpgrade'])",
    $routeContent
);

$routeContent = preg_replace(
    "/Route::delete\('\{id\}\/checkin\/contrato',\s*\[ReservaController::class,\s*'excluirContratoCheckin'\]\)/",
    "Route::delete('{id}/checkin/contrato', [ReservaCheckinController::class, 'excluirContratoCheckin'])",
    $routeContent
);

// Add new imports
$routeContent = str_replace(
    "use App\\Http\\Controllers\\ReservaController;\nuse App\\Http\\Controllers\\ReservaMotoristaAppController;\nuse App\\Http\\Controllers\\ReservaPagamentoController;",
    "use App\\Http\\Controllers\\ReservaController;\nuse App\\Http\\Controllers\\ReservaMotoristaAppController;\nuse App\\Http\\Controllers\\ReservaPagamentoController;\nuse App\\Http\\Controllers\\ReservaCheckinController;\nuse App\\Http\\Controllers\\ReservaCheckoutController;",
    $routeContent
);

file_put_contents($routeFile, $routeContent);
echo "Routes updated\n";
