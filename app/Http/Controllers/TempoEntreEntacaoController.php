<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\TempoEntreEntacao\StoreTempoEntreEntacaoRequest;
use App\Http\Requests\TempoEntreEntacao\UpdateTempoEntreEntacaoRequest;
use App\Services\BreadcrumbService;
use App\Services\TempoEntreEntacaoService;
use App\Traits\AtualizaFiltrosTrait;
use Illuminate\Http\Request;
use App\Traits\FiltroTrait;

class TempoEntreEntacaoController extends BaseController implements FiltroInterface, ResourceInterface
{
    use FiltroTrait;
    use AtualizaFiltrosTrait;
    protected $tempo_entre_entacaoService;
    protected $breadcrumbService;

    public function __construct(TempoEntreEntacaoService $tempo_entre_entacaoService, BreadcrumbService $breadcrumbService)
    {
        $this->titulo = "TempoEntreEntacaos";
        $this->nomeFiltro = 'tempo_entre_entacao';
        $this->tempo_entre_entacaoService = $tempo_entre_entacaoService;
        $this->breadcrumbService = $breadcrumbService;
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $tempo_entre_entacaos = $this->tempo_entre_entacaoService->getAllWithFiltersAndPagination($itensFiltered, $perPage);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('TempoEntreEntacaos');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];

        return view('tempo_entre_entacao.index', compact(
            'tempo_entre_entacaos',
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
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('TempoEntreEntacaos','tempo_entre_entacao.index','Criar');

        [$metodo,$rota] = $this->getFormMethodAndRoute();
        return view('tempo_entre_entacao.add-edit', compact('breadcrumb','status','metodo','rota'))->with('titulo', $this->titulo);
    }

    public function store(StoreTempoEntreEntacaoRequest $request)
    {
        $this->tempo_entre_entacaoService->create($request->all());

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function edit($id)
    {
        $tempo_entre_entacao = $this->tempo_entre_entacaoService->getById($id);
        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo'
        ];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('TempoEntreEntacaos','tempo_entre_entacao.index',"Editar: $tempo_entre_entacao->nome");
        [$metodo,$rota] = $this->getFormMethodAndRoute($tempo_entre_entacao);

        return view('tempo_entre_entacao.add-edit', compact('tempo_entre_entacao','breadcrumb','status','metodo','rota'))->with('titulo', $this->titulo);
    }

    public function update(UpdateTempoEntreEntacaoRequest $request, $id)
    {
        $success = $this->tempo_entre_entacaoService->update($id,$request->all());
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function destroy($id)
    {
        $success = $this->tempo_entre_entacaoService->delete($id);
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
        session()->put("$this->nomeFiltro.urlPadrao", 'tempo_entre_entacao');
        session()->put("$this->nomeFiltro.rotaRedirect", 'tempo_entre_entacao.index');
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
     * @param  TempoEntreEntacao|null  $tempo_entre_entacao
     * @return array
     */
    private function getFormMethodAndRoute($tempo_entre_entacao = null)
    {
        if ($tempo_entre_entacao) {
            return [
                'PUT',
                route('tempo_entre_entacao.update', $tempo_entre_entacao->id),
            ];
        }

        return [
            'POST',
            route('tempo_entre_entacao.store'),
        ];
    }
}