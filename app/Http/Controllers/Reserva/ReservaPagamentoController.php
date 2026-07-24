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
use App\Enums\StatusReservaEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Calcao\AutorizarCalcaoRequest;
use App\Http\Requests\Calcao\CapturarCalcaoRequest;
use App\Models\Reserva;
use App\Models\ReservaMotoristaApp;
use App\Services\BreadcrumbService;
use App\Services\CalcaoService;
use App\Services\ClienteStripeService;
use App\Services\NfseService;
use App\Services\Pagamentos\GatewayEmpresaConfigService;
use App\Services\Pagamentos\GerenciadorDePagamentos;
use App\Services\Reserva\ReservaIdentificadorService;
use App\Services\ReservaCodigoService;
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
        protected ReservaCodigoService $reservaCodigoService,
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

        $reserva = $this->resolverReservaCalcao($codigo, true);

        if (! $reserva) {
            return redirect()->route('reserva.show', $codigo)
                ->with('error', 'Reserva nao encontrada.');
        }

        if (! $this->ehReservaOperacionalMotoristaApp($reserva) && (! $reserva->contratoCheckin || $reserva->contratoCheckin->assinado !== 'S')) {
            return redirect()->route('reserva.show', $codigo)
                ->with('error', 'O contrato de check-in deve ser assinado antes de registrar o calcao.');
        }

        $data = $this->exibirCalcaoCheckinAction->handle($reserva);
        $this->sincronizarStatusCalcaoMotoristaApp($codigo, $reserva);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Reservas', route('reserva.index')),
            new BreadCrumbItem('Detalhes da Reserva Nº'.$codigo, route('reserva.show', $codigo)),
            new BreadCrumbItem('Calcao'),
        ]);

        $voltarUrl = $this->urlVoltarCalcao($reserva, $codigo);

        return view('reserva.calcao', compact(
            'reserva', 'breadcrumb', 'voltarUrl'
        ) + $data)->with('titulo', 'Calcao — Reserva Nº '.$reserva->id);
    }

    public function autorizarCalcao(AutorizarCalcaoRequest $request, $codigo)
    {
        $this->authorize('calcao.autorizar');

        $reserva = $this->resolverReservaCalcao($codigo, true);

        if (! $reserva) {
            return response()->json(['error' => 'Contrato de check-in nao encontrado.'], 404);
        }

        try {
            $calcao = $this->autorizarCalcaoAction->handle($reserva, $request->input('payment_method_id'));

            $this->sincronizarStatusCalcaoMotoristaApp($codigo, $reserva);

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

    public function salvarCartaoCalcao(Request $request, $codigo)
    {
        $this->authorize('calcao.autorizar');

        $request->validate(['payment_method_id' => 'required|string']);

        $reserva = $this->resolverReservaCalcao($codigo, true);
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

    public function capturarCalcao(CapturarCalcaoRequest $request, $codigo)
    {
        $this->authorize('calcao.capturar');

        $reserva = $this->resolverReservaCalcao($codigo);

        if (! $reserva) {
            return response()->json(['error' => 'Reserva nao encontrada.'], 404);
        }

        try {
            $calcao = $this->capturarCalcaoAction->handle(
                $reserva,
                (float) $request->input('valor_captura'),
                $request->input('justificativa')
            );

            $this->sincronizarStatusCalcaoMotoristaApp($codigo, $reserva);

            return response()->json([
                'success' => true,
                'message' => 'Calcao capturado com sucesso.',
                'calcao' => $calcao->only(['id', 'status', 'valor_capturado']),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function liberarCalcao(Request $request, $codigo)
    {
        $this->authorize('calcao.cancelar');

        $request->validate(['observacao' => 'nullable|string|max:500']);

        $reserva = $this->resolverReservaCalcao($codigo);
        if (! $reserva) {
            return response()->json(['error' => 'Reserva nao encontrada.'], 404);
        }

        try {
            $this->liberarCalcaoAction->handle($reserva, $request->input('observacao', ''));

            $reserva->load('calcao');
            $this->sincronizarStatusCalcaoMotoristaApp($codigo, $reserva);

            return response()->json(['success' => true, 'message' => 'Calcao liberado com sucesso.']);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function registrarCalcaoPresencial(Request $request, $codigo)
    {
        $this->authorize('calcao.autorizar');

        $request->validate([
            'metodo' => 'required|string|max:50',
            'valor' => 'nullable|numeric|min:0.01',
            'observacao' => 'nullable|string|max:500',
        ]);

        $reserva = $this->resolverReservaCalcao($codigo, true);
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

            $this->sincronizarStatusCalcaoMotoristaApp($codigo, $reserva);

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

    public function devolverCalcaoPresencial(Request $request, $codigo)
    {
        $this->authorize('calcao.cancelar');

        $request->validate([
            'observacao' => 'nullable|string|max:500',
        ]);

        $reserva = $this->resolverReservaCalcao($codigo);
        if (! $reserva) {
            return response()->json(['error' => 'Reserva nao encontrada.'], 404);
        }

        try {
            $this->devolverCalcaoPresencialAction->handle($reserva, $request->input('observacao'));

            $reserva->load('calcao');
            $this->sincronizarStatusCalcaoMotoristaApp($codigo, $reserva);

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

    private function resolverReservaCalcao(string|int $codigo, bool $criarMotoristaApp = false): ?Reserva
    {
        $codigoReserva = $this->dadosCodigoReservaPorIdentificador($codigo);

        if ($codigoReserva && (string) $codigoReserva->tipo_origem === 'motorista_app') {
            $registro = ReservaMotoristaApp::withoutGlobalScopes()
                ->with(['codigoReserva', 'user', 'veiculo', 'planoMotoristaApp'])
                ->find((int) ($codigoReserva->reserva_motorista_app_id ?? 0));

            return $registro && $criarMotoristaApp
                ? $this->garantirReservaOperacionalCalcaoMotoristaApp($registro)
                : $this->reservaOperacionalMotoristaApp($registro);
        }

        $reservaId = $this->idReservaPorIdentificador($codigo, 'RES', $codigoReserva, 'reserva_id');

        return Reserva::withoutGlobalScopes()
            ->with(['contratoCheckin', 'grupoFrota', 'modelos', 'usuario', 'calcao', 'codigoReserva', 'transacoes'])
            ->find($reservaId);
    }

    private function garantirReservaOperacionalCalcaoMotoristaApp(ReservaMotoristaApp $registro): Reserva
    {
        if ($reserva = $this->reservaOperacionalMotoristaApp($registro)) {
            return $reserva;
        }

        $valorCalcao = round((float) ($registro->valor_caucao ?? $registro->planoMotoristaApp?->calcao ?? 0), 2);

        $reserva = Reserva::create([
            'empresa_id' => $registro->empresa_id,
            'user_id' => $registro->user_id,
            'origem_cadastro' => 'motorista_app',
            'veiculo_id' => $registro->veiculo_id,
            'modelo_id' => $registro->veiculo?->modelo_id,
            'grupo_frota_id' => $registro->veiculo?->grupo_frota_id,
            'estacao_retirada_id' => $registro->estacao_retirada_id,
            'estacao_devolucao_id' => $registro->estacao_retirada_id,
            'data_inicio' => $registro->data_inicio,
            'data_fim' => $registro->data_fim,
            'status_reserva_id' => StatusReservaEnum::Confirmada->value,
            'data_criacao' => now(),
            'pagamento_efetuado' => true,
            'quantidade_dias' => $registro->quantidade_dias,
            'tarifa_base' => $valorCalcao,
            'valor_total_tarifa_base' => $valorCalcao,
            'valor_total_com_desconto' => $valorCalcao,
            'valor_opcionais' => 0,
            'valor_total_final' => $valorCalcao,
            'observacoes' => 'Reserva operacional criada para caução da reserva motorista app '.$registro->codigoReserva?->codigo,
        ]);

        $codigoOperacional = $this->reservaCodigoService->sincronizar($reserva, 'reserva', [
            'empresa_id' => $registro->empresa_id,
            'status_label' => 'Confirmada',
            'data_inicio' => $registro->data_inicio,
            'data_fim' => $registro->data_fim,
            'valor_total' => $valorCalcao,
        ]);

        $documentos = is_array($registro->documentos_json ?? null) ? $registro->documentos_json : [];
        data_set($documentos, 'checkin.reserva_operacional_id', (int) $reserva->id);
        data_set($documentos, 'checkin.reserva_operacional_codigo', (string) $codigoOperacional->codigo);
        $registro->forceFill(['documentos_json' => $documentos])->save();

        return $reserva->load(['contratoCheckin', 'grupoFrota', 'modelos', 'usuario', 'calcao', 'codigoReserva', 'transacoes']);
    }

    private function reservaOperacionalMotoristaApp(ReservaMotoristaApp $registro): ?Reserva
    {
        $documentos = is_array($registro->documentos_json ?? null) ? $registro->documentos_json : [];
        $reservaId = (int) data_get($documentos, 'checkin.reserva_operacional_id', 0);

        if ($reservaId <= 0) {
            $codigo = (string) ($registro->codigoReserva?->codigo ?? '');
            $reservaId = $codigo !== ''
                ? (int) Reserva::withoutGlobalScopes()
                    ->where('user_id', (int) $registro->user_id)
                    ->where('empresa_id', (int) $registro->empresa_id)
                    ->where('observacoes', 'like', '%' . $codigo . '%')
                    ->whereHas('calcao')
                    ->latest('id')
                    ->value('id')
                : 0;

            if ($reservaId <= 0) {
                return null;
            }

            $codigoOperacional = $this->codigoReservaOperacionalPorId($reservaId);
            data_set($documentos, 'checkin.reserva_operacional_id', $reservaId);
            data_set($documentos, 'checkin.reserva_operacional_codigo', $codigoOperacional);
            $registro->forceFill(['documentos_json' => $documentos])->save();
        }

        return Reserva::withoutGlobalScopes()
            ->with(['contratoCheckin', 'grupoFrota', 'modelos', 'usuario', 'calcao', 'codigoReserva', 'transacoes'])
            ->find($reservaId);
    }

    private function ehReservaOperacionalMotoristaApp(Reserva $reserva): bool
    {
        return (string) ($reserva->origem_cadastro ?? '') === 'motorista_app';
    }

    private function sincronizarStatusCalcaoMotoristaApp(string|int $codigo, Reserva $reserva): void
    {
        $codigoReserva = $this->dadosCodigoReservaPorIdentificador($codigo);
        if (! $codigoReserva || (string) $codigoReserva->tipo_origem !== 'motorista_app') {
            return;
        }

        $calcao = $reserva->calcao;
        if (! $calcao || ! in_array((string) $calcao->status, [
            \App\Models\Calcao::STATUS_AUTORIZADO,
            \App\Models\Calcao::STATUS_PARCIALMENTE_CAPTURADO,
            \App\Models\Calcao::STATUS_CAPTURADO,
            \App\Models\Calcao::STATUS_CANCELADO,
        ], true)) {
            return;
        }

        $registro = ReservaMotoristaApp::withoutGlobalScopes()
            ->with('codigoReserva')
            ->find((int) ($codigoReserva->reserva_motorista_app_id ?? 0));

        if (! $registro) {
            return;
        }

        if ((string) ($reserva->origem_cadastro ?? '') !== 'motorista_app') {
            $reserva->forceFill(['origem_cadastro' => 'motorista_app'])->save();
        }

        $codigoOperacional = $reserva->codigoReserva?->codigo
            ?? $this->codigoReservaOperacionalPorId((int) $reserva->id);

        $documentos = is_array($registro->documentos_json ?? null) ? $registro->documentos_json : [];
        data_set($documentos, 'checkin.reserva_operacional_id', (int) $reserva->id);
        data_set($documentos, 'checkin.reserva_operacional_codigo', $codigoOperacional);
        data_set($documentos, 'calcao.id', (int) $calcao->id);
        data_set($documentos, 'calcao.status', (string) $calcao->status);
        data_set($documentos, 'calcao.valor_autorizado', (float) ($calcao->valor_autorizado ?? 0));
        data_set($documentos, 'calcao.valor_capturado', (float) ($calcao->valor_capturado ?? 0));
        data_set($documentos, 'calcao.atualizado_em', now()->toDateTimeString());

        $registro->forceFill(['documentos_json' => $documentos])->save();
    }

    private function urlVoltarCalcao(Reserva $reserva, string|int $codigoOriginal): string
    {
        if (! $this->ehReservaOperacionalMotoristaApp($reserva)) {
            return route('reserva.show', $reserva->codigoReserva?->codigo ?? $reserva->id);
        }

        $codigoMotoristaApp = $this->dadosCodigoReservaPorIdentificador($codigoOriginal)?->codigo;

        return route('reserva.show', $codigoMotoristaApp ?: $codigoOriginal);
    }
}
