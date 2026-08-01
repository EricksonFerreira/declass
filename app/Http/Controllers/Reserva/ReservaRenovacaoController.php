<?php

namespace App\Http\Controllers\Reserva;

use App\Actions\Reserva\Renovacao\Executa\ExecutaRenovacaoActionAction;
use App\Actions\Reserva\Renovacao\Exibir\ExibirRenovacaoActionAction;
use App\Http\Controllers\Controller;
use App\Services\BreadcrumbService;
use App\Services\CategoriaDanoService;
use App\Services\ContratoCheckinService;
use App\Services\ModeloService;
use App\Services\ReservaService;
use App\Services\UpgradeVeiculoService;
use App\Services\VeiculoService;
use Illuminate\Http\Request;

class ReservaRenovacaoController extends Controller
{
    use ReservaHelpersTrait;

    public function __construct(
        protected ReservaService $reservaService,
        protected BreadcrumbService $breadcrumbService,
        protected ContratoCheckinService $contratoCheckinService,
        protected VeiculoService $veiculoService,
        protected ModeloService $modeloService,
        protected CategoriaDanoService $categoriaDanoService,
        protected UpgradeVeiculoService $upgradeVeiculoService,
        protected ExecutaRenovacaoActionAction $executaRenovacaoAction,
        protected ExibirRenovacaoActionAction $exibirenovacaoAction,
    ) {}

    public function exibir(Request $request, $codigo)
    {
        try {
            return $this->executaRenovacaoAction->handle($codigo, $request);
        } catch (\Throwable $e) {
            return redirect()
                ->route('reserva.index')
                ->with('error', $e->getMessage());
        }
    }

    public function executar(Request $request, $codigo)
    {
        try {
            return $this->executaRenovacaoAction->handle($codigo, $request);
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao renovar reserva: '.$e->getMessage());
        }
    }
}
