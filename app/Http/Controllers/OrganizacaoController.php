<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\Organizacao\StoreOrganizacaoRequest;
use App\Http\Requests\Organizacao\UpdateOrganizacaoRequest;
use App\Models\Organizacao;
use App\Services\BreadcrumbService;
use App\Services\OrganizacaoService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\Request;

class OrganizacaoController extends BaseController implements FiltroInterface, ResourceInterface
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    public function __construct(
        protected OrganizacaoService $organizacaoService,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->titulo = 'Organizações';
        $this->nomeFiltro = 'organizacao';

        $this->middleware('can:organizacao.index')->only('index');
        $this->middleware('can:organizacao.show')->only('show');
        $this->middleware('can:organizacao.create')->only('create');
        $this->middleware('can:organizacao.store')->only('store');
        $this->middleware('can:organizacao.edit')->only('edit');
        $this->middleware('can:organizacao.update')->only('update');
        $this->middleware('can:organizacao.destroy')->only('destroy');
        $this->middleware('can:organizacao.filtro')->only('filtro');
        $this->middleware('can:organizacao.removerFiltro')->only('removerFiltro');
        $this->middleware('can:organizacao.setPerPage')->only('setPerPage');
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $organizacoes = $this->organizacaoService->getAllWithFiltersAndPagination(
            $itensFiltered, $perPage, ['escolas', 'empresa']
        );

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Organizações', 'Gerenciar');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];
        $nomeFiltro = $this->nomeFiltro;

        return view('organizacao.index', compact(
            'organizacoes', 'breadcrumb', 'filters', 'itensFiltered',
            'additionalFilters', 'perPages', 'perPage', 'nomeFiltro'
        ))->with('titulo', $this->titulo);
    }

    public function create()
    {
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Organizações', 'organizacao.index', 'Criar', 'Gerenciar');
        $tipos = Organizacao::TIPOS;

        return view('organizacao.add-edit', compact('breadcrumb', 'tipos'))
            ->with('titulo', $this->titulo);
    }

    public function store(StoreOrganizacaoRequest $request)
    {
        $this->organizacaoService->create($request->validated());

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Organização criada com sucesso.');
    }

    public function show($id)
    {
        $organizacao = $this->organizacaoService->getById($id, ['escolas', 'empresa', 'usuarios']);
        abort_if(! $organizacao, 404, 'Organização não encontrada.');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Organizações', 'organizacao.index', $organizacao->nome);

        return view('organizacao.show', compact('organizacao', 'breadcrumb'))->with('titulo', $organizacao->nome);
    }

    public function edit($id)
    {
        $organizacao = $this->organizacaoService->getById($id);
        abort_if(! $organizacao, 404, 'Organização não encontrada.');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Organizações', 'organizacao.index', "Editar: {$organizacao->nome}");
        $tipos = Organizacao::TIPOS;

        return view('organizacao.add-edit', compact('organizacao', 'breadcrumb', 'tipos'))
            ->with('titulo', $this->titulo);
    }

    public function update(UpdateOrganizacaoRequest $request, $id)
    {
        $organizacao = $this->organizacaoService->update($id, $request->validated());
        abort_if(! $organizacao, 403, 'Sem acesso para editar esta organização.');

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Organização atualizada com sucesso.');
    }

    public function destroy($id)
    {
        $success = $this->organizacaoService->delete($id);
        abort_if(! $success, 403, 'Sem acesso para excluir esta organização.');

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
        session()->put("$this->nomeFiltro.urlPadrao", 'organizacao');
        session()->put("$this->nomeFiltro.rotaRedirect", 'organizacao.index');
    }

    public function obterFiltrosPrincipais()
    {
        return [
            ['name' => 'nome', 'label' => 'Nome', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'tipo', 'label' => 'Tipo', 'type' => 'text', 'typeWhere' => 'like'],
        ];
    }

    public function obterFiltrosSecundarios()
    {
        return [];
    }
}
