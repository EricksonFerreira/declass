<?php

namespace App\Http\Controllers;

use App\Actions\Financeiro\CreateLancamentoAction;
use App\Actions\Financeiro\UpdateLancamentoAction;
use App\Actions\Financeiro\DeleteLancamentoAction;
use App\Http\Requests\FluxoCaixa\StoreLancamentoRequest;
use App\Http\Requests\FluxoCaixa\UpdateLancamentoRequest;
use App\DTO\BreadCrumbItem;
use App\Models\CategoriaFinanceira;
use App\Models\LancamentoFinanceiro;
use App\Models\Veiculo;
use App\Models\Reserva;
use App\Support\EmpresaContext;
use App\Services\BreadcrumbService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Support\SchemaCache;

class FluxoCaixaController extends Controller
{
    public function __construct(
        private readonly BreadcrumbService $breadcrumbService,
        private readonly CreateLancamentoAction $createAction,
        private readonly UpdateLancamentoAction $updateAction,
        private readonly DeleteLancamentoAction $deleteAction
    ) {}

    private function getEmpresaId(): ?int
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

    private function getMoedaEmpresa(): string
    {
        $empresaId = $this->getEmpresaId();
        if ($empresaId === null || !SchemaCache::hasTable('empresas') || !SchemaCache::hasColumn('empresas', 'moeda_pagamento')) {
            return 'BRL';
        }
        $moeda = DB::table('empresas')->where('id', $empresaId)->value('moeda_pagamento');
        return in_array(strtoupper((string)$moeda), ['BRL', 'USD', 'EUR'], true) ? strtoupper((string)$moeda) : 'BRL';
    }

    private function breadcrumbFluxoCaixa(string $pagina, ?string $rotaFluxo = null): array
    {
        $items = [
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Financeiro', route('financeiro.index')),
            new BreadCrumbItem('Fluxo de Caixa', $rotaFluxo ?? route('financeiro.fluxo-caixa.dashboard')),
        ];

        if ($pagina !== 'Fluxo de Caixa') {
            $items[] = new BreadCrumbItem($pagina);
        }

        return $this->breadcrumbService->prepararBreadcrumb($items);
    }

    public function dashboard(Request $request)
    {
        $empresaId = $this->getEmpresaId();
        $moeda = $this->getMoedaEmpresa();

        $dataInicio = $request->input('data_inicio', now()->startOfMonth()->toDateString());
        $dataFim = $request->input('data_fim', now()->endOfMonth()->toDateString());

        // Base Query scoped to active tenant
        $baseQuery = LancamentoFinanceiro::where('empresa_id', $empresaId)
            ->where(function($query) use ($dataInicio, $dataFim) {
                $query->whereBetween('data_vencimento', [$dataInicio, $dataFim])
                      ->orWhereBetween('data_pagamento', [$dataInicio, $dataFim]);
            });

        // Totals
        $receitasPagas = (float) (clone $baseQuery)->where('tipo', 'receita')->where('status', 'pago')->sum('valor');
        $receitasPendentes = (float) (clone $baseQuery)->where('tipo', 'receita')->where('status', 'pendente')->sum('valor');
        $despesasPagas = (float) (clone $baseQuery)->where('tipo', 'despesa')->where('status', 'pago')->sum('valor');
        $despesasPendentes = (float) (clone $baseQuery)->where('tipo', 'despesa')->where('status', 'pendente')->sum('valor');

        $saldoRealizado = $receitasPagas - $despesasPagas;
        $saldoProjetado = ($receitasPagas + $receitasPendentes) - ($despesasPagas + $despesasPendentes);

        // Daily breakdown for graph
        $dailyData = LancamentoFinanceiro::where('empresa_id', $empresaId)
            ->whereBetween('data_vencimento', [$dataInicio, $dataFim])
            ->selectRaw('data_vencimento as dia')
            ->selectRaw('SUM(CASE WHEN tipo = "receita" THEN valor ELSE 0 END) as receita')
            ->selectRaw('SUM(CASE WHEN tipo = "despesa" THEN valor ELSE 0 END) as despesa')
            ->groupBy('data_vencimento')
            ->orderBy('data_vencimento')
            ->get();

        $chartLabels = $dailyData->pluck('dia')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m'))->toArray();
        $chartReceitas = $dailyData->pluck('receita')->toArray();
        $chartDespesas = $dailyData->pluck('despesa')->toArray();

        // Top categories
        $topCategorias = LancamentoFinanceiro::where('lancamentos_financeiros.empresa_id', $empresaId)
            ->join('categorias_financeiras', 'categorias_financeiras.id', '=', 'lancamentos_financeiros.categoria_financeira_id')
            ->selectRaw('categorias_financeiras.nome as categoria')
            ->selectRaw('SUM(lancamentos_financeiros.valor) as total')
            ->selectRaw('lancamentos_financeiros.tipo')
            ->groupBy('categorias_financeiras.nome', 'lancamentos_financeiros.tipo')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $breadcrumb = $this->breadcrumbFluxoCaixa('Dashboard');
        $titulo = 'Dashboard de Fluxo de Caixa';

        return view('financeiro.fluxo_caixa.dashboard', compact(
            'receitasPagas', 'receitasPendentes', 'despesasPagas', 'despesasPendentes',
            'saldoRealizado', 'saldoProjetado', 'chartLabels', 'chartReceitas', 'chartDespesas',
            'topCategorias', 'dataInicio', 'dataFim', 'moeda', 'breadcrumb', 'titulo'
        ));
    }

    public function analiseVeiculos(Request $request)
    {
        $empresaId = $this->getEmpresaId();
        $moeda = $this->getMoedaEmpresa();
        $dataInicio = $request->input('data_inicio', now()->startOfMonth()->toDateString());
        $dataFim = $request->input('data_fim', now()->endOfMonth()->toDateString());
        $veiculoId = (int) $request->integer('veiculo_id', 0);
        $busca = trim((string) $request->input('busca', ''));

        $base = LancamentoFinanceiro::query()
            ->from('lancamentos_financeiros')
            ->leftJoin('veiculos', 'veiculos.id', '=', 'lancamentos_financeiros.veiculo_id')
            ->where('lancamentos_financeiros.empresa_id', $empresaId)
            ->whereNotNull('lancamentos_financeiros.veiculo_id')
            ->where('lancamentos_financeiros.status', 'pago')
            ->where(function ($query) use ($dataInicio, $dataFim) {
                $query->whereBetween('lancamentos_financeiros.data_pagamento', [$dataInicio, $dataFim])
                    ->orWhere(function ($fallback) use ($dataInicio, $dataFim) {
                        $fallback->whereNull('lancamentos_financeiros.data_pagamento')
                            ->whereBetween('lancamentos_financeiros.data_vencimento', [$dataInicio, $dataFim]);
                    });
            });

        if ($veiculoId > 0) {
            $base->where('lancamentos_financeiros.veiculo_id', $veiculoId);
        }

        if ($busca !== '') {
            $base->where(function ($query) use ($busca) {
                $query->where('veiculos.nome', 'like', '%'.$busca.'%')
                    ->orWhere('veiculos.placa_licenca', 'like', '%'.$busca.'%')
                    ->orWhere('lancamentos_financeiros.descricao', 'like', '%'.$busca.'%');
            });
        }

        $analise = (clone $base)
            ->select([
                'lancamentos_financeiros.veiculo_id',
                DB::raw('COALESCE(veiculos.nome, CONCAT("Veiculo #", lancamentos_financeiros.veiculo_id)) as veiculo_nome'),
                DB::raw('COALESCE(veiculos.placa_licenca, "-") as placa'),
            ])
            ->selectRaw('SUM(CASE WHEN lancamentos_financeiros.tipo = "receita" THEN lancamentos_financeiros.valor ELSE 0 END) as receitas')
            ->selectRaw('SUM(CASE WHEN lancamentos_financeiros.tipo = "despesa" THEN lancamentos_financeiros.valor ELSE 0 END) as despesas')
            ->selectRaw('SUM(CASE WHEN lancamentos_financeiros.tipo = "receita" THEN lancamentos_financeiros.valor ELSE -lancamentos_financeiros.valor END) as resultado')
            ->selectRaw('COUNT(lancamentos_financeiros.id) as quantidade_lancamentos')
            ->groupBy('lancamentos_financeiros.veiculo_id', 'veiculos.nome', 'veiculos.placa_licenca')
            ->orderByDesc('resultado')
            ->paginate(20)
            ->withQueryString();

        $totais = (clone $base)
            ->selectRaw('SUM(CASE WHEN tipo = "receita" THEN valor ELSE 0 END) as receitas')
            ->selectRaw('SUM(CASE WHEN tipo = "despesa" THEN valor ELSE 0 END) as despesas')
            ->selectRaw('SUM(CASE WHEN tipo = "receita" THEN valor ELSE -valor END) as resultado')
            ->first();

        $receitas = (float) ($totais->receitas ?? 0);
        $despesas = (float) ($totais->despesas ?? 0);
        $resultado = (float) ($totais->resultado ?? 0);
        $margem = $receitas > 0 ? ($resultado / $receitas) * 100 : 0.0;

        $pendentes = LancamentoFinanceiro::query()
            ->where('empresa_id', $empresaId)
            ->whereNotNull('veiculo_id')
            ->where('status', 'pendente')
            ->whereBetween('data_vencimento', [$dataInicio, $dataFim])
            ->selectRaw('SUM(CASE WHEN tipo = "receita" THEN valor ELSE 0 END) as receitas')
            ->selectRaw('SUM(CASE WHEN tipo = "despesa" THEN valor ELSE 0 END) as despesas')
            ->first();

        $veiculos = Veiculo::query()
            ->where('empresa_id', $empresaId)
            ->orderBy('nome')
            ->get(['id', 'nome', 'placa_licenca']);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Financeiro', route('financeiro.index')),
            new BreadCrumbItem('Análise por Veículo'),
        ]);
        $titulo = 'Análise Financeira por Veículo';

        return view('financeiro.analise_veiculos', compact(
            'analise',
            'veiculos',
            'dataInicio',
            'dataFim',
            'veiculoId',
            'busca',
            'moeda',
            'receitas',
            'despesas',
            'resultado',
            'margem',
            'pendentes',
            'breadcrumb',
            'titulo'
        ));
    }

    public function index(Request $request)
    {
        $empresaId = $this->getEmpresaId();
        $moeda = $this->getMoedaEmpresa();

        $query = LancamentoFinanceiro::with(['categoria', 'veiculo', 'reserva.codigoReserva'])
            ->where('empresa_id', $empresaId);

        // Apply filters
        if ($request->filled('descricao')) {
            $query->where('descricao', 'like', '%' . $request->descricao . '%');
        }
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('categoria_id')) {
            $query->where('categoria_financeira_id', $request->categoria_id);
        }
        if ($request->filled('data_inicio')) {
            $query->whereDate('data_vencimento', '>=', $request->data_inicio);
        }
        if ($request->filled('data_fim')) {
            $query->whereDate('data_vencimento', '<=', $request->data_fim);
        }

        $perPage = (int) $request->integer('per_page', 15);
        $perPage = in_array($perPage, [10, 15, 25, 50, 100], true) ? $perPage : 15;

        $lancamentos = $query->orderByDesc('data_vencimento')
            ->paginate($perPage)
            ->withQueryString();

        $categorias = CategoriaFinanceira::where('empresa_id', $empresaId)->get();

        $breadcrumb = $this->breadcrumbFluxoCaixa('Lançamentos', route('financeiro.fluxo-caixa.index'));
        $titulo = 'Lançamentos Financeiros';

        return view('financeiro.fluxo_caixa.lancamentos.index', compact(
            'lancamentos', 'categorias', 'breadcrumb', 'titulo', 'moeda', 'perPage'
        ));
    }

    public function create()
    {
        $empresaId = $this->getEmpresaId();
        $categorias = CategoriaFinanceira::where('empresa_id', $empresaId)->get();
        $veiculos = Veiculo::where('empresa_id', $empresaId)->get();
        $reservas = Reserva::with('codigoReserva')->where('empresa_id', $empresaId)->orderByDesc('id')->limit(50)->get();

        $breadcrumb = $this->breadcrumbFluxoCaixa('Novo Lançamento', route('financeiro.fluxo-caixa.index'));
        $titulo = 'Novo Lançamento';

        return view('financeiro.fluxo_caixa.lancamentos.create', compact('categorias', 'veiculos', 'reservas', 'breadcrumb', 'titulo'));
    }

    public function store(StoreLancamentoRequest $request)
    {
        $data = $request->validated();
        $data['empresa_id'] = $this->getEmpresaId();
        $data['user_id'] = Auth::id();

        $this->createAction->handle($data);

        return redirect()->route('financeiro.fluxo-caixa.index')->with('success', 'Lançamento financeiro criado com sucesso!');
    }

    public function edit(LancamentoFinanceiro $lancamento)
    {
        // Enforce tenant authorization
        if ($lancamento->empresa_id !== $this->getEmpresaId()) {
            abort(403, 'Acesso não autorizado.');
        }

        $empresaId = $this->getEmpresaId();
        $categorias = CategoriaFinanceira::where('empresa_id', $empresaId)->get();
        $veiculos = Veiculo::where('empresa_id', $empresaId)->get();
        $reservas = Reserva::with('codigoReserva')->where('empresa_id', $empresaId)->orderByDesc('id')->limit(50)->get();

        $breadcrumb = $this->breadcrumbFluxoCaixa('Editar Lançamento', route('financeiro.fluxo-caixa.index'));
        $titulo = 'Editar Lançamento';

        return view('financeiro.fluxo_caixa.lancamentos.edit', compact('lancamento', 'categorias', 'veiculos', 'reservas', 'breadcrumb', 'titulo'));
    }

    public function update(UpdateLancamentoRequest $request, LancamentoFinanceiro $lancamento)
    {
        if ($lancamento->empresa_id !== $this->getEmpresaId()) {
            abort(403, 'Acesso não autorizado.');
        }

        $data = $request->validated();
        $this->updateAction->handle($lancamento, $data);

        return redirect()->route('financeiro.fluxo-caixa.index')->with('success', 'Lançamento financeiro atualizado com sucesso!');
    }

    public function destroy(LancamentoFinanceiro $lancamento)
    {
        if ($lancamento->empresa_id !== $this->getEmpresaId()) {
            abort(403, 'Acesso não autorizado.');
        }

        $this->deleteAction->handle($lancamento);

        return redirect()->route('financeiro.fluxo-caixa.index')->with('success', 'Lançamento financeiro excluído com sucesso!');
    }

    // Category Management methods
    public function categoriasIndex()
    {
        $empresaId = $this->getEmpresaId();
        $categorias = CategoriaFinanceira::where('empresa_id', $empresaId)->orderBy('nome')->get();

        $breadcrumb = $this->breadcrumbFluxoCaixa('Categorias', route('financeiro.fluxo-caixa.categorias.index'));
        $titulo = 'Categorias Financeiras';

        return view('financeiro.fluxo_caixa.categorias.index', compact('categorias', 'breadcrumb', 'titulo'));
    }

    public function categoriasStore(Request $request)
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'tipo' => ['required', 'in:receita,despesa'],
            'descricao' => ['nullable', 'string', 'max:255'],
        ]);

        $data['empresa_id'] = $this->getEmpresaId();

        CategoriaFinanceira::create($data);

        return redirect()->route('financeiro.fluxo-caixa.categorias.index')->with('success', 'Categoria financeira criada com sucesso!');
    }

    public function categoriasDestroy(CategoriaFinanceira $categoria)
    {
        if ($categoria->empresa_id !== $this->getEmpresaId()) {
            abort(403, 'Acesso não autorizado.');
        }

        $categoria->delete();

        return redirect()->route('financeiro.fluxo-caixa.categorias.index')->with('success', 'Categoria financeira excluída com sucesso!');
    }
}
