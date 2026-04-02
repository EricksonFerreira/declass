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
use App\Traits\FiltroTrait;
use Illuminate\Http\Request;

class TipoUsuarioController extends BaseController implements FiltroInterface, ResourceInterface
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
        $this->titulo = 'Tipos de Usuario';
        $this->nomeFiltro = 'tipo_usuario';
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

        $tiposUsuario = $this->regraService->getAllWithFiltersAndPagination($itensFiltered, $perPage);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Tipos de Usuario');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];
        $nomeFiltro = $this->nomeFiltro;

        return view('tipo_usuario.index', compact(
            'tiposUsuario',
            'breadcrumb',
            'filters',
            'itensFiltered',
            'additionalFilters',
            'perPages',
            'perPage',
            'nomeFiltro'
        ))->with('titulo', $this->titulo);
    }

    public function create()
    {
        $permissoes = $this->permissaoService->getAll();
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Tipos de Usuario', 'tipo_usuario.index', 'Criar');
        [$metodo, $rota] = $this->getFormMethodAndRoute();

        return view('tipo_usuario.add-edit', compact('breadcrumb', 'metodo', 'rota', 'permissoes'))
            ->with('titulo', $this->titulo);
    }

    public function store(StoreRegraRequest $request)
    {
        $tipoUsuario = $this->regraService->create($request->all());
        $this->regraPermissaoService->atribuirPermissoesARole($tipoUsuario->id, $request->input('permissions', []));

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function edit($id)
    {
        $tipoUsuario = $this->regraService->getById($id);
        if (!$tipoUsuario) {
            abort(403, 'Sem acesso a este tipo de usuario.');
        }

        $permissoes = $this->permissaoService->getAllWithChecked($tipoUsuario->permissoes);
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Tipos de Usuario', 'tipo_usuario.index', "Editar: $tipoUsuario->name");
        [$metodo, $rota] = $this->getFormMethodAndRoute($tipoUsuario);

        return view('tipo_usuario.add-edit', compact('tipoUsuario', 'breadcrumb', 'permissoes', 'metodo', 'rota'))
            ->with('titulo', $this->titulo);
    }

    public function update(UpdateRegraRequest $request, $id)
    {
        $tipoUsuario = $this->regraService->update($id, $request->all());
        if (!$tipoUsuario) {
            abort(403, 'Sem acesso para editar este tipo de usuario.');
        }

        $this->regraPermissaoService->atribuirPermissoesARole($tipoUsuario->id, $request->input('permissions', []));

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function destroy($id)
    {
        $success = $this->regraService->delete($id);
        if (!$success) {
            abort(403, 'Sem acesso para excluir este tipo de usuario.');
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
        $request->session()->put("$this->nomeFiltro.perPage", $request->input('perPage'));
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function configurarSessao()
    {
        session()->put("$this->nomeFiltro.urlPadrao", 'tipo_usuario');
        session()->put("$this->nomeFiltro.rotaRedirect", 'tipo_usuario.index');
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
        return [];
    }

    private function getFormMethodAndRoute($tipoUsuario = null)
    {
        if ($tipoUsuario) {
            return [
                'PUT',
                route('tipo_usuario.update', $tipoUsuario->id),
            ];
        }

        return [
            'POST',
            route('tipo_usuario.store'),
        ];
    }
}
