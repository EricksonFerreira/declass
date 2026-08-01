<?php

namespace App\Http\Controllers\Reserva;

use App\Actions\Reserva\Checkin\Contrato\Assinar\AssinarContratoCheckinAction;
use App\Actions\Reserva\Checkin\Contrato\Excluir\ExcluirContratoCheckinAction;
use App\Actions\Reserva\Checkin\Contrato\Exibir\ExibirContratoCheckinAction;
use App\Actions\Reserva\Checkin\Diagnostico\DiagnosticoDisponibilidadeCheckinAction;
use App\Actions\Reserva\Checkin\Exibir\ExibirCheckinAction;
use App\Actions\Reserva\Checkin\Finalizar\FinalizarCheckinAction;
use App\Actions\Reserva\Checkin\FinalizarMinhaReserva\FinalizarMinhaReservaCheckinAction;
use App\Actions\Reserva\Checkin\Realizar\RealizarCheckinAction;
use App\Actions\Reserva\Checkin\Upgrade\CheckinStatusUpgradeAction;
use App\Actions\Reserva\Checkin\Veiculos\VeiculosDisponiveisCheckinAction;
use App\Actions\Reserva\Checkin\Veiculos\VeiculosModalCheckinAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reserva\AssinarContratoRequest;
use App\Http\Requests\Reserva\CriarContratoCheckInRequest;
use App\Services\BreadcrumbService;
use App\Services\CategoriaDanoService;
use App\Services\ContratoCheckinService;
use App\Services\ModeloService;
use App\Services\Reserva\ReservaResolver;
use App\Services\ReservaService;
use App\Services\UpgradeVeiculoService;
use App\Services\VeiculoService;
use Illuminate\Http\Request;

class ReservaCheckinController extends Controller
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
        protected ExibirCheckinAction $exibirCheckinAction,
        protected RealizarCheckinAction $realizarCheckinAction,
        protected FinalizarCheckinAction $finalizarCheckinAction,
        protected FinalizarMinhaReservaCheckinAction $finalizarMinhaReservaCheckinAction,
        protected ExibirContratoCheckinAction $exibirContratoCheckinAction,
    ) {}

    public function checkin(Request $request, $codigo)
    {
        try {
            return $this->exibirCheckinAction->handle($codigo, $request);
        } catch (\Throwable $e) {
            return redirect()
                ->route('reserva.index')
                ->with('error', $e->getMessage());
        }
    }

    public function criarContratoCheckIn(CriarContratoCheckInRequest $request, $codigo)
    {
        try {
            return $this->realizarCheckinAction->handle($codigo, $request);
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao realizar check-in: ' . $e->getMessage());
        }
    }

    public function veiculosDisponiveisCheckin(Request $request, $codigo, VeiculosDisponiveisCheckinAction $action)
    {
        return $action->handle($request, $codigo);
    }

    public function veiculosModalCheckin(Request $request, $codigo, VeiculosModalCheckinAction $action)
    {
        return $action->handle($request, $codigo);
    }

    public function diagnosticoDisponibilidadeCheckin(Request $request, $codigo, DiagnosticoDisponibilidadeCheckinAction $action)
    {
        return $action->handle($request, $codigo);
    }

    public function checkinStatusUpgrade(Request $request, $id, CheckinStatusUpgradeAction $action)
    {
        return $action->handle($request, $id);
    }

    public function excluirContratoCheckin(Request $request, $id, ExcluirContratoCheckinAction $action)
    {
        return $action->handle($request, $id);
    }

    // Ações de contrato de check-in
    public function assinarContratoCheckin(Request $request, $codigo, ReservaResolver $reservaResolver, AssinarContratoCheckinAction $action)
    {
        return $action->handle($request, $codigo, $reservaResolver);
    }

    public function salvarAssinarContratoCheckIn(AssinarContratoRequest $request, $codigo)
    {
        try {
            return $this->finalizarCheckinAction->handle($codigo, $request);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Falha ao salvar assinatura: ' . $e->getMessage()], 500);
        }
    }

    public function verContratoCheckin(Request $request, $codigo, ReservaResolver $reservaResolver)
    {
        try {
            $pdfContent = $this->exibirContratoCheckinAction->handle($codigo);

            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="contrato-checkin-' . $codigo . '.pdf"',
            ]);
        } catch (\Throwable $e) {
            return redirect()
                ->route('reserva.index')
                ->with('error', $e->getMessage());
        }
    }

    // Minha Reserva
    public function assinarCheckinMinhaReserva(Request $request, $codigo, ReservaResolver $reservaResolver, \App\Actions\Reserva\Checkin\Contrato\Assinar\AssinarCheckinMinhaReservaAction $action)
    {
        return $action->handle($request, $codigo, $reservaResolver);
    }


    public function salvarAssinarContratoCheckInMinhaReserva(AssinarContratoRequest $request, $codigo, ReservaResolver $reservaResolver)
    {
        try {
            return $this->finalizarMinhaReservaCheckinAction->handle($codigo, $request);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Falha ao salvar assinatura: ' . $e->getMessage()], 500);
        }
    }


    public function verContratoCheckinMinhaReserva(Request $request, $codigo, ReservaResolver $reservaResolver, \App\Actions\Reserva\Checkin\Contrato\Exibir\VerContratoCheckinMinhaReservaAction $action)
    {
        return $action->handle($request, $codigo, $reservaResolver);
    }
}
