<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\PeriodoLetivo\StorePeriodoLetivoRequest;
use App\Http\Requests\PeriodoLetivo\UpdatePeriodoLetivoRequest;
use App\Models\AnoLetivo;
use App\Models\Escola;
use App\Services\BreadcrumbService;
use App\Services\PeriodoLetivoService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\Request;

class PeriodoLetivoController extends BaseController implements FiltroInterface, ResourceInterface
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    public function __construct(
        protected PeriodoLetivoService $periodoService,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->titulo = 'Períodos Letivos';
        $this->nomeFiltro = 'periodo_letivo';

        $this->middleware('can:periodo_letivo.index')->only('index');
        $this->middleware('can:periodo_letivo.create')->only('create');
        $this->middleware('can:periodo_letivo.store')->only('store');
        $this->middleware('can:periodo_letivo.show')->only('show');
        $this->middleware('can:periodo_letivo.edit')->only('edit');
        $this->middleware('can:periodo_letivo.update')->only('update');
        $this->middleware('can:periodo_letivo.destroy')->only('destroy');
        $this->middleware('can:periodo_letivo.filtro')->only('filtro');
        $this->middleware('can:periodo_letivo.removerFiltro')->only('removerFiltro');
        $this->middleware('can:periodo_letivo.setPerPage')->only('setPerPage');
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 15);

        $periodos = $this->periodoService->getAllWithFiltersAndPagination(
            $itensFiltered, $perPage, ['escola', 'anoLetivo'], 'ordem', 'asc'
        );

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Períodos Letivos', 'Vida Acadêmica');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];
        $nomeFiltro = $this->nomeFiltro;

        return view('periodo_letivo.index', compact(
            'periodos', 'breadcrumb', 'filters', 'itensFiltered',
            'additionalFilters', 'perPages', 'perPage', 'nomeFiltro'
        ))->with('titulo', $this->titulo);
    }

    public function create()
    {
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Períodos Letivos', 'periodo_letivo.index', 'Novo Período', 'Vida Acadêmica');

        return view('periodo_letivo.add-edit', array_merge(compact('breadcrumb'), $this->opcoesFormulario()))
            ->with('titulo', $this->titulo);
    }

    public function store(StorePeriodoLetivoRequest $request)
    {
        $this->periodoService->create($request->validated());

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Período letivo criado com sucesso.');
    }

    public function show($id)
    {
        $periodo = $this->periodoService->getById($id, ['escola', 'anoLetivo.escola', 'anoLetivo.periodos']);
        abort_if(! $periodo, 404, 'Período letivo não encontrado.');

        $turmas = \App\Models\Turma::query()
            ->where('ano_letivo_id', $periodo->ano_letivo_id)
            ->with('etapa')
            ->withCount('matriculas')
            ->orderBy('nome')
            ->get();

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbShow(
            'Períodos Letivos', 'periodo_letivo.index', $periodo->nome, 'Vida Acadêmica'
        );

        return view('periodo_letivo.show', array_merge(compact('periodo', 'turmas', 'breadcrumb'), $this->opcoesGerador()))
            ->with('titulo', $periodo->nome);
    }

    public function edit($id)
    {
        $periodo = $this->periodoService->getById($id, ['escola', 'anoLetivo']);
        abort_if(! $periodo, 404, 'Período letivo não encontrado.');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Períodos Letivos', 'periodo_letivo.index', "Editar: {$periodo->nome}", 'Vida Acadêmica');

        return view('periodo_letivo.add-edit', array_merge(compact('periodo', 'breadcrumb'), $this->opcoesFormulario()))
            ->with('titulo', $this->titulo);
    }

    public function update(UpdatePeriodoLetivoRequest $request, $id)
    {
        $periodo = $this->periodoService->update($id, $request->validated());
        abort_if(! $periodo, 403, 'Sem acesso para editar este período.');

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Período letivo atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $success = $this->periodoService->delete($id);
        abort_if(! $success, 403, 'Sem acesso para excluir este período.');

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
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
        session()->put("$this->nomeFiltro.urlPadrao", 'periodo_letivo');
        session()->put("$this->nomeFiltro.rotaRedirect", 'periodo_letivo.index');
    }

    public function obterFiltrosPrincipais()
    {
        return [
            [
                'name' => 'ano_letivo_id', 'label' => 'Ano letivo', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => $this->anosLetivosOpcoesFiltro(),
            ],
            [
                'name' => 'escola_id', 'label' => 'Escola', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => $this->escolasOpcoesFiltro(),
            ],
            [
                'name' => 'ativo', 'label' => 'Status', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => [
                    ['value' => 'S', 'label' => 'Ativo', 'selected' => false],
                    ['value' => 'N', 'label' => 'Inativo', 'selected' => false],
                ],
            ],
        ];
    }

    public function obterFiltrosSecundarios()
    {
        return [
            ['name' => 'nome', 'label' => 'Nome', 'type' => 'text', 'typeWhere' => 'like'],
        ];
    }

    private function opcoesFormulario(): array
    {
        return [
            'anosLetivos' => $this->anosLetivosOpcoes(),
        ];
    }

    /**
     * Opcoes do gerador de turmas em lote exibido no show do periodo:
     * etapas ativas e turnos possiveis. O ano letivo vem do periodo.
     */
    private function opcoesGerador(): array
    {
        return [
            'etapasOpcoes' => \App\Models\Etapa::query()->where('ativo', 'S')
                ->orderBy('ordem')->orderBy('nome')
                ->get(['id', 'nome']),
            'turnosOpcoes' => \App\Models\Turma::TURNOS,
        ];
    }

    private function anosLetivosOpcoes(): array
    {
        return AnoLetivo::query()->with('escola')->orderByDesc('ano')->get()
            ->mapWithKeys(fn (AnoLetivo $a) => [
                $a->id => $a->ano.' — '.($a->escola->nome ?? ''),
            ])->all();
    }

    private function escolasOpcoesFiltro()
    {
        return Escola::query()->orderBy('nome')->get(['id', 'nome'])
            ->map(fn (Escola $escola) => ['value' => $escola->id, 'label' => $escola->nome])
            ->all();
    }

    private function anosLetivosOpcoesFiltro()
    {
        return AnoLetivo::query()->with('escola')->orderByDesc('ano')->get(['id', 'ano', 'escola_id'])
            ->map(fn (AnoLetivo $a) => [
                'value' => $a->id,
                'label' => $a->ano.' — '.($a->escola->nome ?? ''),
            ])->all();
    }
}
