<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\Opcional\StoreOpcionalRequest;
use App\Http\Requests\Opcional\UpdateOpcionalRequest;
use App\Services\BreadcrumbService;
use App\Services\OpcionalService;
use App\Services\TipoOpcionalService;
use App\Traits\AtualizaFiltrosTrait;
use Illuminate\Http\Request;
use App\Traits\FiltroTrait;

class OpcionalController extends BaseController implements FiltroInterface, ResourceInterface
{
    use FiltroTrait;
    use AtualizaFiltrosTrait;
    protected $opcionalService;
    protected $breadcrumbService;
    protected $tipoOpcionalService;

    public function __construct(
        OpcionalService $opcionalService,
        BreadcrumbService $breadcrumbService,
        TipoOpcionalService $tipoOpcionalService
    ) {
        $this->titulo = "Opcionais";
        $this->nomeFiltro = 'opcional';
        $this->opcionalService = $opcionalService;
        $this->breadcrumbService = $breadcrumbService;
        $this->tipoOpcionalService = $tipoOpcionalService;
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $opcionals = $this->opcionalService->getAllWithFiltersAndPagination($itensFiltered, $perPage);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Opcionais');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];

        return view('opcional.index', compact(
            'opcionals',
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
        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo'
        ];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Opcionais', 'opcional.index', 'Criar');
        $tipoOpcional = $this->tipoOpcionalService->getAllForSelect();

        [$metodo, $rota] = $this->getFormMethodAndRoute();
        return view('opcional.add-edit', compact('tipoOpcional','breadcrumb', 'status', 'metodo', 'rota'))->with('titulo', $this->titulo);
    }

    public function store(StoreOpcionalRequest $request)
    {
        $this->adjustPrices($request);

        $this->opcionalService->create($request->all());

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function edit($id)
    {
        $opcional = $this->opcionalService->getById($id);
        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo'
        ];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Opcionais', 'opcional.index', "Editar: $opcional->nome");
        [$metodo, $rota] = $this->getFormMethodAndRoute($opcional);
        $tipoOpcional = $this->tipoOpcionalService->getAllForSelect();

        return view('opcional.add-edit', compact('tipoOpcional','opcional', 'breadcrumb', 'status', 'metodo', 'rota'))->with('titulo', $this->titulo);
    }

    public function update(UpdateOpcionalRequest $request, $id)
    {
        $this->adjustPrices($request);

        $this->opcionalService->update($id, $request->all());
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function destroy($id)
    {
        $success = $this->opcionalService->delete($id);
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
        session()->put("$this->nomeFiltro.urlPadrao", 'opcional');
        session()->put("$this->nomeFiltro.rotaRedirect", 'opcional.index');
    }

    public function obterFiltrosPrincipais()
    {
        $tipoOptional = $this->tipoOpcionalService->getAllForSelect();
        $data = FiltroTrait::formatOptions($tipoOptional, 'Selecione o tipo');

        return [
            ['name' => 'nome', 'label' => 'Nome', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'tipo_opcional_id', 'label' => 'Tipo de Opcional', 'type' => 'select', 'typeWhere' => 'where', 'options' => $data ],
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
     * @param  Opcional|null  $opcional
     * @return array
     */
    private function getFormMethodAndRoute($opcional = null)
    {
        if ($opcional) {
            return [
                'PUT',
                route('opcional.update', $opcional->id),
            ];
        }

        return [
            'POST',
            route('opcional.store'),
        ];
    }
    private function adjustPrices(Request $request)
    {
        if ($request->tipo_cobranca == 'por_dia') {
            $request->merge(['preco_integral' => null]);
        }
        if ($request->tipo_cobranca == 'valor_integral') {
            $request->merge(['preco_por_dia' => null]);
        }
    }
}
