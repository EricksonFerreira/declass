<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\Regra\StoreRegraRequest;
use App\Http\Requests\Regra\UpdateRegraRequest;
use App\Models\Regra;
use App\Services\BreadcrumbService;
use App\Services\PermissaoService;
use App\Services\PermissaoSyncService;
use App\Services\RegraPermissaoService;
use App\Services\RegraService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\Request;

class RegraController extends BaseController implements FiltroInterface, ResourceInterface
{
    use FiltroTrait;
    use AtualizaFiltrosTrait;

    protected $regraService;
    protected $breadcrumbService;
    protected $permissaoService;
    protected $regraPermissaoService;
    protected $permissaoSyncService;

    public function __construct(
        RegraService $regraService,
        PermissaoService $permissaoService,
        RegraPermissaoService $regraPermissaoService,
        BreadcrumbService $breadcrumbService,
        PermissaoSyncService $permissaoSyncService
    ) {
        $this->titulo = 'Perfis de Acesso';
        $this->nomeFiltro = 'regra';
        $this->regraService = $regraService;
        $this->breadcrumbService = $breadcrumbService;
        $this->permissaoService = $permissaoService;
        $this->regraPermissaoService = $regraPermissaoService;
        $this->permissaoSyncService = $permissaoSyncService;
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $regras = $this->regraService->getAllWithFiltersAndPagination($itensFiltered, $perPage, ['permissoes', 'empresa'], 'name', 'asc');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Perfis de Acesso');
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
        $this->permissaoSyncService->syncIfNeeded();
        $permissoes = $this->permissaoService->getAllForUserContext(auth()->user());
        $plano = auth()->user()?->empresa?->planoAtivo()?->with('plano')->first()?->plano;
        $limitePermissoesPorRegra = $plano?->limite_permissoes_por_regra;

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Perfis de Acesso', 'regra.index', 'Criar');
        [$metodo, $rota] = $this->getFormMethodAndRoute();

        return view('regra.add-edit', compact('breadcrumb', 'metodo', 'rota', 'permissoes', 'plano', 'limitePermissoesPorRegra'))->with('titulo', $this->titulo);
    }

    public function store(StoreRegraRequest $request)
    {
        $regra = $this->regraService->create($request->all());
        $this->regraPermissaoService->atribuirPermissoesARole($regra->id, $request->input('permissions', []));

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Perfil de acesso criado com sucesso.');
    }

    public function show($id)
    {
        $regra = $this->regraService->getById($id, ['permissoes', 'empresa']);
        if (! $regra) {
            abort(404, 'Perfil de acesso não encontrado.');
        }

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Perfis de Acesso', 'regra.index', $regra->name);

        return view('regra.show', compact('regra', 'breadcrumb'))->with('titulo', $regra->name);
    }

    public function edit($id)
    {
        $regra = $this->regraService->getById($id);
        $this->abortIfRegraGlobal($regra);

        $this->permissaoSyncService->syncIfNeeded();
        $permissoes = $this->permissaoService->getAllWithCheckedForUserContext($regra->permissoes, auth()->user());
        $plano = auth()->user()?->empresa?->planoAtivo()?->with('plano')->first()?->plano;
        $limitePermissoesPorRegra = $plano?->limite_permissoes_por_regra;

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Perfis de Acesso', 'regra.index', "Editar: $regra->name");
        [$metodo, $rota] = $this->getFormMethodAndRoute($regra);

        return view('regra.add-edit', compact('regra', 'breadcrumb', 'permissoes', 'metodo', 'rota', 'plano', 'limitePermissoesPorRegra'))->with('titulo', $this->titulo);
    }

    public function update(UpdateRegraRequest $request, $id)
    {
        $regra = $this->regraService->getById($id);
        $this->abortIfRegraGlobal($regra);

        $regra = $this->regraService->update($id, $request->all());
        if (! $regra) {
            abort(403, 'Sem acesso para editar este perfil de acesso.');
        }

        $this->regraPermissaoService->atribuirPermissoesARole($regra->id, $request->input('permissions', []));

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Perfil de acesso atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $regra = $this->regraService->getById($id);
        $this->abortIfRegraGlobal($regra);

        $success = $this->regraService->delete($id);
        if (! $success) {
            abort(403, 'Sem acesso para excluir este perfil de acesso.');
        }

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    /**
     * Perfis globais (empresa_id nulo: administrador, gerente, cliente...)
     * pertencem a plataforma — usuario de empresa nao pode altera-los.
     */
    private function abortIfRegraGlobal(?Regra $regra): void
    {
        if (! $regra) {
            abort(404, 'Perfil de acesso não encontrado.');
        }

        if ($regra->empresa_id === null && ! auth()->user()?->isSuperAdmin()) {
            abort(403, 'Este é um perfil padrão do sistema e não pode ser alterado.');
        }
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
        session()->put("$this->nomeFiltro.urlPadrao", 'regra');
        session()->put("$this->nomeFiltro.rotaRedirect", 'regra.index');
    }

    public function obterFiltrosPrincipais()
    {
        return [
            ['name' => 'name', 'label' => 'Nome', 'type' => 'text', 'typeWhere' => 'like'],
        ];
    }

    public function obterFiltrosSecundarios()
    {
        return [];
    }

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
