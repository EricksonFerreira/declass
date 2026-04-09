<?php

namespace App\Http\Controllers;

use App\Services\BreadcrumbService;
use App\Support\EmpresaContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FinanceiroController extends Controller
{
    private const STATUS_APROVADOS = ['completa', 'aprovado', 'approved', 'succeeded', 'success'];

    private const STATUS_PENDENTES = ['pendente', 'pending', 'aguardando', 'processando', 'processing'];

    private const STATUS_CANCELADOS = ['cancelado', 'cancelada', 'cancelled', 'canceled', 'refunded', 'reembolsado', 'reembolsada'];

    public function __construct(private readonly BreadcrumbService $breadcrumbService) {}

    public function index(Request $request)
    {
        $filters = $this->extractFilters($request);
        $moedaEmpresa = $this->moedaEmpresaParaConsulta();
        $hasPagoPor = Schema::hasColumn('transacoes', 'pago_por_user_id');
        $hasMoeda = Schema::hasColumn('transacoes', 'moeda');

        $base = DB::table('transacoes')
            ->leftJoin('reservas', 'reservas.id', '=', 'transacoes.reserva_id')
            ->leftJoin('users', 'users.id', '=', 'transacoes.user_id')
            ->select([
                'transacoes.id',
                'transacoes.reserva_id',
                'transacoes.user_id',
                'transacoes.valor',
                'transacoes.metodo_pagamento',
                'transacoes.status',
                'transacoes.created_at',
                'users.nome as usuario_nome',
            ]);

        if ($hasPagoPor) {
            $base->leftJoin('users as usuario_pagador', 'usuario_pagador.id', '=', 'transacoes.pago_por_user_id');
            $base->addSelect('usuario_pagador.nome as usuario_pagador_nome');
        } else {
            $base->addSelect(DB::raw('NULL as usuario_pagador_nome'));
        }

        if ($hasMoeda) {
            $base->addSelect('transacoes.moeda');
        } else {
            $base->addSelect(DB::raw("'BRL' as moeda"));
        }

        $base = $this->applyEmpresaScope($base, 'transacoes');
        $base = $this->applyFilters($base, $filters);

        $perPage = (int) $request->integer('per_page', 15);
        $perPage = in_array($perPage, [10, 15, 25, 50, 100], true) ? $perPage : 15;

        $transacoes = (clone $base)
            ->orderByDesc('transacoes.created_at')
            ->paginate($perPage)
            ->withQueryString();

        $baseAgg = $this->forAggregation($base);

        $totalGeral = (float) (clone $baseAgg)->sum('transacoes.valor');
        $totalFaturado = $totalGeral;
        $qtdTransacoes = (int) (clone $baseAgg)->count('transacoes.id');
        $qtdReservas = (int) (clone $baseAgg)->distinct()->count('transacoes.reserva_id');
        $ticketMedio = $qtdTransacoes > 0 ? $totalFaturado / $qtdTransacoes : 0.0;

        $totalAprovado = $this->sumByStatus((clone $baseAgg), self::STATUS_APROVADOS);
        $totalPendente = $this->sumByStatus((clone $baseAgg), self::STATUS_PENDENTES);
        $totalCancelado = $this->sumByStatus((clone $baseAgg), self::STATUS_CANCELADOS);
        $totalFalha = max(0.0, $totalGeral - $totalAprovado - $totalPendente - $totalCancelado);

        $qtdAprovadas = $this->countByStatus((clone $baseAgg), self::STATUS_APROVADOS);
        $qtdCanceladas = $this->countByStatus((clone $baseAgg), self::STATUS_CANCELADOS);
        $taxaBase = max(0, $qtdTransacoes - $qtdCanceladas);
        $taxaAprovacao = $taxaBase > 0 ? ($qtdAprovadas / $taxaBase) * 100 : 0.0;

        $statusDisponiveis = DB::table('transacoes')
            ->when($this->empresaIdParaConsulta() !== null, function ($query) {
                $empresaId = $this->empresaIdParaConsulta();
                if ($empresaId !== null && Schema::hasColumn('transacoes', 'empresa_id')) {
                    $query->where('transacoes.empresa_id', $empresaId);
                }
            })
            ->whereNotNull('status')
            ->distinct()
            ->orderBy('status')
            ->pluck('status')
            ->values();

        $metodosDisponiveis = DB::table('transacoes')
            ->when($this->empresaIdParaConsulta() !== null, function ($query) {
                $empresaId = $this->empresaIdParaConsulta();
                if ($empresaId !== null && Schema::hasColumn('transacoes', 'empresa_id')) {
                    $query->where('transacoes.empresa_id', $empresaId);
                }
            })
            ->whereNotNull('metodo_pagamento')
            ->distinct()
            ->orderBy('metodo_pagamento')
            ->pluck('metodo_pagamento')
            ->values();

        $statusBreakdown = (clone $baseAgg)
            ->selectRaw('COALESCE(transacoes.status, "sem_status") as status')
            ->selectRaw('COUNT(transacoes.id) as quantidade')
            ->selectRaw('SUM(transacoes.valor) as total')
            ->groupBy('status')
            ->orderByDesc('total')
            ->get();

        $gatewayBreakdown = (clone $baseAgg)
            ->selectRaw('COALESCE(transacoes.metodo_pagamento, "nao_definido") as gateway')
            ->selectRaw('COUNT(transacoes.id) as quantidade')
            ->selectRaw('SUM(transacoes.valor) as total')
            ->groupBy('gateway')
            ->orderByDesc('total')
            ->get();

        $topClientes = (clone $baseAgg)
            ->selectRaw('COALESCE(users.nome, "Sem usuario") as cliente')
            ->selectRaw('COUNT(transacoes.id) as quantidade')
            ->selectRaw('SUM(transacoes.valor) as total')
            ->groupBy('cliente')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $evolucaoDiaria = (clone $baseAgg)
            ->whereDate('transacoes.created_at', '>=', now()->subDays(30)->toDateString())
            ->selectRaw('DATE(transacoes.created_at) as dia')
            ->selectRaw('COUNT(transacoes.id) as quantidade')
            ->selectRaw('SUM(transacoes.valor) as total')
            ->groupBy('dia')
            ->orderBy('dia')
            ->get();

        $transferenciasCount = 0;
        $transferenciasCanceladasCount = 0;
        if (Schema::hasTable('movimentacoes')) {
            $movimentacoes = DB::table('movimentacoes');
            $movimentacoes = $this->applyEmpresaScope($movimentacoes, 'movimentacoes');
            $transferenciasCount = (int) $movimentacoes
                ->where('tipo_movimentacao_id', 10)
                ->count();

            $movimentacoesCanceladas = DB::table('movimentacoes');
            $movimentacoesCanceladas = $this->applyEmpresaScope($movimentacoesCanceladas, 'movimentacoes');
            $statusTable = null;
            $statusColumn = null;

            if (Schema::hasTable('status_movimentacao')) {
                $statusTable = 'status_movimentacao';
                $statusColumn = 'status_movimentacao.nome';
            } elseif (Schema::hasTable('status_movimentacaos')) {
                $statusTable = 'status_movimentacaos';
                $statusColumn = 'status_movimentacaos.nome';
            }

            if ($statusTable) {
                $movimentacoesCanceladas->join($statusTable, 'movimentacoes.status_movimentacao_id', '=', $statusTable.'.id');
            }

            $movimentacoesCanceladas->where('movimentacoes.tipo_movimentacao_id', 10);

            if ($statusColumn) {
                $movimentacoesCanceladas->whereIn(DB::raw("LOWER(COALESCE($statusColumn, ''))"), self::STATUS_CANCELADOS);
            }

            $transferenciasCanceladasCount = (int) $movimentacoesCanceladas->count();
        }

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Financeiro - Transferencias');
        $titulo = 'Transferencias e Transacoes';
        $statusAprovados = self::STATUS_APROVADOS;
        $statusPendentes = self::STATUS_PENDENTES;
        $statusCancelados = self::STATUS_CANCELADOS;

        return view('financeiro.index', compact(
            'transacoes',
            'filters',
            'statusDisponiveis',
            'metodosDisponiveis',
            'totalFaturado',
            'qtdTransacoes',
            'qtdReservas',
            'ticketMedio',
            'totalAprovado',
            'totalPendente',
            'totalCancelado',
            'totalFalha',
            'qtdAprovadas',
            'qtdCanceladas',
            'taxaAprovacao',
            'statusBreakdown',
            'gatewayBreakdown',
            'topClientes',
            'evolucaoDiaria',
            'transferenciasCount',
            'transferenciasCanceladasCount',
            'moedaEmpresa',
            'breadcrumb',
            'titulo',
            'perPage',
            'statusAprovados',
            'statusPendentes',
            'statusCancelados'
        ));
    }

    public function reservas(Request $request)
    {
        $filters = $this->extractFilters($request);
        $moedaEmpresa = $this->moedaEmpresaParaConsulta();

        $base = DB::table('transacoes')
            ->leftJoin('reservas', 'reservas.id', '=', 'transacoes.reserva_id')
            ->leftJoin('users', 'users.id', '=', 'reservas.user_id');

        $base = $this->applyEmpresaScope($base, 'transacoes');
        $base = $this->applyFilters($base, $filters);

        $perPage = (int) $request->integer('per_page', 15);
        $perPage = in_array($perPage, [10, 15, 25, 50, 100], true) ? $perPage : 15;

        $baseAgg = $this->forAggregation($base);
        $baseCanceladas = $this->applyReservasCanceladasScope((clone $base));
        $baseFaturamento = $this->applyReservasNaoCanceladasScope((clone $base));
        $totalCancelado = (float) (clone $baseCanceladas)->sum('transacoes.valor');

        $faturamentoPorReserva = (clone $baseFaturamento)
            ->select([
                'transacoes.reserva_id',
                'users.nome as cliente_nome',
                DB::raw('COUNT(transacoes.id) as quantidade_transacoes'),
                DB::raw('SUM(transacoes.valor) as valor_total'),
                DB::raw('MAX(transacoes.created_at) as ultima_transacao_em'),
            ])
            ->groupBy('transacoes.reserva_id', 'users.nome')
            ->orderByDesc('valor_total')
            ->paginate($perPage)
            ->withQueryString();

        // ── Busca os detalhes das transacoes referentes as reservas paginadas ──
        $reservaIds = collect($faturamentoPorReserva->items())->pluck('reserva_id')->filter()->unique()->toArray();
        $detalhes = collect();
        if (!empty($reservaIds)) {
            $detalhes = \App\Models\Transacao::with(['user', 'pagoPor'])
                ->whereIn('reserva_id', $reservaIds)
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy('reserva_id');
        }

        $statusCancelados = ['canceled', 'cancelled', 'refunded', 'chargeback', 'voided', 'failed', 'refused', 'denied', 'cancelada'];
        $statusAprovados = ['paid', 'approved', 'authorized', 'success', 'succeeded', 'pago', 'aprovada', 'concluido'];
        $statusPendentes = ['pending', 'processing', 'in_process', 'waiting', 'pendente', 'aguardando'];

        $totalFaturado = (float) (clone $baseFaturamento)->sum('transacoes.valor');
        $qtdReservasFaturadas = (int) (clone $baseFaturamento)->distinct()->count('transacoes.reserva_id');
        $ticketMedioPorReserva = $qtdReservasFaturadas > 0 ? $totalFaturado / $qtdReservasFaturadas : 0.0;

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Financeiro - Faturamento por Reserva');
        $titulo = 'Faturamento por Reserva';

        return view('financeiro.reservas', compact(
            'faturamentoPorReserva',
            'filters',
            'totalFaturado',
            'qtdReservasFaturadas',
            'ticketMedioPorReserva',
            'totalCancelado',
            'moedaEmpresa',
            'breadcrumb',
            'titulo',
            'perPage',
            'detalhes',
            'statusCancelados',
            'statusAprovados',
            'statusPendentes'
        ));
    }

    private function extractFilters(Request $request): array
    {
        return [
            'reserva_id' => $request->input('reserva_id'),
            'status' => $request->input('status'),
            'metodo_pagamento' => $request->input('metodo_pagamento'),
            'data_inicio' => $request->input('data_inicio'),
            'data_fim' => $request->input('data_fim'),
            'canal' => $request->input('canal'),
            'cliente' => $request->input('cliente'),
        ];
    }

    private function applyFilters($query, array $filters)
    {
        if (! empty($filters['reserva_id'])) {
            $query->where('transacoes.reserva_id', (int) $filters['reserva_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('transacoes.status', $filters['status']);
        }

        if (! empty($filters['metodo_pagamento'])) {
            $query->where('transacoes.metodo_pagamento', $filters['metodo_pagamento']);
        }
        
        if (! empty($filters['canal'])) {
            if ($filters['canal'] === 'reserva') {
                $query->where(function($q) {
                    $q->where('transacoes.info_pagamento->tipo', 'reserva')
                      ->orWhereNull('transacoes.info_pagamento->tipo');
                });
            } else {
                $query->where('transacoes.info_pagamento->tipo', $filters['canal']);
            }
        }

        if (! empty($filters['cliente'])) {
            $clienteText = '%' . $filters['cliente'] . '%';
            $query->where(function($q) use ($clienteText) {
                // As the query uses leftJoins for users as u (reserva client) and users as up (payer)
                // in the index action, we check if either name matches.
                $q->where('u.nome', 'like', $clienteText)
                  ->orWhere('up.nome', 'like', $clienteText);
            });
        }

        if (! empty($filters['data_inicio'])) {
            $query->whereDate('transacoes.created_at', '>=', $filters['data_inicio']);
        }

        if (! empty($filters['data_fim'])) {
            $query->whereDate('transacoes.created_at', '<=', $filters['data_fim']);
        }

        return $query;
    }

    private function sumByStatus($query, array $statuses): float
    {
        return (float) $query
            ->whereIn(DB::raw('LOWER(COALESCE(transacoes.status, ""))'), $statuses)
            ->sum('transacoes.valor');
    }

    private function countByStatus($query, array $statuses): int
    {
        return (int) $query
            ->whereIn(DB::raw('LOWER(COALESCE(transacoes.status, ""))'), $statuses)
            ->count('transacoes.id');
    }

    private function applyCanceladosExclusion($query)
    {
        return $query->whereNotIn(DB::raw('LOWER(COALESCE(transacoes.status, ""))'), self::STATUS_CANCELADOS);
    }

    private function applyReservasNaoCanceladasScope($query)
    {
        if (Schema::hasColumn('reservas', 'data_cancelamento')) {
            return $query->whereNull('reservas.data_cancelamento');
        }

        if (Schema::hasColumn('reservas', 'status_reserva_id')) {
            return $query->where('reservas.status_reserva_id', '!=', 3);
        }

        return $query;
    }

    private function applyReservasCanceladasScope($query)
    {
        if (Schema::hasColumn('reservas', 'data_cancelamento')) {
            return $query->whereNotNull('reservas.data_cancelamento');
        }

        if (Schema::hasColumn('reservas', 'status_reserva_id')) {
            return $query->where('reservas.status_reserva_id', 3);
        }

        return $query;
    }

    private function applyEmpresaScope($query, string $table)
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'empresa_id')) {
            return $query;
        }

        $empresaId = $this->empresaIdParaConsulta();
        if ($empresaId === null) {
            return $query;
        }

        return $query->where($table.'.empresa_id', $empresaId);
    }

    private function empresaIdParaConsulta(): ?int
    {
        $user = Auth::user();
        if (! $user) {
            return null;
        }

        if (! $user->isAdministrador()) {
            return $user->empresa_id ? (int) $user->empresa_id : null;
        }

        $empresaContexto = app(EmpresaContext::class)->empresaId();

        return $empresaContexto !== null ? (int) $empresaContexto : null;
    }

    private function moedaEmpresaParaConsulta(): string
    {
        $empresaId = $this->empresaIdParaConsulta();

        if ($empresaId === null || ! Schema::hasTable('empresas') || ! Schema::hasColumn('empresas', 'moeda_pagamento')) {
            return 'BRL';
        }

        $moeda = DB::table('empresas')
            ->where('id', $empresaId)
            ->value('moeda_pagamento');

        $moeda = strtoupper((string) $moeda);

        return in_array($moeda, ['BRL', 'USD', 'EUR'], true) ? $moeda : 'BRL';
    }

    private function forAggregation($query)
    {
        return (clone $query)
            ->cloneWithout(['columns', 'orders', 'groups', 'havings', 'limit', 'offset'])
            ->cloneWithoutBindings(['select', 'order']);
    }
}
