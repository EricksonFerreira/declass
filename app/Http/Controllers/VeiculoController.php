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
use App\Models\Movimentacao;
use App\Models\Reserva;
use App\Models\StatusVeiculo;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        $perPage  = session()->get("$this->nomeFiltro.perPage", 10);
        $orderBy  = session()->get("$this->nomeFiltro.orderBy", 'id');
        $orderDir = session()->get("$this->nomeFiltro.orderDir", 'asc');

        $veiculos = $this->veiculoService->getAllWithFiltersAndPagination($itensFiltered, $perPage, [], $orderBy, $orderDir);

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
            'perPage',
            'orderBy',
            'orderDir'
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
        $defaultStatusId = $this->statusVeiculoService->getModel()->newQuery()
            ->whereRaw('LOWER(nome) = ?', ['ativo'])
            ->value('id');

        [$metodo, $rota] = $this->getFormMethodAndRoute();
        return view('veiculo.add-edit', compact(
            'tipoFrotas',
            'grupoFrotas',
            'marcas',
            'modelos',
            'estacoes',
            'segmentos',
            'statusVeiculos',
            'defaultStatusId',
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
        $dispMes = (int) request()->query('disp_mes', date('n'));
        $dispAno = (int) request()->query('disp_ano', date('Y'));
        $dispEstacaoId = (int) request()->query('disp_estacao_id', 0);
        if ($dispMes < 1 || $dispMes > 12) {
            $dispMes = (int) date('n');
        }
        if ($dispAno < 2024 || $dispAno > ((int) date('Y') + 1)) {
            $dispAno = (int) date('Y');
        }
        if ($dispEstacaoId < 0) {
            $dispEstacaoId = 0;
        }

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
            'sinistros' => function ($query) {
                $query->orderByDesc('id');
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

        $reservaBloqueioHoras = 0;
        $empresaId = null;
        /** @var EmpresaContext $empresaContext */
        $empresaContext = app(EmpresaContext::class);
        if (Auth::user()?->isAdministrador()) {
            $empresaId = $empresaContext->empresaId();
        } else {
            $empresaId = Auth::user()?->empresa_id ?? $empresaContext->empresaId();
        }

        if ($empresaId && Schema::hasTable('empresas') && Schema::hasColumn('empresas', 'reserva_bloqueio_pos_reserva_horas')) {
            $reservaBloqueioHoras = (int) (DB::table('empresas')->where('id', (int) $empresaId)->value('reserva_bloqueio_pos_reserva_horas') ?? 0);
            if ($reservaBloqueioHoras < 0) {
                $reservaBloqueioHoras = 0;
            }
        }

        $dispDiasNoMes = (int) cal_days_in_month(CAL_GREGORIAN, $dispMes, $dispAno);
        $dispInicioMes = Carbon::create($dispAno, $dispMes, 1)->startOfDay();
        $dispFimMes = Carbon::create($dispAno, $dispMes, $dispDiasNoMes)->endOfDay();
        $dispInicioMesComFolga = $dispInicioMes->copy()->subHours($reservaBloqueioHoras);
        $dispFirstDow = (int) Carbon::create($dispAno, $dispMes, 1)->dayOfWeekIso; // 1..7 (Seg..Dom)

        $dispReservas = Reserva::query()
            ->select(['id', 'veiculo_id', 'data_inicio', 'data_fim', 'status_reserva_id', 'user_id'])
            ->where('veiculo_id', (int) $veiculo->id)
            ->whereIn('status_reserva_id', [1, 2, 4, 6])
            ->where('data_inicio', '<', $dispFimMes->toDateTimeString())
            ->where('data_fim', '>', $dispInicioMesComFolga->toDateTimeString())
            ->orderBy('data_inicio')
            ->get();

        $dispMovimentacoes = Movimentacao::query()
            ->select(['id', 'veiculo_id', 'data_saida', 'data_chegada_real', 'status_movimentacao_id', 'reserva_id'])
            ->where('veiculo_id', (int) $veiculo->id)
            ->whereIn('status_movimentacao_id', [1, 2, 5, 6, 7])
            ->where(function ($query) use ($dispFimMes) {
                $query->whereNull('data_saida')
                    ->orWhere('data_saida', '<=', $dispFimMes->toDateTimeString());
            })
            ->where(function ($query) use ($dispInicioMes) {
                $query->whereNull('data_chegada_real')
                    ->orWhere('data_chegada_real', '>=', $dispInicioMes->toDateTimeString());
            })
            ->orderBy('data_saida')
            ->get();

        $dispGrade = [];
        $dispStats = [
            'ok' => 0,
            'reserved' => 0,
            'off' => 0,
            'pending' => 0,
        ];

        $inicioDisponibilidade = !empty($veiculo->data_hora_disponibilidade) ? Carbon::parse($veiculo->data_hora_disponibilidade) : null;

        for ($dia = 1; $dia <= $dispDiasNoMes; $dia++) {
            $diaInicio = Carbon::create($dispAno, $dispMes, $dia)->startOfDay();
            $diaFim = Carbon::create($dispAno, $dispMes, $dia)->endOfDay();

            $cell = [
                'tipo' => 'ok',
                'titulo' => 'Disponivel',
                'reserva_id' => null,
            ];

            if ($inicioDisponibilidade && $diaFim->lt($inicioDisponibilidade)) {
                $cell['tipo'] = 'off';
                $cell['titulo'] = 'Indisponivel (disponivel a partir de ' . $inicioDisponibilidade->format('d/m/Y H:i') . ')';
                $dispGrade[$dia] = $cell;
                $dispStats['off']++;
                continue;
            }

            $movIndisp = $dispMovimentacoes->first(function ($mov) use ($diaInicio, $diaFim) {
                $saida = !empty($mov->data_saida) ? Carbon::parse($mov->data_saida) : null;
                $chegada = !empty($mov->data_chegada_real) ? Carbon::parse($mov->data_chegada_real) : null;

                $inicio = $saida ?? $diaInicio;
                $fim = $chegada ?? Carbon::create(2999, 12, 31)->endOfDay();

                return $inicio->lt($diaFim) && $fim->gt($diaInicio);
            });

            if ($movIndisp) {
                $cell['tipo'] = 'off';
                $cell['titulo'] = 'Indisponivel (movimentacao)';
                $dispGrade[$dia] = $cell;
                $dispStats['off']++;
                continue;
            }

            $resIndisp = $dispReservas->first(function ($res) use ($diaInicio, $diaFim, $reservaBloqueioHoras) {
                $inicio = !empty($res->data_inicio) ? Carbon::parse($res->data_inicio) : null;
                $fim = !empty($res->data_fim) ? Carbon::parse($res->data_fim) : null;
                if (!$inicio || !$fim) {
                    return false;
                }

                $fimComFolga = $reservaBloqueioHoras > 0 ? $fim->copy()->addHours($reservaBloqueioHoras) : $fim;

                return $inicio->lt($diaFim) && $fimComFolga->gt($diaInicio);
            });

            if ($resIndisp) {
                $cell['tipo'] = 'reserved';
                $cell['reserva_id'] = (int) $resIndisp->id;
                $cell['titulo'] = 'Indisponivel por reserva #' . (int) $resIndisp->id . ' (clique para abrir)';
                $dispGrade[$dia] = $cell;
                $dispStats['reserved']++;
                continue;
            }

            if (empty($veiculo->local_disponibilidade)) {
                $cell['tipo'] = 'pending';
                $cell['titulo'] = 'Sem local de disponibilidade (nao aparece na reserva)';
                $dispGrade[$dia] = $cell;
                $dispStats['pending']++;
                continue;
            }

            if ($dispEstacaoId > 0 && (int) $veiculo->local_disponibilidade !== $dispEstacaoId) {
                $cell['tipo'] = 'pending';
                $cell['titulo'] = 'Nao reservavel para a estacao selecionada (veiculo em outra estacao)';
                $dispGrade[$dia] = $cell;
                $dispStats['pending']++;
                continue;
            }

            $dispGrade[$dia] = $cell;
            $dispStats['ok']++;
        }

        $dispEstacoes = $this->estacaoService->getAllAtivos('id', 'nome', 'nome', 'asc');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Veiculos', route('veiculo.index')),
            new BreadCrumbItem($veiculo->nome ?? 'Detalhes'),
        ]);

        $titulo = 'Veículo: ' . ($veiculo->nome ?? '#' . $veiculo->id);

        return view('veiculo.show', compact(
            'veiculo',
            'breadcrumb',
            'titulo',
            'dispMes',
            'dispAno',
            'dispEstacaoId',
            'dispDiasNoMes',
            'dispFirstDow',
            'dispGrade',
            'dispStats',
            'dispReservas',
            'dispMovimentacoes',
            'reservaBloqueioHoras',
            'dispEstacoes'
        ));
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
        $defaultStatusId = $veiculo->status_veiculo_id;

        return view('veiculo.add-edit', compact(
            'veiculo',
            'tipoFrotas',
            'grupoFrotas',
            'marcas',
            'estacoes',
            'modelos',
            'segmentos',
            'statusVeiculos',
            'defaultStatusId',
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


    public function gruposPorTipo(Request $request)
    {
        $tipoId = (int) $request->input('tipo_frota_id');
        $grupos = \App\Models\GrupoFrota::where('ativo', 'S')
            ->when($tipoId, fn($q) => $q->where('tipo_frota_id', $tipoId))
            ->orderBy('nome')
            ->get(['id', 'nome']);

        return response()->json($grupos);
    }

    public function setOrdenacao(Request $request)
    {
        $campo  = $request->input('orderBy', 'id');
        $dir    = $request->input('orderDir', 'asc');
        $allowed = ['id','nome','placa_licenca','renavam','ano_fabricacao','ano_modelo','kms','created_at'];
        if (!in_array($campo, $allowed)) $campo = 'id';
        if (!in_array($dir, ['asc','desc'])) $dir = 'asc';
        session(['veiculo.orderBy' => $campo, 'veiculo.orderDir' => $dir]);
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function modelosPorMarca(Request $request)
    {
        $marcaId = (int) $request->input('marca_id');
        $modelos = \App\Models\Modelo::where('ativo', 'S')
            ->when($marcaId, fn($q) => $q->where('marca_id', $marcaId))
            ->orderBy('nome')
            ->get(['id', 'nome']);

        return response()->json($modelos);
    }

    public function buscar(Request $request)
    {
        $empresaId = app(EmpresaContext::class)->empresaId()
            ?? Auth::user()?->empresa_id;

        $query = \App\Models\Veiculo::with(['modelo', 'grupoFrota', 'marca', 'tipoFrota', 'statusVeiculo', 'localDisponibilidade'])
            ->select([
                'veiculos.id',
                'veiculos.nome',
                'veiculos.placa_licenca',
                'veiculos.chassi',
                'veiculos.ano_fabricacao',
                'veiculos.ano_modelo',
                'veiculos.cor',
                'veiculos.kms',
                'veiculos.ativo',
                'veiculos.empresa_id',
                'veiculos.modelo_id',
                'veiculos.grupo_frota_id',
                'veiculos.marca_id',
                'veiculos.tipo_frota_id',
                'veiculos.status_veiculo_id',
                'veiculos.local_disponibilidade',
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
                    ->orWhereHas('modelo', fn($m) => $m->where('nome', 'like', "%{$q}%"))
                    ->orWhereHas('marca', fn($m) => $m->where('nome', 'like', "%{$q}%"))
                    ->orWhereHas('grupoFrota', fn($g) => $g->where('nome', 'like', "%{$q}%"));
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
            $query->whereHas('modelo', fn($m) => $m->where('nome', 'like', "%{$v}%"));
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

        $veiculos = $query->orderBy('veiculos.placa_licenca')->limit(80)->get()->map(fn($v) => [
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
        $mes = (int) ($request->mes ?? date('n'));
        $ano = (int) ($request->ano ?? date('Y'));
        $estacaoFiltro = $request->input('estacao_id');
        $estacaoId = is_numeric($estacaoFiltro) ? (int) $estacaoFiltro : 0;
        $diasNoMes = (int) cal_days_in_month(CAL_GREGORIAN, $mes, $ano);
        $diasDoMes = range(1, $diasNoMes);

        $inicioMes = Carbon::create($ano, $mes, 1)->startOfDay();
        $fimMes = Carbon::create($ano, $mes, $diasNoMes)->endOfDay();

        $filtros = [];
        if ($estacaoFiltro === 'sem_estacao') {
            $filtros['local_disponibilidade'] = [
                'typeWhere' => 'whereNull',
                'valor' => null,
            ];
        } elseif ($estacaoId > 0) {
            $filtros['local_disponibilidade'] = [
                'typeWhere' => 'where',
                'valor' => $estacaoId,
            ];
        }

        $estacaoFiltro = $request->input('estacao_id');
        $veiculos = $this->veiculoService->getVeiculosDisponibilidade($estacaoFiltro, 1000);

        $veiculosCollection = method_exists($veiculos, 'getCollection') ? $veiculos->getCollection() : collect($veiculos);
        $veiculoIds = $veiculosCollection->pluck('id')->map(static fn($id) => (int) $id)->filter()->values()->toArray();

        $reservaBloqueioHoras = 0;
        $empresaId = null;
        /** @var EmpresaContext $empresaContext */
        $empresaContext = app(EmpresaContext::class);
        if (Auth::user()?->isAdministrador()) {
            $empresaId = $empresaContext->empresaId();
        } else {
            $empresaId = Auth::user()?->empresa_id ?? $empresaContext->empresaId();
        }

        if ($empresaId && Schema::hasTable('empresas') && Schema::hasColumn('empresas', 'reserva_bloqueio_pos_reserva_horas')) {
            $reservaBloqueioHoras = (int) (DB::table('empresas')->where('id', (int) $empresaId)->value('reserva_bloqueio_pos_reserva_horas') ?? 0);
            if ($reservaBloqueioHoras < 0) {
                $reservaBloqueioHoras = 0;
            }
        }

        $inicioMesComFolga = $inicioMes->copy()->subHours($reservaBloqueioHoras);

        $reservasPorVeiculo = collect();
        $movimentacoesPorVeiculo = collect();

        if (!empty($veiculoIds)) {
            $reservasPorVeiculo = Reserva::query()
                ->select(['id', 'veiculo_id', 'data_inicio', 'data_fim'])
                ->whereIn('veiculo_id', $veiculoIds)
                ->whereIn('status_reserva_id', [1, 2, 4, 6])
                ->where('data_inicio', '<', $fimMes->toDateTimeString())
                ->where('data_fim', '>', $inicioMesComFolga->toDateTimeString())
                ->orderBy('data_inicio')
                ->get()
                ->groupBy('veiculo_id');

            $movimentacoesPorVeiculo = Movimentacao::query()
                ->select(['id', 'veiculo_id', 'data_saida', 'data_chegada_real', 'status_movimentacao_id', 'reserva_id'])
                ->whereIn('veiculo_id', $veiculoIds)
                ->whereIn('status_movimentacao_id', [1, 2, 5, 6, 7])
                ->where(function ($query) use ($fimMes) {
                    $query->whereNull('data_saida')
                        ->orWhere('data_saida', '<=', $fimMes->toDateTimeString());
                })
                ->where(function ($query) use ($inicioMes) {
                    $query->whereNull('data_chegada_real')
                        ->orWhere('data_chegada_real', '>=', $inicioMes->toDateTimeString());
                })
                ->orderBy('data_saida')
                ->get()
                ->groupBy('veiculo_id');
        }

        $grade = [];
        foreach ($veiculosCollection as $veiculo) {
            $veiculoReservas = $reservasPorVeiculo->get($veiculo->id, collect());
            $veiculoMovs = $movimentacoesPorVeiculo->get($veiculo->id, collect());
            $inicioDisponibilidade = !empty($veiculo->data_hora_disponibilidade) ? Carbon::parse($veiculo->data_hora_disponibilidade) : null;

            foreach ($diasDoMes as $dia) {
                $diaInicio = Carbon::create($ano, $mes, (int) $dia)->startOfDay();
                $diaFim = Carbon::create($ano, $mes, (int) $dia)->endOfDay();

                $cell = [
                    'tipo' => 'ok',
                    'titulo' => 'Disponivel',
                    'reserva_id' => null,
                ];

                if ($inicioDisponibilidade && $diaFim->lt($inicioDisponibilidade)) {
                    $cell['tipo'] = 'off';
                    $cell['titulo'] = 'Indisponivel (disponivel a partir de ' . $inicioDisponibilidade->format('d/m/Y H:i') . ')';
                    $grade[$veiculo->id][$dia] = $cell;
                    continue;
                }

                $movIndisp = $veiculoMovs->first(function ($mov) use ($diaInicio, $diaFim) {
                    $saida = !empty($mov->data_saida) ? Carbon::parse($mov->data_saida) : null;
                    $chegada = !empty($mov->data_chegada_real) ? Carbon::parse($mov->data_chegada_real) : null;

                    $inicio = $saida ?? $diaInicio;
                    $fim = $chegada ?? Carbon::create(2999, 12, 31)->endOfDay();

                    return $inicio->lt($diaFim) && $fim->gt($diaInicio);
                });

                if ($movIndisp) {
                    $cell['tipo'] = 'off';
                    $cell['titulo'] = 'Indisponivel (movimentacao)';
                    $grade[$veiculo->id][$dia] = $cell;
                    continue;
                }

                $reservaIndisp = $veiculoReservas->first(function ($res) use ($diaInicio, $diaFim, $reservaBloqueioHoras) {
                    $inicio = !empty($res->data_inicio) ? Carbon::parse($res->data_inicio) : null;
                    $fim = !empty($res->data_fim) ? Carbon::parse($res->data_fim) : null;
                    if (!$inicio || !$fim) {
                        return false;
                    }

                    $fimComFolga = $reservaBloqueioHoras > 0 ? $fim->copy()->addHours($reservaBloqueioHoras) : $fim;

                    return $inicio->lt($diaFim) && $fimComFolga->gt($diaInicio);
                });

                if ($reservaIndisp) {
                    $cell['tipo'] = 'reserved';
                    $cell['reserva_id'] = (int) $reservaIndisp->id;
                    $cell['titulo'] = 'Indisponivel por reserva #' . (int) $reservaIndisp->id . ' (clique para abrir)';
                    $grade[$veiculo->id][$dia] = $cell;
                    continue;
                }

                if (empty($veiculo->local_disponibilidade)) {
                    $cell['tipo'] = 'pending';
                    $cell['titulo'] = 'Sem local de disponibilidade (nao aparece na reserva)';
                }

                $grade[$veiculo->id][$dia] = $cell;
            }
        }

        $estacoes = $this->estacaoService->getAllAtivos('id', 'nome', 'nome', 'asc', $empresaId);

        return view('veiculo.disponibilidade', compact(
            'totalVeiculos',
            'veiculosDisponiveis',
            'veiculosReservados',
            'veiculosIndisponiveis',
            'diasDoMes',
            'veiculos',
            'grade',
            'reservaBloqueioHoras',
            'anos',
            'meses',
            'estacoes',
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
            ['name' => 'tipo_frota_id', 'label' => 'Tipos de Frotas', 'type' => 'select', 'typeWhere' => 'where', 'options' => $dataTipoFrota],
            ['name' => 'grupo_frota_id', 'label' => 'Grupos de Frotas', 'type' => 'select', 'typeWhere' => 'where', 'options' => $dataGrupoFrota],
            ['name' => 'marca_id', 'label' => 'Marcas', 'type' => 'select', 'typeWhere' => 'where', 'options' => $dataMarca],
            ['name' => 'modelo_id', 'label' => 'Modelos', 'type' => 'select', 'typeWhere' => 'where', 'options' => $dataModelo],
            ['name' => 'status_veiculo_id', 'label' => 'Status', 'type' => 'select', 'typeWhere' => 'where', 'options' => $dataStatusVeiculo],
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
