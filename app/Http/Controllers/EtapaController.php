<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\Etapa\StoreEtapaRequest;
use App\Http\Requests\Etapa\UpdateEtapaRequest;
use App\Models\Etapa;
use App\Services\BreadcrumbService;
use App\Services\EtapaService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\Request;

class EtapaController extends BaseController implements FiltroInterface, ResourceInterface
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    public function __construct(
        protected EtapaService $etapaService,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->titulo = 'Etapas';
        $this->nomeFiltro = 'etapa';

        $this->middleware('can:etapa.index')->only('index');
        $this->middleware('can:etapa.create')->only('create');
        $this->middleware('can:etapa.store')->only('store');
        $this->middleware('can:etapa.edit')->only('edit');
        $this->middleware('can:etapa.update')->only('update');
        $this->middleware('can:etapa.destroy')->only('destroy');
        $this->middleware('can:etapa.filtro')->only('filtro');
        $this->middleware('can:etapa.removerFiltro')->only('removerFiltro');
        $this->middleware('can:etapa.setPerPage')->only('setPerPage');
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $etapas = $this->etapaService->getAllWithFiltersAndPagination(
            $itensFiltered, $perPage, [], 'ordem', 'asc',
            fn ($query) => $query->withCount('turmas')
        );

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Etapas', 'Vida Acadêmica');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];
        $nomeFiltro = $this->nomeFiltro;

        return view('etapa.index', compact(
            'etapas', 'breadcrumb', 'filters', 'itensFiltered',
            'additionalFilters', 'perPages', 'perPage', 'nomeFiltro'
        ))->with('titulo', $this->titulo);
    }

    public function create()
    {
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Etapas', 'etapa.index', 'Nova Etapa', 'Vida Acadêmica');

        return view('etapa.add-edit', array_merge(compact('breadcrumb'), $this->opcoesFormulario()))
            ->with('titulo', $this->titulo);
    }

    public function store(StoreEtapaRequest $request)
    {
        $this->etapaService->create($request->validated());

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Etapa criada com sucesso.');
    }

    public function edit($id)
    {
        $etapa = $this->etapaService->getById($id);
        abort_if(! $etapa, 404, 'Etapa não encontrada.');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Etapas', 'etapa.index', "Editar: {$etapa->nome}", 'Vida Acadêmica');

        return view('etapa.add-edit', array_merge(compact('etapa', 'breadcrumb'), $this->opcoesFormulario()))
            ->with('titulo', $this->titulo);
    }

    public function update(UpdateEtapaRequest $request, $id)
    {
        $etapa = $this->etapaService->update($id, $request->validated());
        abort_if(! $etapa, 403, 'Sem acesso para editar esta etapa.');

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Etapa atualizada com sucesso.');
    }

    public function destroy($id)
    {
        $success = $this->etapaService->delete($id);
        abort_if(! $success, 403, 'Sem acesso para excluir esta etapa.');

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
        session()->put("$this->nomeFiltro.urlPadrao", 'etapa');
        session()->put("$this->nomeFiltro.rotaRedirect", 'etapa.index');
    }

    public function obterFiltrosPrincipais()
    {
        return [
            ['name' => 'nome', 'label' => 'Nome', 'type' => 'text', 'typeWhere' => 'like'],
        ];
    }

    public function obterFiltrosSecundarios()
    {
        return [
            [
                'name' => 'segmento', 'label' => 'Segmento', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => collect(Etapa::SEGMENTOS)
                    ->map(fn ($label, $v) => ['value' => $v, 'label' => $label, 'selected' => false])
                    ->values()->all(),
            ],
            [
                'name' => 'ativo', 'label' => 'Status', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => [
                    ['value' => 'S', 'label' => 'Ativa', 'selected' => false],
                    ['value' => 'N', 'label' => 'Inativa', 'selected' => false],
                ],
            ],
        ];
    }

    private function opcoesFormulario(): array
    {
        return ['segmentos' => Etapa::SEGMENTOS];
    }
}
