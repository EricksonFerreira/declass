<?php

namespace App\Http\Controllers;

use App\DTO\BreadCrumbItem;
use App\Http\Requests\Multa\StoreMultaRequest;
use App\Http\Requests\Multa\UpdateMultaRequest;
use App\Models\Marca;
use App\Models\Multa;
use App\Models\GrupoFrota;
use App\Models\Reserva;
use App\Models\Transacao;
use App\Models\TipoFrota;
use App\Models\StatusVeiculo;
use App\Models\Veiculo;
use App\Notifications\AvisoSistemaNotification;
use App\Services\BreadcrumbService;
use App\Services\ClienteStripeService;
use App\Services\MultaService;
use App\Services\Pagamentos\GatewayEmpresaConfigService;
use App\Services\Pagamentos\GerenciadorDePagamentos;
use App\Services\ReservaService;
use App\Services\UsuarioService;
use App\Services\VeiculoService;
use App\Support\EmpresaContext;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

class MultaController extends BaseController
{
    public function __construct(
        private readonly MultaService      $multaService,
        private readonly BreadcrumbService $breadcrumbService,
        private readonly ReservaService    $reservaService,
        private readonly UsuarioService    $usuarioService,
        private readonly VeiculoService    $veiculoService,
        private readonly GatewayEmpresaConfigService $gatewayEmpresaConfigService,
        private readonly GerenciadorDePagamentos $gerenciadorDePagamentos,
        private readonly ClienteStripeService $clienteStripeService,
    ) {
        $this->titulo = 'Multas';
    }

    public function index(Request $request)
    {
        $this->authorize('multa.index');

        $filtros = $request->only([
            'data_inicio', 'data_fim', 'reserva_id', 'placa',
            'cliente_id', 'status_multa', 'status_desconto',
            'vencimento_ate', 'veiculo_id',
        ]);

        $perPage = $request->input('per_page', 15);

        $query = $this->multaService->queryScoped()
            ->with(['reserva', 'veiculo', 'cliente', 'responsavelLancamento'])
            ->orderBy('id', 'desc');

        if (!empty($filtros['data_inicio'])) {
            $query->where('data_hora_infracao', '>=', $filtros['data_inicio']);
        }
        if (!empty($filtros['data_fim'])) {
            $query->where('data_hora_infracao', '<=', $filtros['data_fim'] . ' 23:59:59');
        }
        if (!empty($filtros['reserva_id'])) {
            $query->where('reserva_id', $filtros['reserva_id']);
        }
        if (!empty($filtros['placa'])) {
            $query->where('placa_momento', 'like', '%' . $filtros['placa'] . '%');
        }
        if (!empty($filtros['cliente_id'])) {
            $query->where('cliente_id', $filtros['cliente_id']);
        }
        if (!empty($filtros['status_multa'])) {
            $query->where('status_multa', $filtros['status_multa']);
        }
        if (!empty($filtros['status_desconto'])) {
            $query->where('status_desconto', $filtros['status_desconto']);
        }
        if (!empty($filtros['vencimento_ate'])) {
            $query->where('data_vencimento', '<=', $filtros['vencimento_ate']);
        }
        if (!empty($filtros['veiculo_id'])) {
            $query->where('veiculo_id', $filtros['veiculo_id']);
        }

        $multas   = $query->paginate($perPage)->withQueryString();
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Multas');

        return view('multa.index', compact('multas', 'breadcrumb', 'filtros'))
            ->with('titulo', $this->titulo);
    }

    private function dadosFiltroVeiculo(): array
    {
        $empresaId = app(EmpresaContext::class)->empresaId()
            ?? Auth::user()?->empresa_id;

        $scope = fn ($q) => $empresaId ? $q->where('empresa_id', $empresaId) : $q;

        return [
            'filtroMarcas'      => $scope(Marca::query())->orderBy('nome')->pluck('nome', 'id'),
            'filtroGrupos'      => $scope(GrupoFrota::query())->orderBy('nome')->pluck('nome', 'id'),
            'filtroTipos'       => $scope(TipoFrota::query())->orderBy('nome')->pluck('nome', 'id'),
            'filtroStatus'      => $scope(StatusVeiculo::query())->orderBy('nome')->pluck('nome', 'id'),
        ];
    }

    public function create(Request $request)
    {
        $this->authorize('multa.create');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Multas', 'multa.index', 'Nova Multa');
        $reservaId  = $request->query('reserva_id');
        $reserva    = $reservaId ? $this->reservaService->getById($reservaId, ['usuario', 'grupoFrota']) : null;

        if ($reserva && ($reserva->pagamento_efetuado ?? 'N') !== 'S') {
            return redirect()
                ->route('reserva.show', $reserva->id)
                ->with('warning', 'O pagamento deve ser efetuado antes de registrar multas.');
        }

        $veiculoId = $request->query('veiculo_id');
        $veiculo   = $veiculoId ? $this->veiculoService->getById($veiculoId, ['marca', 'modelo']) : null;

        return view('multa.add-edit', array_merge($this->dadosFiltroVeiculo(), [
            'multa'            => null,
            'reserva'          => $reserva,
            'veiculo'          => $veiculo,
            'statusMulta'      => Multa::STATUS_MULTA,
            'statusDesconto'   => Multa::STATUS_DESCONTO,
            'breadcrumb'       => $breadcrumb,
            'titulo'           => 'Nova Multa',
            'metodo'           => 'POST',
            'rota'             => route('multa.store'),
        ]));
    }

    public function buscarReservaPorVeiculoData(Request $request): JsonResponse
    {
        $veiculoId = $request->query('veiculo_id');
        $dataHora  = $request->query('data_hora');

        if (!$veiculoId || !$dataHora) {
            return response()->json(['reserva' => null]);
        }

        try {
            $dt = Carbon::parse($dataHora);
        } catch (\Throwable) {
            return response()->json(['reserva' => null]);
        }

        $veiculo = Veiculo::find($veiculoId);
        if (!$veiculo) {
            return response()->json(['reserva' => null]);
        }

        $reserva = Reserva::where('modelo_id', $veiculo->modelo_id)
            ->where('data_inicio', '<=', $dt->toDateTimeString())
            ->where('data_fim', '>=', $dt->toDateString())
            ->with(['usuario'])
            ->orderByDesc('data_inicio')
            ->first();

        if (!$reserva) {
            return response()->json(['reserva' => null]);
        }

        return response()->json([
            'reserva' => [
                'id'          => $reserva->id,
                'cliente'     => $reserva->usuario?->nome ?? 'Cliente #' . $reserva->user_id,
                'data_inicio' => $reserva->data_inicio,
                'data_fim'    => $reserva->data_fim,
            ],
        ]);
    }

    public function store(StoreMultaRequest $request)
    {
        $this->authorize('multa.create');

        $dados = $request->except(['anexo_comprovante', 'anexo_pdf']);

        $multa = $this->multaService->criar($dados);

        $this->notificarLocatarioMultaCriada($multa);

        if (!empty($multa->reserva_id)) {
            $this->reservaService->adicionaLog((int) $multa->reserva_id, Auth::id(), json_encode([
                'acao' => 'Multa criada',
                'multa_id' => $multa->id,
                'valor_final' => $multa->valor_final,
                'status_multa' => $multa->status_multa,
            ]));
        }

        if ($request->hasFile('anexo_comprovante')) {
            $path = $this->multaService->salvarAnexo($request->file('anexo_comprovante'), $multa->id);
            $multa->update(['anexo_comprovante_path' => $path]);
        }

        if ($request->hasFile('anexo_pdf')) {
            $path = $this->multaService->salvarAnexo($request->file('anexo_pdf'), $multa->id);
            $multa->update(['anexo_pdf_path' => $path]);
        }

        return redirect()->route('multa.show', $multa->id)
            ->with('success', 'Multa registrada com sucesso.');
    }

    public function show(int $id)
    {
        $this->authorize('multa.index');

        $multa = $this->multaService->queryScoped()->with([
            'reserva.usuario', 'veiculo', 'cliente', 'condutor',
            'responsavelLancamento', 'pagoPor', 'historico.usuario',
        ])->findOrFail($id);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbShow('Multas', 'multa.index', 'Detalhes');

        $metodosPagamento = [];
        $empresaId = (int) ($multa->empresa_id ?? $multa->reserva?->empresa_id ?? 0);
        $empresa = $empresaId ? $this->gatewayEmpresaConfigService->empresaPorId($empresaId) : null;
        $gateway = $empresa ? $this->gatewayEmpresaConfigService->gateway($empresa) : null;
        if ($empresa && $gateway === 'stripe' && !empty($empresa->stripe_secret) && !empty($multa->reserva?->usuario?->stripe_id)) {
            try {
                $metodosPagamento = $this->clienteStripeService->getPaymentMethods(
                    (string) $multa->reserva->usuario->stripe_id,
                    (string) $empresa->stripe_secret
                );
            } catch (\Throwable $e) {
                $metodosPagamento = [];
            }
        }

        return view('multa.show', compact('multa', 'breadcrumb', 'metodosPagamento'))
            ->with('titulo', 'Multa #' . $multa->id);
    }

    public function edit(int $id)
    {
        $this->authorize('multa.edit');

        $multa      = $this->multaService->queryScoped()->with(['reserva', 'veiculo.marca', 'veiculo.modelo'])->findOrFail($id);
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Multas', 'multa.index', 'Editar Multa');

        return view('multa.add-edit', array_merge($this->dadosFiltroVeiculo(), [
            'multa'          => $multa,
            'reserva'        => $multa->reserva,
            'veiculo'        => $multa->veiculo,
            'statusMulta'    => Multa::STATUS_MULTA,
            'statusDesconto' => Multa::STATUS_DESCONTO,
            'breadcrumb'     => $breadcrumb,
            'titulo'         => 'Editar Multa #' . $multa->id,
            'metodo'         => 'PUT',
            'rota'           => route('multa.update', $multa->id),
        ]));
    }

    public function update(UpdateMultaRequest $request, int $id)
    {
        $this->authorize('multa.edit');

        $dados = $request->except(['anexo_comprovante', 'anexo_pdf']);

        $multa = $this->multaService->atualizar($id, $dados);
        if (!$multa) {
            return redirect()->route('multa.index')
                ->with('error', 'Multa nao encontrada.');
        }

        if ($request->hasFile('anexo_comprovante')) {
            $path = $this->multaService->salvarAnexo($request->file('anexo_comprovante'), $multa->id);
            $multa->update(['anexo_comprovante_path' => $path]);
        }

        if ($request->hasFile('anexo_pdf')) {
            $path = $this->multaService->salvarAnexo($request->file('anexo_pdf'), $multa->id);
            $multa->update(['anexo_pdf_path' => $path]);
        }

        return redirect()->route('multa.show', $multa->id)
            ->with('success', 'Multa atualizada com sucesso.');
    }

    public function confirmarPagamento(Request $request, int $id): \Illuminate\Http\JsonResponse
    {
        $this->authorize('multa.edit');

        $multa = $this->multaService->queryScoped()->findOrFail($id);

        if ($multa->pago_em) {
            return response()->json(['error' => 'Pagamento já registrado.'], 422);
        }

        $request->validate([
            'forma_pagamento'      => 'required|in:dinheiro,cartao_credito,cartao_debito,pix,boleto,debito_automatico',
            'referencia_pagamento' => 'nullable|string|max:100',
            'payment_method'       => 'nullable|string|max:255',
        ]);

        $forma = (string) $request->input('forma_pagamento');
        $referencia = $request->input('referencia_pagamento');

        // Se cartao de credito, tenta cobrar no Stripe usando o cartao do autor da reserva.
        if ($forma === 'cartao_credito') {
            $reserva = $multa->reserva_id ? $this->reservaService->getById((int) $multa->reserva_id, ['usuario']) : null;
            $empresaId = (int) ($multa->empresa_id ?? $reserva?->empresa_id ?? 0);
            $empresa = $empresaId ? $this->gatewayEmpresaConfigService->empresaPorId($empresaId) : null;
            if (!$empresa) {
                return response()->json(['error' => 'Empresa nao encontrada para processar pagamento.'], 422);
            }

            $gateway = $this->gatewayEmpresaConfigService->gateway($empresa);
            if ($gateway !== 'stripe') {
                return response()->json(['error' => 'Pagamento por cartao de credito esta disponivel apenas no Stripe.'], 422);
            }

            $user = $reserva?->usuario;
            if (!$user || empty($user->stripe_id)) {
                return response()->json(['error' => 'Cliente da reserva nao possui cadastro/cartao no Stripe.'], 422);
            }

            $paymentMethod = (string) ($request->input('payment_method') ?? '');
            if ($paymentMethod === '') {
                return response()->json(['error' => 'Selecione um cartao do cliente para cobrar.'], 422);
            }

            $valor = (float) ($multa->valor_final ?? $multa->valor_original ?? 0);
            if ($valor <= 0) {
                return response()->json(['error' => 'Valor invalido para cobranca.'], 422);
            }

            $currency = $this->gatewayEmpresaConfigService->moeda($empresa);
            $credenciais = $this->gatewayEmpresaConfigService->credenciais($empresa, $gateway);

            $payload = array_merge([
                'valor' => $valor,
                'currency' => $currency,
                'payment_method' => $paymentMethod,
                'customer_id' => (string) $user->stripe_id,
                'descricao' => 'Pagamento de multa #' . $multa->id,
                'payer_email' => (string) ($user->email ?? ''),
            ], $credenciais);

            try {
                $resultado = $this->gerenciadorDePagamentos->resolver($gateway)->cobrarPagamento($payload);
            } catch (\Throwable $e) {
                return response()->json(['error' => 'Falha ao cobrar no Stripe: ' . $e->getMessage()], 500);
            }

            if (($resultado['status'] ?? 'error') !== 'success') {
                return response()->json(['error' => $resultado['message'] ?? 'Pagamento nao efetuado.'], 422);
            }

            $referencia = $resultado['transaction_id'] ?? $resultado['id'] ?? $referencia;

            if (Schema::hasTable('transacoes')) {
                Transacao::create([
                    'user_id' => $user->id,
                    'pago_por_user_id' => Auth::id(),
                    'reserva_id' => $reserva?->id ?? $multa->reserva_id,
                    'valor' => $valor,
                    'moeda' => strtoupper((string) ($resultado['currency'] ?? $currency ?? 'BRL')),
                    'metodo_pagamento' => 'stripe',
                    'gateway_transaction_id' => $resultado['transaction_id'] ?? null,
                    'status' => $resultado['raw_status'] ?? 'success',
                    'info_pagamento' => $resultado,
                ]);
            }
        } else {
            // Outras formas: apenas registra confirmacao (acao de funcionario)
            if (Schema::hasTable('transacoes')) {
                $valor = (float) ($multa->valor_final ?? $multa->valor_original ?? 0);
                if ($valor > 0) {
                    $userId = $multa->cliente_id;
                    if (!$userId && $multa->reserva_id) {
                        $userId = $this->reservaService->getById((int) $multa->reserva_id)?->user_id;
                    }
                    $userId = $userId ?: Auth::id();

                    $payload = [
                        'user_id' => $userId,
                        'pago_por_user_id' => Auth::id(),
                        'reserva_id' => $multa->reserva_id,
                        'valor' => $valor,
                        'moeda' => 'BRL',
                        'metodo_pagamento' => $forma,
                        'status' => 'approved',
                        'info_pagamento' => [
                            'tipo' => 'multa',
                            'multa_id' => $multa->id,
                        ],
                    ];
                    Transacao::create($payload);
                }
            }
        }

        $multa->update([
            'forma_pagamento'      => $forma,
            'referencia_pagamento' => $referencia,
            'pago_em'              => now(),
            'pago_por_user_id'     => Auth::id(),
            'status_multa'         => 'paga',
        ]);

        if (!empty($multa->reserva_id)) {
            $this->reservaService->adicionaLog((int) $multa->reserva_id, Auth::id(), json_encode([
                'acao' => 'Pagamento de multa confirmado',
                'multa_id' => $multa->id,
                'valor' => (float) ($multa->valor_final ?? $multa->valor_original ?? 0),
                'forma_pagamento' => $forma,
                'referencia' => $referencia,
            ]));
        }

        return response()->json([
            'success'  => true,
            'message'  => 'Pagamento da multa confirmado com sucesso.',
            'pago_em'  => $multa->pago_em->format('d/m/Y H:i'),
            'pago_por' => Auth::user()->nome ?? Auth::user()->name,
        ]);
    }

    private function notificarLocatarioMultaCriada(Multa $multa): void
    {
        $multa->loadMissing(['reserva.usuario', 'cliente']);
        $usuario = $multa->reserva?->usuario ?? $multa->cliente;

        if (!$usuario || empty($usuario->id)) {
            return;
        }

        $chave = 'multa:' . (int) $multa->id . ':criacao';
        $titulo = 'Nova multa registrada';
        $mensagem = sprintf(
            'Foi registrada uma multa para sua locação. Valor: %s. Vencimento: %s.',
            number_format((float) ($multa->valor_final ?? $multa->valor_original ?? 0), 2, ',', '.'),
            $multa->data_vencimento?->format('d/m/Y') ?? 'sem data'
        );

        if (DB::table('notifications')
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $usuario->id)
            ->where('type', AvisoSistemaNotification::class)
            ->where('data->chave', $chave)
            ->exists()) {
            return;
        }

        $usuario->notify(new AvisoSistemaNotification(
            $titulo,
            $mensagem,
            route('site.multa.pagamento', ['multa' => $multa->id]),
            'fas fa-exclamation-triangle',
            'warning',
            $chave,
            'multa_registrada'
        ));
    }

    public function destroy(int $id)
    {
        $this->authorize('multa.delete');

        $multa = $this->multaService->queryScoped()->findOrFail($id);
        $multa->delete();

        return redirect()->route('multa.index')
            ->with('success', 'Multa removida com sucesso.');
    }

    public function export(Request $request)
    {
        $this->authorize('multa.export');

        $filtros = $request->only([
            'data_inicio', 'data_fim', 'reserva_id', 'placa',
            'cliente_id', 'status_multa', 'status_desconto',
            'vencimento_ate', 'veiculo_id',
        ]);

        return $this->multaService->exportarCsv($filtros);
    }
}
