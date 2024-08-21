<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\HorarioDiario\StoreHorarioDiarioRequest;
use App\Http\Requests\HorarioDiario\UpdateHorarioDiarioRequest;
use App\Services\BreadcrumbService;
use App\Services\HorarioDiarioService;
use App\Traits\AtualizaFiltrosTrait;
use Illuminate\Http\Request;
use App\Traits\FiltroTrait;

class HorarioDiarioController extends BaseController implements FiltroInterface, ResourceInterface
{
    use FiltroTrait;
    use AtualizaFiltrosTrait;
    protected $horarioDiarioService;
    protected $breadcrumbService;

    public function __construct(HorarioDiarioService $horarioDiarioService, BreadcrumbService $breadcrumbService)
    {
        $this->titulo = "Horários Diários";
        $this->nomeFiltro = 'horario_diario';
        $this->horarioDiarioService = $horarioDiarioService;
        $this->breadcrumbService = $breadcrumbService;
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $horarios_diarios = $this->horarioDiarioService->getAllWithFiltersAndPagination($itensFiltered, $perPage,[],'nome','asc');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('HorarioDiarios');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];

        return view('horario_diario.index', compact(
            'horarios_diarios',
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
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('HorarioDiarios','horario_diario.index','Criar');

        [$metodo,$rota] = $this->getFormMethodAndRoute();
        return view('horario_diario.add-edit', compact('breadcrumb','status','metodo','rota'))->with('titulo', $this->titulo);
    }

    public function store(StoreHorarioDiarioRequest $request)
    {
        $this->horarioDiarioService->create($request->all());

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function edit($id)
    {
        $horario_diario = $this->horarioDiarioService->getById($id);
        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo'
        ];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('HorarioDiarios','horario_diario.index',"Editar: $horario_diario->nome");
        [$metodo,$rota] = $this->getFormMethodAndRoute($horario_diario);

        return view('horario_diario.add-edit', compact('horario_diario','breadcrumb','status','metodo','rota'))->with('titulo', $this->titulo);
    }

    public function update(UpdateHorarioDiarioRequest $request, $id)
    {
        $success = $this->horarioDiarioService->update($id,$request->all());
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function destroy($id)
    {
        $success = $this->horarioDiarioService->delete($id);
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
        session()->put("$this->nomeFiltro.urlPadrao", 'horario_diario');
        session()->put("$this->nomeFiltro.rotaRedirect", 'horario_diario.index');
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
     * @param  HorarioDiario|null  $horario_diario
     * @return array
     */
    private function getFormMethodAndRoute($horario_diario = null)
    {
        if ($horario_diario) {
            return [
                'PUT',
                route('horario_diario.update', $horario_diario->id),
            ];
        }

        return [
            'POST',
            route('horario_diario.store'),
        ];
    }
}
