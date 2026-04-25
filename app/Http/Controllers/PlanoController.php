<?php

namespace App\Http\Controllers;

use App\Models\ItemPlano;
use App\Models\Permissao;
use App\Models\Plano;
use App\Services\BreadcrumbService;
use App\Services\PlanoPermissaoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PlanoController extends Controller
{
    public function __construct(
        private readonly BreadcrumbService $breadcrumbService,
        private readonly PlanoPermissaoService $planoPermissaoService
    ) {}

    public function hub(): View
    {
        $titulo = 'Planos';
        $subtitulo = 'Central de gestao: catalogo, assinaturas e regras/politicas';
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Planos');

        return view('plano.hub', compact('breadcrumb', 'titulo', 'subtitulo'));
    }

    public function index(): View
    {
        $titulo = 'Catalogo de Planos';
        $subtitulo = 'Gerencie os planos disponiveis para locadoras';
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new \App\DTO\BreadCrumbItem('Home', route('home')),
            new \App\DTO\BreadCrumbItem('Planos', route('planos.hub')),
            new \App\DTO\BreadCrumbItem('Catalogo', null),
        ]);

        $planos = Plano::withCount('assinaturas')->orderBy('ordem')->get();

        return view('plano.index', compact('planos', 'breadcrumb', 'titulo', 'subtitulo'));
    }

    public function create(): View
    {
        $titulo = 'Novo Plano';
        $subtitulo = 'Crie um novo plano e defina recursos e limites';
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new \App\DTO\BreadCrumbItem('Home', route('home')),
            new \App\DTO\BreadCrumbItem('Planos', route('planos.hub')),
            new \App\DTO\BreadCrumbItem('Catalogo', route('plano.index')),
            new \App\DTO\BreadCrumbItem('Novo', null),
        ]);

        return view('plano.create', compact('breadcrumb', 'titulo', 'subtitulo'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nome'            => 'required|string|max:80',
            'descricao'       => 'nullable|string',
            'valor_mensal'    => 'required|numeric|min:0',
            'valor_anual'     => 'required|numeric|min:0',
            'limite_veiculos' => 'nullable|integer|min:1',
            'site_vendas'     => 'boolean',
            'modulo_multas'   => 'boolean',
            'modulo_nfse'     => 'boolean',
            'multi_estacao'   => 'boolean',
            'api_integracao'  => 'boolean',
            'ativo'           => 'boolean',
            'destaque'        => 'boolean',
            'ordem'           => 'integer|min:0',
            'itens'           => 'nullable|array',
            'itens.*.descricao' => 'required_with:itens|string|max:200',
            'itens.*.incluso'   => 'boolean',
        ]);

        $data['slug'] = Str::slug($data['nome']);
        $itens = $data['itens'] ?? [];
        unset($data['itens']);

        $this->normalizarBooleans($data, $request);

        $plano = Plano::create($data);
        $this->sincronizarItens($plano, $itens);

        return redirect()->route('plano.index')
            ->with('success', "Plano \"{$plano->nome}\" criado com sucesso.");
    }

    public function edit(Plano $plano): View
    {
        $titulo = 'Editar Plano';
        $subtitulo = 'Atualize precos, limites e recursos do plano';
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new \App\DTO\BreadCrumbItem('Home', route('home')),
            new \App\DTO\BreadCrumbItem('Planos', route('planos.hub')),
            new \App\DTO\BreadCrumbItem('Catalogo', route('plano.index')),
            new \App\DTO\BreadCrumbItem('Editar: ' . $plano->nome, null),
        ]);

        $plano->load('itens');

        return view('plano.edit', compact('plano', 'breadcrumb', 'titulo', 'subtitulo'));
    }

    public function acessos(Plano $plano): View
    {
        $titulo = 'Acessos do Plano';
        $subtitulo = 'Defina o que o administrador da empresa pode usar e conceder neste plano';
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new \App\DTO\BreadCrumbItem('Home', route('home')),
            new \App\DTO\BreadCrumbItem('Planos', route('planos.hub')),
            new \App\DTO\BreadCrumbItem('Catalogo', route('plano.index')),
            new \App\DTO\BreadCrumbItem($plano->nome, route('plano.edit', $plano)),
            new \App\DTO\BreadCrumbItem('Acessos', null),
        ]);

        $permissoes = Permissao::query()
            ->where('guard_name', 'web')
            ->orderBy('grupo')
            ->orderBy('name')
            ->get();

        $permitidas = Schema::hasTable('plano_permissao')
            ? $plano->permissoes()->pluck('permissions.id')->map(fn ($id) => (int) $id)->all()
            : [];
        $gruposPermissao = $this->planoPermissaoService->gruposDePermissoesParaTela($permissoes);

        return view('plano.acessos', compact(
            'plano',
            'breadcrumb',
            'titulo',
            'subtitulo',
            'gruposPermissao',
            'permitidas'
        ));
    }

    public function salvarAcessos(Request $request, Plano $plano): RedirectResponse
    {
        $data = $request->validate([
            'limite_permissoes_por_regra' => 'nullable|integer|min:1|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);

        $permissionIds = collect($data['permissions'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $plano->update([
            'limite_permissoes_por_regra' => $data['limite_permissoes_por_regra'] ?? null,
        ]);
        if (Schema::hasTable('plano_permissao')) {
            $plano->permissoes()->sync($permissionIds);
        }

        return redirect()
            ->route('plano.acessos', $plano)
            ->with('success', 'Politica de acesso do plano atualizada com sucesso.');
    }

    public function update(Request $request, Plano $plano): RedirectResponse
    {
        $data = $request->validate([
            'nome'            => 'required|string|max:80',
            'descricao'       => 'nullable|string',
            'valor_mensal'    => 'required|numeric|min:0',
            'valor_anual'     => 'required|numeric|min:0',
            'limite_veiculos' => 'nullable|integer|min:1',
            'site_vendas'     => 'boolean',
            'modulo_multas'   => 'boolean',
            'modulo_nfse'     => 'boolean',
            'multi_estacao'   => 'boolean',
            'api_integracao'  => 'boolean',
            'ativo'           => 'boolean',
            'destaque'        => 'boolean',
            'ordem'           => 'integer|min:0',
            'itens'           => 'nullable|array',
            'itens.*.id'        => 'nullable|integer',
            'itens.*.descricao' => 'required_with:itens|string|max:200',
            'itens.*.incluso'   => 'boolean',
        ]);

        $itens = $data['itens'] ?? [];
        unset($data['itens']);

        $this->normalizarBooleans($data, $request);

        $plano->update($data);
        $this->sincronizarItens($plano, $itens);

        return redirect()->route('plano.index')
            ->with('success', "Plano \"{$plano->nome}\" atualizado com sucesso.");
    }

    public function destroy(Plano $plano): RedirectResponse
    {
        if ($plano->assinaturas()->whereIn('status', ['ativo', 'trial'])->exists()) {
            return back()->with('error', 'Não é possível excluir um plano com assinaturas ativas.');
        }

        $plano->delete();

        return redirect()->route('plano.index')
            ->with('success', "Plano \"{$plano->nome}\" removido.");
    }

    private function sincronizarItens(Plano $plano, array $itens): void
    {
        $idsRecebidos = collect($itens)->pluck('id')->filter()->all();

        $plano->itens()->whereNotIn('id', $idsRecebidos)->delete();

        foreach ($itens as $index => $itemData) {
            $payload = [
                'plano_id'  => $plano->id,
                'descricao' => $itemData['descricao'],
                'incluso'   => (bool) ($itemData['incluso'] ?? true),
                'ordem'     => $index + 1,
            ];

            if (!empty($itemData['id'])) {
                ItemPlano::where('id', $itemData['id'])->where('plano_id', $plano->id)->update($payload);
            } else {
                ItemPlano::create($payload);
            }
        }
    }

    private function normalizarBooleans(array &$data, Request $request): void
    {
        foreach (['site_vendas', 'modulo_multas', 'modulo_nfse', 'multi_estacao', 'api_integracao', 'ativo', 'destaque'] as $campo) {
            $data[$campo] = $request->boolean($campo);
        }
    }
}
