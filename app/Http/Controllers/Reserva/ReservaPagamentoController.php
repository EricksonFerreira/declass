<?php

namespace App\Http\Controllers\Reserva;

use App\Actions\Reserva\Pagamento\Calcao\AutorizarCalcaoAction;
use App\Actions\Reserva\Pagamento\Calcao\CapturarCalcaoAction;
use App\Actions\Reserva\Pagamento\Calcao\DevolverCalcaoPresencialAction;
use App\Actions\Reserva\Pagamento\Calcao\ExibirCalcaoCheckinAction;
use App\Actions\Reserva\Pagamento\Calcao\LiberarCalcaoAction;
use App\Actions\Reserva\Pagamento\Calcao\RegistrarCalcaoPresencialAction;
use App\Actions\Reserva\Pagamento\Calcao\SalvarCartaoCalcaoAction;
use App\Actions\Reserva\Pagamento\Confirmar\Admin\ConfirmarPagamentoAdminAction;
use App\Actions\Reserva\Pagamento\Confirmar\Cliente\ConfirmarPagamentoClienteAction;
use App\Actions\Reserva\Pagamento\ConfirmarPagamentoDinheiroAction;
use App\Actions\Reserva\Pagamento\Exibir\Admin\ExibirPagamentoAdminAction;
use App\Actions\Reserva\Pagamento\Exibir\Cliente\ExibirPagamentoClienteAction;
use App\Actions\Reserva\Pagamento\ExibirFormularioPagamentoAction;
use App\Actions\Reserva\Pagamento\RealizarPagamentoAction;
use App\DTO\BreadCrumbItem;
use App\Http\Controllers\Controller;
use App\Http\Requests\Calcao\AutorizarCalcaoRequest;
use App\Http\Requests\Calcao\CapturarCalcaoRequest;
use App\Models\Reserva;
use App\Services\BreadcrumbService;
use App\Services\CalcaoService;
use App\Services\ClienteStripeService;
use App\Services\NfseService;
use App\Services\Pagamentos\GatewayEmpresaConfigService;
use App\Services\Pagamentos\GerenciadorDePagamentos;
use App\Services\Reserva\ReservaIdentificadorService;
use App\Services\ReservaService;
use App\Services\UsuarioService;
use Illuminate\Http\Request;

class ReservaPagamentoController extends Controller
{
    use ReservaHelpersTrait;

    public function __construct(
        protected ReservaService $reservaService,
        protected BreadcrumbService $breadcrumbService,
        protected CalcaoService $calcaoService,
        protected ClienteStripeService $clienteStripeService,
        protected GatewayEmpresaConfigService $gatewayEmpresaConfigService,
        protected GerenciadorDePagamentos $gerenciadorDePagamentos,
        protected UsuarioService $userService,
        protected ReservaIdentificadorService $identificadorService,
        protected NfseService $nfseService,
        protected RealizarPagamentoAction $realizarPagamentoAction,
        protected ExibirPagamentoAdminAction $exibirPagamentoAdminAction,
        protected ExibirPagamentoClienteAction $exibirPagamentoClienteAction,
        protected ConfirmarPagamentoAdminAction $confirmarPagamentoAdminAction,
        protected ConfirmarPagamentoClienteAction $confirmarPagamentoClienteAction,
        protected ExibirFormularioPagamentoAction $exibirFormularioPagamentoAction,
        protected AutorizarCalcaoAction $autorizarCalcaoAction,
        protected CapturarCalcaoAction $capturarCalcaoAction,
        protected LiberarCalcaoAction $liberarCalcaoAction,
        protected RegistrarCalcaoPresencialAction $registrarCalcaoPresencialAction,
        protected DevolverCalcaoPresencialAction $devolverCalcaoPresencialAction,
        protected ConfirmarPagamentoDinheiroAction $confirmarPagamentoDinheiroAction,
        protected ExibirCalcaoCheckinAction $exibirCalcaoCheckinAction,
        protected SalvarCartaoCalcaoAction $salvarCartaoCalcaoAction,
    ) {}

    public function efetuarPagamentoMinhas($codigo)
    {
        return $this->exibirPagamentoClienteAction->handle($codigo);
    }

    public function efetuarPagamento($codigo)
    {
        return $this->exibirPagamentoAdminAction->handle($codigo);
    }

    public function confirmarPagamentoMinhas(Request $request, $codigo)
    {
        return $this->confirmarPagamentoClienteAction->handle($codigo, $request);
    }

    public function confirmarPagamento(Request $request, $codigo)
    {
        return $this->confirmarPagamentoAdminAction->handle($codigo, $request);
    }

    public function processarPagamento(Request $request, $codigo)
    {
        $reserva = Reserva::withoutGlobalScopes()
            ->with(['opcionais', 'cupomDesconto', 'estacaoRetirada', 'estacaoDevolucao', 'transacoes'])
            ->find($codigo);

        if (! $reserva) {
            return redirect()->route('reserva.index')->with('error', 'Reserva nao encontrada.');
        }

        try {
            $resultado = $this->realizarPagamentoAction->handle($reserva, $request);

            return redirect()
                ->route('reserva.show', $codigo)
                ->with('success', 'Pagamento efetuado com sucesso')
                ->with('warning', $resultado['warning'] ?? null);
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return redirect()->route('reserva.show', $codigo)->with('error', $e->getMessage());
        }
    }

    public function calcaoCheckin(Request $request, $codigo)
    {
        $this->authorize('calcao.autorizar');

        $reserva = Reserva::withoutGlobalScopes()
            ->with(['contratoCheckin', 'grupoFrota', 'modelos', 'usuario', 'calcao', 'codigoReserva'])
            ->whereHas('codigoReserva', function ($query) use ($codigo) {
                $query->where('codigo', $codigo);
            })
            ->first();

        if (! $reserva || ! $reserva->contratoCheckin || $reserva->contratoCheckin->assinado !== 'S') {
            return redirect()->route('reserva.show', $codigo)
                ->with('error', 'O contrato de check-in deve ser assinado antes de registrar o calcao.');
        }

        $data = $this->exibirCalcaoCheckinAction->handle($reserva);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Reservas', route('reserva.index')),
            new BreadCrumbItem('Detalhes da Reserva Nº'.$codigo, route('reserva.show', $codigo)),
            new BreadCrumbItem('Calcao'),
        ]);

        return view('reserva.calcao', compact(
            'reserva', 'breadcrumb'
        ) + $data)->with('titulo', 'Calcao — Reserva Nº '.$reserva->id);
    }

    public function autorizarCalcao(AutorizarCalcaoRequest $request, $codigo)
    {
        $this->authorize('calcao.autorizar');

        $reserva = Reserva::withoutGlobalScopes()
            ->with(['contratoCheckin', 'usuario', 'calcao', 'codigoReserva'])
            ->whereHas('codigoReserva', function ($query) use ($codigo) {
                $query->where('codigo', $codigo);
            })
            ->first();

        if (! $reserva) {
            return response()->json(['error' => 'Contrato de check-in nao encontrado.'], 404);
        }

        try {
            $calcao = $this->autorizarCalcaoAction->handle($reserva, $request->input('payment_method_id'));

            return response()->json([
                'success' => true,
                'calcao_id' => $calcao->id,
                'message' => 'Calcao autorizado com sucesso. Valor pre-autorizado: R$ '.number_format((float) $calcao->valor_autorizado, 2, ',', '.'),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Falha inesperada ao autorizar calcao.'], 500);
        }
    }

    public function salvarCartaoCalcao(Request $request, $reserva_id)
    {
        $this->authorize('calcao.autorizar');

        $request->validate(['payment_method_id' => 'required|string']);

        $reserva = $this->reservaService->getById($reserva_id, ['usuario']);
        if (! $reserva || ! $reserva->usuario) {
            return response()->json(['error' => 'Reserva ou cliente não encontrado.'], 404);
        }

        try {
            $pm = $this->salvarCartaoCalcaoAction->handle($reserva, $request->input('payment_method_id'));

            return response()->json([
                'success' => true,
                'pm' => $pm,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Não foi possível salvar o cartão: '.$e->getMessage()], 422);
        }
    }

    public function capturarCalcao(CapturarCalcaoRequest $request, $reserva_id)
    {
        $this->authorize('calcao.capturar');

        $reserva = Reserva::withoutGlobalScopes()
            ->with('transacoes')
            ->find($reserva_id);

        if (! $reserva) {
            return response()->json(['error' => 'Reserva nao encontrada.'], 404);
        }

        try {
            $calcao = $this->capturarCalcaoAction->handle(
                $reserva,
                (float) $request->input('valor_captura'),
                $request->input('justificativa')
            );

            return response()->json([
                'success' => true,
                'message' => 'Calcao capturado com sucesso.',
                'calcao' => $calcao->only(['id', 'status', 'valor_capturado']),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function liberarCalcao(Request $request, $reserva_id)
    {
        $this->authorize('calcao.cancelar');

        $request->validate(['observacao' => 'nullable|string|max:500']);

        $reserva = $this->reservaService->getById($reserva_id);
        if (! $reserva) {
            return response()->json(['error' => 'Reserva nao encontrada.'], 404);
        }

        try {
            $this->liberarCalcaoAction->handle($reserva, $request->input('observacao', ''));

            return response()->json(['success' => true, 'message' => 'Calcao liberado com sucesso.']);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function registrarCalcaoPresencial(Request $request, $reserva_id)
    {
        $this->authorize('calcao.autorizar');

        $request->validate([
            'metodo' => 'required|string|max:50',
            'valor' => 'nullable|numeric|min:0.01',
            'observacao' => 'nullable|string|max:500',
        ]);

        $reserva = $this->reservaService->getById((int) $reserva_id, ['contratoCheckin', 'usuario', 'calcao']);
        if (! $reserva) {
            return response()->json(['error' => 'Reserva nao encontrada.'], 404);
        }

        try {
            $calcao = $this->registrarCalcaoPresencialAction->handle(
                $reserva,
                trim((string) $request->input('metodo')),
                $request->input('valor') ? (float) $request->input('valor') : null,
                $request->input('observacao')
            );

            return response()->json([
                'success' => true,
                'message' => 'Calção presencial registrado com sucesso.',
                'calcao' => $calcao->only(['id', 'status', 'valor_calculado', 'valor_capturado']),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Falha ao registrar calção presencial: '.$e->getMessage()], 500);
        }
    }

    public function devolverCalcaoPresencial(Request $request, $reserva_id)
    {
        $this->authorize('calcao.cancelar');

        $request->validate([
            'observacao' => 'nullable|string|max:500',
        ]);

        $reserva = $this->reservaService->getById((int) $reserva_id, ['calcao']);
        if (! $reserva) {
            return response()->json(['error' => 'Reserva nao encontrada.'], 404);
        }

        try {
            $this->devolverCalcaoPresencialAction->handle($reserva, $request->input('observacao'));

            return response()->json(['success' => true, 'message' => 'Calção devolvido com sucesso.']);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Falha ao devolver calção: '.$e->getMessage()], 500);
        }
    }

    public function confirmarPagamentoDinheiro(Request $request, $reserva_id)
    {
        $reserva = Reserva::withoutGlobalScopes()
            ->with('transacoes')
            ->find($reserva_id);

        if (! $reserva) {
            return response()->json(['error' => 'Reserva nao encontrada.'], 404);
        }

        try {
            $this->confirmarPagamentoDinheiroAction->handle(
                $reserva,
                $request->input('transacao_id') ? (int) $request->input('transacao_id') : null,
                (float) ($request->input('valor', 0)),
                strtolower((string) $request->input('metodo_pagamento', 'dinheiro'))
            );

            return response()->json(['success' => true, 'message' => 'Pagamento em dinheiro registrado com sucesso.']);
        } catch (\InvalidArgumentException|\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Erro ao confirmar pagamento em dinheiro.'], 500);
        }
    }
}
