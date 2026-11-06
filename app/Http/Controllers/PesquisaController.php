<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\Pesquisa\StorePesquisaRequest;
use App\Http\Requests\Pesquisa\UpdatePesquisaRequest;
use App\Models\Escola;
use App\Models\Pesquisa;
use App\Models\PesquisaPergunta;
use App\Services\BreadcrumbService;
use App\Services\PesquisaService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\Request;

class PesquisaController extends BaseController implements FiltroInterface, ResourceInterface
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    public function __construct(
        protected PesquisaService $pesquisaService,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->titulo = 'Pesquisas';
        $this->nomeFiltro = 'pesquisa';

        $this->middleware('can:pesquisa.index')->only(['index', 'resultados']);
        $this->middleware('can:pesquisa.create')->only('create');
        $this->middleware('can:pesquisa.store')->only('store');
        $this->middleware('can:pesquisa.edit')->only('edit');
        $this->middleware('can:pesquisa.update')->only('update');
        $this->middleware('can:pesquisa.destroy')->only('destroy');
        $this->middleware('can:pesquisa.filtro')->only('filtro');
        $this->middleware('can:pesquisa.removerFiltro')->only('removerFiltro');
        $this->middleware('can:pesquisa.setPerPage')->only('setPerPage');
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 15);

        $pesquisas = $this->pesquisaService->getAllWithFiltersAndPagination(
            $itensFiltered, $perPage, ['escola', 'perguntas', 'respostas'], 'data_fim', 'desc'
        );

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Pesquisas', 'Comunicação');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];
        $nomeFiltro = $this->nomeFiltro;

        return view('pesquisa.index', compact(
            'pesquisas', 'breadcrumb', 'filters', 'itensFiltered',
            'additionalFilters', 'perPages', 'perPage', 'nomeFiltro'
        ))->with('titulo', $this->titulo);
    }

    public function create()
    {
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Pesquisas', 'pesquisa.index', 'Nova Pesquisa', 'Comunicação');

        return view('pesquisa.add-edit', array_merge(compact('breadcrumb'), $this->opcoesFormulario()))
            ->with('titulo', $this->titulo);
    }

    public function store(StorePesquisaRequest $request)
    {
        $this->pesquisaService->createComPerguntas($request->validated());

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Pesquisa criada com sucesso.');
    }

    public function edit($id)
    {
        $pesquisa = $this->pesquisaService->getById($id, ['escola', 'perguntas']);
        abort_if(! $pesquisa, 404, 'Pesquisa não encontrada.');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Pesquisas', 'pesquisa.index', "Editar: {$pesquisa->titulo}", 'Comunicação');

        return view('pesquisa.add-edit', array_merge(compact('pesquisa', 'breadcrumb'), $this->opcoesFormulario()))
            ->with('titulo', $this->titulo);
    }

    public function update(UpdatePesquisaRequest $request, $id)
    {
        $pesquisa = $this->pesquisaService->updateComPerguntas($id, $request->validated());
        abort_if(! $pesquisa, 403, 'Sem acesso para editar esta pesquisa.');

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Pesquisa atualizada com sucesso.');
    }

    public function destroy($id)
    {
        $success = $this->pesquisaService->delete($id);
        abort_if(! $success, 403, 'Sem acesso para excluir esta pesquisa.');

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    /** Resultados agregados da pesquisa. */
    public function resultados($id)
    {
        $pesquisa = $this->pesquisaService->getById($id, ['escola', 'perguntas', 'respostas.usuario']);
        abort_if(! $pesquisa, 404, 'Pesquisa não encontrada.');

        $resultados = $this->pesquisaService->resultados($pesquisa);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbShow('Pesquisas', 'pesquisa.index', "Resultados: {$pesquisa->titulo}", 'Comunicação');

        return view('pesquisa.resultados', [
            'pesquisa' => $pesquisa,
            'resultados' => $resultados,
            'breadcrumb' => $breadcrumb,
            'titulo' => 'Resultados — '.$pesquisa->titulo,
        ]);
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
        session()->put("$this->nomeFiltro.urlPadrao", 'pesquisa');
        session()->put("$this->nomeFiltro.rotaRedirect", 'pesquisa.index');
    }

    public function obterFiltrosPrincipais()
    {
        return [
            [
                'name' => 'titulo', 'label' => 'Título', 'type' => 'text',
                'typeWhere' => 'like',
            ],
        ];
    }

    public function obterFiltrosSecundarios()
    {
        return [
            [
                'name' => 'escola_id', 'label' => 'Escola', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => Escola::query()->orderBy('nome')->get(['id', 'nome'])
                    ->map(fn (Escola $escola) => ['value' => $escola->id, 'label' => $escola->nome])
                    ->all(),
            ],
            [
                'name' => 'status', 'label' => 'Status', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => Pesquisa::STATUS,
            ],
        ];
    }

    private function opcoesFormulario(): array
    {
        return [
            'escolas' => Escola::query()->orderBy('nome')->pluck('nome', 'id')->all(),
            'statusList' => Pesquisa::STATUS,
            'tiposPergunta' => PesquisaPergunta::TIPOS,
        ];
    }
}
