<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\TipoValorCaracteristicaFrota\StoreTipoValorCaracteristicaFrotaRequest;
use App\Http\Requests\TipoValorCaracteristicaFrota\UpdateTipoValorCaracteristicaFrotaRequest;
use App\Services\BreadcrumbService;
use App\Services\TipoValorCaracteristicaFrotaService;
use App\Traits\AtualizaFiltrosTrait;
use Illuminate\Http\Request;
use App\Traits\FiltroTrait;

class TipoValorCaracteristicaFrotaController extends BaseController implements FiltroInterface, ResourceInterface
{
    use FiltroTrait;
    use AtualizaFiltrosTrait;
    protected $tipo_valor_caracteristica_frotaService;
    protected $breadcrumbService;

    public function __construct(TipoValorCaracteristicaFrotaService $tipo_valor_caracteristica_frotaService, BreadcrumbService $breadcrumbService)
    {
        $this->titulo = "Tipo Valor Caracteristica Frotas";
        $this->nomeFiltro = 'tipo_valor_caracteristica_frota';
        $this->tipo_valor_caracteristica_frotaService = $tipo_valor_caracteristica_frotaService;
        $this->breadcrumbService = $breadcrumbService;
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $tipo_valor_caracteristica_frotas = $this->tipo_valor_caracteristica_frotaService->getAllWithFiltersAndPagination($itensFiltered, $perPage);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('TipoValorCaracteristicaFrotas');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];

        return view('tipo_valor_caracteristica_frota.index', compact(
            'tipo_valor_caracteristica_frotas',
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
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('TipoValorCaracteristicaFrotas','tipo_valor_caracteristica_frota.index','Criar');

        [$metodo,$rota] = $this->getFormMethodAndRoute();
        return view('tipo_valor_caracteristica_frota.add-edit', compact('breadcrumb','status','metodo','rota'))->with('titulo', $this->titulo);
    }

    public function store(StoreTipoValorCaracteristicaFrotaRequest $request)
    {
        $this->tipo_valor_caracteristica_frotaService->create($request->all());

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function edit($id)
    {
        $tipo_valor_caracteristica_frota = $this->tipo_valor_caracteristica_frotaService->getById($id);
        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo'
        ];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('TipoValorCaracteristicaFrotas','tipo_valor_caracteristica_frota.index',"Editar: $tipo_valor_caracteristica_frota->nome");
        [$metodo,$rota] = $this->getFormMethodAndRoute($tipo_valor_caracteristica_frota);

        return view('tipo_valor_caracteristica_frota.add-edit', compact('tipo_valor_caracteristica_frota','breadcrumb','status','metodo','rota'))->with('titulo', $this->titulo);
    }

    public function update(UpdateTipoValorCaracteristicaFrotaRequest $request, $id)
    {
        $success = $this->tipo_valor_caracteristica_frotaService->update($id,$request->all());
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function destroy($id)
    {
        $success = $this->tipo_valor_caracteristica_frotaService->delete($id);
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
        session()->put("$this->nomeFiltro.urlPadrao", 'tipo_valor_caracteristica_frota');
        session()->put("$this->nomeFiltro.rotaRedirect", 'tipo_valor_caracteristica_frota.index');
    }

    public function obterFiltrosPrincipais()
    {
        return [
            ['name' => 'nome', 'label' => 'Nome', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'ativo', 'label' => 'Status', 'type' => 'select', 'typeWhere' => 'where', 'options' => [
                ['value' => '', 'label' => 'Selecione o status', 'selected' => false],
                ['value' => 'S', 'label' => 'Ativo', 'selected' => false],
                ['value' => 'N', 'label' => 'Inativo', 'selected' => false]
            ]]
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
     * @param  TipoValorCaracteristicaFrota|null  $tipo_valor_caracteristica_frota
     * @return array
     */
    private function getFormMethodAndRoute($tipo_valor_caracteristica_frota = null)
    {
        if ($tipo_valor_caracteristica_frota) {
            return [
                'PUT',
                route('tipo_valor_caracteristica_frota.update', $tipo_valor_caracteristica_frota->id),
            ];
        }

        return [
            'POST',
            route('tipo_valor_caracteristica_frota.store'),
        ];
    }
}
