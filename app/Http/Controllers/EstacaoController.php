<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\Estacao\SalvarHorariosDiariosRequest;
use App\Http\Requests\Estacao\StoreEstacaoRequest;
use App\Http\Requests\Estacao\UpdateEstacaoRequest;
use App\Models\Estacao;
use App\Services\BreadcrumbService;
use App\Services\DiaUtilService;
use App\Services\EstacaoService;
use App\Services\HorarioDiarioService;
use App\Services\PaisService;
use App\Traits\AtualizaFiltrosTrait;
use Illuminate\Http\Request;
use App\Traits\FiltroTrait;
use Illuminate\Support\Facades\DB;

class EstacaoController extends BaseController implements FiltroInterface, ResourceInterface
{
    use FiltroTrait;
    use AtualizaFiltrosTrait;
    protected $estacaoService;
    protected $breadcrumbService;
    protected $paisService;
    protected $horarioDiarioService;
    protected $diaUtilService;

    public function __construct(
        EstacaoService $estacaoService,
        HorarioDiarioService $horarioDiarioService,
        PaisService $paisService,
        BreadcrumbService $breadcrumbService,
        DiaUtilService $diaUtilService
    ) {
        $this->titulo = "Estações";
        $this->nomeFiltro = 'estacao';
        $this->estacaoService = $estacaoService;
        $this->breadcrumbService = $breadcrumbService;
        $this->paisService = $paisService;
        $this->horarioDiarioService = $horarioDiarioService;
        $this->diaUtilService = $diaUtilService;
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $estacaos = $this->estacaoService->getAllWithFiltersAndPagination($itensFiltered, $perPage, ['pais']);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Estações');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];

        return view('estacao.index', compact(
            'estacaos',
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
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Estações', 'estacao.index', 'Criar');

        $paises =  $this->paisService->getAllForSelectId();
        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo'
        ];

        $checkin = [
            'S' => 'Permite',
            'N' => 'Não Permite'
        ];

        $checkout = [
            'S' => 'Permite',
            'N' => 'Não Permite'
        ];
        $diasUteis = $this->diaUtilService->getAllAtivos('id', 'dia', 'dia', 'asc');

        $horariosDiarios = $this->horarioDiarioService->getAllAtivos();

        [$metodo, $rota] = $this->getFormMethodAndRoute();
        return view('estacao.add-edit', compact('breadcrumb', 'paises', 'status', 'diasUteis', 'metodo', 'rota', 'checkin', 'checkout', 'horariosDiarios'))->with('titulo', $this->titulo);
    }

    public function store(StoreEstacaoRequest $request)
    {
        $this->estacaoService->create($request->all());

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function show($id)
    {
        echo "show";
    }
    public function edit($id)
    {
        $estacao = $this->estacaoService->getById($id);

        $this->estacaoService->salvarNovosHorariosDiarios($estacao);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Estações', 'estacao.index', "Editar: $estacao->nome");

        $paises =  $this->paisService->getAllForSelectId();
        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo'
        ];

        $checkin = $checkout = $cobrarColeta = $cobrarEntrega =  $validoIntervalo = [
            'S' => 'Permite',
            'N' => 'Não Permite'
        ];

        $diasUteis = $this->diaUtilService->getAllAtivos('id', 'dia', 'dia', 'asc');
        [$metodo, $rota] = $this->getFormMethodAndRoute($estacao);
        $diasUteisEstacao = $estacao->diasUteis->pluck('id')->toArray();

        return view(
            'estacao.add-edit',
            compact(
                'estacao',
                'paises',
                'status',
                'checkin',
                'checkout',
                'diasUteis',
                'breadcrumb',
                'status',
                'metodo',
                'rota',
                'cobrarColeta',
                'cobrarEntrega',
                'validoIntervalo',
                'diasUteisEstacao'
            )
        )
            ->with('titulo', $this->titulo);
    }

    public function update(UpdateEstacaoRequest $request, $id)
    {
        $this->estacaoService->update($id, $request->all());
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }


    public function horarios_diarios($id)
    {
        $estacao = $this->estacaoService->getById($id);
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Horários Diários');

        $horarios_diarios = $estacao->horariosDiarios;

        return view('estacao.horarios_diarios', compact('horarios_diarios', 'breadcrumb', 'estacao'))->with('titulo', $this->titulo);
    }

    public function salvar_horarios_diarios(SalvarHorariosDiariosRequest $request, $id)
    {
        $this->estacaoService->salvarHorariosDiarios($id, $request->all());
        return redirect()->route('estacao.horarios_diarios', $id)->with('success', 'Horários diários salvos com sucesso.');
    }



    public function destroy($id)
    {
        $success = $this->estacaoService->delete($id);
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
        session()->put("$this->nomeFiltro.urlPadrao", 'estacao');
        session()->put("$this->nomeFiltro.rotaRedirect", 'estacao.index');
    }

    public function obterFiltrosPrincipais()
    {
        $paises = $this->paisService->getAllForSelectId();
        $dataPaises = FiltroTrait::formatOptions($paises, 'Selecione o país');

        return [
            ['name' => 'nome', 'label' => 'Nome', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'email', 'label' => 'E-mail', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'celular', 'label' => 'Celular', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'cidade', 'label' => 'Cidade', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'pais_id', 'label' => 'País', 'type' => 'select', 'typeWhere' => 'where', 'options' => $dataPaises],
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


    public function getEstacoesDisponiveis(Request $request)
    {
        if(isset($request->estacao_origem_id)){
            $estacoes = $this->estacaoService->getEstacoesDisponiveis($request->estacao_origem_id);
            return response()->json($estacoes);
        }
        return response()->json([]);
    }

    /**
     * Função para obter o método e a rota para o formulário.
     *
     * @param  Estacao|null  $estacao
     * @return array
     */
    private function getFormMethodAndRoute($estacao = null)
    {
        if ($estacao) {
            return [
                'PUT',
                route('estacao.update', $estacao->id),
            ];
        }

        return [
            'POST',
            route('estacao.store'),
        ];
    }
}
