<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Models\AnoLetivo;
use App\Models\Escola;
use App\Models\Turma;
use App\Services\BreadcrumbService;
use App\Services\FrequenciaService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\Request;

class RelatorioFrequenciaController extends BaseController implements FiltroInterface
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    public function __construct(
        protected FrequenciaService $frequenciaService,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->titulo = 'Relatório de Frequência';
        $this->nomeFiltro = 'relatorio_frequencia';

        $this->middleware('can:relatorio_frequencia.index')->only('index');
        $this->middleware('can:relatorio_frequencia.filtro')->only('filtro');
        $this->middleware('can:relatorio_frequencia.removerFiltro')->only('removerFiltro');
        $this->middleware('can:relatorio_frequencia.setPerPage')->only('setPerPage');
    }

    /**
     * Frequencia consolidada: ranking de faltas por aluno com
     * filtros de aluno, escola, ano letivo, turma e "somente em risco".
     */
    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 25);

        $matriculas = $this->frequenciaService->consolidadoFaltas($itensFiltered, (int) $perPage);
        $resumo = $this->frequenciaService->resumoFaltas($itensFiltered);
        $graficos = $this->frequenciaService->graficosFaltas($itensFiltered);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Relatório de Frequência', 'Relatórios');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];
        $nomeFiltro = $this->nomeFiltro;

        return view('relatorio_frequencia.index', compact(
            'matriculas', 'resumo', 'graficos', 'breadcrumb', 'filters', 'itensFiltered',
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
        session()->put("$this->nomeFiltro.urlPadrao", 'relatorio-frequencia');
        session()->put("$this->nomeFiltro.rotaRedirect", 'relatorio_frequencia.index');
    }

    public function obterFiltrosPrincipais()
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
                'name' => 'ano_letivo_id', 'label' => 'Ano letivo', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => array_merge(
                    [['value' => '', 'label' => 'Todos', 'selected' => false]],
                    AnoLetivo::query()->with('escola:id,nome')->orderByDesc('ano')->get(['id', 'ano', 'escola_id'])
                        ->map(fn ($a) => ['value' => $a->id, 'label' => 'Ano '.$a->ano.($a->escola ? ' — '.$a->escola->nome : ''), 'selected' => false])
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
            ['name' => 'nome', 'label' => 'Aluno', 'type' => 'text', 'typeWhere' => 'like', 'placeholder' => 'Buscar por nome...'],
            [
                'name' => 'apenas_risco', 'label' => 'Risco', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => [
                    ['value' => '', 'label' => 'Todos', 'selected' => false],
                    ['value' => '1', 'label' => 'Somente em risco (<75%)', 'selected' => false],
                ],
            ],
        ];
    }

    public function obterFiltrosSecundarios()
    {
        return [
            [
                'name' => 'turno', 'label' => 'Turno', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => Turma::TURNOS,
            ],
        ];
    }
}
