<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\StatusItemChecklist\StoreStatusItemChecklistRequest;
use App\Http\Requests\StatusItemChecklist\UpdateStatusItemChecklistRequest;
use App\Services\BreadcrumbService;
use App\Services\StatusItemChecklistService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\Request;

class StatusItemChecklistController extends BaseController implements FiltroInterface, ResourceInterface
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    protected $status_item_checklistService;

    protected $breadcrumbService;

    public function __construct(StatusItemChecklistService $status_item_checklistService, BreadcrumbService $breadcrumbService)
    {
        $this->titulo = 'Status Item Checklists';
        $this->nomeFiltro = 'status_item_checklist';
        $this->status_item_checklistService = $status_item_checklistService;
        $this->breadcrumbService = $breadcrumbService;
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $status_item_checklists = $this->status_item_checklistService->getAllWithFiltersAndPagination($itensFiltered, $perPage);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Status Item Checklists');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];

        return view('status_item_checklist.index', compact(
            'status_item_checklists',
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
            'N' => 'Inativo',
        ];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('StatusItemChecklists', 'status_item_checklist.index', 'Criar');

        [$metodo,$rota] = $this->getFormMethodAndRoute();

        return view('status_item_checklist.add-edit', compact('breadcrumb', 'status', 'metodo', 'rota'))->with('titulo', $this->titulo);
    }

    public function store(StoreStatusItemChecklistRequest $request)
    {
        $this->status_item_checklistService->create($request->all());

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function edit($id)
    {
        $status_item_checklist = $this->status_item_checklistService->getById($id);
        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo',
        ];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('StatusItemChecklists', 'status_item_checklist.index', "Editar: $status_item_checklist->nome");
        [$metodo,$rota] = $this->getFormMethodAndRoute($status_item_checklist);

        return view('status_item_checklist.add-edit', compact('status_item_checklist', 'breadcrumb', 'status', 'metodo', 'rota'))->with('titulo', $this->titulo);
    }

    public function update(UpdateStatusItemChecklistRequest $request, $id)
    {
        $success = $this->status_item_checklistService->update($id, $request->all());

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function destroy($id)
    {
        $success = $this->status_item_checklistService->delete($id);

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
        session()->put("$this->nomeFiltro.urlPadrao", 'status_item_checklist');
        session()->put("$this->nomeFiltro.rotaRedirect", 'status_item_checklist.index');
    }

    public function obterFiltrosPrincipais()
    {
        return [
            ['name' => 'nome', 'label' => 'Nome', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'ativo', 'label' => 'Status', 'type' => 'select', 'typeWhere' => 'where', 'options' => [
                ['value' => '', 'label' => 'Selecione o status', 'selected' => false],
                ['value' => 'S', 'label' => 'Ativo', 'selected' => false],
                ['value' => 'N', 'label' => 'Inativo', 'selected' => false],
            ]],
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
     * @param  StatusItemChecklist|null  $status_item_checklist
     * @return array
     */
    private function getFormMethodAndRoute($status_item_checklist = null)
    {
        if ($status_item_checklist) {
            return [
                'PUT',
                route('status_item_checklist.update', $status_item_checklist->id),
            ];
        }

        return [
            'POST',
            route('status_item_checklist.store'),
        ];
    }
}
