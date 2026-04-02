<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\Permissao\StorePermissaoRequest;
use App\Http\Requests\Permissao\UpdatePermissaoRequest;
use App\Services\BreadcrumbService;
use App\Services\PermissaoService;
use App\Traits\AtualizaFiltrosTrait;
use Illuminate\Http\Request;
use App\Traits\FiltroTrait;

class PermissaoController extends BaseController implements FiltroInterface, ResourceInterface
{
    use FiltroTrait;
    use AtualizaFiltrosTrait;
    protected $permissaoService;
    protected $breadcrumbService;

    public function __construct(PermissaoService $permissaoService, BreadcrumbService $breadcrumbService)
    {
        $this->titulo = "Permissões";
        $this->nomeFiltro = 'permissao';
        $this->permissaoService = $permissaoService;
        $this->breadcrumbService = $breadcrumbService;
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $permissaos = $this->permissaoService->getAllWithFiltersAndPagination($itensFiltered, $perPage);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Permissaos');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];

        return view('permissao.index', compact(
            'permissaos',
            'breadcrumb',
            'filters',
            'itensFiltered',
            'additionalFilters',
            'perPages',
            'perPage'
        ))->with('titulo', $this->titulo);
    }

    public function create(){
        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo'
        ];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Permissaos','permissao.index','Criar');

        [$metodo,$rota] = $this->getFormMethodAndRoute();
        return view('permissao.add-edit', compact('breadcrumb','status','metodo','rota'))->with('titulo', $this->titulo);
    }

    public function store(StorePermissaoRequest $request)
    {
        $this->permissaoService->create($request->all());

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function edit($id)
    {
        $permissao = $this->permissaoService->getById($id);
        if (!$permissao) {
            abort(403, 'Sem acesso a esta permissao.');
        }
        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo'
        ];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Permissaos','permissao.index',"Editar: $permissao->name");
        [$metodo,$rota] = $this->getFormMethodAndRoute($permissao);

        return view('permissao.add-edit', compact('permissao','breadcrumb','status','metodo','rota'))->with('titulo', $this->titulo);
    }

    public function update(UpdatePermissaoRequest $request, $id)
    {
        $success = $this->permissaoService->update($id,$request->all());
        if (!$success) {
            abort(403, 'Sem acesso para editar esta permissao.');
        }
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function destroy($id)
    {
        $success = $this->permissaoService->delete($id);
        if (!$success) {
            abort(403, 'Sem acesso para excluir esta permissao.');
        }
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function filtro(Request $request)
    {
        $this->criarFiltros($request, $this->nomeFiltro);
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function removerFiltro(Request $request)
    {
        $atributo = $request->input('atributo') ?? null;
        $this->removeSessao($this->nomeFiltro, $atributo);
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function setPerPage(Request $request)
    {
        // Salva o valor de paginação na sessão
        $request->session()->put("$this->nomeFiltro.perPage", $request->input('perPage'));

        // Redireciona de volta para a página de listagem
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function configurarSessao()
    {
        session()->put("$this->nomeFiltro.urlPadrao", 'permissao');
        session()->put("$this->nomeFiltro.rotaRedirect", 'permissao.index');
    }

    public function obterFiltrosPrincipais()
    {
        return [
            ['name' => 'name', 'label' => 'Nome', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'guard_name', 'label' => 'Guard', 'type' => 'text', 'typeWhere' => 'like'],
        ];
    }

    public function obterFiltrosSecundarios()
    {
        return [
            // Adicione filtros secundários aqui, se necessário
        ];
    }

    /**
     * Função para obter o método e a rota para o formulário.
     *
     * @param  Permissao|null  $permissao
     * @return array
     */
    private function getFormMethodAndRoute($permissao = null)
    {
        if ($permissao) {
            return [
                'PUT',
                route('permissao.update', $permissao->id),
            ];
        }

        return [
            'POST',
            route('permissao.store'),
        ];
    }
}
