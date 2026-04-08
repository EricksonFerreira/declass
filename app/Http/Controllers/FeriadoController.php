<?php

namespace App\Http\Controllers;

use App\Http\Requests\Feriado\StoreFeriadoRequest;
use App\Http\Requests\Feriado\UpdateFeriadoRequest;
use App\Services\BreadcrumbService;
use App\Services\FeriadoService;
use App\Services\EstacaoService;
use App\Models\Feriado;
use Illuminate\Http\Request;
use App\Traits\FiltroTrait;
use App\Traits\AtualizaFiltrosTrait;

class FeriadoController extends BaseController
{
    use FiltroTrait;
    use AtualizaFiltrosTrait;

    protected FeriadoService $feriadoService;
    protected BreadcrumbService $breadcrumbService;
    protected EstacaoService $estacaoService;

    protected $titulo;
    protected $nomeFiltro = 'feriado';

    public function __construct(FeriadoService $feriadoService, BreadcrumbService $breadcrumbService, EstacaoService $estacaoService)
    {
        $this->middleware('can:gerenciar-empresa');
        $this->feriadoService = $feriadoService;
        $this->breadcrumbService = $breadcrumbService;
        $this->estacaoService = $estacaoService;
        $this->titulo = 'Feriados';
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 15);

        $feriados = $this->feriadoService->getAllWithFiltersAndPagination($itensFiltered, $perPage, ['estacao'], 'data', 'desc');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Feriados');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];

        return view('feriados.index', compact(
            'feriados',
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
        $status = ['S' => 'Ativo', 'N' => 'Inativo'];
        $estacoes = $this->estacaoService->getAllFormatSelect('id','nome');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Feriados','feriado.index','Criar');
        [$metodo,$rota] = $this->getFormMethodAndRoute();

        return view('feriados.add-edit', compact('breadcrumb','status','estacoes','metodo','rota'))->with('titulo', $this->titulo);
    }

    public function store(StoreFeriadoRequest $request)
    {
        $this->feriadoService->create($request->all());
        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Feriado criado com sucesso.');
    }

    public function edit($id)
    {
        $feriado = $this->feriadoService->getById($id);
        $status = ['S' => 'Ativo', 'N' => 'Inativo'];
        $estacoes = $this->estacaoService->getAllFormatSelect('id','nome');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Feriados','feriado.index',"Editar: $feriado->nome");
        [$metodo,$rota] = $this->getFormMethodAndRoute($feriado);

        return view('feriados.add-edit', compact('feriado','breadcrumb','status','estacoes','metodo','rota'))->with('titulo', $this->titulo);
    }

    public function update(UpdateFeriadoRequest $request, $id)
    {
        $this->feriadoService->update($id, $request->all());
        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Feriado atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $this->feriadoService->delete($id);
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

    private function getFormMethodAndRoute($feriado = null)
    {
        if ($feriado) {
            return ['PUT', route('feriado.update', $feriado->id)];
        }
        return ['POST', route('feriado.store')];
    }

    public function configurarSessao()
    {
        session()->put("$this->nomeFiltro.urlPadrao", 'feriado');
        session()->put("$this->nomeFiltro.rotaRedirect", 'feriado.index');
    }

    public function obterFiltrosPrincipais()
    {
        return [
            ['name' => 'data', 'label' => 'Data', 'type' => 'text', 'typeWhere' => 'where'],
            ['name' => 'nome', 'label' => 'Nome', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'estacao_id', 'label' => 'Estação', 'type' => 'select', 'typeWhere' => 'where', 'options' => []],
            ['name' => 'ativo', 'label' => 'Status', 'type' => 'select', 'typeWhere' => 'where', 'options' => [
                ['value' => '', 'label' => 'Selecione o status', 'selected' => false],
                ['value' => 'S', 'label' => 'Ativo', 'selected' => false],
                ['value' => 'N', 'label' => 'Inativo', 'selected' => false]
            ]]
        ];
    }

    public function obterFiltrosSecundarios()
    {
        return [];
    }
}
