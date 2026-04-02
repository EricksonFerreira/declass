<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\TermoConsentimento\StoreTermoConsentimentoRequest;
use App\Http\Requests\TermoConsentimento\UpdateTermoConsentimentoRequest;
use App\Services\BreadcrumbService;
use App\Services\TermoConsentimentoService;
use App\Traits\AtualizaFiltrosTrait;
use Illuminate\Http\Request;
use App\Traits\FiltroTrait;

class TermoConsentimentoController extends BaseController implements FiltroInterface, ResourceInterface
{
    use FiltroTrait;
    use AtualizaFiltrosTrait;
    protected $termo_consentimentoService;
    protected $breadcrumbService;

    public function __construct(TermoConsentimentoService $termo_consentimentoService, BreadcrumbService $breadcrumbService)
    {
        $this->titulo = "Termos de Consentimentos";
        $this->nomeFiltro = 'termo_consentimento';
        $this->termo_consentimentoService = $termo_consentimentoService;
        $this->breadcrumbService = $breadcrumbService;
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $termo_consentimentos = $this->termo_consentimentoService->getAllWithFiltersAndPagination($itensFiltered, $perPage);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Termos de Consentimento');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];

        return view('termo_consentimento.index', compact(
            'termo_consentimentos',
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
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Termos de Consentimento','termo_consentimento.index','Criar');

        [$metodo,$rota] = $this->getFormMethodAndRoute();
        return view('termo_consentimento.add-edit', compact('breadcrumb','status','metodo','rota'))->with('titulo', $this->titulo);
    }

    public function store(StoreTermoConsentimentoRequest $request)
    {
        $this->termo_consentimentoService->create($request->all());

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function edit($id)
    {
        $termo_consentimento = $this->termo_consentimentoService->getById($id);
        if (!$termo_consentimento) {
            abort(403, 'Sem acesso a este termo de consentimento.');
        }
        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo'
        ];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Termos de Consentimento','termo_consentimento.index',"Editar: $termo_consentimento->nome");
        [$metodo,$rota] = $this->getFormMethodAndRoute($termo_consentimento);

        return view('termo_consentimento.add-edit', compact('termo_consentimento','breadcrumb','status','metodo','rota'))->with('titulo', $this->titulo);
    }

    public function update(UpdateTermoConsentimentoRequest $request, $id)
    {
        $success = $this->termo_consentimentoService->update($id,$request->all());
        if (!$success) {
            abort(403, 'Sem acesso para editar este termo de consentimento.');
        }
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function destroy($id)
    {
        $success = $this->termo_consentimentoService->delete($id);
        if (!$success) {
            abort(403, 'Sem acesso para excluir este termo de consentimento.');
        }
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
        session()->put("$this->nomeFiltro.urlPadrao", 'termo_consentimento');
        session()->put("$this->nomeFiltro.rotaRedirect", 'termo_consentimento.index');
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
     * @param  TermoConsentimento|null  $termo_consentimento
     * @return array
     */
    private function getFormMethodAndRoute($termo_consentimento = null)
    {
        if ($termo_consentimento) {
            return [
                'PUT',
                route('termo_consentimento.update', $termo_consentimento->id),
            ];
        }

        return [
            'POST',
            route('termo_consentimento.store'),
        ];
    }
}
