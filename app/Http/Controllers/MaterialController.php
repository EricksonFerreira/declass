<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\Material\RegistrarMovimentacaoRequest;
use App\Http\Requests\Material\StoreMaterialRequest;
use App\Http\Requests\Material\UpdateMaterialRequest;
use App\Models\Escola;
use App\Models\Material;
use App\Models\MovimentacaoEstoque;
use App\Services\BreadcrumbService;
use App\Services\MaterialService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\Request;

class MaterialController extends BaseController implements FiltroInterface, ResourceInterface
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    public function __construct(
        protected MaterialService $materialService,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->titulo = 'Materiais e Estoque';
        $this->nomeFiltro = 'material';

        $this->middleware('can:material.index')->only(['index', 'movimentacoes']);
        $this->middleware('can:material.create')->only('create');
        $this->middleware('can:material.store')->only('store');
        $this->middleware('can:material.edit')->only('edit');
        $this->middleware('can:material.update')->only('update');
        $this->middleware('can:material.destroy')->only('destroy');
        $this->middleware('can:material.movimentar')->only('movimentar');
        $this->middleware('can:material.movimentacao.index')->only('movimentacao');
        $this->middleware('can:material.movimentacao.store')->only('registrarMovimentacao');
        $this->middleware('can:material.filtro')->only('filtro');
        $this->middleware('can:material.removerFiltro')->only('removerFiltro');
        $this->middleware('can:material.setPerPage')->only('setPerPage');
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 15);

        // Filtro virtual "baixo_estoque" nao e coluna — vira scope
        $baixoEstoque = collect($itensFiltered)->contains(
            fn ($f) => ($f['name'] ?? null) === 'baixo_estoque' && ! empty($f['valor'])
        );

        $materiais = $this->materialService->getAllWithFiltersAndPagination(
            $itensFiltered, $perPage, ['escola'], 'nome', 'asc',
            $baixoEstoque ? fn ($q) => $q->whereColumn('estoque_atual', '<=', 'estoque_minimo') : null
        );

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Materiais e Estoque', 'Estoque');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [10, 15, 25, 50, 100];
        $nomeFiltro = $this->nomeFiltro;

        return view('material.index', compact(
            'materiais', 'breadcrumb', 'filters', 'itensFiltered',
            'additionalFilters', 'perPages', 'perPage', 'nomeFiltro'
        ))->with('titulo', $this->titulo);
    }

    public function create()
    {
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Materiais', 'material.index', 'Novo Material', 'Estoque');

        return view('material.add-edit', array_merge(compact('breadcrumb'), $this->opcoesFormulario()))
            ->with('titulo', $this->titulo);
    }

    public function store(StoreMaterialRequest $request)
    {
        $data = $request->validated();
        $data['ativo'] = $data['ativo'] ?? 'S';
        $data['estoque_atual'] = $data['estoque_atual'] ?? 0;
        $data['estoque_minimo'] = $data['estoque_minimo'] ?? 0;

        $this->materialService->create($data);

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Material cadastrado com sucesso.');
    }

    public function edit($id)
    {
        $material = $this->materialService->getById($id, ['escola']);
        abort_if(! $material, 404, 'Material não encontrado.');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Materiais', 'material.index', "Editar: {$material->nome}", 'Estoque');

        return view('material.add-edit', array_merge(compact('material', 'breadcrumb'), $this->opcoesFormulario()))
            ->with('titulo', $this->titulo);
    }

    public function update(UpdateMaterialRequest $request, $id)
    {
        $data = $request->validated();
        $data['ativo'] = $data['ativo'] ?? 'S';

        $material = $this->materialService->update($id, $data);
        abort_if(! $material, 403, 'Sem acesso para editar este material.');

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Material atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $material = $this->materialService->getById($id);
        abort_if(! $material, 404, 'Material não encontrado.');

        if ($this->materialService->totalMovimentacoes($material->id) > 0) {
            return redirect()->route('material.index')
                ->with('error', 'Material com movimentações não pode ser excluído — inative-o.');
        }

        $success = $this->materialService->delete($id);
        abort_if(! $success, 403, 'Sem acesso para excluir este material.');

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    /** Historico de movimentacoes + formulario de nova entrada/saida/ajuste. */
    public function movimentacoes($id)
    {
        $material = $this->materialService->getById($id, ['escola']);
        abort_if(! $material, 404, 'Material não encontrado.');

        $movimentacoes = $this->materialService->movimentacoesDoMaterial($material->id, 25);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbShow(
            'Materiais', 'material.index', "Movimentações: {$material->nome}", 'Estoque'
        );

        return view('material.movimentacoes', [
            'titulo' => 'Movimentações — '.$material->nome,
            'material' => $material,
            'movimentacoes' => $movimentacoes,
            'breadcrumb' => $breadcrumb,
            'tipos' => MovimentacaoEstoque::TIPOS,
        ]);
    }

    /** Registra entrada/saida/ajuste e atualiza o saldo. */
    public function movimentar(RegistrarMovimentacaoRequest $request, $id)
    {
        $material = $this->materialService->getById($id);
        abort_if(! $material, 404, 'Material não encontrado.');

        $this->materialService->movimentar($material, $request->validated());

        return redirect()->route('material.movimentacoes', $material->id)
            ->with('success', 'Movimentação registrada com sucesso.');
    }

    /**
     * Tela central de movimentacao: escolhe o material, registra
     * entrada/saida/ajuste e mostra as movimentacoes recentes
     * de todo o estoque.
     */
    public function movimentacao()
    {
        $materiais = Material::query()
            ->where('ativo', 'S')
            ->with('escola:id,nome')
            ->orderBy('nome')
            ->get(['id', 'empresa_id', 'escola_id', 'nome', 'estoque_atual', 'estoque_minimo', 'unidade_medida', 'categoria', 'localizacao']);

        $movimentacoes = MovimentacaoEstoque::query()
            ->with(['material:id,nome', 'usuario:id,name'])
            ->orderBy('data', 'desc')
            ->orderBy('id', 'desc')
            ->limit(30)
            ->get();

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Movimentação de Materiais', 'Estoque');

        return view('material.movimentacao', [
            'titulo' => 'Movimentação de Materiais',
            'materiais' => $materiais,
            'movimentacoes' => $movimentacoes,
            'breadcrumb' => $breadcrumb,
            'tipos' => MovimentacaoEstoque::TIPOS,
        ]);
    }

    /** POST central: resolve o material e delega ao service. */
    public function registrarMovimentacao(Request $request)
    {
        $request->validate([
            'material_id' => ['required', 'integer'],
            'tipo' => ['required', 'string', 'in:'.implode(',', array_keys(MovimentacaoEstoque::TIPOS))],
            'quantidade' => ['required', 'numeric', 'min:0.01'],
            'motivo' => ['nullable', 'string', 'max:1000'],
            'data' => ['nullable', 'date'],
        ]);

        $material = $this->materialService->getById($request->integer('material_id'));
        abort_if(! $material, 404, 'Material não encontrado.');

        // ValidationException do service (ex.: estoque insuficiente)
        // ja redireciona de volta com os erros preenchidos.
        $this->materialService->movimentar($material, $request->only('tipo', 'quantidade', 'motivo', 'data'));

        return redirect()->route('material.movimentacao.index')
            ->with('success', 'Movimentação registrada com sucesso.');
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
        session()->put("$this->nomeFiltro.urlPadrao", 'material');
        session()->put("$this->nomeFiltro.rotaRedirect", 'material.index');
    }

    public function obterFiltrosPrincipais()
    {
        return [
            [
                'name' => 'nome', 'label' => 'Nome do material', 'type' => 'text',
                'typeWhere' => 'like',
            ],
        ];
    }

    public function obterFiltrosSecundarios()
    {
        return [
            [
                'name' => 'categoria', 'label' => 'Categoria', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => Material::CATEGORIAS,
            ],
            [
                'name' => 'escola_id', 'label' => 'Escola', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => Escola::query()->orderBy('nome')->get(['id', 'nome'])
                    ->map(fn (Escola $escola) => ['value' => $escola->id, 'label' => $escola->nome])
                    ->all(),
            ],
            [
                'name' => 'ativo', 'label' => 'Status', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => ['S' => 'Ativo', 'N' => 'Inativo'],
            ],
            [
                'name' => 'baixo_estoque', 'label' => 'Estoque', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => [['value' => '1', 'label' => 'Somente baixo estoque']],
            ],
        ];
    }

    private function opcoesFormulario(): array
    {
        return [
            'escolas' => ['' => 'Rede toda'] + Escola::query()->orderBy('nome')->pluck('nome', 'id')->all(),
            'categorias' => Material::CATEGORIAS,
            'unidades' => Material::UNIDADES,
        ];
    }
}
