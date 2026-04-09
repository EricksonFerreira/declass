<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\Permissao\StorePermissaoRequest;
use App\Http\Requests\Permissao\UpdatePermissaoRequest;
use App\Services\BreadcrumbService;
use App\Services\PermissaoService;
use App\Services\PermissaoSyncService;
use App\Traits\AtualizaFiltrosTrait;
use Illuminate\Http\Request;
use App\Traits\FiltroTrait;

class PermissaoController extends BaseController implements FiltroInterface, ResourceInterface
{
    use FiltroTrait;
    use AtualizaFiltrosTrait;
    protected $permissaoService;
    protected $breadcrumbService;
    protected $permissaoSyncService;

    public function __construct(PermissaoService $permissaoService, BreadcrumbService $breadcrumbService, PermissaoSyncService $permissaoSyncService)
    {
        $this->titulo = "Permissões";
        $this->nomeFiltro = 'permissao';
        $this->permissaoService = $permissaoService;
        $this->breadcrumbService = $breadcrumbService;
        $this->permissaoSyncService = $permissaoSyncService;
    }

    public function index()
    {
        $this->configurarSessao();
        $this->permissaoSyncService->syncIfNeeded();

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Permissaos');

        $busca = request('busca', '');

        $query = \App\Models\Permissao::query()
            ->where('guard_name', 'web')
            ->orderBy('grupo')
            ->orderBy('name');

        if ($busca !== '') {
            $query->where(function ($q) use ($busca) {
                $q->where('name', 'like', "%{$busca}%")
                  ->orWhere('descricao', 'like', "%{$busca}%")
                  ->orWhere('grupo', 'like', "%{$busca}%");
            });
        }

        $permissaos = $query->get()->groupBy(fn ($p) => $p->grupo_efetivo ?: '_outros');

        $totalPermissoes = \App\Models\Permissao::where('guard_name', 'web')->count();
        $totalGrupos     = \App\Models\Permissao::where('guard_name', 'web')
            ->selectRaw('COALESCE(grupo, SUBSTRING_INDEX(name, ".", 1)) as g')
            ->distinct()
            ->count();

        return view('permissao.index', compact(
            'permissaos',
            'breadcrumb',
            'busca',
            'totalPermissoes',
            'totalGrupos'
        ))->with('titulo', $this->titulo);
    }

    public function create(){
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Permissaos','permissao.index','Criar');
        $grupos = $this->getGruposDisponiveis();
        $grupoPreSelecionado = request('grupo', '');

        [$metodo,$rota] = $this->getFormMethodAndRoute();
        return view('permissao.add-edit', compact('breadcrumb','metodo','rota','grupos','grupoPreSelecionado'))->with('titulo', $this->titulo);
    }

    public function store(StorePermissaoRequest $request)
    {
        $data = $request->all();
        if (empty($data['grupo']) && !empty($data['name'])) {
            $name = $data['name'];
            $data['grupo'] = str_contains($name, '.') ? explode('.', $name)[0] : $name;
        }
        $this->permissaoService->create($data);

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function edit($id)
    {
        $permissao = $this->permissaoService->getById($id);
        if (!$permissao) {
            abort(403, 'Sem acesso a esta permissao.');
        }
        $grupos = $this->getGruposDisponiveis();
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Permissaos','permissao.index',"Editar: $permissao->name");
        [$metodo,$rota] = $this->getFormMethodAndRoute($permissao);

        $grupoPreSelecionado = '';
        return view('permissao.add-edit', compact('permissao','breadcrumb','metodo','rota','grupos','grupoPreSelecionado'))->with('titulo', $this->titulo);
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
        return [];
    }

    private function getGruposDisponiveis(): array
    {
        return \App\Models\Permissao::where('guard_name', 'web')
            ->whereNotNull('grupo')
            ->where('grupo', '!=', '')
            ->distinct()
            ->orderBy('grupo')
            ->pluck('grupo')
            ->toArray();
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
