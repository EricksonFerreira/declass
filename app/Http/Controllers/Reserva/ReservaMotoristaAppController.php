<?php

namespace App\Http\Controllers\Reserva;

use App\Actions\Reserva\Pagamento\Exibir\Admin\ExibirPagamentoAdminMotoristaAppAction;
use App\Actions\Reserva\Pagamento\Exibir\Cliente\ExibirPagamentoClienteAction;
use App\Actions\Reserva\Pagamento\Exibir\Cliente\ExibirPagamentoClienteMotoristaAppAction;
use App\Actions\Reserva\Pagamento\Exibir\Cliente\ExibirPagamentoClienteReservaComumAction;
use App\Actions\ReservaMotoristaApp\AceitarTermoMotoristaAppAction;
use App\Actions\ReservaMotoristaApp\AssinarContratoMotoristaAppAction;
use App\Actions\ReservaMotoristaApp\CancelarMotoristaAppAction;
use App\Actions\ReservaMotoristaApp\ConfirmarPagamentoMotoristaAppAction;
use App\Actions\ReservaMotoristaApp\EfetivarMotoristaAppAction;
use App\Actions\ReservaMotoristaApp\ExibirContratoMotoristaAppAction;
use App\Actions\ReservaMotoristaApp\RenovarMotoristaAppAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\MotoristaApp\EfetivarMotoristaAppRequest;
use App\Models\ReservaMotoristaApp;
use App\Models\ReservaMotoristaAppPagamento;
use App\Services\BreadcrumbService;
use App\Services\ContratoCheckinService;
use App\Services\PdfService;
use App\Services\Reserva\ReservaIdentificadorService;
use App\Services\ReservaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReservaMotoristaAppController extends Controller
{
    use ReservaHelpersTrait;

    public function __construct(
        protected ReservaService $reservaService,
        protected BreadcrumbService $breadcrumbService,
        protected ContratoCheckinService $contratoCheckinService,
        protected PdfService $pdfService,
        protected ReservaIdentificadorService $identificadorService,
        protected RenovarMotoristaAppAction $renovarAction,
        protected EfetivarMotoristaAppAction $efetivarAction,
        protected AceitarTermoMotoristaAppAction $aceitarTermoAction,
        protected AssinarContratoMotoristaAppAction $assinarContratoAction,
        protected CancelarMotoristaAppAction $cancelarAction,
        protected ConfirmarPagamentoMotoristaAppAction $confirmarPagamentoAction,
        protected ExibirContratoMotoristaAppAction $exibirContratoAction,
        protected ExibirPagamentoClienteAction $exibirPagamentoClienteAction,
        protected ExibirPagamentoClienteReservaComumAction $exibirPagamentoClienteReservaComumAction,
        protected ExibirPagamentoClienteMotoristaAppAction $exibirPagamentoClienteMotoristaAppAction,
        protected ExibirPagamentoAdminMotoristaAppAction $exibirPagamentoAdminMotoristaAppAction,
    ) {}

    public function renovarMotoristaApp(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }
        $ehAdmin = (bool) ($user->isAdministrador() ?? false);

        $reserva = ReservaMotoristaApp::query()
            ->with(['planoMotoristaApp', 'empresa', 'veiculo', 'pagamentos'])
            ->find($id);

        if ($reserva && !$ehAdmin && (int) $reserva->user_id !== (int) $user->id) {
            $reserva = null;
        }

        if (!$reserva) {
            return redirect()->route('reserva.minhas')->with('error', 'Reserva nao encontrada.');
        }

        if (strtolower((string) ($reserva->status ?? '')) === 'cancelada') {
            return back()->with('error', 'Nao e possivel renovar uma reserva cancelada.');
        }

        if (!empty($reserva->checkout_realizado_em)) {
            return back()->with('error', 'Nao e possivel renovar o plano apos o checkout da locacao.');
        }

        if (empty(data_get($reserva->contrato_snapshot_json, 'assinatura'))) {
            return back()->with('error', 'A renovacao so fica disponivel apos o check-in e a assinatura do contrato.');
        }

        $request->validate([
            'periodos' => ['required', 'integer', 'min:1', 'max:120'],
            'observacoes' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $pagamento = $this->renovarAction->handle(
                $reserva,
                fn ($r) => $this->valorUnitarioMotoristaApp($r),
                fn ($i, $p, $qtd) => $this->aplicarPeriodoMotoristaApp($i, $p, $qtd),
                fn ($p) => $this->rotuloPeriodoMotoristaApp($p)
            );

            return redirect()
                ->route('reserva.minhas.motorista-app.show', ['id' => $reserva->id])
                ->with('success', 'Renovacao criada. Agora basta efetuar o pagamento para confirmar o novo periodo.')
                ->with('pagamento', $pagamento->id);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function efetivarMotoristaApp(EfetivarMotoristaAppRequest $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }
        $ehAdmin = (bool) ($user->isAdministrador() ?? false);

        $reserva = ReservaMotoristaApp::query()
            ->with(['empresa', 'user', 'solicitacao.user', 'planoMotoristaApp', 'veiculo', 'pagamentos'])
            ->find($id);

        if ($reserva && !$ehAdmin && (int) $reserva->user_id !== (int) $user->id) {
            $reserva = null;
        }

        if (!$reserva) {
            return redirect()->route('reserva.minhas')->with('error', 'Reserva nao encontrada.');
        }

        if (strtolower((string) ($reserva->status ?? '')) === 'ativa' && !empty($reserva->contrato_efetivado_em)) {
            return back()->with('success', 'Esta locação já foi efetivada.');
        }

        try {
            $this->efetivarAction->handle(
                $reserva,
                $request->validated(),
                fn ($i, $p, $qtd) => $this->aplicarPeriodoMotoristaApp($i, $p, $qtd),
                fn ($p) => $this->rotuloPeriodoMotoristaApp($p)
            );

            return redirect()
                ->route('reserva.minhas.motorista-app.show', ['id' => $reserva->id])
                ->with('success', 'Locação efetivada com sucesso. O contrato foi gerado e o primeiro ciclo financeiro foi criado.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Erro ao efetivar locação: ' . $e->getMessage());
        }
    }

    public function aceitarTermoConfirmacaoMotoristaApp(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }
        $ehAdmin = (bool) ($user->isAdministrador() ?? false);

        $reserva = ReservaMotoristaApp::query()
            ->with(['codigoReserva'])
            ->find($id);

        if ($reserva && !$ehAdmin && (int) $reserva->user_id !== (int) $user->id) {
            $reserva = null;
        }

        if (!$reserva) {
            return redirect()->route('reserva.minhas')->with('error', 'Reserva nao encontrada.');
        }

        $request->validate(['aceite_termo' => ['accepted']]);

        try {
            $this->aceitarTermoAction->handle($reserva, $request);

            return back()->with('success', 'Termo de confirmacao aceito. O check-in ja pode ser realizado.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function verContratoMotoristaApp($id)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }
        $ehAdmin = (bool) ($user->isAdministrador() ?? false);

        $reserva = ReservaMotoristaApp::query()->find($id);
        if ($reserva && !$ehAdmin && (int) $reserva->user_id !== (int) $user->id) {
            $reserva = null;
        }

        if (!$reserva) {
            return back()->with('error', 'Reserva não encontrada.');
        }

        try {
            return $this->exibirContratoAction->handle($reserva);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function assinarContratoMotoristaApp(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }
        $ehAdmin = (bool) ($user->isAdministrador() ?? false);

        $reserva = ReservaMotoristaApp::query()
            ->with(['empresa', 'user', 'planoMotoristaApp', 'veiculo.marca', 'veiculo.modelo', 'solicitacao'])
            ->find($id);

        if ($reserva && !$ehAdmin && (int) $reserva->user_id !== (int) $user->id) {
            $reserva = null;
        }

        if (!$reserva) {
            return redirect()->route('reserva.minhas')->with('error', 'Reserva nao encontrada.');
        }

        $validated = $request->validate(['assinatura' => ['required', 'string']]);

        try {
            $this->assinarContratoAction->handle($reserva, $validated['assinatura']);

            return back()->with('success', 'Contrato assinado com sucesso.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function cancelarMotoristaApp(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }
        $ehAdmin = (bool) ($user->isAdministrador() ?? false);

        $reserva = ReservaMotoristaApp::query()
            ->with(['pagamentos'])
            ->find($id);

        if ($reserva && !$ehAdmin && (int) $reserva->user_id !== (int) $user->id) {
            $reserva = null;
        }

        if (!$reserva) {
            return redirect()->route('reserva.minhas')->with('error', 'Reserva nao encontrada.');
        }

        try {
            $this->cancelarAction->handle($reserva);

            return redirect()
                ->route('reserva.minhas.motorista-app.show', ['id' => $reserva->id])
                ->with('success', 'Reserva cancelada com sucesso.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function confirmarPagamentoMotoristaApp(Request $request, $pagamentoId)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }
        $ehAdmin = (bool) ($user->isAdministrador() ?? false);

        $pagamento = ReservaMotoristaAppPagamento::query()
            ->with(['empresa', 'reservaMotoristaApp.empresa', 'reservaMotoristaApp.user', 'reservaMotoristaApp.planoMotoristaApp'])
            ->find($pagamentoId);

        if (!$pagamento || (!$ehAdmin && (int) ($pagamento->user_id ?? 0) !== (int) $user->id)) {
            return redirect()->route('reserva.minhas')->with('error', 'Pagamento nao encontrado.');
        }

        $validated = $request->validate([
            'metodo_pagamento' => ['nullable', 'string', 'max:30'],
            'referencia_pagamento' => ['nullable', 'string', 'max:100'],
            'payment_method' => ['nullable', 'string', 'max:120'],
            'installments' => ['nullable', 'integer', 'min:1', 'max:12'],
            'valor_pagamento' => ['nullable', 'numeric', 'min:0.01'],
        ]);

        try {
            $pagamentoConfirmado = $this->confirmarPagamentoAction->handle(
                $pagamento,
                $validated,
                fn ($r) => $this->valorUnitarioMotoristaApp($r)
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pagamento confirmado. Reserva confirmada e aguardando retirada.',
                    'pagamento' => $pagamentoConfirmado->id,
                ]);
            }

            return back()
                ->with('success', 'Pagamento confirmado. Reserva confirmada e aguardando retirada.')
                ->with('pagamento', $pagamentoConfirmado->id);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function efetuarPagamentoPorReserva($id)
    {
        return $this->exibirPagamentoClienteAction->handle((string) $id);
    }

    public function efetuarPagamentoMotoristaApp($pagamentoId)
    {
        return $this->exibirPagamentoAdminMotoristaAppAction->handle((int) $pagamentoId);
    }

    private function garantirPrimeiroPagamentoMotoristaApp(ReservaMotoristaApp $reserva): void
    {
        $plano = $reserva->planoMotoristaApp;
        $periodoContrato = (string) ($reserva->periodo_contrato ?: ($plano?->periodo ?? 'semana'));
        $valorUnitario = $this->valorUnitarioMotoristaApp($reserva);
        $dataInicio = $reserva->data_inicio ? \Carbon\Carbon::parse($reserva->data_inicio) : now();
        $dataFim = $reserva->data_fim ? \Carbon\Carbon::parse($reserva->data_fim) : $this->aplicarPeriodoMotoristaApp($dataInicio->copy(), $periodoContrato, 1);

        $pagamentoPendenteZerado = $reserva->pagamentos()
            ->where('status', 'pendente')
            ->where(function ($query) {
                $query->whereNull('valor_total')->orWhere('valor_total', '<=', 0);
            })
            ->orderByDesc('id')
            ->first();

        $valorOpcionais = (float) ($reserva->valor_opcionais ?? 0);

        if ($pagamentoPendenteZerado && $valorUnitario > 0) {
            $periodosPendente = max(1, (int) ($pagamentoPendenteZerado->periodos ?? 1));
            $pagamentoPendenteZerado->update([
                'periodos' => $periodosPendente,
                'periodo_unidade' => $pagamentoPendenteZerado->periodo_unidade ?: $this->rotuloPeriodoMotoristaApp($periodoContrato),
                'valor_unitario' => $valorUnitario,
                'valor_total' => round(($valorUnitario * $periodosPendente) + $valorOpcionais, 2),
                'data_inicio_prevista' => $pagamentoPendenteZerado->data_inicio_prevista ?: $dataInicio,
                'data_fim_prevista' => $pagamentoPendenteZerado->data_fim_prevista ?: $dataFim,
            ]);
            return;
        }

        if ($reserva->pagamentos()->exists()) {
            return;
        }

        ReservaMotoristaAppPagamento::create([
            'reserva_motorista_app_id' => (int) $reserva->id,
            'empresa_id' => $reserva->empresa_id,
            'user_id' => (int) $reserva->user_id,
            'periodos' => 1,
            'periodo_unidade' => $this->rotuloPeriodoMotoristaApp($periodoContrato),
            'valor_unitario' => $valorUnitario,
            'valor_total' => round($valorUnitario + $valorOpcionais, 2),
            'data_inicio_prevista' => $dataInicio,
            'data_fim_prevista' => $dataFim,
            'status' => 'pendente',
            'observacoes' => 'Primeiro ciclo criado automaticamente na abertura da reserva.',
            'metadados' => [
                'tipo' => 'primeiro_ciclo',
                'plano_id' => (int) ($reserva->plano_motorista_app_id ?? 0),
                'valor_contrato' => $valorUnitario,
            ],
        ]);
    }

    private function valorUnitarioMotoristaApp(ReservaMotoristaApp $reserva): float
    {
        $plano = $reserva->relationLoaded('planoMotoristaApp')
            ? $reserva->planoMotoristaApp
            : $reserva->planoMotoristaApp()->first();

        foreach ([
            $reserva->valor_periodo_contrato ?? null,
            $reserva->valor_plano ?? null,
            $plano?->valor_periodo ?? null,
        ] as $valor) {
            $valorFloat = (float) $valor;
            if ($valorFloat > 0) {
                if ($reserva->exists && ((float) $reserva->valor_periodo_contrato <= 0 || (float) $reserva->valor_plano <= 0)) {
                    $reserva->update([
                        'valor_periodo_contrato' => (float) $reserva->valor_periodo_contrato > 0 ? $reserva->valor_periodo_contrato : $valorFloat,
                        'valor_plano' => (float) $reserva->valor_plano > 0 ? $reserva->valor_plano : $valorFloat,
                    ]);
                }
                return $valorFloat;
            }
        }

        return 0.0;
    }

    private function aplicarPeriodoMotoristaApp(\Carbon\Carbon $inicio, ?string $periodo, int $periodos): \Carbon\Carbon
    {
        $periodos = max(1, $periodos);

        return match ($periodo) {
            'quinze' => $inicio->copy()->addDays(15 * $periodos),
            'mes' => $inicio->copy()->addMonthsNoOverflow($periodos),
            'trimestre' => $inicio->copy()->addMonthsNoOverflow(3 * $periodos),
            'semestre' => $inicio->copy()->addMonthsNoOverflow(6 * $periodos),
            'ano' => $inicio->copy()->addYearsNoOverflow($periodos),
            default => $inicio->copy()->addWeeks($periodos),
        };
    }

    private function rotuloPeriodoMotoristaApp(?string $periodo): string
    {
        return match ($periodo) {
            'quinze' => 'Quinze dias',
            'mes' => 'Mês',
            'trimestre' => 'Trimestre',
            'semestre' => 'Semestre',
            'ano' => 'Ano',
            default => 'Semana',
        };
    }

    public function assinarDevolucaoMotoristaApp(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }
        $ehAdmin = (bool) ($user->isAdministrador() ?? false);

        $reserva = ReservaMotoristaApp::query()
            ->find($id);

        if ($reserva && !$ehAdmin && (int) $reserva->user_id !== (int) $user->id) {
            $reserva = null;
        }

        if (!$reserva) {
            return redirect()->route('reserva.minhas')->with('error', 'Reserva nao encontrada.');
        }

        $validated = $request->validate(['assinatura' => ['required', 'string']]);

        try {
            $docs = is_array($reserva->documentos_json) ? $reserva->documentos_json : [];
            $checkout = isset($docs['checkout']) && is_array($docs['checkout']) ? $docs['checkout'] : [];
            
            $checkout['assinatura'] = $validated['assinatura'];
            $checkout['data_assinatura'] = now()->toDateTimeString();
            
            $docs['checkout'] = $checkout;
            $reserva->documentos_json = $docs;
            $reserva->save();

            return back()->with('success', 'Termo de devolução assinado com sucesso.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
