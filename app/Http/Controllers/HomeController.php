<?php

namespace App\Http\Controllers;

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
use Illuminate\Support\Facades\Schema;

class HomeController extends Controller
{
    private const STATUS_APROVADOS = ['completa', 'aprovado', 'approved', 'succeeded', 'success'];
    private const STATUS_PENDENTES = ['pendente', 'pending', 'aguardando', 'processando', 'processing'];

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

    if ($homeSections['frota']) {
        $veiculosQuery = \App\Models\Veiculo::query();
        $veiculosQuery = $this->applyEmpresaScope($veiculosQuery, 'veiculos');
        $totalVeiculos = (int) $veiculosQuery->count();
    }

    return view('home', compact('breadcrumb', 'items', 'totalVeiculos', 'homeSections', 'hasVisibleItems', 'hasVisibleCharts'))
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

    if ($homeSections['reservas'] || $homeSections['ocupacao']) {
        $reservasNoPeriodo = \App\Models\Reserva::query()
            ->whereBetween('created_at', [$dataInicio, $dataFim]);
        $reservasNoPeriodo = $this->applyEmpresaScope($reservasNoPeriodo, 'reservas');

        if ($homeSections['reservas']) {
            $resConfirmadas = (clone $reservasNoPeriodo)
                ->whereNull('data_cancelamento')
                ->selectRaw('YEAR(created_at) as ano, MONTH(created_at) as mes, COUNT(*) as total')
                ->groupBy('ano', 'mes')
                ->get()
                ->keyBy(fn($r) => $r->ano . '-' . $r->mes);

            $resCanceladas = (clone $reservasNoPeriodo)
                ->whereNotNull('data_cancelamento')
                ->selectRaw('YEAR(created_at) as ano, MONTH(created_at) as mes, COUNT(*) as total')
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
        }

        if ($homeSections['ocupacao'] && Schema::hasTable('grupos_frota')) {
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

    if ($homeSections['frota'] && Schema::hasTable('grupos_frota')) {
        $gruposVeiculosBase = DB::table('veiculos')
            ->join('grupos_frota', 'veiculos.grupo_frota_id', '=', 'grupos_frota.id')
            ->selectRaw('grupos_frota.nome as label, COUNT(*) as total');
        $gruposVeiculosBase = $this->applyEmpresaScope($gruposVeiculosBase, 'veiculos');
        $gruposVeiculos = $gruposVeiculosBase
            ->groupBy('grupos_frota.id', 'grupos_frota.nome')
            ->get();
    }

    if ($homeSections['financeiro'] && Schema::hasTable('transacoes')) {
        $baseFinanceiro = DB::table('transacoes')
            ->whereBetween('transacoes.created_at', [$dataInicio, $dataFim]);
        $baseFinanceiro = $this->applyEmpresaScope($baseFinanceiro, 'transacoes');

        $financeiroMensal = (clone $baseFinanceiro)
            ->selectRaw('YEAR(transacoes.created_at) as ano, MONTH(transacoes.created_at) as mes')
            ->selectRaw('SUM(transacoes.valor) as total')
            ->selectRaw('COUNT(transacoes.id) as quantidade')
            ->groupBy('ano', 'mes')
            ->get()
            ->keyBy(fn($r) => $r->ano . '-' . $r->mes);

        foreach ($meses as $index => $m) {
            $key = $m->year . '-' . $m->month;
            $financeiroValores[$index] = round((float) ($financeiroMensal[$key]->total ?? 0.0), 2);
            $financeiroQuantidades[$index] = (int) ($financeiroMensal[$key]->quantidade ?? 0);
        }

        $totalFinanceiro = (float) (clone $baseFinanceiro)->sum('transacoes.valor');
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
            ->selectRaw('SUM(transacoes.valor) as total')
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

    private function simboloMoedaEmpresa(): string
    {
        return match ($this->moedaEmpresaParaConsulta()) {
            'USD' => '$',
            'EUR' => 'EUR',
            default => 'R$',
        };
    }

    private function getHomeSectionPermissions(): array
    {
        return [
            'reservas' => $this->userCanAccessAny(['reserva.index', 'status_reserva.index']),
            'movimentacoes' => $this->userCanAccessAny(['movimentacao.index', 'status_movimentacao.index', 'tipo_movimentacao.index']),
            'frota' => $this->userCanAccessAny(['veiculo.index', 'grupo_frota.index', 'tipo_frota.index', 'marca.index', 'modelo.index', 'segmento.index']),
            'ocupacao' => $this->userCanAccessAny(['veiculo.index', 'grupo_frota.index', 'reserva.index']),
            'financeiro' => $this->userCanAccessAny(['financeiro.index', 'financeiro.reservas', 'tipo_transacao.index', 'nfse.index']),
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
                        'nome' => 'Estações',
                        'route' => 'estacao.index',
                        'icone' => 'fas fa-map-marker-alt',
                        'prefix' => null,
                        'suffix' => null,
                        'valor' => \App\Models\Estacao::count()
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
            ]
        ];
    }
}

