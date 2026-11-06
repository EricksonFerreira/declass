<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\Disciplina\StoreDisciplinaRequest;
use App\Http\Requests\Disciplina\UpdateDisciplinaRequest;
use App\Services\BreadcrumbService;
use App\Services\DisciplinaService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\Request;

class DisciplinaController extends BaseController implements FiltroInterface, ResourceInterface
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    public function __construct(
        protected DisciplinaService $disciplinaService,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->titulo = 'Disciplinas';
        $this->nomeFiltro = 'disciplina';

        $this->middleware('can:disciplina.index')->only('index');
        $this->middleware('can:disciplina.create')->only('create');
        $this->middleware('can:disciplina.store')->only('store');
        $this->middleware('can:disciplina.edit')->only('edit');
        $this->middleware('can:disciplina.update')->only('update');
        $this->middleware('can:disciplina.destroy')->only('destroy');
        $this->middleware('can:disciplina.filtro')->only('filtro');
        $this->middleware('can:disciplina.removerFiltro')->only('removerFiltro');
        $this->middleware('can:disciplina.setPerPage')->only('setPerPage');
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $disciplinas = $this->disciplinaService->getAllWithFiltersAndPagination($itensFiltered, $perPage);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Disciplinas', 'Vida Acadêmica');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];
        $nomeFiltro = $this->nomeFiltro;

        return view('disciplina.index', compact(
            'disciplinas', 'breadcrumb', 'filters', 'itensFiltered',
            'additionalFilters', 'perPages', 'perPage', 'nomeFiltro'
        ))->with('titulo', $this->titulo);
    }

    public function create()
    {
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Disciplinas', 'disciplina.index', 'Nova Disciplina', 'Vida Acadêmica');

        return view('disciplina.add-edit', compact('breadcrumb'))->with('titulo', $this->titulo);
    }

    public function store(StoreDisciplinaRequest $request)
    {
        $this->disciplinaService->create($request->validated());

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Disciplina criada com sucesso.');
    }

    public function edit($id)
    {
        $disciplina = $this->disciplinaService->getById($id);
        abort_if(! $disciplina, 404, 'Disciplina não encontrada.');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Disciplinas', 'disciplina.index', "Editar: {$disciplina->nome}", 'Vida Acadêmica');

        return view('disciplina.add-edit', compact('disciplina', 'breadcrumb'))->with('titulo', $this->titulo);
    }

    public function update(UpdateDisciplinaRequest $request, $id)
    {
        $disciplina = $this->disciplinaService->update($id, $request->validated());
        abort_if(! $disciplina, 403, 'Sem acesso para editar esta disciplina.');

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Disciplina atualizada com sucesso.');
    }

    public function destroy($id)
    {
        $success = $this->disciplinaService->delete($id);
        abort_if(! $success, 403, 'Sem acesso para excluir esta disciplina.');

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
        session()->put("$this->nomeFiltro.urlPadrao", 'disciplina');
        session()->put("$this->nomeFiltro.rotaRedirect", 'disciplina.index');
    }

    public function obterFiltrosPrincipais()
    {
        return [
            ['name' => 'nome', 'label' => 'Nome', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'sigla', 'label' => 'Sigla', 'type' => 'text', 'typeWhere' => 'like'],
        ];
    }

    public function obterFiltrosSecundarios()
    {
        return [
            [
                'name' => 'ativo', 'label' => 'Status', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => ['S' => 'Ativo', 'N' => 'Inativo'],
            ],
        ];
    }
}
