<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\TipoChecklist\StoreTipoChecklistRequest;
use App\Http\Requests\TipoChecklist\UpdateTipoChecklistRequest;
use App\Services\BreadcrumbService;
use App\Services\ItemChecklistService;
use App\Services\TipoChecklistService;
use App\Traits\AtualizaFiltrosTrait;
use Illuminate\Http\Request;
use App\Traits\FiltroTrait;

class TipoChecklistController extends BaseController implements FiltroInterface, ResourceInterface
{
    use FiltroTrait;
    use AtualizaFiltrosTrait;
    protected $tipo_checklistService;
    protected $breadcrumbService;
    protected $itemChecklistService;

    public function __construct(
        TipoChecklistService $tipo_checklistService,
        BreadcrumbService $breadcrumbService,
        ItemChecklistService $itemChecklistService
    )
    {
        $this->titulo = "Tipos de Checklist";
        $this->nomeFiltro = 'tipo_checklist';
        $this->tipo_checklistService = $tipo_checklistService;
        $this->breadcrumbService = $breadcrumbService;
        $this->itemChecklistService = $itemChecklistService;
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $tipo_checklists = $this->tipo_checklistService->getAllWithFiltersAndPagination(
            $itensFiltered,
            $perPage,
            ['configuracaoChecklist.itemChecklist']
        );

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Tipos de Checklist');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];

        return view('tipo_checklist.index', compact(
            'tipo_checklists',
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
        $itemChecklist = $this->itemChecklistService->getAllForSelect();
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Tipos de Checklist','tipo_checklist.index','Criar');

        [$metodo,$rota] = $this->getFormMethodAndRoute();
        return view('tipo_checklist.add-edit', compact('breadcrumb','status','metodo','rota','itemChecklist'))->with('titulo', $this->titulo);
    }

    public function store(StoreTipoChecklistRequest $request)
    {
        $this->tipo_checklistService->create($request->all());

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function edit($id)
    {
        $tipo_checklist = $this->tipo_checklistService->getById($id, ['configuracaoChecklist.itemChecklist']);
        if (!$tipo_checklist) {
            return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Tipo de checklist não encontrado.', 'error');
        }
        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo'
        ];
        $itemChecklist = $this->itemChecklistService->getAllForSelect();
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Tipos de Checklist','tipo_checklist.index',"Editar: $tipo_checklist->nome");
        [$metodo,$rota] = $this->getFormMethodAndRoute($tipo_checklist);

        return view('tipo_checklist.add-edit', compact('tipo_checklist','breadcrumb','status','metodo','rota','itemChecklist'))->with('titulo', $this->titulo);
    }

    public function update(UpdateTipoChecklistRequest $request, $id)
    {
        $success = $this->tipo_checklistService->update($id,$request->all());
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function destroy($id)
    {
        $success = $this->tipo_checklistService->delete($id);
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
        session()->put("$this->nomeFiltro.urlPadrao", 'tipo_checklist');
        session()->put("$this->nomeFiltro.rotaRedirect", 'tipo_checklist.index');
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
     * @param  TipoChecklist|null  $tipo_checklist
     * @return array
     */
    private function getFormMethodAndRoute($tipo_checklist = null)
    {
        if ($tipo_checklist) {
            return [
                'PUT',
                route('tipo_checklist.update', $tipo_checklist->id),
            ];
        }

        return [
            'POST',
            route('tipo_checklist.store'),
        ];
    }
}
