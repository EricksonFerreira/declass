<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Models\Escola;
use App\Models\Mensalidade;
use App\Models\Turma;
use App\Services\BreadcrumbService;
use App\Services\MensalidadeService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\Request;

class RelatorioInadimplenciaController extends BaseController implements FiltroInterface
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    public function __construct(
        protected MensalidadeService $mensalidadeService,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->titulo = 'Relatório de Inadimplência';
        $this->nomeFiltro = 'relatorio_inadimplencia';

        $this->middleware('can:relatorio_inadimplencia.index')->only('index');
        $this->middleware('can:relatorio_inadimplencia.filtro')->only('filtro');
        $this->middleware('can:relatorio_inadimplencia.removerFiltro')->only('removerFiltro');
        $this->middleware('can:relatorio_inadimplencia.setPerPage')->only('setPerPage');
    }

    /**
     * Inadimplencia: mensalidades por situacao (vencida/pendente/
     * paga/cancelada) com filtros de aluno, escola, turma,
     * competencia e situacao. Dividas mais antigas primeiro.
     */
    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 25);

        $mensalidades = $this->mensalidadeService->relatorioInadimplencia($itensFiltered, (int) $perPage);
        $resumo = $this->mensalidadeService->resumoInadimplencia($itensFiltered);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Relatório de Inadimplência', 'Relatórios');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];
        $nomeFiltro = $this->nomeFiltro;

        return view('relatorio_inadimplencia.index', compact(
            'mensalidades', 'resumo', 'breadcrumb', 'filters', 'itensFiltered',
            'additionalFilters', 'perPages', 'perPage', 'nomeFiltro'
        ))->with('titulo', $this->titulo);
    }

    public function filtro(Request $request)
    {
        $this->criarFiltros($request, $this->nomeFiltro);

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function removerFiltro(Request $request)
    {
        $this->removeSessao($this->nomeFiltro, $request->input('atributo'));

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function setPerPage(Request $request)
    {
        $request->session()->put("$this->nomeFiltro.perPage", $request->input('perPage'));

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function configurarSessao()
    {
        session()->put("$this->nomeFiltro.urlPadrao", 'relatorio-inadimplencia');
        session()->put("$this->nomeFiltro.rotaRedirect", 'relatorio_inadimplencia.index');
    }

    public function obterFiltrosPrincipais()
    {
        return [
            ['name' => 'nome', 'label' => 'Aluno', 'type' => 'text', 'typeWhere' => 'like'],
            [
                'name' => 'situacao', 'label' => 'Situação', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => array_merge(
                    [['value' => '', 'label' => 'Todas', 'selected' => false]],
                    collect(['vencida' => 'Vencida', 'a_vencer' => 'A vencer', 'paga' => 'Paga', 'cancelada' => 'Cancelada'])
                        ->map(fn ($label, $v) => ['value' => $v, 'label' => $label, 'selected' => false])
                        ->values()->all()
                ),
            ],
        ];
    }

    public function obterFiltrosSecundarios()
    {
        return [
            [
                'name' => 'escola_id', 'label' => 'Escola', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => array_merge(
                    [['value' => '', 'label' => 'Todas', 'selected' => false]],
                    Escola::query()->orderBy('nome')->get(['id', 'nome'])
                        ->map(fn ($e) => ['value' => $e->id, 'label' => $e->nome, 'selected' => false])
                        ->values()->all()
                ),
            ],
            [
                'name' => 'turma_id', 'label' => 'Turma', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => array_merge(
                    [['value' => '', 'label' => 'Todas', 'selected' => false]],
                    Turma::query()->with('escola:id,nome')->orderBy('nome')->get(['id', 'nome', 'escola_id'])
                        ->map(fn ($t) => ['value' => $t->id, 'label' => $t->nome.' — '.($t->escola->nome ?? ''), 'selected' => false])
                        ->values()->all()
                ),
            ],
            [
                'name' => 'competencia', 'label' => 'Competência', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => array_merge(
                    [['value' => '', 'label' => 'Todas', 'selected' => false]],
                    $this->competenciasDisponiveis()
                        ->map(fn ($c) => ['value' => $c, 'label' => $c, 'selected' => false])
                        ->values()->all()
                ),
            ],
        ];
    }

    /** Ultimas competencias (YYYY-MM) distintas, para o select. */
    private function competenciasDisponiveis()
    {
        return Mensalidade::query()
            ->select('competencia')
            ->distinct()
            ->orderByDesc('competencia')
            ->limit(24)
            ->pluck('competencia');
    }
}
