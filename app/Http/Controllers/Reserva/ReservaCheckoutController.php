<?php

namespace App\Http\Controllers\Reserva;

use App\Actions\Reserva\Checkout\Dano\Adicionar\AdicionarDanoVeiculoAction;
use App\Actions\Reserva\Checkout\Exibir\ExibirCheckoutAction;
use App\Actions\Reserva\Checkout\Realizar\RealizarCheckoutAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reserva\AdicionarDanoVeiculoRequest;
use App\Http\Requests\Reserva\FinalizarCheckoutVistoriaRequest;
use App\Services\BreadcrumbService;
use App\Services\CategoriaDanoService;
use App\Services\ReservaService;
use App\Actions\Reserva\Checkout\Contrato\AssinarContratoCheckoutAction;
use App\Actions\Reserva\Checkout\Contrato\SalvarAssinarContratoCheckoutAction;
use App\Services\Reserva\ReservaResolver;
use Illuminate\Http\Request;

class ReservaCheckoutController extends Controller
{
    use ReservaHelpersTrait;

    public function __construct(
        protected ReservaService $reservaService,
        protected BreadcrumbService $breadcrumbService,
        protected CategoriaDanoService $categoriaDanoService,
        protected ExibirCheckoutAction $exibirCheckoutAction,
        protected RealizarCheckoutAction $realizarCheckoutAction,
        protected AdicionarDanoVeiculoAction $adicionarDanoVeiculoAction
    ) {}

    public function checkoutVistoria(Request $request, $codigo)
    {
        try {
            return $this->exibirCheckoutAction->handle($codigo, $request);
        } catch (\Throwable $e) {
            return redirect()
                ->route('reserva.show', $codigo)
                ->with('error', $e->getMessage());
        }
    }

    public function finalizarCheckoutVistoria(FinalizarCheckoutVistoriaRequest $request, $codigo)
    {
        try {
            $this->realizarCheckoutAction->handle($codigo, $request->validated(), $request);

            return response()->json([
                'success' => true,
                'message' => 'Check-out concluído e reserva finalizada.',
                'redirect' => route('reserva.show', $codigo),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao finalizar check-out: '.$e->getMessage(),
            ], 422);
        }
    }

    public function adicionarDanoVeiculo(AdicionarDanoVeiculoRequest $request, $codigo)
    {
        try {
            $dano = $this->adicionarDanoVeiculoAction->handle($request->validated(), $codigo, $request);

            return response()->json($dano);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Erro ao salvar dano: '.$e->getMessage()], 500);
        }
    }

    public function assinarContratoCheckout(Request $request, $codigo, ReservaResolver $reservaResolver, AssinarContratoCheckoutAction $action)
    {
        return $action->handle($request, $codigo, $reservaResolver);
    }

    public function salvarAssinarContratoCheckout(Request $request, $codigo, ReservaResolver $reservaResolver, SalvarAssinarContratoCheckoutAction $action)
    {
        return $action->handle($request, $codigo, $reservaResolver);
    }

    public function verContratoCheckout(Request $request, $codigo, \App\Services\Reserva\ReservaResolver $reservaResolver)
    {
        try {
            $reservaResolvida = $reservaResolver->resolver($codigo);

            // Exibir/Gerar PDF de devolução
            $exibirAction = app(\App\Actions\Reserva\Checkout\TermoDevolucao\Exibir\ExibirTermoDevolucaoAction::class);
            return $exibirAction->handle($codigo);
        } catch (\Throwable $e) {
            return redirect()
                ->route('reserva.index')
                ->with('error', $e->getMessage());
        }
    }
}
