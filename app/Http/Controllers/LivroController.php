<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\Livro\StoreLivroRequest;
use App\Http\Requests\Livro\UpdateLivroRequest;
use App\Models\Escola;
use App\Models\Livro;
use App\Services\BreadcrumbService;
use App\Services\LivroService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\Request;

class LivroController extends BaseController implements FiltroInterface, ResourceInterface
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    public function __construct(
        protected LivroService $livroService,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->titulo = 'Acervo da Biblioteca';
        $this->nomeFiltro = 'livro';

        $this->middleware('can:livro.index')->only('index');
        $this->middleware('can:livro.create')->only('create');
        $this->middleware('can:livro.store')->only('store');
        $this->middleware('can:livro.edit')->only('edit');
        $this->middleware('can:livro.update')->only('update');
        $this->middleware('can:livro.destroy')->only('destroy');
        $this->middleware('can:livro.filtro')->only('filtro');
        $this->middleware('can:livro.removerFiltro')->only('removerFiltro');
        $this->middleware('can:livro.setPerPage')->only('setPerPage');
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $livros = $this->livroService->getAllWithFiltersAndPagination(
            $itensFiltered, $perPage, ['escola'], 'titulo', 'asc'
        );

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Livros', 'Biblioteca');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];
        $nomeFiltro = $this->nomeFiltro;

        return view('livro.index', compact(
            'livros', 'breadcrumb', 'filters', 'itensFiltered',
            'additionalFilters', 'perPages', 'perPage', 'nomeFiltro'
        ))->with('titulo', $this->titulo);
    }

    public function create()
    {
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Livros', 'livro.index', 'Novo Livro', 'Biblioteca');

        return view('livro.add-edit', array_merge(compact('breadcrumb'), $this->opcoesFormulario()))
            ->with('titulo', $this->titulo);
    }

    public function store(StoreLivroRequest $request)
    {
        $this->livroService->create($request->validated());

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Livro cadastrado com sucesso.');
    }

    public function edit($id)
    {
        $livro = $this->livroService->getById($id, ['escola']);
        abort_if(! $livro, 404, 'Livro não encontrado.');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Livros', 'livro.index', "Editar: {$livro->titulo}", 'Biblioteca');

        return view('livro.add-edit', array_merge(compact('livro', 'breadcrumb'), $this->opcoesFormulario()))
            ->with('titulo', $this->titulo);
    }

    public function update(UpdateLivroRequest $request, $id)
    {
        $livro = $this->livroService->update($id, $request->validated());
        abort_if(! $livro, 403, 'Sem acesso para editar este livro.');

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Livro atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $success = $this->livroService->delete($id);
        abort_if(! $success, 403, 'Sem acesso para excluir este livro ou ele possui empréstimos.');

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
        session()->put("$this->nomeFiltro.urlPadrao", 'livro');
        session()->put("$this->nomeFiltro.rotaRedirect", 'livro.index');
    }

    public function obterFiltrosPrincipais()
    {
        return [
            ['name' => 'titulo', 'label' => 'Título', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'autor', 'label' => 'Autor', 'type' => 'text', 'typeWhere' => 'like'],
        ];
    }

    public function obterFiltrosSecundarios()
    {
        return [
            [
                'name' => 'escola_id', 'label' => 'Escola', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => $this->escolasOpcoesFiltro(),
            ],
            [
                'name' => 'categoria', 'label' => 'Categoria', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => Livro::CATEGORIAS,
            ],
            [
                'name' => 'ativo', 'label' => 'Status', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => ['S' => 'Ativo', 'N' => 'Inativo'],
            ],
        ];
    }

    private function opcoesFormulario(): array
    {
        return [
            'escolas' => $this->escolasOpcoes(),
            'categorias' => Livro::CATEGORIAS,
        ];
    }

    private function escolasOpcoes(): array
    {
        return Escola::query()->orderBy('nome')->pluck('nome', 'id')->all();
    }

    /**
     * Formato exigido pelo componente x-filter para selects:
     * lista de {value, label} (diferente do mapa id=>nome do x-form-select).
     */
    private function escolasOpcoesFiltro()
    {
        return Escola::query()->orderBy('nome')->get(['id', 'nome'])
            ->map(fn (Escola $escola) => ['value' => $escola->id, 'label' => $escola->nome])
            ->all();
    }
}
