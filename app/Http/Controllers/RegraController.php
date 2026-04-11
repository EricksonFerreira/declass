<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\Regra\StoreRegraRequest;
use App\Http\Requests\Regra\UpdateRegraRequest;
use App\Services\BreadcrumbService;
use App\Services\PermissaoService;
use App\Services\RegraPermissaoService;
use App\Services\RegraService;
use App\Traits\AtualizaFiltrosTrait;
use Illuminate\Http\Request;
use App\Traits\FiltroTrait;

class RegraController extends BaseController implements FiltroInterface, ResourceInterface
{
    use FiltroTrait;
    use AtualizaFiltrosTrait;
    protected $regraService;
    protected $breadcrumbService;
    protected $permissaoService;
    protected $regraPermissaoService;

    public function __construct(
        RegraService $regraService,
        PermissaoService $permissaoService,
        RegraPermissaoService $regraPermissaoService,
        BreadcrumbService $breadcrumbService
    ) {
        $this->titulo = "Regras";
        $this->nomeFiltro = 'regra';
        $this->regraService = $regraService;
        $this->breadcrumbService = $breadcrumbService;
        $this->permissaoService = $permissaoService;
        $this->regraPermissaoService = $regraPermissaoService;
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $regras = $this->regraService->getAllWithFiltersAndPagination($itensFiltered, $perPage);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Regras');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];

        return view('regra.index', compact(
            'regras',
            'breadcrumb',
            'filters',
            'itensFiltered',
            'additionalFilters',
            'perPages',
            'perPage'
        ))->with('titulo', $this->titulo);
    }

    public function create()
    {
        $permissoes = $this->permissaoService->getAll();

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Regras', 'regra.index', 'Criar');

        [$metodo, $rota] = $this->getFormMethodAndRoute();
        return view('regra.add-edit', compact('breadcrumb', 'metodo', 'rota', 'permissoes'))->with('titulo', $this->titulo);
    }

    public function store(StoreRegraRequest $request)
    {
        $regra = $this->regraService->create($request->all());
        $this->regraPermissaoService->atribuirPermissoesARole($regra->id, $request->input('permissions', []));

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function show($id)
    {
        $regra = $this->regraService->getById($id);
        if (!$regra) {
            abort(404, 'Regra não encontrada.');
        }

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Regras', 'regra.index', $regra->name);

        return view('regra.show', compact('regra', 'breadcrumb'))->with('titulo', $regra->name);
    }

    public function edit($id)
    {
        $regra = $this->regraService->getById($id);
        if (!$regra) {
            abort(403, 'Sem acesso a esta regra.');
        }
        $permissoes = $this->permissaoService->getAllWithChecked($regra->permissoes);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Regras', 'regra.index', "Editar: $regra->name");
        [$metodo, $rota] = $this->getFormMethodAndRoute($regra);

        return view('regra.add-edit', compact('regra', 'breadcrumb','permissoes', 'metodo', 'rota'))->with('titulo', $this->titulo);
    }

    public function update(UpdateRegraRequest $request, $id)
    {
        $regra = $this->regraService->update($id, $request->all());
        if (!$regra) {
            abort(403, 'Sem acesso para editar esta regra.');
        }
        $this->regraPermissaoService->atribuirPermissoesARole($regra->id, $request->input('permissions', []));
        return $this->redirecionaRotaRedirect($this->nomeFiltro,'Adicionado com sucesso');
    }

    public function destroy($id)
    {
        $success = $this->regraService->delete($id);
        if (!$success) {
            abort(403, 'Sem acesso para excluir esta regra.');
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
        session()->put("$this->nomeFiltro.urlPadrao", 'regra');
        session()->put("$this->nomeFiltro.rotaRedirect", 'regra.index');
    }

    public function obterFiltrosPrincipais()
    {
        return [
            ['name' => 'name', 'label' => 'Nome', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'guard_name', 'label' => 'Nome da Política Segurança', 'type' => 'text', 'typeWhere' => 'like'],
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
     * @param  Regra|null  $regra
     * @return array
     */
    private function getFormMethodAndRoute($regra = null)
    {
        if ($regra) {
            return [
                'PUT',
                route('regra.update', $regra->id),
            ];
        }

        return [
            'POST',
            route('regra.store'),
        ];
    }
}
