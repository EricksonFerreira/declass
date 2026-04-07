<?php

namespace App\Http\Controllers;

use App\DTO\BreadCrumbItem;
use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\Veiculo\StoreVeiculoRequest;
use App\Http\Requests\Veiculo\UpdateVeiculoRequest;
use App\Services\BreadcrumbService;
use App\Services\EstacaoService;
use App\Services\GrupoFrotaService;
use App\Services\MarcaService;
use App\Services\ModeloService;
use App\Services\SegmentoService;
use App\Services\StatusVeiculoService;
use App\Services\TipoFrotaService;
use App\Services\VeiculoService;
use App\Support\EmpresaContext;
use App\Traits\AtualizaFiltrosTrait;
use Illuminate\Http\Request;
use App\Traits\FiltroTrait;
use App\Models\TipoFrota;
use App\Models\GrupoFrota;
use App\Models\Marca;
use App\Models\Modelo;
use App\Models\StatusVeiculo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class VeiculoController extends BaseController implements FiltroInterface, ResourceInterface
{
    use FiltroTrait;
    use AtualizaFiltrosTrait;
    protected $veiculoService;
    protected $breadcrumbService;
    protected $tipoFrotaService;
    protected $grupoFrotaService;
    protected $marcaService;
    protected $modeloService;
    protected $segmentoService;
    protected $statusVeiculoService;
    protected $estacaoService;


    public function __construct(
        VeiculoService $veiculoService,
        BreadcrumbService $breadcrumbService,
        TipoFrotaService $tipoFrotaService,
        GrupoFrotaService $grupoFrotaService,
        MarcaService $marcaService,
        ModeloService $modeloService,
        SegmentoService $segmentoService,
        StatusVeiculoService $statusVeiculoService,
        EstacaoService $estacaoService
    ) {
        $this->titulo = "Veiculos";
        $this->nomeFiltro = 'veiculo';
        $this->veiculoService = $veiculoService;
        $this->breadcrumbService = $breadcrumbService;
        $this->tipoFrotaService = $tipoFrotaService;
        $this->grupoFrotaService = $grupoFrotaService;
        $this->marcaService = $marcaService;
        $this->modeloService = $modeloService;
        $this->segmentoService = $segmentoService;
        $this->statusVeiculoService = $statusVeiculoService;
        $this->estacaoService = $estacaoService;
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $veiculos = $this->veiculoService->getAllWithFiltersAndPagination($itensFiltered, $perPage);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Veiculos');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];

        return view('veiculo.index', compact(
            'veiculos',
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
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Veiculos', 'veiculo.index', 'Criar');

        $tipoFrotas = $this->tipoFrotaService->getAllAtivos();
        $grupoFrotas = $this->grupoFrotaService->getAllAtivos();
        $marcas = $this->marcaService->getAllAtivos();
        $modelos = $this->modeloService->getAllAtivos();
        $segmentos = $this->segmentoService->getAllAtivos();
        $statusVeiculos = $this->statusVeiculoService->getAllAtivos();
        $estacoes = $this->estacaoService->getAllAtivos();

        [$metodo, $rota] = $this->getFormMethodAndRoute();
        return view('veiculo.add-edit', compact(
            'tipoFrotas',
            'grupoFrotas',
            'marcas',
            'modelos',
            'estacoes',
            'segmentos',
            'statusVeiculos',
            'breadcrumb',
            'status',
            'metodo',
            'rota'
        ))->with('titulo', $this->titulo);
    }

    public function store(StoreVeiculoRequest $request)
    {
        $this->veiculoService->create($request->all());

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function show($id)
    {
        $veiculo = $this->veiculoService->getById($id, [
            'tipoFrota',
            'grupoFrota',
            'marca',
            'modelo',
            'statusVeiculo',
            'localDisponibilidade',
            'reservas.statusReserva',
            'veiculoDanos.dano.categoria',
            'ordensServico',
            'multas' => function ($query) {
                $query->with(['reserva:id', 'responsavelLancamento:id,nome'])
                    ->orderByDesc('id');
            },
            'movimentacoes' => function ($query) {
                $query->with([
                    'usuario:id,nome',
                    'tipoMovimentacao:id,nome',
                    'statusMovimentacao:id,nome',
                    'localOrigem:id,nome',
                    'localDestino:id,nome',
                    'reserva:id',
                    'ordemServico:id,numero,status',
                ])->orderByDesc('id');
            },
        ]);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Veiculos', route('veiculo.index')),
            new BreadCrumbItem($veiculo->nome ?? 'Detalhes'),
        ]);

        $titulo = 'Veículo: ' . ($veiculo->nome ?? '#' . $veiculo->id);

        return view('veiculo.show', compact('veiculo', 'breadcrumb', 'titulo'));
    }

    public function edit($id)
    {
        $veiculo = $this->veiculoService->getById($id);
        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo'
        ];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Veiculos', 'veiculo.index', "Editar: $veiculo->nome");
        [$metodo, $rota] = $this->getFormMethodAndRoute($veiculo);


        $tipoFrotas = $this->tipoFrotaService->getAllAtivos();
        $grupoFrotas = $this->grupoFrotaService->getAllAtivos();
        $marcas = $this->marcaService->getAllAtivos();
        $modelos = $this->modeloService->getAllAtivos();
        $segmentos = $this->segmentoService->getAllAtivos();
        $statusVeiculos = $this->statusVeiculoService->getAllAtivos();
        $estacoes = $this->estacaoService->getAllAtivos();

        return view('veiculo.add-edit', compact(
            'veiculo',
            'tipoFrotas',
            'grupoFrotas',
            'marcas',
            'estacoes',
            'modelos',
            'segmentos',
            'statusVeiculos',
            'breadcrumb',
            'status',
            'metodo',
            'rota'
        ))->with('titulo', $this->titulo);
    }

    public function update(UpdateVeiculoRequest $request, $id)
    {
        $success = $this->veiculoService->update($id, $request->all());
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function destroy($id)
    {
        $success = $this->veiculoService->delete($id);
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }


    public function buscar(Request $request)
    {
        $empresaId = app(EmpresaContext::class)->empresaId()
            ?? Auth::user()?->empresa_id;

        $query = \App\Models\Veiculo::with(['modelo', 'grupoFrota', 'marca', 'tipoFrota', 'statusVeiculo', 'localDisponibilidade'])
            ->select([
                'veiculos.id', 'veiculos.nome', 'veiculos.placa_licenca', 'veiculos.chassi',
                'veiculos.ano_fabricacao', 'veiculos.ano_modelo', 'veiculos.cor', 'veiculos.kms',
                'veiculos.ativo', 'veiculos.empresa_id',
                'veiculos.modelo_id', 'veiculos.grupo_frota_id', 'veiculos.marca_id',
                'veiculos.tipo_frota_id', 'veiculos.status_veiculo_id', 'veiculos.local_disponibilidade',
            ]);

        if ($empresaId && Schema::hasColumn('veiculos', 'empresa_id')) {
            $query->where('veiculos.empresa_id', $empresaId);
        }

        // Busca global
        $q = trim((string) $request->input('q', ''));
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('veiculos.nome', 'like', "%{$q}%")
                    ->orWhere('veiculos.placa_licenca', 'like', "%{$q}%")
                    ->orWhere('veiculos.chassi', 'like', "%{$q}%")
                    ->orWhere('veiculos.cor', 'like', "%{$q}%")
                    ->orWhereHas('modelo', fn ($m) => $m->where('nome', 'like', "%{$q}%"))
                    ->orWhereHas('marca', fn ($m) => $m->where('nome', 'like', "%{$q}%"))
                    ->orWhereHas('grupoFrota', fn ($g) => $g->where('nome', 'like', "%{$q}%"));
            });
        }

        // Filtros individuais
        if ($v = trim((string) $request->input('placa', ''))) {
            $query->where('veiculos.placa_licenca', 'like', "%{$v}%");
        }
        if ($v = trim((string) $request->input('nome', ''))) {
            $query->where('veiculos.nome', 'like', "%{$v}%");
        }
        if ($v = trim((string) $request->input('chassi', ''))) {
            $query->where('veiculos.chassi', 'like', "%{$v}%");
        }
        if ($v = trim((string) $request->input('cor', ''))) {
            $query->where('veiculos.cor', 'like', "%{$v}%");
        }
        if ($v = (int) $request->input('marca_id', 0)) {
            $query->where('veiculos.marca_id', $v);
        }
        if ($v = trim((string) $request->input('modelo', ''))) {
            $query->whereHas('modelo', fn ($m) => $m->where('nome', 'like', "%{$v}%"));
        }
        if ($v = (int) $request->input('grupo_frota_id', 0)) {
            $query->where('veiculos.grupo_frota_id', $v);
        }
        if ($v = (int) $request->input('tipo_frota_id', 0)) {
            $query->where('veiculos.tipo_frota_id', $v);
        }
        if ($v = (int) $request->input('status_veiculo_id', 0)) {
            $query->where('veiculos.status_veiculo_id', $v);
        }
        if ($request->input('ativo') !== null && $request->input('ativo') !== '') {
            $query->where('veiculos.ativo', (bool) $request->input('ativo'));
        }
        if ($v = (int) $request->input('ano_de', 0)) {
            $query->where('veiculos.ano_modelo', '>=', $v);
        }
        if ($v = (int) $request->input('ano_ate', 0)) {
            $query->where('veiculos.ano_modelo', '<=', $v);
        }
        if ($v = (int) $request->input('kms_ate', 0)) {
            $query->where('veiculos.kms', '<=', $v);
        }

        $veiculos = $query->orderBy('veiculos.placa_licenca')->limit(80)->get()->map(fn ($v) => [
            'id'          => $v->id,
            'nome'        => $v->nome ?? ('Veiculo #' . $v->id),
            'placa'       => $v->placa_licenca ?? '',
            'chassi'      => $v->chassi ?? '',
            'marca'       => $v->marca?->nome ?? '',
            'modelo'      => $v->modelo?->nome ?? '',
            'grupo'       => $v->grupoFrota?->nome ?? '',
            'tipo'        => $v->tipoFrota?->nome ?? '',
            'status'      => $v->statusVeiculo?->nome ?? '',
            'ano_fab'     => $v->ano_fabricacao ?? '',
            'ano_mod'     => $v->ano_modelo ?? '',
            'cor'         => $v->cor ?? '',
            'kms'         => $v->kms !== null ? number_format((float)$v->kms, 0, ',', '.') : '',
            'ativo'       => $v->ativo ? 'Sim' : 'Não',
            'estacao'     => $v->localDisponibilidade?->nome ?? '',
        ]);

        return response()->json(['veiculos' => $veiculos]);
    }

    public function disponibilidade(Request $request)
    {
        $titulo = 'Disponibilidade de Veiculos';
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex($titulo);

        $anos = range(2024, date('Y'));
        $meses = [
            1 => 'Janeiro',
            2 => 'Fevereiro',
            3 => 'Março',
            4 => 'Abril',
            5 => 'Maio',
            6 => 'Junho',
            7 => 'Julho',
            8 => 'Agosto',
            9 => 'Setembro',
            10 => 'Outubro',
            11 => 'Novembro',
            12 => 'Dezembro',
        ];
        $totalVeiculos = $this->veiculoService->getTotalVeiculos();
        $veiculosDisponiveis = $this->veiculoService->getVeiculosDisponiveis();
        $veiculosReservados = $this->veiculoService->getVeiculosReservados();
        $veiculosIndisponiveis = $this->veiculoService->getVeiculosIndisponiveis();

        // $diasDoMes = range(1, 31);
       // pegue os dias do mes dependendo do mês selecionado. se nao tiver mes selecionado pegue o mes atual
       $mes = $request->mes ?? date('m');
       $ano = $request->ano ?? date('Y');
       $diasDoMes = range(1, cal_days_in_month(CAL_GREGORIAN, $mes, $ano));
        $veiculos = $this->veiculoService->getAllWithFiltersAndPagination([], 1000);
        // dd($veiculos);

        return view('veiculo.disponibilidade', compact(
            'totalVeiculos',
            'veiculosDisponiveis',
            'veiculosReservados',
            'veiculosIndisponiveis',
            'diasDoMes',
            'veiculos',
            'anos',
            'meses',
            'titulo',
            'breadcrumb'
        ));
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
        session()->put("$this->nomeFiltro.urlPadrao", 'veiculo');
        session()->put("$this->nomeFiltro.rotaRedirect", 'veiculo.index');
    }

    public function obterFiltrosPrincipais()
    {
        $empresaId = $this->empresaIdParaFiltro();

        $dataTipoFrota = $this->buildOptionsByEmpresa(TipoFrota::query(), $empresaId, 'Selecione o tipo de Frota');
        $dataGrupoFrota = $this->buildOptionsByEmpresa(GrupoFrota::query(), $empresaId, 'Selecione o grupo de Frota');
        $dataMarca = $this->buildOptionsByEmpresa(Marca::query(), $empresaId, 'Selecione a marca');
        $dataModelo = $this->buildOptionsByEmpresa(Modelo::query(), $empresaId, 'Selecione o modelo');
        $dataStatusVeiculo = $this->buildOptionsByEmpresa(StatusVeiculo::query(), $empresaId, 'Selecione o status');

        return [
            ['name' => 'nome', 'label' => 'Nome', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'tipo_frota_id', 'label'=> 'Tipos de Frotas','type'=>'select','typeWhere' => 'where','options'=> $dataTipoFrota],
            ['name' => 'grupo_frota_id', 'label'=> 'Grupos de Frotas','type'=>'select','typeWhere' => 'where','options'=> $dataGrupoFrota],
            ['name' => 'marca_id', 'label'=> 'Marcas','type'=>'select','typeWhere' => 'where','options'=> $dataMarca],
            ['name' => 'modelo_id', 'label'=> 'Modelos','type'=>'select','typeWhere' => 'where','options'=> $dataModelo],
            ['name' => 'status_veiculo_id', 'label'=> 'Status','type'=>'select','typeWhere' => 'where','options'=> $dataStatusVeiculo],
        ];
    }

    public function obterFiltrosSecundarios()
    {
        return [
            // Adicione filtros secundários aqui, se necessário
        ];
    }

    private function empresaIdParaFiltro(): ?int
    {
        /** @var EmpresaContext $empresaContext */
        $empresaContext = app(EmpresaContext::class);
        $empresaId = $empresaContext->empresaId();

        if ($empresaId !== null) {
            return (int) $empresaId;
        }

        $userEmpresaId = Auth::user()?->empresa_id;
        return $userEmpresaId ? (int) $userEmpresaId : null;
    }

    private function buildOptionsByEmpresa($query, ?int $empresaId, string $label): array
    {
        $model = $query->getModel();
        if ($empresaId !== null && Schema::hasColumn($model->getTable(), 'empresa_id')) {
            $query->where($model->getTable() . '.empresa_id', $empresaId);
        }

        if (Schema::hasColumn($model->getTable(), 'ativo')) {
            $query->where($model->getTable() . '.ativo', 'S');
        }

        $items = $query->orderBy('nome')->pluck('nome', 'id')->toArray();

        return FiltroTrait::formatOptions($items, $label);
    }

    /**
     * Função para obter o método e a rota para o formulário.
     *
     * @param  Veiculo|null  $veiculo
     * @return array
     */
    private function getFormMethodAndRoute($veiculo = null)
    {
        if ($veiculo) {
            return [
                'PUT',
                route('veiculo.update', $veiculo->id),
            ];
        }

        return [
            'POST',
            route('veiculo.store'),
        ];
    }
}
