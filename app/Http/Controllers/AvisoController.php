<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\Aviso\StoreAvisoRequest;
use App\Http\Requests\Aviso\UpdateAvisoRequest;
use App\Models\Aviso;
use App\Models\Escola;
use App\Models\Turma;
use App\Services\AvisoService;
use App\Services\BreadcrumbService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\Request;

class AvisoController extends BaseController implements FiltroInterface, ResourceInterface
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    public function __construct(
        protected AvisoService $avisoService,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->titulo = 'Avisos';
        $this->nomeFiltro = 'aviso';

        $this->middleware('can:aviso.index')->only('index');
        $this->middleware('can:aviso.create')->only('create');
        $this->middleware('can:aviso.store')->only('store');
        $this->middleware('can:aviso.edit')->only('edit');
        $this->middleware('can:aviso.update')->only('update');
        $this->middleware('can:aviso.destroy')->only('destroy');
        $this->middleware('can:aviso.filtro')->only('filtro');
        $this->middleware('can:aviso.removerFiltro')->only('removerFiltro');
        $this->middleware('can:aviso.setPerPage')->only('setPerPage');
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $busca = $itensFiltered['busca']['valor'] ?? null;
        $avisos = $this->avisoService->getAllWithFiltersAndPagination(
            $itensFiltered, $perPage, ['escola', 'turma', 'turmas'], 'data_publicacao', 'desc',
            $busca ? fn ($q) => $q->where(fn ($s) => $s->where('titulo', 'like', "%{$busca}%")->orWhere('mensagem', 'like', "%{$busca}%")) : null
        );

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Avisos', 'Comunicação');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];
        $nomeFiltro = $this->nomeFiltro;

        return view('aviso.index', compact(
            'avisos', 'breadcrumb', 'filters', 'itensFiltered',
            'additionalFilters', 'perPages', 'perPage', 'nomeFiltro'
        ))->with('titulo', $this->titulo);
    }

    public function create()
    {
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Avisos', 'aviso.index', 'Novo Aviso', 'Comunicação');

        return view('aviso.add-edit', array_merge(compact('breadcrumb'), $this->opcoesFormulario()))
            ->with('titulo', $this->titulo);
    }

    public function store(StoreAvisoRequest $request)
    {
        $aviso = $this->avisoService->create($request->validated());
        $aviso->turmas()->sync($request->input('turma_ids', []));

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Aviso criado com sucesso.');
    }

    public function edit($id)
    {
        $aviso = $this->avisoService->getById($id, ['escola', 'turma']);
        abort_if(! $aviso, 404, 'Aviso não encontrado.');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Avisos', 'aviso.index', "Editar: {$aviso->titulo}", 'Comunicação');

        return view('aviso.add-edit', array_merge(compact('aviso', 'breadcrumb'), $this->opcoesFormulario()))
            ->with('titulo', $this->titulo);
    }

    public function update(UpdateAvisoRequest $request, $id)
    {
        $aviso = $this->avisoService->update($id, $request->validated());
        $aviso->turmas()->sync($request->input('turma_ids', []));
        abort_if(! $aviso, 403, 'Sem acesso para editar este aviso.');

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Aviso atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $success = $this->avisoService->delete($id);
        abort_if(! $success, 403, 'Sem acesso para excluir este aviso.');

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
        session()->put("$this->nomeFiltro.urlPadrao", 'aviso');
        session()->put("$this->nomeFiltro.rotaRedirect", 'aviso.index');
    }

    public function obterFiltrosPrincipais()
    {
        return [
            ['name' => 'busca', 'label' => 'Buscar (título ou mensagem)', 'type' => 'text', 'typeWhere' => 'like'],
            [
                'name' => 'tipo', 'label' => 'Tipo', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => Aviso::TIPOS,
            ],
            [
                'name' => 'ativo', 'label' => 'Status', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => ['S' => 'Ativo', 'N' => 'Inativo'],
            ],
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
                'name' => 'turma_id', 'label' => 'Turma', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => $this->turmasOpcoesFiltro(),
            ],
            [
                'name' => 'destino', 'label' => 'Público', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => Aviso::DESTINOS,
            ],
            [
                'name' => 'data_publicacao', 'label' => 'Publicação', 'type' => 'date', 'typeWhere' => 'between',
            ],
        ];
    }

    private function opcoesFormulario(): array
    {
        return [
            'escolas' => $this->escolasOpcoes(),
            'turmas' => $this->turmasOpcoes(),
            'tipos' => Aviso::TIPOS,
            'destinos' => Aviso::DESTINOS,
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

    private function turmasOpcoesFiltro()
    {
        return Turma::query()->with('escola')->orderBy('nome')->get(['id', 'nome', 'escola_id'])
            ->map(fn (Turma $t) => [
                'value' => $t->id,
                'label' => $t->nome.' — '.($t->escola->nome ?? ''),
            ])->all();
    }

    private function turmasOpcoes(): array
    {
        return Turma::query()->with('escola')->orderBy('nome')->get()
            ->mapWithKeys(fn (Turma $t) => [
                $t->id => $t->nome.' — '.($t->escola->nome ?? ''),
            ])->all();
    }
}
