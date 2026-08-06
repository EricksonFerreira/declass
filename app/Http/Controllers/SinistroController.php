<?php

namespace App\Http\Controllers;

use App\Http\Requests\Sinistro\StoreSinistroRequest;
use App\Http\Requests\Sinistro\UpdateSinistroRequest;
use App\Actions\Financeiro\RegistrarLancamentoSinistroAction;
use App\Models\GrupoFrota;
use App\Models\Marca;
use App\Models\Reserva;
use App\Models\Sinistro;
use App\Models\SinistroHistorico;
use App\Models\StatusVeiculo;
use App\Models\TipoFrota;
use App\Models\Transacao;
use App\Models\Veiculo;
use App\Services\BreadcrumbService;
use App\Services\ClienteStripeService;
use App\Services\Pagamentos\GatewayEmpresaConfigService;
use App\Services\Pagamentos\GerenciadorDePagamentos;
use App\Services\ReservaService;
use App\Services\SinistroService;
use App\Services\UsuarioService;
use App\Services\VeiculoService;
use App\Support\EmpresaContext;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Support\SchemaCache;

class SinistroController extends BaseController
{
    public function __construct(
        private readonly SinistroService   $sinistroService,
        private readonly BreadcrumbService $breadcrumbService,
        private readonly ReservaService    $reservaService,
        private readonly UsuarioService    $usuarioService,
        private readonly VeiculoService    $veiculoService,
        private readonly GatewayEmpresaConfigService $gatewayEmpresaConfigService,
        private readonly GerenciadorDePagamentos $gerenciadorDePagamentos,
        private readonly ClienteStripeService $clienteStripeService,
        private readonly RegistrarLancamentoSinistroAction $registrarLancamentoSinistroAction,
    ) {
        $this->titulo = 'Sinistros';
    }

    public function index(Request $request)
    {
        $this->authorize('sinistro.index');

        $filtros = $request->only([
            'data_inicio', 'data_fim', 'placa', 'tipo_sinistro', 'status', 'reserva_id', 'veiculo_id',
        ]);

        $perPage = $request->input('per_page', 15);

        $query = $this->sinistroService->queryScoped()
            ->with(['veiculo', 'cliente', 'reserva', 'responsavel'])
            ->orderBy('id', 'desc');

        if (!empty($filtros['data_inicio'])) {
            $query->where('data_hora_ocorrido', '>=', $filtros['data_inicio']);
        }
        if (!empty($filtros['data_fim'])) {
            $query->where('data_hora_ocorrido', '<=', $filtros['data_fim'] . ' 23:59:59');
        }
        if (!empty($filtros['placa'])) {
            $query->where('placa_momento', 'like', '%' . $filtros['placa'] . '%');
        }
        if (!empty($filtros['tipo_sinistro'])) {
            $query->where('tipo_sinistro', $filtros['tipo_sinistro']);
        }
        if (!empty($filtros['status'])) {
            $query->where('status', $filtros['status']);
        }
        if (!empty($filtros['reserva_id'])) {
            $query->where('reserva_id', $filtros['reserva_id']);
        }
        if (!empty($filtros['veiculo_id'])) {
            $query->where('veiculo_id', $filtros['veiculo_id']);
        }

        $sinistros  = $query->paginate($perPage)->withQueryString();
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Sinistros');

        return view('sinistro.index', compact('sinistros', 'breadcrumb', 'filtros'))
            ->with('titulo', $this->titulo);
    }

    private function dadosFiltroVeiculo(): array
    {
        $empresaId = app(EmpresaContext::class)->empresaId()
            ?? Auth::user()?->empresa_id;

        $scope = function ($q) use ($empresaId) {
            if (!$empresaId) {
                return $q;
            }

            $model = method_exists($q, 'getModel') ? $q->getModel() : null;
            $table = $model ? $model->getTable() : null;

            if ($table && SchemaCache::hasTable($table) && SchemaCache::hasColumn($table, 'empresa_id')) {
                return $q->where('empresa_id', $empresaId);
            }

            return $q;
        };

        return [
            'filtroMarcas'  => $scope(Marca::query())->orderBy('nome')->pluck('nome', 'id'),
            'filtroGrupos'  => $scope(GrupoFrota::query())->orderBy('nome')->pluck('nome', 'id'),
            'filtroTipos'   => $scope(TipoFrota::query())->orderBy('nome')->pluck('nome', 'id'),
            'filtroStatus'  => $scope(StatusVeiculo::query())->orderBy('nome')->pluck('nome', 'id'),
        ];
    }

    public function create(Request $request)
    {
        $this->authorize('sinistro.create');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Sinistros', 'sinistro.index', 'Novo Sinistro');
        $reservaId  = $request->query('reserva_id');
        $reserva    = $reservaId ? $this->reservaService->getById($reservaId, ['usuario', 'grupoFrota']) : null;

        $veiculoId = $request->query('veiculo_id');
        $veiculo   = $veiculoId ? $this->veiculoService->getById($veiculoId, ['marca', 'modelo']) : null;

        return view('sinistro.add-edit', array_merge($this->dadosFiltroVeiculo(), [
            'sinistro'  => null,
            'reserva'   => $reserva,
            'veiculo'   => $veiculo,
            'tipos'     => Sinistro::TIPOS,
            'statusOpts'=> Sinistro::STATUS,
            'breadcrumb'=> $breadcrumb,
            'titulo'    => 'Novo Sinistro',
            'metodo'    => 'POST',
            'rota'      => route('sinistro.store'),
        ]));
    }

    public function store(StoreSinistroRequest $request)
    {
        $this->authorize('sinistro.create');

        $dados = $request->except(['anexo_bo', 'anexo_foto1', 'anexo_foto2', 'anexo_foto3', 'anexo_laudo']);
        $dados['terceiros_envolvidos'] = $request->boolean('terceiros_envolvidos');
        $dados['vitimas']              = $request->boolean('vitimas');

        $sinistro = $this->sinistroService->criar($dados);

        if (!empty($sinistro->reserva_id)) {
            $this->reservaService->adicionaLog((int) $sinistro->reserva_id, Auth::id(), json_encode([
                'acao' => 'Sinistro criado',
                'sinistro_id' => $sinistro->id,
                'tipo' => $sinistro->tipo_sinistro,
                'valor_responsabilidade_cliente' => $sinistro->valor_responsabilidade_cliente,
                'status' => $sinistro->status,
            ]));
        }

        foreach (['bo', 'foto1', 'foto2', 'foto3', 'laudo'] as $tipo) {
            $campo = "anexo_{$tipo}";
            if ($request->hasFile($campo)) {
                $path = $this->sinistroService->salvarAnexo($request->file($campo), $sinistro->id, $tipo);
                $sinistro->update(["{$campo}_path" => $path]);
            }
        }

        return redirect()->route('sinistro.show', $sinistro->id)
            ->with('success', 'Sinistro registrado com sucesso.');
    }

    public function show(int $id)
    {
        $this->authorize('sinistro.index');

        $sinistro = $this->sinistroService->queryScoped()->with([
            'veiculo.marca', 'veiculo.modelo',
            'reserva.usuario',
            'cliente', 'condutor', 'responsavel',
            'pagoEmpresaPor', 'pagoClientePor',
            'historico.usuario',
        ])->findOrFail($id);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbShow('Sinistros', 'sinistro.index', 'Detalhes');

        $metodosPagamento = [];
        $empresaId = (int) ($sinistro->empresa_id ?? $sinistro->reserva?->empresa_id ?? 0);
        $empresa = $empresaId ? $this->gatewayEmpresaConfigService->empresaPorId($empresaId) : null;
        $gateway = $empresa ? $this->gatewayEmpresaConfigService->gateway($empresa) : null;
        $stripeIdCliente = (string) ($sinistro->reserva?->usuario?->stripe_id ?? $sinistro->cliente?->stripe_id ?? '');
        if ($empresa && $gateway === 'stripe' && !empty($empresa->stripe_secret) && $stripeIdCliente !== '') {
            try {
                $metodosPagamento = $this->clienteStripeService->getPaymentMethods($stripeIdCliente, (string) $empresa->stripe_secret);
            } catch (\Throwable $e) {
                $metodosPagamento = [];
            }
        }

        return view('sinistro.show', compact('sinistro', 'breadcrumb', 'metodosPagamento'))
            ->with('titulo', 'Sinistro #' . $sinistro->id);
    }

    public function edit(int $id)
    {
        $this->authorize('sinistro.edit');

        $sinistro   = $this->sinistroService->queryScoped()->with(['reserva', 'veiculo.marca', 'veiculo.modelo'])->findOrFail($id);
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Sinistros', 'sinistro.index', 'Editar Sinistro');

        return view('sinistro.add-edit', array_merge($this->dadosFiltroVeiculo(), [
            'sinistro'  => $sinistro,
            'reserva'   => $sinistro->reserva,
            'veiculo'   => $sinistro->veiculo,
            'tipos'     => Sinistro::TIPOS,
            'statusOpts'=> Sinistro::STATUS,
            'breadcrumb'=> $breadcrumb,
            'titulo'    => 'Editar Sinistro #' . $sinistro->id,
            'metodo'    => 'PUT',
            'rota'      => route('sinistro.update', $sinistro->id),
        ]));
    }

    public function update(UpdateSinistroRequest $request, int $id)
    {
        $this->authorize('sinistro.edit');

        $dados = $request->except(['anexo_bo', 'anexo_foto1', 'anexo_foto2', 'anexo_foto3', 'anexo_laudo']);
        $dados['terceiros_envolvidos'] = $request->boolean('terceiros_envolvidos');
        $dados['vitimas']              = $request->boolean('vitimas');

        $sinistro = $this->sinistroService->atualizar($id, $dados);
        if (!$sinistro) {
            return redirect()->route('sinistro.index')->with('error', 'Sinistro não encontrado.');
        }

        foreach (['bo', 'foto1', 'foto2', 'foto3', 'laudo'] as $tipo) {
            $campo = "anexo_{$tipo}";
            if ($request->hasFile($campo)) {
                $path = $this->sinistroService->salvarAnexo($request->file($campo), $sinistro->id, $tipo);
                $sinistro->update(["{$campo}_path" => $path]);
            }
        }

        return redirect()->route('sinistro.show', $sinistro->id)
            ->with('success', 'Sinistro atualizado com sucesso.');
    }

    /**
     * AJAX: busca a reserva ativa de um veículo em uma data/hora específica.
     */
    public function buscarReservaPorVeiculoData(Request $request): JsonResponse
    {
        $veiculoId     = $request->query('veiculo_id');
        $dataHora      = $request->query('data_hora');

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

        $reserva = $this->buscarReservaContratualPorVeiculoData((int) $veiculo->id, $dt);

        if (!$reserva) {
            return response()->json(['reserva' => null]);
        }

        return response()->json([
            'reserva' => [
                'id'          => $reserva->id,
                'cliente'     => $reserva->usuario?->nome ?? 'Cliente #' . $reserva->user_id,
                'veiculo_id'   => $reserva->veiculo_id ?? null,
                'veiculo'      => trim((string) ($reserva->veiculo?->nome ?? $reserva->veiculo?->modelo?->nome ?? '')),
                'placa'        => $reserva->veiculo?->placa_licenca ?? null,
                'data_inicio' => $reserva->data_inicio,
                'data_fim'    => $reserva->data_fim,
            ],
        ]);
    }

    private function buscarReservaContratualPorVeiculoData(int $veiculoId, Carbon $dataHora): ?Reserva
    {
        $dataHoraSql = $dataHora->format('Y-m-d H:i:s');
        $reservasBase = Reserva::query()
            ->with(['usuario', 'veiculo', 'codigoReserva'])
            ->where('veiculo_id', $veiculoId);

        $reservaReal = (clone $reservasBase)
            ->whereNotNull('checkin_realizado_em')
            ->where('checkin_realizado_em', '<=', $dataHoraSql)
            ->where(function ($query) use ($dataHoraSql): void {
                $query
                    ->whereNull('checkout_realizado_em')
                    ->orWhere('checkout_realizado_em', '>=', $dataHoraSql);
            })
            ->orderByDesc('checkin_realizado_em')
            ->orderByDesc('id')
            ->first();

        if ($reservaReal) {
            return $reservaReal;
        }

        return (clone $reservasBase)
            ->where(function ($query) use ($dataHoraSql): void {
                $query
                    ->whereNull('checkin_realizado_em')
                    ->orWhereNull('checkout_realizado_em');
            })
            ->where('data_inicio', '<=', $dataHoraSql)
            ->where('data_fim', '>=', $dataHoraSql)
            ->orderByDesc('data_inicio')
            ->orderByDesc('id')
            ->first();
    }

    public function confirmarPagamentoEmpresa(Request $request, int $id): \Illuminate\Http\JsonResponse
    {
        $this->authorize('sinistro.edit');

        $sinistro = $this->sinistroService->queryScoped()->findOrFail($id);

        if ($sinistro->pago_empresa_em) {
            return response()->json(['error' => 'Pagamento da empresa já registrado.'], 422);
        }

        $request->validate([
            'forma_pagamento'      => 'required|in:dinheiro,cartao_credito,cartao_debito,pix,boleto,transferencia',
            'referencia_pagamento' => 'nullable|string|max:100',
        ]);

        $sinistro->update([
            'forma_pagamento_empresa'       => $request->input('forma_pagamento'),
            'referencia_pagamento_empresa'  => $request->input('referencia_pagamento'),
            'pago_empresa_em'               => now(),
            'pago_empresa_por_user_id'      => Auth::id(),
        ]);

        $lancamentoFinanceiro = $this->registrarLancamentoSinistroAction->lancamentoDespesaEmpresa($sinistro, $sinistro->pago_empresa_em);
        if ($lancamentoFinanceiro && $lancamentoFinanceiro->status !== 'pago') {
            $lancamentoFinanceiro->update([
                'status' => 'pago',
                'data_pagamento' => now()->toDateString(),
            ]);
        }

        $this->registrarHistoricoPagamento($sinistro->id, 'Pagamento da empresa registrado', [
            'forma_pagamento' => $request->input('forma_pagamento'),
            'referencia' => $request->input('referencia_pagamento'),
            'pago_em' => now()->format('d/m/Y H:i'),
            'pago_por' => Auth::user()->nome ?? Auth::user()->name,
        ]);

        return response()->json([
            'success'  => true,
            'message'  => 'Pagamento (empresa) confirmado com sucesso.',
            'pago_em'  => now()->format('d/m/Y H:i'),
            'pago_por' => Auth::user()->nome ?? Auth::user()->name,
        ]);
    }

    public function confirmarPagamentoCliente(Request $request, int $id): \Illuminate\Http\JsonResponse
    {
        $this->authorize('sinistro.edit');

        $sinistro = $this->sinistroService->queryScoped()->findOrFail($id);

        if ($sinistro->pago_cliente_em) {
            return response()->json(['error' => 'Pagamento do cliente já registrado.'], 422);
        }

        $request->validate([
            'forma_pagamento'      => 'required|in:dinheiro,cartao_credito,cartao_debito,pix,boleto',
            'referencia_pagamento' => 'nullable|string|max:100',
            'payment_method'       => 'nullable|string|max:255',
        ]);

        $forma = (string) $request->input('forma_pagamento');
        $referencia = $request->input('referencia_pagamento');

        if ($forma === 'cartao_credito') {
            $reserva = $sinistro->reserva_id ? $this->reservaService->getById((int) $sinistro->reserva_id, ['usuario']) : null;
            $empresaId = (int) ($sinistro->empresa_id ?? $reserva?->empresa_id ?? 0);
            $empresa = $empresaId ? $this->gatewayEmpresaConfigService->empresaPorId($empresaId) : null;
            if (!$empresa) {
                return response()->json(['error' => 'Empresa nao encontrada para processar pagamento.'], 422);
            }

            $gateway = $this->gatewayEmpresaConfigService->gateway($empresa);
            if ($gateway !== 'stripe') {
                return response()->json(['error' => 'Pagamento por cartao de credito esta disponivel apenas no Stripe.'], 422);
            }

            $user = $reserva?->usuario ?? $sinistro->cliente;
            if (!$user || empty($user->stripe_id)) {
                return response()->json(['error' => 'Cliente nao possui cadastro/cartao no Stripe.'], 422);
            }

            $paymentMethod = (string) ($request->input('payment_method') ?? '');
            if ($paymentMethod === '') {
                return response()->json(['error' => 'Selecione um cartao do cliente para cobrar.'], 422);
            }

            $valor = (float) ($sinistro->valor_responsabilidade_cliente ?? 0);
            if ($valor <= 0) {
                return response()->json(['error' => 'Valor invalido para cobranca do cliente.'], 422);
            }

            $currency = $this->gatewayEmpresaConfigService->moeda($empresa);
            $credenciais = $this->gatewayEmpresaConfigService->credenciais($empresa, $gateway);

            $payload = array_merge([
                'valor' => $valor,
                'currency' => $currency,
                'payment_method' => $paymentMethod,
                'customer_id' => (string) $user->stripe_id,
                'descricao' => 'Pagamento de sinistro #' . $sinistro->id . ' (cliente)',
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

            if (SchemaCache::hasTable('transacoes')) {
                Transacao::create([
                    'user_id' => $user->id,
                    'pago_por_user_id' => Auth::id(),
                    'reserva_id' => $reserva?->id ?? $sinistro->reserva_id,
                    'valor' => $valor,
                    'moeda' => strtoupper((string) ($resultado['currency'] ?? $currency ?? 'BRL')),
                    'metodo_pagamento' => 'stripe',
                    'gateway_transaction_id' => $resultado['transaction_id'] ?? null,
                    'status' => $resultado['raw_status'] ?? 'success',
                    'info_pagamento' => $resultado,
                ]);
            }
        } else {
            if (SchemaCache::hasTable('transacoes')) {
                $valor = (float) ($sinistro->valor_responsabilidade_cliente ?? 0);
                if ($valor > 0) {
                    $userId = $sinistro->cliente_id;
                    if (!$userId && $sinistro->reserva_id) {
                        $userId = $this->reservaService->getById((int) $sinistro->reserva_id)?->user_id;
                    }
                    $userId = $userId ?: Auth::id();

                    Transacao::create([
                        'user_id' => $userId,
                        'pago_por_user_id' => Auth::id(),
                        'reserva_id' => $sinistro->reserva_id,
                        'valor' => $valor,
                        'moeda' => 'BRL',
                        'metodo_pagamento' => $forma,
                        'status' => 'approved',
                        'info_pagamento' => [
                            'tipo' => 'sinistro_cliente',
                            'sinistro_id' => $sinistro->id,
                        ],
                    ]);
                }
            }
        }

        $sinistro->update([
            'forma_pagamento_cliente'      => $forma,
            'referencia_pagamento_cliente' => $referencia,
            'pago_cliente_em'              => now(),
            'pago_cliente_por_user_id'     => Auth::id(),
        ]);

        $lancamentoFinanceiro = $this->registrarLancamentoSinistroAction->lancamentoReceitaCliente($sinistro, $sinistro->pago_cliente_em);
        if ($lancamentoFinanceiro && $lancamentoFinanceiro->status !== 'pago') {
            $lancamentoFinanceiro->update([
                'status' => 'pago',
                'data_pagamento' => now()->toDateString(),
            ]);
        }

        $this->registrarHistoricoPagamento($sinistro->id, 'Pagamento do cliente registrado', [
            'forma_pagamento' => $forma,
            'referencia' => $referencia,
            'pago_em' => now()->format('d/m/Y H:i'),
            'pago_por' => Auth::user()->nome ?? Auth::user()->name,
        ]);

        if (!empty($sinistro->reserva_id)) {
            $this->reservaService->adicionaLog((int) $sinistro->reserva_id, Auth::id(), json_encode([
                'acao' => 'Pagamento de sinistro (cliente) confirmado',
                'sinistro_id' => $sinistro->id,
                'valor' => (float) ($sinistro->valor_responsabilidade_cliente ?? 0),
                'forma_pagamento' => $forma,
                'referencia' => $referencia,
            ]));
        }

        return response()->json([
            'success'  => true,
            'message'  => 'Pagamento (cliente) confirmado com sucesso.',
            'pago_em'  => now()->format('d/m/Y H:i'),
            'pago_por' => Auth::user()->nome ?? Auth::user()->name,
        ]);
    }

    private function registrarHistoricoPagamento(int $sinistroId, string $descricao, array $dados): void
    {
        SinistroHistorico::create([
            'sinistro_id' => $sinistroId,
            'user_id' => Auth::id(),
            'campo_alterado' => 'pagamento',
            'valor_anterior' => null,
            'valor_novo' => json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'descricao' => $descricao,
        ]);
    }

    public function destroy(int $id)
    {
        $this->authorize('sinistro.delete');

        $sinistro = $this->sinistroService->queryScoped()->findOrFail($id);
        $sinistro->delete();

        return redirect()->route('sinistro.index')
            ->with('success', 'Sinistro removido com sucesso.');
    }

    public function export(Request $request)
    {
        $this->authorize('sinistro.export');

        $filtros = $request->only([
            'data_inicio', 'data_fim', 'placa', 'tipo_sinistro', 'status', 'reserva_id', 'veiculo_id',
        ]);

        return $this->sinistroService->exportarCsv($filtros);
    }
}
