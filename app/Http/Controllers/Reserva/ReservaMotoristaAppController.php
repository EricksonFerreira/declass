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
use App\Models\User;
use App\Models\PlanoMotoristaApp;
use App\Models\Veiculo;
use App\Models\Estacao;
use App\Models\ReservaCodigo;
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
            ->with(['planoMotoristaApp.opcionais', 'empresa', 'veiculo', 'pagamentos'])
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
            'periodos'        => ['required', 'integer', 'min:1', 'max:120'],
            'acao_renovacao'  => ['nullable', 'string', 'in:mantem,trocar'],
            'plano_id'        => ['nullable', 'integer', 'exists:planos_motorista_app,id'],
            'cupom_codigo'    => ['nullable', 'string', 'max:100'],
            'opcionais'       => ['nullable', 'array'],
            'opcionais.*'     => ['integer'],
            'observacoes'     => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            // Usar o novo strategy handle() que suporta troca de plano, cupom e opcionais
            $pagamento = $this->renovarAction->handle($request, $reserva);

            // Redirecionar para a página da reserva (padrão admin)
            $codigo = $reserva->codigoReserva?->codigo ?? ('MAP-' . str_pad((string) $reserva->id, 6, '0', STR_PAD_LEFT));

            return redirect()
                ->route('reserva.show', $codigo)
                ->with('success', 'Renovação criada com sucesso! Agora basta efetuar o pagamento para confirmar o novo período.')
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

    public function create()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }
        $empresaId = $user->empresa_id;

        $titulo = 'Nova Reserva Motorista APP';
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Reservas', 'reserva.index', 'Nova Motorista APP');

        $users = User::where('empresa_id', $empresaId)->orderBy('nome')->get();
        $planos = PlanoMotoristaApp::with('gruposFrota')->where('empresa_id', $empresaId)->where('ativo', 'S')->get();
        $veiculos = Veiculo::where('empresa_id', $empresaId)->orderBy('placa_licenca')->get();
        
        $estacoesComVeiculosIds = Veiculo::where('empresa_id', $empresaId)
            ->where('ativo', 'S')
            ->whereNotNull('local_disponibilidade')
            ->pluck('local_disponibilidade')
            ->unique()
            ->toArray();
        $estacoes = Estacao::where('empresa_id', $empresaId)
            ->whereIn('id', $estacoesComVeiculosIds)
            ->orderBy('nome')
            ->get();

        return view('reserva.add-edit-motorista-app', compact(
            'titulo', 'breadcrumb', 'users', 'planos', 'veiculos', 'estacoes'
        ));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }
        $empresaId = $user->empresa_id;

        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'plano_motorista_app_id' => ['required', 'integer', 'exists:planos_motorista_app,id'],
            'veiculo_id' => ['nullable', 'integer', 'exists:veiculos,id'],
            'estacao_retirada_id' => ['required', 'integer', 'exists:estacoes,id'],
            'data_inicio' => ['required', 'date'],
            'data_fim' => ['required', 'date', 'after_or_equal:data_inicio'],
            'status' => ['required', 'string', 'in:pendente_analise,pendente_pagamento,ativa,cancelada'],
            'status_pagamento' => ['nullable', 'string', 'in:pendente,pago,cancelado'],
            'valor_plano' => ['required', 'numeric', 'min:0'],
            'valor_caucao' => ['nullable', 'numeric', 'min:0'],
            'valor_opcionais' => ['nullable', 'numeric', 'min:0'],
            'observacoes' => ['nullable', 'string', 'max:2000'],
        ]);

        $plano = PlanoMotoristaApp::findOrFail($validated['plano_motorista_app_id']);
        
        $dataInicio = \Carbon\Carbon::parse($validated['data_inicio']);
        $dataFim = \Carbon\Carbon::parse($validated['data_fim']);
        $diasPlano = max(1, $dataInicio->diffInDays($dataFim));
        $statusPagamento = $validated['status_pagamento'] ?? 'pendente';

        $reserva = ReservaMotoristaApp::create([
            'empresa_id' => $empresaId,
            'user_id' => $validated['user_id'],
            'plano_motorista_app_id' => $validated['plano_motorista_app_id'],
            'veiculo_id' => $validated['veiculo_id'],
            'estacao_retirada_id' => $validated['estacao_retirada_id'],
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim,
            'quantidade_dias' => $diasPlano,
            'status' => $validated['status'],
            'status_pagamento' => $statusPagamento,
            'pagamento_efetuado' => $statusPagamento === 'pago',
            'valor_plano' => $validated['valor_plano'],
            'valor_periodo_contrato' => $validated['valor_plano'],
            'valor_opcionais' => $validated['valor_opcionais'] ?? 0,
            'valor_caucao' => $validated['valor_caucao'] ?? (float) ($plano->calcao ?? 0),
            'valor_total_final' => (float)$validated['valor_plano'] + (float)($validated['valor_opcionais'] ?? 0),
            'periodo_contrato' => (string) ($plano->periodo ?? 'semana'),
            'observacoes' => $validated['observacoes'],
        ]);

        if ($reserva) {
            $this->garantirPrimeiroPagamentoMotoristaApp($reserva);

            app(\App\Services\ReservaCodigoService::class)->sincronizar($reserva, 'motorista_app', [
                'empresa_id' => $empresaId,
                'status_label' => $reserva->status,
                'data_inicio' => $reserva->data_inicio,
                'data_fim' => $reserva->data_fim,
                'valor_total' => $reserva->valor_total_final,
            ]);
        }

        $codigo = $reserva->codigoReserva?->codigo ?? ('MAP-' . str_pad((string) $reserva->id, 6, '0', STR_PAD_LEFT));

        return redirect()->route('reserva.show', ['reserva' => $codigo])->with('success', 'Reserva de motorista de app cadastrada com sucesso!');
    }

    public function edit($id)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }
        $empresaId = $user->empresa_id;

        $reserva = ReservaMotoristaApp::with('codigoReserva')->findOrFail($id);
        if ($reserva->empresa_id !== $empresaId && !$user->isAdministrador()) {
            abort(403, 'Acesso não autorizado.');
        }

        $titulo = 'Editar Reserva Motorista APP';
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Reservas', 'reserva.index', "Editar Motorista APP: $reserva->id");

        $users = User::where('empresa_id', $empresaId)->orderBy('nome')->get();
        $planos = PlanoMotoristaApp::with('gruposFrota')->where('empresa_id', $empresaId)->where('ativo', 'S')->get();
        $veiculos = Veiculo::where('empresa_id', $empresaId)->orderBy('placa_licenca')->get();
        
        $estacoesComVeiculosIds = Veiculo::where('empresa_id', $empresaId)
            ->where('ativo', 'S')
            ->whereNotNull('local_disponibilidade')
            ->pluck('local_disponibilidade')
            ->unique()
            ->toArray();
        $estacoes = Estacao::where('empresa_id', $empresaId)
            ->whereIn('id', $estacoesComVeiculosIds)
            ->orderBy('nome')
            ->get();

        return view('reserva.add-edit-motorista-app', compact(
            'titulo', 'breadcrumb', 'reserva', 'users', 'planos', 'veiculos', 'estacoes'
        ));
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }
        $empresaId = $user->empresa_id;

        $reserva = ReservaMotoristaApp::findOrFail($id);
        if ($reserva->empresa_id !== $empresaId && !$user->isAdministrador()) {
            abort(403, 'Acesso não autorizado.');
        }

        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'plano_motorista_app_id' => ['required', 'integer', 'exists:planos_motorista_app,id'],
            'veiculo_id' => ['nullable', 'integer', 'exists:veiculos,id'],
            'estacao_retirada_id' => ['required', 'integer', 'exists:estacoes,id'],
            'data_inicio' => ['required', 'date'],
            'data_fim' => ['required', 'date', 'after_or_equal:data_inicio'],
            'status' => ['required', 'string', 'in:pendente_analise,pendente_pagamento,ativa,cancelada'],
            'status_pagamento' => ['nullable', 'string', 'in:pendente,pago,cancelado'],
            'valor_plano' => ['required', 'numeric', 'min:0'],
            'valor_caucao' => ['nullable', 'numeric', 'min:0'],
            'valor_opcionais' => ['nullable', 'numeric', 'min:0'],
            'observacoes' => ['nullable', 'string', 'max:2000'],
        ]);

        $plano = PlanoMotoristaApp::findOrFail($validated['plano_motorista_app_id']);

        $dataInicio = \Carbon\Carbon::parse($validated['data_inicio']);
        $dataFim = \Carbon\Carbon::parse($validated['data_fim']);
        $diasPlano = max(1, $dataInicio->diffInDays($dataFim));
        $statusPagamento = $validated['status_pagamento'] ?? $reserva->status_pagamento ?? 'pendente';

        $reserva->update([
            'user_id' => $validated['user_id'],
            'plano_motorista_app_id' => $validated['plano_motorista_app_id'],
            'veiculo_id' => $validated['veiculo_id'],
            'estacao_retirada_id' => $validated['estacao_retirada_id'],
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim,
            'quantidade_dias' => $diasPlano,
            'status' => $validated['status'],
            'status_pagamento' => $statusPagamento,
            'pagamento_efetuado' => $statusPagamento === 'pago',
            'valor_plano' => $validated['valor_plano'],
            'valor_periodo_contrato' => $validated['valor_plano'],
            'valor_opcionais' => $validated['valor_opcionais'] ?? 0,
            'valor_caucao' => $validated['valor_caucao'] ?? (float) ($plano->calcao ?? 0),
            'valor_total_final' => (float)$validated['valor_plano'] + (float)($validated['valor_opcionais'] ?? 0),
            'periodo_contrato' => (string) ($plano->periodo ?? 'semana'),
            'observacoes' => $validated['observacoes'],
        ]);

        // Sincronizar o código
        app(\App\Services\ReservaCodigoService::class)->sincronizar($reserva, 'motorista_app', [
            'empresa_id' => $reserva->empresa_id,
            'status_label' => $reserva->status,
            'data_inicio' => $reserva->data_inicio,
            'data_fim' => $reserva->data_fim,
            'valor_total' => $reserva->valor_total_final,
        ]);

        $codigo = $reserva->codigoReserva?->codigo ?? ('MAP-' . str_pad((string) $reserva->id, 6, '0', STR_PAD_LEFT));

        return redirect()->route('reserva.show', ['reserva' => $codigo])->with('success', 'Reserva de motorista de app atualizada com sucesso!');
    }

    public function criarMotoristaAjax(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Não autenticado'], 401);
        }
        
        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'celular' => ['nullable', 'string', 'max:50'],
            'numero_identificacao' => ['nullable', 'string', 'max:50'], // CPF
            'numero_carta_conducao' => ['nullable', 'string', 'max:50'], // CNH
        ]);
        
        // Find role for 'cliente'
        $role = \App\Models\Regra::whereRaw('LOWER(name) = ?', ['cliente'])->first();
        
        $novoUsuario = \App\Models\User::create([
            'nome' => $validated['nome'],
            'email' => $validated['email'],
            'celular' => $validated['celular'],
            'numero_identificacao' => $validated['numero_identificacao'],
            'numero_carta_conducao' => $validated['numero_carta_conducao'],
            'password' => bcrypt(str_replace(['.', '-'], '', $validated['numero_identificacao'] ?? '12345678')),
            'empresa_id' => $user->empresa_id,
            'role_id' => $role->id ?? null,
        ]);
        
        return response()->json([
            'success' => true,
            'user' => $novoUsuario
        ]);
    }

    public function solicitacaoStatusAjax($userId)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Não autenticado'], 401);
        }
        
        $solicitacao = \App\Models\MotoristaAppSolicitacao::where('user_id', $userId)
            ->where('empresa_id', $user->empresa_id)
            ->latest()
            ->first();
            
        return response()->json([
            'success' => true,
            'status' => $solicitacao->status ?? 'sem_solicitacao',
            'solicitacao' => $solicitacao
        ]);
    }

    public function salvarDocumentosAjax(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Não autenticado'], 401);
        }
        
        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'plano_id' => ['nullable', 'integer'],
        ]);
        
        $userId = $request->input('user_id');
        $planoId = $request->input('plano_id');
        
        $solicitacao = \App\Models\MotoristaAppSolicitacao::where('user_id', $userId)
            ->where('empresa_id', $user->empresa_id)
            ->latest()
            ->first();
            
        if (!$solicitacao) {
            $solicitacao = new \App\Models\MotoristaAppSolicitacao();
            $solicitacao->empresa_id = $user->empresa_id;
            $solicitacao->user_id = $userId;
            $solicitacao->status = 'em_analise';
            $solicitacao->dados_json = [];
            $solicitacao->documentos_json = [];
        }
        
        if ($planoId) {
            $solicitacao->plano_motorista_app_id = $planoId;
        }
        
        $docs = $solicitacao->documentos_json ?? [];
        
        // Handle CNH Front
        if ($request->hasFile('doc_cnh_frente')) {
            $file = $request->file('doc_cnh_frente');
            $antigo = isset($docs['cnh_frente']['path']) ? $docs['cnh_frente']['path'] : null;
            $path = \App\Support\ArquivoAnexoHelper::armazenar($file, 'motorista-app/documentos', $antigo);
            $docs['cnh_frente'] = [
                'path' => $path,
                'name' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
                'uploaded_at' => now()->toDateTimeString()
            ];
        }
        
        // Handle CNH Verso
        if ($request->hasFile('doc_cnh_verso')) {
            $file = $request->file('doc_cnh_verso');
            $antigo = isset($docs['cnh_verso']['path']) ? $docs['cnh_verso']['path'] : null;
            $path = \App\Support\ArquivoAnexoHelper::armazenar($file, 'motorista-app/documentos', $antigo);
            $docs['cnh_verso'] = [
                'path' => $path,
                'name' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
                'uploaded_at' => now()->toDateTimeString()
            ];
        }

        // Handle Comprovante Residencia
        if ($request->hasFile('doc_comprovante_residencia')) {
            $file = $request->file('doc_comprovante_residencia');
            $antigo = isset($docs['comprovante_residencia']['path']) ? $docs['comprovante_residencia']['path'] : null;
            $path = \App\Support\ArquivoAnexoHelper::armazenar($file, 'motorista-app/documentos', $antigo);
            $docs['comprovante_residencia'] = [
                'path' => $path,
                'name' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
                'uploaded_at' => now()->toDateTimeString()
            ];
        }
        
        $solicitacao->documentos_json = $docs;
        $solicitacao->status = 'em_analise';
        $solicitacao->save();
        
        return response()->json([
            'success' => true,
            'status' => $solicitacao->status,
            'solicitacao' => $solicitacao
        ]);
    }

    public function veiculosDisponiveisAjax(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Não autenticado'], 401);
        }
        
        $request->validate([
            'estacao_retirada_id' => ['required', 'integer'],
            'data_inicio' => ['required', 'date'],
            'hora_inicio' => ['required', 'string'],
            'data_fim' => ['required', 'date'],
            'plano_id' => ['required', 'integer'],
        ]);
        
        $estacaoId = $request->input('estacao_retirada_id');
        $planoId = $request->input('plano_id');
        
        $dataInicioStr = $request->input('data_inicio') . ' ' . $request->input('hora_inicio') . ':00';
        $dataFimStr = $request->input('data_fim') . ' 09:00:00'; 
        
        $plano = \App\Models\PlanoMotoristaApp::with('gruposFrota')->findOrFail($planoId);
        $gruposIds = $plano->gruposFrota->pluck('id')->toArray();
        
        if (empty($gruposIds)) {
            return response()->json(['veiculos' => []]);
        }
        
        $query = \App\Models\Veiculo::with('modelo')
            ->where('empresa_id', $user->empresa_id)
            ->where('ativo', 'S')
            ->where('local_disponibilidade', $estacaoId)
            ->whereIn('grupo_frota_id', $gruposIds)
            ->where(function ($q) use ($dataInicioStr) {
                $q->whereNull('data_hora_disponibilidade')
                    ->orWhere('data_hora_disponibilidade', '<=', $dataInicioStr);
            })
            ->whereDoesntHave('movimentacoes', function ($q) use ($dataInicioStr, $dataFimStr) {
                $q->join('reservas', 'reservas.id', '=', 'movimentacoes.reserva_id')
                    ->whereIn('reservas.status_reserva_id', [
                        \App\Enums\StatusReservaEnum::Pendente->value ?? 1, 
                        \App\Enums\StatusReservaEnum::Confirmada->value ?? 2, 
                        \App\Enums\StatusReservaEnum::Checkout->value ?? 3, 
                        \App\Enums\StatusReservaEnum::NoShow->value ?? 4
                    ])
                    ->where('reservas.data_inicio', '<', $dataFimStr)
                    ->where('reservas.data_fim', '>', $dataInicioStr);
            })
            ->whereDoesntHave('movimentacoes', function ($q) use ($dataFimStr) {
                $q->whereNull('data_chegada_real')
                    ->where(function ($sub) use ($dataFimStr) {
                        $sub->whereNull('data_saida')->orWhere('data_saida', '<', $dataFimStr);
                    });
            });
            
        $veiculos = $query->get();
        
        return response()->json([
            'success' => true,
            'veiculos' => $veiculos
        ]);
    }
}
