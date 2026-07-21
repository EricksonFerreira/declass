<?php

$sourceFile = 'app/Http/Controllers/ReservaController.php';
$targetFile = 'app/Http/Controllers/ReservaPagamentoController.php';

$lines = file($sourceFile);
if (!$lines) die("Cannot read source file");

$methodsToExtract = [
    'efetuarPagamento',
    'processarPagamento',
    'confirmarPagamentoDinheiro',
    'calcaoCheckin',
    'autorizarCalcao',
    'capturarCalcao',
    'liberarCalcao',
    'salvarCartaoCalcao',
    'registrarCalcaoPresencial',
    'devolverCalcaoPresencial',
];

$methodRanges = [];
$currentMethod = null;

for ($i = 0; $i < count($lines); $i++) {
    if (preg_match('/^\s*(public|private|protected)\s+function\s+(\w+)\s*\(/', $lines[$i], $m)) {
        if ($currentMethod !== null) {
            $methodRanges[$currentMethod]['end'] = $i - 1;
        }
        $methodName = $m[2];
        if (in_array($methodName, $methodsToExtract)) {
            $methodRanges[$methodName] = ['start' => $i, 'end' => null];
            echo "Found: $methodName at line " . ($i + 1) . "\n";
        }
        $currentMethod = in_array($methodName, $methodsToExtract) ? $methodName : null;
    }
}
if ($currentMethod !== null) {
    $methodRanges[$currentMethod]['end'] = count($lines) - 1;
}

uasort($methodRanges, fn($a, $b) => $a['start'] <=> $b['start']);

$header = <<<'HEADER'
<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use App\Models\Calcao;
use App\Models\Transacao;
use App\Services\BreadcrumbService;
use App\Services\CalcaoService;
use App\Services\ClienteStripeService;
use App\Services\Pagamentos\GatewayEmpresaConfigService;
use App\Services\Pagamentos\GerenciadorDePagamentos;
use App\Services\Reserva\ReservaIdentificadorService;
use App\Services\ReservaService;
use App\Services\UsuarioService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Enums\StatusReservaEnum;

class ReservaPagamentoController extends Controller
{
    public function __construct(
        protected ReservaService $reservaService,
        protected BreadcrumbService $breadcrumbService,
        protected CalcaoService $calcaoService,
        protected ClienteStripeService $clienteStripeService,
        protected GatewayEmpresaConfigService $gatewayEmpresaConfigService,
        protected GerenciadorDePagamentos $gerenciadorDePagamentos,
        protected UsuarioService $userService,
        protected ReservaIdentificadorService $identificadorService,
    ) {}

    private function dadosCodigoReservaPorIdentificador(string|int $id): ?object
    {
        return $this->identificadorService->dadosCodigoReservaPorIdentificador($id);
    }

    private function idReservaPorIdentificador(string|int $id, string $prefixo, ?object $codigo, string $campo): string|int
    {
        return $this->identificadorService->idReservaPorIdentificador($id, $prefixo, $codigo, $campo);
    }

    // ── Metodos extraidos do ReservaController ──

HEADER;

file_put_contents($targetFile, $header);

foreach ($methodRanges as $methodName => $range) {
    file_put_contents($targetFile, "\n", FILE_APPEND);
    for ($i = $range['start']; $i <= $range['end']; $i++) {
        file_put_contents($targetFile, rtrim($lines[$i]) . "\n", FILE_APPEND);
    }
}

file_put_contents($targetFile, "}\n", FILE_APPEND);

echo "\nWritten to $targetFile\n";
