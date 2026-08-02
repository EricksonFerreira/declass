<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\ConfiguracaoChecklist\StoreConfiguracaoChecklistRequest;
use App\Http\Requests\ConfiguracaoChecklist\UpdateConfiguracaoChecklistRequest;
use App\Services\BreadcrumbService;
use App\Services\ConfiguracaoChecklistService;
use App\Traits\AtualizaFiltrosTrait;
use Illuminate\Http\Request;
use App\Traits\FiltroTrait;
use App\Services\TipoChecklistService;
use App\Services\ItemChecklistService;
class ConfiguracaoChecklistController extends BaseController implements FiltroInterface, ResourceInterface
{
    use FiltroTrait;
    use AtualizaFiltrosTrait;
    protected $configuracaochecklistService;
    protected $breadcrumbService;
    protected $tipoChecklistService;
    protected $itemChecklistService;

    public function __construct(
        ConfiguracaoChecklistService $configuracaochecklistService,
        BreadcrumbService $breadcrumbService,
        TipoChecklistService $tipoChecklistService,
        ItemChecklistService $itemChecklistService
    ) {
        $this->titulo = "Configuração do Checklist";
        $this->nomeFiltro = 'configuracao-checklist';
        $this->configuracaochecklistService = $configuracaochecklistService;
        $this->breadcrumbService = $breadcrumbService;
        $this->tipoChecklistService = $tipoChecklistService;
        $this->itemChecklistService = $itemChecklistService;
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $configuracaochecklists = $this->configuracaochecklistService->getAllWithFiltersAndPagination(
            $itensFiltered,
            $perPage,
            ['tipoChecklist', 'itemChecklist']
        );

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Configuração do Checklist');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];

        return view('configuracao_checklist.index', compact(
            'configuracaochecklists',
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

        $tipoChecklist = $this->tipoChecklistService->getAllForSelect();
        $itemChecklist = $this->itemChecklistService->getAllForSelect();
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Configuração do Checklist','configuracao-checklist.index','Criar');

        [$metodo,$rota] = $this->getFormMethodAndRoute();
        return view('configuracao_checklist.add-edit',
            compact('breadcrumb', 'status', 'metodo', 'rota', 'tipoChecklist', 'itemChecklist')
        )->with('titulo', $this->titulo);
    }

    public function store(StoreConfiguracaoChecklistRequest $request)
    {
        $this->configuracaochecklistService->create($request->all());

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Configuração do Checklist criado com sucesso');
    }

    public function edit($id)
    {
        $configuracaochecklist = $this->configuracaochecklistService->getById($id);
        if (!$configuracaochecklist) {
            return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Configuração de checklist não encontrada.', 'error');
        }
        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo'
        ];
        $tipoChecklist = $this->tipoChecklistService->getAllForSelect();
        $itemChecklist = $this->itemChecklistService->getAllForSelect();
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('ConfiguracaoChecklists','configuracao-checklist.index',"Editar: $configuracaochecklist->nome");
        [$metodo,$rota] = $this->getFormMethodAndRoute($configuracaochecklist);

        return view('configuracao_checklist.add-edit',
            compact('configuracaochecklist','breadcrumb','status','metodo','rota','tipoChecklist','itemChecklist')
        )->with('titulo', $this->titulo);
    }

    public function update(UpdateConfiguracaoChecklistRequest $request, $id)
    {
        $success = $this->configuracaochecklistService->update($id,$request->all());
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function destroy($id)
    {
        $success = $this->configuracaochecklistService->delete($id);
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
        session()->put("$this->nomeFiltro.urlPadrao", 'configuracao-checklist');
        session()->put("$this->nomeFiltro.rotaRedirect", 'configuracao-checklist.index');
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
     * @param  ConfiguracaoChecklist|null  $configuracaochecklist
     * @return array
     */
    private function getFormMethodAndRoute($configuracaochecklist = null)
    {
        if ($configuracaochecklist) {
            return [
                'PUT',
                route('configuracao-checklist.update', $configuracaochecklist->id),
            ];
        }

        return [
            'POST',
            route('configuracao-checklist.store'),
        ];
    }
}
