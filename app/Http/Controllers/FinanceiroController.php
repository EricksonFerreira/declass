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

    public function __construct(private readonly BreadcrumbService $breadcrumbService)
    {
    }

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

        $totalFaturado = (float) (clone $baseAgg)->sum('transacoes.valor');
        $qtdTransacoes = (int) (clone $baseAgg)->count('transacoes.id');
        $qtdReservas = (int) (clone $baseAgg)->distinct()->count('transacoes.reserva_id');
        $ticketMedio = $qtdTransacoes > 0 ? $totalFaturado / $qtdTransacoes : 0.0;

        $totalAprovado = $this->sumByStatus((clone $baseAgg), self::STATUS_APROVADOS);
        $totalPendente = $this->sumByStatus((clone $baseAgg), self::STATUS_PENDENTES);
        $totalFalha = max(0.0, $totalFaturado - $totalAprovado - $totalPendente);

        $qtdAprovadas = $this->countByStatus((clone $baseAgg), self::STATUS_APROVADOS);
        $taxaAprovacao = $qtdTransacoes > 0 ? ($qtdAprovadas / $qtdTransacoes) * 100 : 0.0;

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
        if (Schema::hasTable('movimentacoes')) {
            $movimentacoes = DB::table('movimentacoes');
            $movimentacoes = $this->applyEmpresaScope($movimentacoes, 'movimentacoes');
            $transferenciasCount = (int) $movimentacoes
                ->where('tipo_movimentacao_id', 10)
                ->count();
        }

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Financeiro - Transferencias');
        $titulo = 'Transferencias e Transacoes';

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
            'totalFalha',
            'qtdAprovadas',
            'taxaAprovacao',
            'statusBreakdown',
            'gatewayBreakdown',
            'topClientes',
            'evolucaoDiaria',
            'transferenciasCount',
            'moedaEmpresa',
            'breadcrumb',
            'titulo',
            'perPage'
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

        $faturamentoPorReserva = (clone $base)
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

        $totalFaturado = (float) (clone $base)->sum('transacoes.valor');
        $qtdReservasFaturadas = (int) (clone $base)->distinct()->count('transacoes.reserva_id');
        $ticketMedioPorReserva = $qtdReservasFaturadas > 0 ? $totalFaturado / $qtdReservasFaturadas : 0.0;

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Financeiro - Faturamento por Reserva');
        $titulo = 'Faturamento por Reserva';

        return view('financeiro.reservas', compact(
            'faturamentoPorReserva',
            'filters',
            'totalFaturado',
            'qtdReservasFaturadas',
            'ticketMedioPorReserva',
            'moedaEmpresa',
            'breadcrumb',
            'titulo',
            'perPage'
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
        ];
    }

    private function applyFilters($query, array $filters)
    {
        if (!empty($filters['reserva_id'])) {
            $query->where('transacoes.reserva_id', (int) $filters['reserva_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('transacoes.status', $filters['status']);
        }

        if (!empty($filters['metodo_pagamento'])) {
            $query->where('transacoes.metodo_pagamento', $filters['metodo_pagamento']);
        }

        if (!empty($filters['data_inicio'])) {
            $query->whereDate('transacoes.created_at', '>=', $filters['data_inicio']);
        }

        if (!empty($filters['data_fim'])) {
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

    private function applyEmpresaScope($query, string $table)
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'empresa_id')) {
            return $query;
        }

        $empresaId = $this->empresaIdParaConsulta();
        if ($empresaId === null) {
            return $query;
        }

        return $query->where($table . '.empresa_id', $empresaId);
    }

    private function empresaIdParaConsulta(): ?int
    {
        $user = Auth::user();
        if (!$user) {
            return null;
        }

        if (!$user->isAdministrador()) {
            return $user->empresa_id ? (int) $user->empresa_id : null;
        }

        $empresaContexto = app(EmpresaContext::class)->empresaId();

        return $empresaContexto !== null ? (int) $empresaContexto : null;
    }

    private function moedaEmpresaParaConsulta(): string
    {
        $empresaId = $this->empresaIdParaConsulta();

        if ($empresaId === null || !Schema::hasTable('empresas') || !Schema::hasColumn('empresas', 'moeda_pagamento')) {
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
