<?php

namespace App\Http\Controllers;

use App\Models\EmpresaAlerta;
use App\Models\Permissao;
use App\Models\Regra;
use App\Models\TipoTransacao;
use App\Models\User;
use App\Support\EmpresaContext;
use App\Services\BreadcrumbService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use App\Support\SchemaCache;

class HomeController extends Controller
{
    private const STATUS_APROVADOS = ['completa', 'aprovado', 'approved', 'succeeded', 'success'];
    private const STATUS_PENDENTES = ['pendente', 'pending', 'aguardando', 'processando', 'processing'];
    private const ALERTAS_OCULTOS = ['modulo_nfse_indisponivel', 'site_vendas_indisponivel'];

    protected $titulo;
    protected $breadcrumbService;
    public function __construct(BreadcrumbService $breadcrumbService)
    {
        $this->titulo = 'Home';
        $this->breadcrumbService = $breadcrumbService;
    }

public function home()
{
    $breadcrumb    = $this->breadcrumbService->prepararBreadcrumbUnico('Home');
    $homeSections  = $this->getHomeSectionPermissions();
    $items         = $this->filterSystemCards($this->getSystemCards());
    $hasVisibleItems = collect($items)->contains(fn (array $grupo) => !empty($grupo['items']));
    $hasVisibleCharts = in_array(true, $homeSections, true);
    $totalVeiculos = 0;

    $alertas = collect();
    $empresaUser = Auth::user()?->empresa;
    if ($empresaUser) {
        $alertas = EmpresaAlerta::where('empresa_id', $empresaUser->id)
            ->where('resolvido', false)
            ->whereNotIn('tipo', self::ALERTAS_OCULTOS)
            ->orderByRaw("FIELD(nivel, 'critico', 'aviso', 'info')")
            ->get();
    }

    if ($homeSections['frota']) {
        $veiculosQuery = \App\Models\Veiculo::query();
        $veiculosQuery = $this->applyEmpresaScope($veiculosQuery, 'veiculos');
        $totalVeiculos = (int) $veiculosQuery->count();
    }

    return view('home', compact('breadcrumb', 'items', 'totalVeiculos', 'homeSections', 'hasVisibleItems', 'hasVisibleCharts', 'alertas'))
        ->with('titulo', $this->titulo);
}

public function chartData(Request $request)
{
    $homeSections = $this->getHomeSectionPermissions();
    $mesesPt = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
    $dataInicio = $request->filled('data_inicio')
        ? Carbon::parse($request->data_inicio)->startOfMonth()
        : Carbon::now()->subMonths(11)->startOfMonth();

    $dataFim = $request->filled('data_fim')
        ? Carbon::parse($request->data_fim)->endOfMonth()
        : Carbon::now()->endOfMonth();

    $labels = [];
    $meses = [];
    $cursor = $dataInicio->copy();
    while ($cursor->lte($dataFim)) {
        $labels[] = $mesesPt[$cursor->month - 1] . '/' . $cursor->format('y');
        $meses[] = $cursor->copy();
        $cursor->addMonth();
    }

    $confirmadas = [];
    $cancelamentos = [];
    $tendencia = [];
    $movStatus = collect();
    $gruposVeiculos = collect();

    $cores = ['#0891b2', '#059669', '#d97706', '#7c3aed', '#e11d48', '#0ea5e9'];
    $ocupacaoFrota = [];
    $ocupacaoGeral = 0;
    $totalReservas = 0;
    $totalMovimentacoes = 0;

    $financeiroValores = array_fill(0, count($labels), 0.0);
    $financeiroQuantidades = array_fill(0, count($labels), 0);
    $financeiroStatus = [0.0, 0.0, 0.0];
    $financeiroGatewayLabels = [];
    $financeiroGatewayData = [];
    $totalFinanceiro = 0.0;
    $ticketMedioFinanceiro = 0.0;
    $qtdTransacoesFinanceiro = 0;
    $taxaAprovacaoFinanceiro = 0.0;
    $satisfacaoResumo = [
        'total_respostas' => 0,
        'media_geral' => 0.0,
        'media_facilidade' => 0.0,
        'nps' => 0.0,
        'promotores' => 0,
        'neutros' => 0,
        'detratores' => 0,
    ];
    $satisfacaoEvolucao = [
        'labels' => $labels,
        'satisfacao_geral' => array_fill(0, count($labels), 0.0),
        'facilidade_reserva' => array_fill(0, count($labels), 0.0),
        'nps' => array_fill(0, count($labels), 0.0),
        'respostas' => array_fill(0, count($labels), 0),
    ];
    $reservasOrigemResumo = [
        'total' => 0,
        'site' => 0,
        'colaborador' => 0,
        'sem_origem' => 0,
        'percentual_site' => 0.0,
        'percentual_colaborador' => 0.0,
        'percentual_sem_origem' => 0.0,
    ];
    $reservasOrigemDistribuicao = [
        'labels' => ['Site', 'Colaborador', 'Sem origem'],
        'data' => [0, 0, 0],
    ];
    $reservasColaboradoresRanking = [
        'labels' => [],
        'data' => [],
        'rows' => [],
    ];
    $satisfacaoDistribuicao = [
        'labels' => ['Promotores', 'Neutros', 'Detratores'],
        'data' => [0, 0, 0],
    ];
    $satisfacaoGrupos = ['labels' => [], 'data' => [], 'respostas' => []];
    $satisfacaoVeiculos = ['labels' => [], 'data' => [], 'respostas' => []];
    $satisfacaoMarcas = ['labels' => [], 'data' => [], 'respostas' => []];
    $satisfacaoModelos = ['labels' => [], 'data' => [], 'respostas' => []];
    $satisfacaoResponsaveis = ['labels' => [], 'data' => [], 'respostas' => []];
    $analiseAvancadaResumo = [
        'total_veiculos' => 0,
        'veiculos_lucrativos' => 0,
        'veiculos_prejuizo' => 0,
        'veiculos_protegidos' => 0,
        'saude_media' => 0.0,
        'eficiencia_media' => 0.0,
        'custo_real_total' => 0.0,
        'lucro_real_total' => 0.0,
        'valor_protegido_total' => 0.0,
    ];
    $analiseAvancadaLucrativos = ['labels' => [], 'data' => [], 'rows' => []];
    $analiseAvancadaPrejuizo = ['labels' => [], 'data' => [], 'rows' => []];
    $analiseAvancadaProtecao = ['labels' => ['Protegidos', 'Sem proteção'], 'data' => [0, 0]];
    $analiseAvancadaDetalhes = [];
    $analiseAvancadaRecomendacoes = [];

    if ($homeSections['reservas'] || $homeSections['ocupacao']) {
        $reservasNoPeriodo = \App\Models\Reserva::query()
            ->whereBetween('reservas.created_at', [$dataInicio, $dataFim]);
        $reservasNoPeriodo = $this->applyEmpresaScope($reservasNoPeriodo, 'reservas');

        if ($homeSections['reservas']) {
            $resConfirmadas = (clone $reservasNoPeriodo)
                ->whereNull('data_cancelamento')
                ->selectRaw('YEAR(reservas.created_at) as ano, MONTH(created_at) as mes, COUNT(*) as total')
                ->groupBy('ano', 'mes')
                ->get()
                ->keyBy(fn($r) => $r->ano . '-' . $r->mes);

            $resCanceladas = (clone $reservasNoPeriodo)
                ->whereNotNull('data_cancelamento')
                ->selectRaw('YEAR(reservas.created_at) as ano, MONTH(created_at) as mes, COUNT(*) as total')
                ->groupBy('ano', 'mes')
                ->get()
                ->keyBy(fn($r) => $r->ano . '-' . $r->mes);

            foreach ($meses as $m) {
                $key = $m->year . '-' . $m->month;
                $conf = (int) ($resConfirmadas[$key]->total ?? 0);
                $canc = (int) ($resCanceladas[$key]->total ?? 0);
                $confirmadas[] = $conf;
                $cancelamentos[] = $canc;
                $tendencia[] = $conf + $canc;
            }

            $totalReservas = (int) (clone $reservasNoPeriodo)->count();

            if (SchemaCache::hasColumn('reservas', 'origem_cadastro') || SchemaCache::hasColumn('reservas', 'criado_por_user_id')) {
                $baseOrigemReservas = clone $reservasNoPeriodo;
                $origemRows = $baseOrigemReservas
                    ->selectRaw("COALESCE(NULLIF(origem_cadastro, ''), 'sem_origem') as origem")
                    ->selectRaw('COUNT(*) as total')
                    ->groupBy('origem')
                    ->get()
                    ->keyBy('origem');

                $site = (int) ($origemRows['site']->total ?? 0);
                $colaborador = (int) ($origemRows['colaborador']->total ?? 0);
                $semOrigem = (int) ($origemRows['sem_origem']->total ?? 0);
                $totalOrigem = $site + $colaborador + $semOrigem;

                $reservasOrigemResumo = [
                    'total' => $totalOrigem,
                    'site' => $site,
                    'colaborador' => $colaborador,
                    'sem_origem' => $semOrigem,
                    'percentual_site' => $totalOrigem > 0 ? round(($site / $totalOrigem) * 100, 1) : 0.0,
                    'percentual_colaborador' => $totalOrigem > 0 ? round(($colaborador / $totalOrigem) * 100, 1) : 0.0,
                    'percentual_sem_origem' => $totalOrigem > 0 ? round(($semOrigem / $totalOrigem) * 100, 1) : 0.0,
                ];

                $reservasOrigemDistribuicao = [
                    'labels' => ['Site', 'Colaborador', 'Sem origem'],
                    'data' => [$site, $colaborador, $semOrigem],
                ];
            }

            if (SchemaCache::hasColumn('reservas', 'criado_por_user_id')) {
                $rankingColaboradores = (clone $reservasNoPeriodo)
                    ->leftJoin('users', 'users.id', '=', 'reservas.criado_por_user_id')
                    ->whereNotNull('reservas.criado_por_user_id')
                    ->selectRaw("COALESCE(users.nome, users.nome, CONCAT('Usuario #', reservas.criado_por_user_id)) as label")
                    ->selectRaw('reservas.criado_por_user_id as colaborador_id')
                    ->selectRaw('COUNT(*) as total')
                    ->groupBy('reservas.criado_por_user_id', 'users.nome', 'users.nome')
                    ->orderByDesc('total')
                    ->limit(10)
                    ->get();

                $reservasColaboradoresRanking = [
                    'labels' => $rankingColaboradores->pluck('label')->toArray(),
                    'data' => $rankingColaboradores->pluck('total')->map(fn ($v) => (int) $v)->toArray(),
                    'rows' => $rankingColaboradores->map(function ($row): array {
                        return [
                            'label' => (string) $row->label,
                            'total' => (int) $row->total,
                        ];
                    })->values()->all(),
                ];
            }
        }

        if ($homeSections['ocupacao'] && SchemaCache::hasTable('grupos_frota')) {
            $totalPorGrupoBase = DB::table('veiculos')
                ->join('grupos_frota', 'veiculos.grupo_frota_id', '=', 'grupos_frota.id')
                ->selectRaw('grupos_frota.id, grupos_frota.nome, COUNT(*) as total');
            $totalPorGrupoBase = $this->applyEmpresaScope($totalPorGrupoBase, 'veiculos');
            $totalPorGrupo = $totalPorGrupoBase
                ->groupBy('grupos_frota.id', 'grupos_frota.nome')
                ->get();

            $ativasPorGrupoBase = DB::table('reservas')
                ->whereNull('data_cancelamento')
                ->where('data_inicio', '<=', $dataFim)
                ->where('data_fim', '>=', $dataInicio)
                ->selectRaw('grupo_frota_id, COUNT(*) as total');
            $ativasPorGrupoBase = $this->applyEmpresaScope($ativasPorGrupoBase, 'reservas');
            $ativasPorGrupo = $ativasPorGrupoBase
                ->groupBy('grupo_frota_id')
                ->get()
                ->keyBy('grupo_frota_id');

            foreach ($totalPorGrupo as $i => $grupo) {
                $ativas = (int) ($ativasPorGrupo[$grupo->id]->total ?? 0);
                $pct = $grupo->total > 0 ? round($ativas / $grupo->total * 100) : 0;
                $ocupacaoFrota[] = [
                    'label' => $grupo->nome,
                    'pct' => $pct,
                    'color' => $cores[$i % count($cores)],
                ];
            }

            $totalVeiculosGeral = (int) $totalPorGrupo->sum('total');
            $totalAtivas = (int) $ativasPorGrupo->sum('total');
            $ocupacaoGeral = $totalVeiculosGeral > 0 ? round($totalAtivas / $totalVeiculosGeral * 100) : 0;
        }
    }

    if ($homeSections['movimentacoes']) {
        $movStatusBase = DB::table('movimentacoes')
            ->join('status_movimentacao', 'movimentacoes.status_movimentacao_id', '=', 'status_movimentacao.id')
            ->selectRaw('status_movimentacao.nome as label, COUNT(*) as total')
            ->whereBetween('movimentacoes.created_at', [$dataInicio, $dataFim]);
        $movStatusBase = $this->applyEmpresaScope($movStatusBase, 'movimentacoes');
        $movStatus = $movStatusBase
            ->groupBy('status_movimentacao.id', 'status_movimentacao.nome')
            ->get();

        $movimentacoesNoPeriodo = \App\Models\Movimentacao::query()
            ->whereBetween('created_at', [$dataInicio, $dataFim]);
        $movimentacoesNoPeriodo = $this->applyEmpresaScope($movimentacoesNoPeriodo, 'movimentacoes');
        $totalMovimentacoes = (int) (clone $movimentacoesNoPeriodo)->count();
    }

    if ($homeSections['frota'] && SchemaCache::hasTable('grupos_frota')) {
        $gruposVeiculosBase = DB::table('veiculos')
            ->join('grupos_frota', 'veiculos.grupo_frota_id', '=', 'grupos_frota.id')
            ->selectRaw('grupos_frota.nome as label, COUNT(*) as total');
        $gruposVeiculosBase = $this->applyEmpresaScope($gruposVeiculosBase, 'veiculos');
        $gruposVeiculos = $gruposVeiculosBase
            ->groupBy('grupos_frota.id', 'grupos_frota.nome')
            ->get();
    }

    if ($homeSections['financeiro'] && SchemaCache::hasTable('transacoes')) {
        $positiveAmountExpr = 'SUM(CASE WHEN COALESCE(transacoes.valor, 0) > 0 THEN COALESCE(transacoes.valor, 0) ELSE 0 END)';
        $baseFinanceiro = DB::table('transacoes')
            ->whereBetween('transacoes.created_at', [$dataInicio, $dataFim]);
        $baseFinanceiro = $this->applyEmpresaScope($baseFinanceiro, 'transacoes');

        $financeiroMensal = (clone $baseFinanceiro)
            ->selectRaw('YEAR(transacoes.created_at) as ano, MONTH(transacoes.created_at) as mes')
            ->selectRaw($positiveAmountExpr . ' as total')
            ->selectRaw('COUNT(transacoes.id) as quantidade')
            ->groupBy('ano', 'mes')
            ->get()
            ->keyBy(fn($r) => $r->ano . '-' . $r->mes);

        foreach ($meses as $index => $m) {
            $key = $m->year . '-' . $m->month;
            $financeiroValores[$index] = round((float) ($financeiroMensal[$key]->total ?? 0.0), 2);
            $financeiroQuantidades[$index] = (int) ($financeiroMensal[$key]->quantidade ?? 0);
        }

        $totalFinanceiroQuery = (clone $baseFinanceiro)->selectRaw($positiveAmountExpr . ' as total_pos');
        $totalFinanceiro = (float) ($totalFinanceiroQuery->value('total_pos') ?? 0);
        $qtdTransacoesFinanceiro = (int) (clone $baseFinanceiro)->count('transacoes.id');
        $ticketMedioFinanceiro = $qtdTransacoesFinanceiro > 0 ? $totalFinanceiro / $qtdTransacoesFinanceiro : 0.0;

        $totalAprovado = $this->sumByStatus((clone $baseFinanceiro), self::STATUS_APROVADOS);
        $totalPendente = $this->sumByStatus((clone $baseFinanceiro), self::STATUS_PENDENTES);
        $totalFalha = max(0.0, $totalFinanceiro - $totalAprovado - $totalPendente);
        $financeiroStatus = [
            round($totalAprovado, 2),
            round($totalPendente, 2),
            round($totalFalha, 2),
        ];

        $qtdAprovadas = $this->countByStatus((clone $baseFinanceiro), self::STATUS_APROVADOS);
        $taxaAprovacaoFinanceiro = $qtdTransacoesFinanceiro > 0
            ? round(($qtdAprovadas / $qtdTransacoesFinanceiro) * 100, 1)
            : 0.0;

        $gatewayRows = (clone $baseFinanceiro)
            ->selectRaw('COALESCE(transacoes.metodo_pagamento, "nao_definido") as gateway')
            ->selectRaw($positiveAmountExpr . ' as total')
            ->groupBy('gateway')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        $financeiroGatewayLabels = $gatewayRows->pluck('gateway')->toArray();
        $financeiroGatewayData = $gatewayRows
            ->pluck('total')
            ->map(fn($v) => round((float) $v, 2))
            ->toArray();
    }

    if ($homeSections['satisfacao'] && SchemaCache::hasTable('reserva_satisfacoes')) {
        $baseSatisfacao = DB::table('reserva_satisfacoes as rs')
            ->join('reservas as r', 'r.id', '=', 'rs.reserva_id')
            ->whereBetween('rs.created_at', [$dataInicio, $dataFim]);
        $empresaId = $this->empresaIdParaConsulta();
        if ($empresaId !== null) {
            if (SchemaCache::hasColumn('reservas', 'empresa_id')) {
                $baseSatisfacao->where('r.empresa_id', $empresaId);
            } elseif (SchemaCache::hasColumn('reserva_satisfacoes', 'empresa_id')) {
                $baseSatisfacao->where('rs.empresa_id', $empresaId);
            }
        }
        $satisfacaoResumo['total_respostas'] = (int) (clone $baseSatisfacao)->count();
        $satisfacaoResumo['media_geral'] = round((float) (clone $baseSatisfacao)->avg('rs.satisfacao_geral'), 2);
        $satisfacaoResumo['media_facilidade'] = round((float) (clone $baseSatisfacao)->avg('rs.facilidade_reserva'), 2);

        $promotores = (int) (clone $baseSatisfacao)->where('rs.nps_nota', '>=', 9)->count();
        $detratores = (int) (clone $baseSatisfacao)->where('rs.nps_nota', '<=', 6)->count();
        $neutros = max(0, $satisfacaoResumo['total_respostas'] - $promotores - $detratores);
        $satisfacaoResumo['promotores'] = $promotores;
        $satisfacaoResumo['neutros'] = $neutros;
        $satisfacaoResumo['detratores'] = $detratores;
        $satisfacaoResumo['nps'] = $satisfacaoResumo['total_respostas'] > 0
            ? round((($promotores / $satisfacaoResumo['total_respostas']) * 100) - (($detratores / $satisfacaoResumo['total_respostas']) * 100), 1)
            : 0.0;

        $satisfacaoMensal = (clone $baseSatisfacao)
            ->selectRaw('YEAR(rs.created_at) as ano, MONTH(rs.created_at) as mes')
            ->selectRaw('AVG(rs.satisfacao_geral) as media_geral')
            ->selectRaw('AVG(rs.facilidade_reserva) as media_facilidade')
            ->selectRaw('AVG(rs.nps_nota) as media_nps')
            ->selectRaw('COUNT(rs.id) as respostas')
            ->groupBy('ano', 'mes')
            ->get()
            ->keyBy(fn ($r) => $r->ano . '-' . $r->mes);

        foreach ($meses as $index => $m) {
            $key = $m->year . '-' . $m->month;
            $satisfacaoEvolucao['satisfacao_geral'][$index] = round((float) ($satisfacaoMensal[$key]->media_geral ?? 0.0), 2);
            $satisfacaoEvolucao['facilidade_reserva'][$index] = round((float) ($satisfacaoMensal[$key]->media_facilidade ?? 0.0), 2);
            $satisfacaoEvolucao['nps'][$index] = round((float) ($satisfacaoMensal[$key]->media_nps ?? 0.0), 2);
            $satisfacaoEvolucao['respostas'][$index] = (int) ($satisfacaoMensal[$key]->respostas ?? 0);
        }

        $satisfacaoDistribuicao['data'] = [
            $promotores,
            $neutros,
            $detratores,
        ];

        $satisfacaoGrupos = $this->rankingSatisfacaoPorDimensao((clone $baseSatisfacao), 'grupo');
        $satisfacaoVeiculos = $this->rankingSatisfacaoPorDimensao((clone $baseSatisfacao), 'veiculo');
        $satisfacaoMarcas = $this->rankingSatisfacaoPorDimensao((clone $baseSatisfacao), 'marca');
        $satisfacaoModelos = $this->rankingSatisfacaoPorDimensao((clone $baseSatisfacao), 'modelo');
        $satisfacaoResponsaveis = $this->rankingSatisfacaoPorDimensao((clone $baseSatisfacao), 'respondente');
    }

    if ($homeSections['analise_avancada'] && SchemaCache::hasTable('veiculos')) {
        [
            $analiseAvancadaResumo,
            $analiseAvancadaLucrativos,
            $analiseAvancadaPrejuizo,
            $analiseAvancadaProtecao,
            $analiseAvancadaDetalhes,
            $analiseAvancadaRecomendacoes,
        ] = $this->analiseAvancadaFrota($dataInicio, $dataFim);
    }

    return response()->json([
        'secoes' => $homeSections,
        'dadosReservas' => compact('labels', 'confirmadas', 'cancelamentos', 'tendencia'),
        'movPorStatus' => [
            'labels' => $movStatus->pluck('label')->toArray(),
            'data' => $movStatus->pluck('total')->map(fn($v) => (int) $v)->toArray(),
        ],
        'veiculosPorGrupo' => [
            'labels' => $gruposVeiculos->pluck('label')->toArray(),
            'data' => $gruposVeiculos->pluck('total')->map(fn($v) => (int) $v)->toArray(),
        ],
        'ocupacaoFrota' => $ocupacaoFrota,
        'ocupacaoGeral' => $ocupacaoGeral,
        'totalReservas' => $totalReservas,
        'totalMovimentacoes' => $totalMovimentacoes,
        'reservasOrigemResumo' => $reservasOrigemResumo,
        'reservasOrigemDistribuicao' => $reservasOrigemDistribuicao,
        'reservasColaboradoresRanking' => $reservasColaboradoresRanking,
        'financeiroEvolucao' => [
            'labels' => $labels,
            'valores' => $financeiroValores,
            'quantidades' => $financeiroQuantidades,
        ],
        'financeiroStatus' => [
            'labels' => ['Aprovado', 'Pendente', 'Falha'],
            'data' => $financeiroStatus,
        ],
        'financeiroGateway' => [
            'labels' => $financeiroGatewayLabels,
            'data' => $financeiroGatewayData,
        ],
        'financeiroResumo' => [
            'total' => round($totalFinanceiro, 2),
            'ticket_medio' => round($ticketMedioFinanceiro, 2),
            'qtd_transacoes' => $qtdTransacoesFinanceiro,
            'taxa_aprovacao' => $taxaAprovacaoFinanceiro,
        ],
        'satisfacaoResumo' => $satisfacaoResumo,
        'satisfacaoEvolucao' => $satisfacaoEvolucao,
        'satisfacaoDistribuicao' => $satisfacaoDistribuicao,
        'satisfacaoGrupos' => $satisfacaoGrupos,
        'satisfacaoVeiculos' => $satisfacaoVeiculos,
        'satisfacaoMarcas' => $satisfacaoMarcas,
        'satisfacaoModelos' => $satisfacaoModelos,
        'satisfacaoResponsaveis' => $satisfacaoResponsaveis,
        'analiseAvancadaResumo' => $analiseAvancadaResumo,
        'analiseAvancadaLucrativos' => $analiseAvancadaLucrativos,
        'analiseAvancadaPrejuizo' => $analiseAvancadaPrejuizo,
        'analiseAvancadaProtecao' => $analiseAvancadaProtecao,
        'analiseAvancadaDetalhes' => $analiseAvancadaDetalhes,
        'analiseAvancadaRecomendacoes' => $analiseAvancadaRecomendacoes,
        'simboloMoeda' => $this->simboloMoedaEmpresa(),
    ]);
}
public function dashboard()
{
    return redirect()->route('home');
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
        if (!SchemaCache::hasTable($table) || !SchemaCache::hasColumn($table, 'empresa_id')) {
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

        if ($empresaId === null || !SchemaCache::hasTable('empresas') || !SchemaCache::hasColumn('empresas', 'moeda_pagamento')) {
            return 'BRL';
        }

        $moeda = DB::table('empresas')
            ->where('id', $empresaId)
            ->value('moeda_pagamento');

        $moeda = strtoupper((string) $moeda);

        return in_array($moeda, ['BRL', 'USD', 'EUR'], true) ? $moeda : 'BRL';
    }

    private function simboloMoedaEmpresa(): string
    {
        return match ($this->moedaEmpresaParaConsulta()) {
            'USD' => '$',
            'EUR' => 'EUR',
            default => 'R$',
        };
    }

    public function getHomeSectionPermissions(): array
    {
        return [
            'reservas' => $this->userCanAccessAny(['reserva.index', 'status_reserva.index']),
            'movimentacoes' => $this->userCanAccessAny(['movimentacao.index', 'status_movimentacao.index', 'tipo_movimentacao.index']),
            'frota' => $this->userCanAccessAny(['veiculo.index', 'grupo_frota.index', 'tipo_frota.index', 'marca.index', 'modelo.index', 'segmento.index']),
            'ocupacao' => $this->userCanAccessAny(['veiculo.index', 'grupo_frota.index', 'reserva.index']),
            'financeiro' => $this->userCanAccessAny(['financeiro.index', 'financeiro.reservas', 'tipo_transacao.index', 'nfse.index']),
            'satisfacao' => $this->userCanAccessAny(['dashboard', 'powerbi', 'reserva.index']),
            'analise_avancada' => $this->userCanAccessAny(['veiculo.index', 'financeiro.index', 'reserva.index']),
        ];
    }

    private function analiseAvancadaFrota(Carbon $dataInicio, Carbon $dataFim): array
    {
        $defaults = [
            [
                'total_veiculos' => 0,
                'veiculos_lucrativos' => 0,
                'veiculos_prejuizo' => 0,
                'veiculos_protegidos' => 0,
                'saude_media' => 0.0,
                'eficiencia_media' => 0.0,
                'custo_real_total' => 0.0,
                'lucro_real_total' => 0.0,
                'valor_protegido_total' => 0.0,
            ],
            ['labels' => [], 'data' => [], 'rows' => []],
            ['labels' => [], 'data' => [], 'rows' => []],
            ['labels' => ['Protegidos', 'Sem proteção'], 'data' => [0, 0]],
            [],
            [],
        ];

        if (!SchemaCache::hasTable('veiculos')) {
            return $defaults;
        }

        $empresaId = $this->empresaIdParaConsulta();

        $veiculosBase = DB::table('veiculos')
            ->leftJoin('marcas', 'marcas.id', '=', 'veiculos.marca_id')
            ->leftJoin('modelos', 'modelos.id', '=', 'veiculos.modelo_id')
            ->leftJoin('grupos_frota', 'grupos_frota.id', '=', 'veiculos.grupo_frota_id')
            ->selectRaw('veiculos.id, veiculos.nome, veiculos.placa_licenca, veiculos.valor_compra, veiculos.valor_fipe, veiculos.data_compra, veiculos.taxa_depreciacao_anual')
            ->selectRaw('COALESCE(marcas.nome_fipe, marcas.nome, "") as marca_nome')
            ->selectRaw('COALESCE(modelos.nome_fipe, modelos.nome, "") as modelo_nome')
            ->selectRaw('COALESCE(grupos_frota.nome, "") as grupo_nome');

        if (SchemaCache::hasColumn('veiculos', 'deleted_at')) {
            $veiculosBase->whereNull('veiculos.deleted_at');
        }

        if ($empresaId !== null && SchemaCache::hasColumn('veiculos', 'empresa_id')) {
            $veiculosBase->where('veiculos.empresa_id', $empresaId);
        }

        $receitasSub = DB::table('reservas')
            ->selectRaw('veiculo_id, COUNT(*) as reservas_qtd, COALESCE(SUM(COALESCE(valor_total_final, 0)), 0) as receita_total')
            ->whereNotNull('veiculo_id')
            ->whereNull('data_cancelamento')
            ->whereBetween('reservas.created_at', [$dataInicio, $dataFim]);

        if ($empresaId !== null && SchemaCache::hasColumn('reservas', 'empresa_id')) {
            $receitasSub->where('empresa_id', $empresaId);
        }

        $receitasSub->groupBy('veiculo_id');

        $multasSub = DB::table('multas')
            ->selectRaw('veiculo_id, COUNT(*) as multas_qtd, COALESCE(SUM(COALESCE(valor_final, 0)), 0) as custo_multas')
            ->whereNotNull('veiculo_id')
            ->whereBetween('data_hora_infracao', [$dataInicio, $dataFim]);

        if ($empresaId !== null && SchemaCache::hasColumn('multas', 'empresa_id')) {
            $multasSub->where('empresa_id', $empresaId);
        }

        if (SchemaCache::hasColumn('multas', 'destino_cobranca')) {
            $multasSub->where('destino_cobranca', 'empresa');
        }

        $multasSub->groupBy('veiculo_id');

        $sinistrosSub = DB::table('sinistros')
            ->selectRaw('veiculo_id, COUNT(*) as sinistros_qtd, COALESCE(SUM(COALESCE(valor_custo_empresa, 0)), 0) as custo_sinistros')
            ->whereNotNull('veiculo_id')
            ->whereBetween('data_hora_ocorrido', [$dataInicio, $dataFim]);

        if ($empresaId !== null && SchemaCache::hasColumn('sinistros', 'empresa_id')) {
            $sinistrosSub->where('empresa_id', $empresaId);
        }

        $sinistrosSub->groupBy('veiculo_id');

        $danosSub = DB::table('veiculo_danos as vd')
            ->join('veiculos as v_danos', 'v_danos.id', '=', 'vd.veiculo_id')
            ->selectRaw('vd.veiculo_id, COUNT(*) as danos_qtd, COALESCE(SUM(COALESCE(vd.custo_estimado, 0)), 0) as custo_danos')
            ->whereNotNull('vd.veiculo_id');

        if ($empresaId !== null && SchemaCache::hasColumn('veiculos', 'empresa_id')) {
            $danosSub->where('v_danos.empresa_id', $empresaId);
        }

        if (SchemaCache::hasColumn('veiculo_danos', 'created_at')) {
            $danosSub->whereBetween('vd.created_at', [$dataInicio, $dataFim]);
        }

        $danosSub->groupBy('vd.veiculo_id');

        $veiculos = $veiculosBase
            ->leftJoinSub($receitasSub, 'receitas', 'receitas.veiculo_id', '=', 'veiculos.id')
            ->leftJoinSub($multasSub, 'multas_analise', 'multas_analise.veiculo_id', '=', 'veiculos.id')
            ->leftJoinSub($sinistrosSub, 'sinistros_analise', 'sinistros_analise.veiculo_id', '=', 'veiculos.id')
            ->leftJoinSub($danosSub, 'danos_analise', 'danos_analise.veiculo_id', '=', 'veiculos.id')
            ->orderBy('veiculos.nome')
            ->get();

        if ($veiculos->isEmpty()) {
            return $defaults;
        }

        $rows = [];
        $totalVeiculos = 0;
        $totalLucroReal = 0.0;
        $totalCustoReal = 0.0;
        $totalSaude = 0.0;
        $totalEficiencia = 0.0;
        $veiculosLucro = 0;
        $veiculosPrejuizo = 0;
        $veiculosProtegidos = 0;
        $valorProtegidoTotal = 0.0;

        foreach ($veiculos as $veiculo) {
            $receita = (float) ($veiculo->receita_total ?? 0);
            $custoMultas = (float) ($veiculo->custo_multas ?? 0);
            $custoSinistros = (float) ($veiculo->custo_sinistros ?? 0);
            $custoDanos = (float) ($veiculo->custo_danos ?? 0);
            $custoOperacional = $custoMultas + $custoSinistros + $custoDanos;

            $valorCompra = (float) ($veiculo->valor_compra ?? 0);
            $valorFipe = (float) ($veiculo->valor_fipe ?? 0);
            $baseDepreciacao = $valorFipe > 0 ? $valorFipe : $valorCompra;
            $taxaDepreciacao = (float) ($veiculo->taxa_depreciacao_anual ?? 0);
            $percentualDepreciacao = 0.0;
            $valorDepreciacao = 0.0;

            if ($baseDepreciacao > 0 && $taxaDepreciacao > 0 && !empty($veiculo->data_compra)) {
                $dataCompra = Carbon::parse($veiculo->data_compra);
                if ($dataCompra->lte($dataFim)) {
                    $mesesEmUso = max(0, $dataCompra->diffInMonths($dataFim));
                    $percentualDepreciacao = min(100, ($mesesEmUso / 12) * $taxaDepreciacao);
                    $valorDepreciacao = round(($baseDepreciacao * $percentualDepreciacao) / 100, 2);
                }
            }

            $custoReal = round($custoOperacional + $valorDepreciacao, 2);
            $lucroReal = round($receita - $custoReal, 2);
            $eficiencia = round(($receita + $custoReal) > 0 ? ($receita / ($receita + $custoReal)) * 100 : 0, 1);

            $protecaoValor = 0.0;
            $protecaoPercentual = 0.0;
            if ($valorCompra > 0 && $valorFipe > 0 && $valorCompra < $valorFipe) {
                $protecaoValor = round($valorFipe - $valorCompra, 2);
                $protecaoPercentual = round(($protecaoValor / $valorFipe) * 100, 1);
                $veiculosProtegidos++;
                $valorProtegidoTotal += $protecaoValor;
            }

            $indiceSaude = round(min(100, max(0, ($eficiencia * 0.7) + ($protecaoPercentual * 0.3))), 1);

            $labelPrincipal = trim((string) ($veiculo->placa_licenca ?: $veiculo->nome ?: ('Veiculo #' . $veiculo->id)));
            $labelSecundario = trim(implode(' • ', array_filter([
                $veiculo->marca_nome ?: null,
                $veiculo->modelo_nome ?: null,
                $veiculo->grupo_nome ?: null,
            ])));

            $status = $lucroReal < 0
                ? 'prejuizo'
                : ($indiceSaude >= 70 ? 'saudavel' : ($indiceSaude >= 45 ? 'atencao' : 'critico'));

            $recomendacao = $lucroReal < 0
                ? 'Rever precificacao, manutencoes e ocupacao.'
                : ($indiceSaude < 45
                    ? 'Acompanhar custos e revisar uso da frota.'
                    : ($protecaoValor > 0
                        ? 'Veiculo com protecao contra depreciacao.'
                        : 'Sem protecao de depreciacao identificada.'));

            $rows[] = [
                'id' => (int) $veiculo->id,
                'label' => $labelPrincipal,
                'sub_label' => $labelSecundario,
                'receita' => round($receita, 2),
                'custo_operacional' => round($custoOperacional, 2),
                'depreciacao' => round($valorDepreciacao, 2),
                'custo_real' => $custoReal,
                'lucro_real' => $lucroReal,
                'eficiencia' => $eficiencia,
                'saude' => $indiceSaude,
                'protecao_valor' => round($protecaoValor, 2),
                'protecao_percentual' => $protecaoPercentual,
                'status' => $status,
                'recomendacao' => $recomendacao,
                'marca' => (string) ($veiculo->marca_nome ?? ''),
                'modelo' => (string) ($veiculo->modelo_nome ?? ''),
                'grupo' => (string) ($veiculo->grupo_nome ?? ''),
                'reservas_qtd' => (int) ($veiculo->reservas_qtd ?? 0),
            ];

            $totalVeiculos++;
            $totalLucroReal += $lucroReal;
            $totalCustoReal += $custoReal;
            $totalSaude += $indiceSaude;
            $totalEficiencia += $eficiencia;
            if ($lucroReal > 0) {
                $veiculosLucro++;
            } elseif ($lucroReal < 0) {
                $veiculosPrejuizo++;
            }
        }

        $sortByLucroDesc = $rows;
        usort($sortByLucroDesc, fn (array $a, array $b) => $b['lucro_real'] <=> $a['lucro_real']);

        $sortByLucroAsc = $rows;
        usort($sortByLucroAsc, fn (array $a, array $b) => $a['lucro_real'] <=> $b['lucro_real']);

        $protegidosCount = $veiculosProtegidos;
        $semProtecaoCount = max(0, $totalVeiculos - $protegidosCount);

        $topLucrativos = array_slice(array_values(array_filter($sortByLucroDesc, fn (array $row) => $row['lucro_real'] > 0)), 0, 10);
        $topPrejuizo = array_slice(array_values(array_filter($sortByLucroAsc, fn (array $row) => $row['lucro_real'] < 0)), 0, 10);

        $recomendacoes = [];
        if ($veiculosPrejuizo > 0) {
            $pior = $sortByLucroAsc[0] ?? null;
            if ($pior) {
                $recomendacoes[] = [
                    'tipo' => 'danger',
                    'titulo' => 'Veículos no prejuízo',
                    'descricao' => sprintf(
                        '%d veículos ficaram negativos no período. O pior caso foi %s com prejuízo de %s.',
                        $veiculosPrejuizo,
                        $pior['label'],
                        number_format(abs((float) $pior['lucro_real']), 2, ',', '.')
                    ),
                ];
            }
        } else {
            $recomendacoes[] = [
                'tipo' => 'success',
                'titulo' => 'Frota sem prejuízo',
                'descricao' => 'Nenhum veículo fechou o período com lucro real negativo.',
            ];
        }

        if ($protegidosCount > 0) {
            $recomendacoes[] = [
                'tipo' => 'info',
                'titulo' => 'Proteção contra depreciação',
                'descricao' => sprintf(
                    '%d veículos foram comprados abaixo da FIPE, somando proteção estimada de %s.',
                    $protegidosCount,
                    number_format($valorProtegidoTotal, 2, ',', '.')
                ),
            ];
        } else {
            $recomendacoes[] = [
                'tipo' => 'warning',
                'titulo' => 'Sem proteção FIPE identificada',
                'descricao' => 'Nenhum veículo do recorte atual foi comprado abaixo da FIPE.',
            ];
        }

        if ($totalVeiculos > 0 && $totalSaude / $totalVeiculos < 45) {
            $recomendacoes[] = [
                'tipo' => 'warning',
                'titulo' => 'Saúde da frota baixa',
                'descricao' => 'A saúde média da frota está abaixo do ideal. Vale revisar preço, ocupação e custos.',
            ];
        }

        if ($topLucrativos) {
            $melhor = $topLucrativos[0];
            $recomendacoes[] = [
                'tipo' => 'success',
                'titulo' => 'Melhor veículo no período',
                'descricao' => sprintf(
                    '%s lidera a rentabilidade com lucro real de %s.',
                    $melhor['label'],
                    number_format((float) $melhor['lucro_real'], 2, ',', '.')
                ),
            ];
        }

        $analiseAvancadaResumo = [
            'total_veiculos' => $totalVeiculos,
            'veiculos_lucrativos' => $veiculosLucro,
            'veiculos_prejuizo' => $veiculosPrejuizo,
            'veiculos_protegidos' => $protegidosCount,
            'saude_media' => $totalVeiculos > 0 ? round($totalSaude / $totalVeiculos, 1) : 0.0,
            'eficiencia_media' => $totalVeiculos > 0 ? round($totalEficiencia / $totalVeiculos, 1) : 0.0,
            'custo_real_total' => round($totalCustoReal, 2),
            'lucro_real_total' => round($totalLucroReal, 2),
            'valor_protegido_total' => round($valorProtegidoTotal, 2),
        ];

        $analiseAvancadaLucrativos = [
            'labels' => array_map(fn (array $row) => $row['label'], $topLucrativos),
            'data' => array_map(fn (array $row) => round((float) $row['lucro_real'], 2), $topLucrativos),
            'rows' => $topLucrativos,
        ];

        $analiseAvancadaPrejuizo = [
            'labels' => array_map(fn (array $row) => $row['label'], $topPrejuizo),
            'data' => array_map(fn (array $row) => round((float) $row['lucro_real'], 2), $topPrejuizo),
            'rows' => $topPrejuizo,
        ];

        $analiseAvancadaProtecao = [
            'labels' => ['Protegidos', 'Sem proteção'],
            'data' => [$protegidosCount, $semProtecaoCount],
        ];

        $analiseAvancadaDetalhes = array_slice($sortByLucroDesc, 0, 12);
        $analiseAvancadaRecomendacoes = $recomendacoes;

        return [
            $analiseAvancadaResumo,
            $analiseAvancadaLucrativos,
            $analiseAvancadaPrejuizo,
            $analiseAvancadaProtecao,
            $analiseAvancadaDetalhes,
            $analiseAvancadaRecomendacoes,
        ];
    }

    private function rankingSatisfacaoPorDimensao($baseSatisfacao, string $tipo): array
    {
        if (!SchemaCache::hasTable('reserva_satisfacoes')) {
            return ['labels' => [], 'data' => [], 'respostas' => []];
        }

        $query = clone $baseSatisfacao;
        $selectLabel = 'COALESCE(dim.nome, "Sem dado")';

        switch ($tipo) {
            case 'grupo':
                $query->leftJoin('grupos_frota as dim', 'dim.id', '=', 'r.grupo_frota_id');
                $selectLabel = 'COALESCE(dim.nome, "Sem grupo")';
                break;
            case 'veiculo':
                $query->leftJoin('veiculos as dim', 'dim.id', '=', 'r.veiculo_id');
                $selectLabel = 'COALESCE(dim.nome, dim.placa_licenca, "Sem veículo")';
                break;
            case 'marca':
                $query->leftJoin('veiculos as v', 'v.id', '=', 'r.veiculo_id')
                    ->leftJoin('marcas as dim', 'dim.id', '=', 'v.marca_id');
                $selectLabel = 'COALESCE(dim.nome_fipe, dim.nome, "Sem marca")';
                break;
            case 'modelo':
                $query->leftJoin('veiculos as v', 'v.id', '=', 'r.veiculo_id')
                    ->leftJoin('modelos as dim', 'dim.id', '=', 'v.modelo_id');
                $selectLabel = 'COALESCE(dim.nome_fipe, dim.nome, "Sem modelo")';
                break;
            case 'respondente':
                $query->leftJoin('users as dim', 'dim.id', '=', 'rs.respondente_user_id');
                $selectLabel = 'COALESCE(dim.nome, "Sem responsável")';
                break;
        }

        $rows = $query
            ->selectRaw($selectLabel . ' as label')
            ->selectRaw('AVG(rs.satisfacao_geral) as media_geral')
            ->selectRaw('AVG(rs.facilidade_reserva) as media_facilidade')
            ->selectRaw('AVG(rs.nps_nota) as media_nps')
            ->selectRaw('COUNT(rs.id) as respostas')
            ->groupBy('label')
            ->orderByDesc('media_geral')
            ->limit(10)
            ->get();

        return [
            'labels' => $rows->pluck('label')->toArray(),
            'data' => $rows->map(fn ($row) => [
                'label' => (string) $row->label,
                'media_geral' => round((float) $row->media_geral, 2),
                'media_facilidade' => round((float) $row->media_facilidade, 2),
                'media_nps' => round((float) $row->media_nps, 2),
                'respostas' => (int) $row->respostas,
            ])->toArray(),
            'respostas' => $rows->pluck('respostas')->map(fn ($v) => (int) $v)->toArray(),
        ];
    }

    private function userCanAccessAny(array $routes): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        if ($user->isAdministrador()) {
            return true;
        }

        foreach (array_unique($routes) as $routeName) {
            if ($user->hasPermissao($routeName)) {
                return true;
            }
        }

        return false;
    }

    private function filterSystemCards(array $groups): array
    {
        if ($this->userCanAccessAny(['*'])) {
            return $groups;
        }

        return collect($groups)
            ->map(function (array $group) {
                $group['items'] = collect($group['items'])
                    ->filter(fn (array $item) => $this->userCanAccessAny([$item['route']]))
                    ->values()
                    ->all();

                return $group;
            })
            ->filter(fn (array $group) => !empty($group['items']))
            ->values()
            ->all();
    }

    private function getSystemCards()
    {
        return [
            [
                'grupo' => 'Reservas e Movimentações',
                'backgroundIcone' => 'bg-info',
                'items' => [
                    [
                        'nome' => 'Reservas',
                        'route' => 'reserva.index',
                        'icone' => 'fas fa-calendar-check',
                        'prefix' => null,
                        'suffix' => null,
                        'valor' => \App\Models\Reserva::count()
                    ],
                    [
                        'nome' => 'Status Reservas',
                        'route' => 'status_reserva.index',
                        'icone' => 'fas fa-check-circle',
                        'prefix' => null,
                        'suffix' => null,
                        'valor' => \App\Models\StatusReserva::count()
                    ],
                    [
                        'nome' => 'Movimentações',
                        'route' => 'movimentacao.index',
                        'icone' => 'fas fa-truck-moving',
                        'prefix' => null,
                        'suffix' => null,
                        'valor' => \App\Models\Movimentacao::count()
                    ],
                    [
                        'nome' => 'Status Movimentações',
                        'route' => 'status_movimentacao.index',
                        'icone' => 'fas fa-circle',
                        'prefix' => null,
                        'suffix' => null,
                        'valor' => \App\Models\StatusMovimentacao::count()
                    ],
                    [
                        'nome' => 'Tipo Movimentações',
                        'route' => 'tipo_movimentacao.index',
                        'icone' => 'fas fa-truck',
                        'prefix' => null,
                        'suffix' => null,
                        'valor' => \App\Models\TipoMovimentacao::count()
                    ],
                ]
            ],
            [
                'grupo' => 'Configurações de Usuário',
                'backgroundIcone' => 'bg-warning',
                'items' => [
                    [
                        'nome' => 'Usuários',
                        'route' => 'usuario.index',
                        'icone' => 'fas fa-fw fa-user',
                        'prefix' => null,
                        'suffix' => null,
                        'valor' => \App\Models\User::count()
                    ],
                    [
                        'nome' => 'Regras',
                        'route' => 'regra.index',
                        'icone' => 'fas fa-key',
                        'prefix' => null,
                        'suffix' => null,
                        'valor' => \App\Models\Regra::count()
                    ],
                    [
                        'nome' => 'Permissões',
                        'route' => 'permissao.index',
                        'icone' => 'fas fa-lock',
                        'prefix' => null,
                        'suffix' => null,
                        'valor' => \App\Models\Permissao::count()
                    ],
                    [
                        'nome' => 'Termos de Consentimento',
                        'route' => 'termo_consentimento.index',
                        'icone' => 'fas fa-file-signature',
                        'prefix' => null,
                        'suffix' => null,
                        'valor' => \App\Models\TermoConsentimento::count()
                    ],
                ]
            ],
            [
                'grupo' => 'Configurações de Reserva',
                'backgroundIcone' => 'bg-success',
                'items' => [
                    [
                        'nome' => 'Países',
                        'route' => 'pais.index',
                        'icone' => 'fas fa-globe',
                        'prefix' => null,
                        'suffix' => null,
                        'valor' => \App\Models\Pais::count()
                    ],
                    [
                        'nome' => 'Dias de Trabalho',
                        'route' => 'dia_util.index',
                        'icone' => 'fas fa-calendar-day',
                        'prefix' => null,
                        'suffix' => null,
                        'valor' => \App\Models\DiaUtil::count()
                    ],
                    [
                        'nome' => 'Horários Diários',
                        'route' => 'horario_diario.index',
                        'icone' => 'fas fa-clock',
                        'prefix' => null,
                        'suffix' => null,
                        'valor' => \App\Models\HorarioDiario::count()
                    ],
                ]
            ],
            [
                'grupo' => 'Configurações de Frota',
                'backgroundIcone' => 'bg-danger',
                'items' => [
                    [
                        'nome' => 'Veículos',
                        'route' => 'veiculo.index',
                        'icone' => 'fas fa-car',
                        'prefix' => null,
                        'suffix' => null,
                        'valor' => \App\Models\Veiculo::count()
                    ],
                    [
                        'nome' => 'Cupons de Desconto',
                        'route' => 'cupom_desconto.index',
                        'icone' => 'fas fa-percentage',
                        'prefix' => null,
                        'suffix' => null,
                        'valor' => \App\Models\CupomDesconto::count()
                    ],
                    [
                        'nome' => 'Categorias de Dano',
                        'route' => 'categoria_dano.index',
                        'icone' => 'fas fa-tags',
                        'prefix' => null,
                        'suffix' => null,
                        'valor' => \App\Models\CategoriaDano::count()
                    ],
                    [
                        'nome' => 'Danos',
                        'route' => 'dano.index',
                        'icone' => 'fas fa-tools',
                        'prefix' => null,
                        'suffix' => null,
                        'valor' => \App\Models\Dano::count()
                    ],
                    [
                        'nome' => 'Tipos de Frota',
                        'route' => 'tipo_frota.index',
                        'icone' => 'fas fa-list-alt',
                        'prefix' => null,
                        'suffix' => null,
                        'valor' => \App\Models\TipoFrota::count()
                    ],
                    [
                        'nome' => 'Grupos de Frota',
                        'route' => 'grupo_frota.index',
                        'icone' => 'fas fa-th-list',
                        'prefix' => null,
                        'suffix' => null,
                        'valor' => \App\Models\GrupoFrota::count()
                    ],
                    [
                        'nome' => 'Marcas',
                        'route' => 'marca.index',
                        'icone' => 'fas fa-certificate',
                        'prefix' => null,
                        'suffix' => null,
                        'valor' => \App\Models\Marca::count()
                    ],
                    [
                        'nome' => 'Modelos',
                        'route' => 'modelo.index',
                        'icone' => 'fas fa-truck',
                        'prefix' => null,
                        'suffix' => null,
                        'valor' => \App\Models\Modelo::count()
                    ],
                    [
                        'nome' => 'Segmentos',
                        'route' => 'segmento.index',
                        'icone' => 'fas fa-bars',
                        'prefix' => null,
                        'suffix' => null,
                        'valor' => \App\Models\Segmento::count()
                    ],
                ]
            ],
            auth()->user()?->isSuperAdmin() ? [
                'grupo' => 'Configurações Gerais',
                'backgroundIcone' => 'bg-secondary',
                'items' => array_values(array_filter([
                    Route::has('status_movimentacao.index') ? [
                        'nome' => 'Status de Movimentação',
                        'route' => 'status_movimentacao.index',
                        'icone' => 'fas fa-toggle-on',
                        'prefix' => null,
                        'suffix' => null,
                        'valor' => \App\Models\StatusMovimentacao::count(),
                    ] : null,
                    Route::has('tipo_movimentacao.index') ? [
                        'nome' => 'Tipos de Movimentação',
                        'route' => 'tipo_movimentacao.index',
                        'icone' => 'fas fa-arrows-alt',
                        'prefix' => null,
                        'suffix' => null,
                        'valor' => \App\Models\TipoMovimentacao::count(),
                    ] : null,
                    Route::has('tipotransacao.index') ? [
                        'nome' => 'Tipos de Transação',
                        'route' => 'tipotransacao.index',
                        'icone' => 'fas fa-exchange-alt',
                        'prefix' => null,
                        'suffix' => null,
                        'valor' => \App\Models\TipoTransacao::count(),
                    ] : null,
                    Route::has('status_veiculo.index') ? [
                        'nome' => 'Status de Veículo',
                        'route' => 'status_veiculo.index',
                        'icone' => 'fas fa-car',
                        'prefix' => null,
                        'suffix' => null,
                        'valor' => \App\Models\StatusVeiculo::count(),
                    ] : null,
                    Route::has('status_item_checklist.index') ? [
                        'nome' => 'Status Item Checklist',
                        'route' => 'status_item_checklist.index',
                        'icone' => 'fas fa-check-square',
                        'prefix' => null,
                        'suffix' => null,
                        'valor' => \App\Models\StatusItemChecklist::count(),
                    ] : null,
                ]))
            ] : null
        ];
    }
}
