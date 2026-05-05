<?php

namespace App\Http\Controllers;

use App\DTO\BreadCrumbItem;
use App\Enums\StatusReservaEnum;
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
use Illuminate\Http\JsonResponse;
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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use App\Support\SchemaCache;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

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
        $allowedOrderBy = ['id', 'nome', 'placa_licenca', 'renavam', 'ano_fabricacao', 'kms', 'created_at'];

        if (!in_array($orderBy, $allowedOrderBy, true)) {
            $orderBy = 'id';
        }

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
        $modelosDetalhados = $this->modeloService->getModel()->newQuery()
            ->where('ativo', 'S')
            ->orderBy('nome')
            ->get(['id', 'nome', 'ano_modelo']);
        $modelos = $modelosDetalhados->pluck('nome', 'id')->all();
        $modelosAno = $modelosDetalhados->pluck('ano_modelo', 'id')->all();
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
            'modelosAno',
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

    public function consultarFipe(Request $request): JsonResponse
    {
        $empresaId = app(EmpresaContext::class)->empresaId() ?? Auth::user()?->empresa_id;

        $marcaExists = Rule::exists('marcas', 'id');
        $modeloExists = Rule::exists('modelos', 'id');

        if ($empresaId !== null) {
            $marcaExists = $marcaExists->where(fn ($query) => $query->where('empresa_id', (int) $empresaId));
            $modeloExists = $modeloExists->where(fn ($query) => $query->where('empresa_id', (int) $empresaId));
        }

        $dados = $request->validate([
            'marca_id' => ['required', 'integer', $marcaExists],
            'modelo_id' => ['required', 'integer', $modeloExists],
        ]);

        $marca = Marca::query()->find($dados['marca_id']);
        $modelo = Modelo::query()->find($dados['modelo_id']);

        if (!$marca || !$modelo) {
            return response()->json(['message' => 'Marca ou modelo nao encontrado no cadastro local.'], 422);
        }

        $anoModelo = (int) ($modelo->ano_modelo ?? 0);
        if ($anoModelo <= 0) {
            return response()->json(['message' => 'O modelo selecionado nao possui ano modelo cadastrado.'], 422);
        }

        $baseUrl = rtrim((string) config('services.fipe.base_url', 'https://fipe.parallelum.com.br/api/v2'), '/');
        $vehicleType = (string) config('services.fipe.vehicle_type', 'cars');

        try {
            $urlMarcas = $baseUrl . '/' . $vehicleType . '/brands';
            $marcas = Http::acceptJson()->timeout(15)->get($urlMarcas)->throw()->json();
            $marcaApi = collect($marcas)->first(function (array $item) use ($marca) {
                $nomeApi = $this->normalizarTextoFipe((string) ($item['name'] ?? $item['nome'] ?? ''));
                $nomeSistema = $this->normalizarTextoFipe((string) ($marca->nome_fipe ?? $marca->nome ?? ''));
                $codigoLocal = (string) ($marca->codigo_fipe ?? '');
                $codigoApi = (string) ($item['code'] ?? $item['codigo'] ?? '');

                return ($codigoLocal !== '' && $codigoApi !== '' && $codigoLocal === $codigoApi)
                    || $nomeApi === $nomeSistema
                    || str_contains($nomeApi, $nomeSistema)
                    || str_contains($nomeSistema, $nomeApi);
            });

            if (!$marcaApi) {
                return response()->json(['message' => 'Nao foi possivel localizar a marca na FIPE.'], 422);
            }

            $codigoMarcaApi = (string) ($marcaApi['code'] ?? $marcaApi['codigo'] ?? '');
            $urlMarcaModelos = $baseUrl . '/' . $vehicleType . '/brands/' . $codigoMarcaApi . '/models';
            $modelosResponse = Http::acceptJson()
                ->timeout(15)
                ->get($urlMarcaModelos)
                ->throw()
                ->json();

            $modelosApi = collect($modelosResponse);
            $modeloApi = $modelosApi->first(function (array $item) use ($modelo) {
                $nomeApi = $this->normalizarTextoFipe((string) ($item['name'] ?? $item['nome'] ?? ''));
                $nomeSistema = $this->normalizarTextoFipe((string) ($modelo->nome_fipe ?? $modelo->nome ?? ''));
                $codigoLocal = (string) ($modelo->codigo_fipe ?? '');
                $codigoApi = (string) ($item['code'] ?? $item['codigo'] ?? '');

                return ($codigoLocal !== '' && $codigoApi !== '' && $codigoLocal === $codigoApi)
                    || $nomeApi === $nomeSistema
                    || str_contains($nomeApi, $nomeSistema)
                    || str_contains($nomeSistema, $nomeApi);
            });

            if (!$modeloApi) {
                return response()->json(['message' => 'Nao foi possivel localizar o modelo na FIPE.'], 422);
            }

            $codigoModeloApi = (string) ($modeloApi['code'] ?? $modeloApi['codigo'] ?? '');
            $urlMarcaModeloAnos = $baseUrl . '/' . $vehicleType . '/brands/' . $codigoMarcaApi . '/models/' . $codigoModeloApi . '/years';
            $anosResponse = Http::acceptJson()
                ->timeout(15)
                ->get($urlMarcaModeloAnos)
                ->throw()
                ->json();

            $anoApi = collect($anosResponse)->first(function (array $item) use ($anoModelo) {
                return Str::contains((string) ($item['name'] ?? $item['nome'] ?? ''), (string) $anoModelo);
            });

            if (!$anoApi) {
                return response()->json(['message' => 'Nao foi possivel localizar o ano/modelo na FIPE.'], 422);
            }


            $urlDetalhe = $baseUrl . '/' . $vehicleType . '/brands/' . $codigoMarcaApi . '/models/' . $codigoModeloApi . '/years/' . $anoApi['code'];
            $detalhe = Http::acceptJson()
                ->timeout(15)
                ->get($urlDetalhe)
                ->throw()
                ->json();

            return response()->json([
                'marca' => $marcaApi['name'] ?? $marcaApi['nome'] ?? $marca->nome,
                'modelo' => $modeloApi['name'] ?? $modeloApi['nome'] ?? $modelo->nome,
                'ano_modelo' => $anoModelo,
                'codigo_fipe' => $detalhe['codeFipe'] ?? $detalhe['code'] ?? $detalhe['CodigoFipe'] ?? null,
                'referencia_fipe' => $detalhe['referenceMonth'] ?? $detalhe['reference'] ?? $detalhe['MesReferencia'] ?? null,
                'valor_fipe' => $this->normalizarValorFipe($detalhe['price'] ?? $detalhe['Valor'] ?? null),
                'valor_fipe_formatado' => $detalhe['price'] ?? $detalhe['Valor'] ?? null,
                'combustivel' => $detalhe['fuel'] ?? $detalhe['Combustivel'] ?? null,
                'ano_fipe' => $detalhe['modelYear'] ?? $detalhe['AnoModelo'] ?? null,
                'data_consulta_fipe' => now()->format('Y-m-d'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Falha ao consultar a FIPE. Tente novamente.',
            ], 500);
        }
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

        if (!$veiculo) {
            abort(404, 'Veiculo nao encontrado.');
        }

        $reservaBloqueioHoras = 0;
        $empresaId = null;
        /** @var EmpresaContext $empresaContext */
        $empresaContext = app(EmpresaContext::class);
        if (Auth::user()?->isAdministrador()) {
            $empresaId = $empresaContext->empresaId();
        } else {
            $empresaId = $empresaContext->empresaId() ?? Auth::user()?->empresa_id;
        }

        if ($empresaId && SchemaCache::hasTable('empresas') && SchemaCache::hasColumn('empresas', 'reserva_bloqueio_pos_reserva_horas')) {
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
            ->whereIn('status_reserva_id', [StatusReservaEnum::Pendente->value, StatusReservaEnum::Confirmada->value, StatusReservaEnum::Checkout->value, StatusReservaEnum::NoShow->value])
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

        // --- Financeiro ---
        $finReceita      = (float) $veiculo->reservas->sum('valor_total_final');
        $finCustoMultas  = (float) $veiculo->multas->where('destino_cobranca', 'empresa')->sum('valor_final');
        $finCustoSinistros = (float) $veiculo->sinistros->sum('valor_custo_empresa');
        $finLucroLiquido = $finReceita - $finCustoMultas - $finCustoSinistros;

        $finValorCompra  = (float) ($veiculo->valor_compra ?? 0);
        $finValorFipe    = (float) ($veiculo->valor_fipe ?? 0);
        $finValorAtualEstimado = $veiculo->valor_atual_estimado !== null ? (float) $veiculo->valor_atual_estimado : null;
        $finDepreciacaoAcumulada = $veiculo->valor_depreciacao_acumulada !== null ? (float) $veiculo->valor_depreciacao_acumulada : null;
        $finDiferencaFipeCompra = $veiculo->diferenca_fipe_compra !== null ? (float) $veiculo->diferenca_fipe_compra : null;
        $finPctDepreciacao = $veiculo->percentual_depreciacao_acumulada !== null ? (float) $veiculo->percentual_depreciacao_acumulada : null;
        $finTemProtecaoDepreciacao = (bool) $veiculo->tem_protecao_depreciacao;
        $finValorProtegidoDepreciacao = $veiculo->valor_protegido_depreciacao !== null ? (float) $veiculo->valor_protegido_depreciacao : null;
        $finRentabilidade = ($finValorCompra > 0) ? ($finLucroLiquido / $finValorCompra * 100) : null;

        // Payback: meses desde a compra e receita líquida média mensal
        $finPaybackMeses = null;
        if ($finValorCompra > 0 && !empty($veiculo->data_compra)) {
            $mesesUso = (int) Carbon::parse($veiculo->data_compra)->diffInMonths(Carbon::now());
            if ($mesesUso > 0 && $finLucroLiquido > 0) {
                $receitaMediaMensal = $finLucroLiquido / $mesesUso;
                $finPaybackMeses = (int) ceil($finValorCompra / $receitaMediaMensal);
            }
        }
        // ---

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
            'dispEstacoes',
            'finReceita',
            'finCustoMultas',
            'finCustoSinistros',
            'finLucroLiquido',
            'finValorCompra',
            'finValorFipe',
            'finValorAtualEstimado',
            'finDepreciacaoAcumulada',
            'finDiferencaFipeCompra',
            'finPctDepreciacao',
            'finTemProtecaoDepreciacao',
            'finValorProtegidoDepreciacao',
            'finRentabilidade',
            'finPaybackMeses'
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
        $modelosDetalhados = $this->modeloService->getModel()->newQuery()
            ->where('ativo', 'S')
            ->orderBy('nome')
            ->get(['id', 'nome', 'ano_modelo']);
        $modelos = $modelosDetalhados->pluck('nome', 'id')->all();
        $modelosAno = $modelosDetalhados->pluck('ano_modelo', 'id')->all();
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
            'modelosAno',
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

    public function buscarGruposModal(Request $request): JsonResponse
    {
        $queryTexto = trim((string) $request->input('q', ''));
        $tipoFrotaId = (int) $request->input('tipo_frota_id', 0);

        $query = $this->grupoFrotaService->getModel()->newQuery()
            ->from('grupos_frota')
            ->leftJoin('tipo_frotas', 'tipo_frotas.id', '=', 'grupos_frota.tipo_frota_id')
            ->select([
                'grupos_frota.id',
                'grupos_frota.nome',
                'grupos_frota.valor_diario',
                'tipo_frotas.nome as tipo_frota_nome',
            ])
            ->where('grupos_frota.ativo', 'S');

        if ($tipoFrotaId > 0) {
            $query->where('grupos_frota.tipo_frota_id', $tipoFrotaId);
        }

        if ($queryTexto !== '') {
            $query->where('grupos_frota.nome', 'like', '%' . $queryTexto . '%');
        }

        $items = $query
            ->orderBy('grupos_frota.nome')
            ->limit(50)
            ->get()
            ->map(function ($grupo) {
                $partes = [];
                if (!empty($grupo->tipo_frota_nome)) {
                    $partes[] = 'Tipo: ' . $grupo->tipo_frota_nome;
                }
                if ($grupo->valor_diario !== null) {
                    $partes[] = 'Diária: ' . number_format((float) $grupo->valor_diario, 2, ',', '.');
                }

                return [
                    'id' => $grupo->id,
                    'nome' => $grupo->nome,
                    'extra' => implode(' • ', $partes),
                ];
            })
            ->values();

        return response()->json(['items' => $items]);
    }

    public function buscarMarcasModal(Request $request): JsonResponse
    {
        $queryTexto = trim((string) $request->input('q', ''));
        $grupoFrotaId = (int) $request->input('grupo_frota_id', 0);

        $query = $this->marcaService->getModel()->newQuery()
            ->from('marcas')
            ->select(['id', 'nome', 'nome_fipe', 'codigo_fipe'])
            ->where('ativo', 'S');

        if ($grupoFrotaId > 0) {
            $query->whereHas('modelos', function ($sub) use ($grupoFrotaId) {
                $sub->where('modelos.ativo', 'S')
                    ->whereHas('gruposFrota', function ($grupos) use ($grupoFrotaId) {
                        $grupos->where('grupos_frota.id', $grupoFrotaId);
                    });
            });
        }

        if ($queryTexto !== '') {
            $query->where(function ($sub) use ($queryTexto) {
                $sub->where('marcas.nome', 'like', '%' . $queryTexto . '%')
                    ->orWhere('marcas.nome_fipe', 'like', '%' . $queryTexto . '%')
                    ->orWhere('marcas.codigo_fipe', 'like', '%' . $queryTexto . '%');
            });
        }

        $items = $query
            ->orderBy('marcas.nome')
            ->limit(50)
            ->get()
            ->map(function ($marca) {
                $partes = [];
                if (!empty($marca->codigo_fipe)) {
                    $partes[] = 'Código FIPE: ' . $marca->codigo_fipe;
                }
                if (!empty($marca->nome_fipe) && $marca->nome_fipe !== $marca->nome) {
                    $partes[] = 'FIPE: ' . $marca->nome_fipe;
                }

                return [
                    'id' => $marca->id,
                    'nome' => $marca->nome,
                    'extra' => implode(' • ', $partes),
                ];
            })
            ->values();

        return response()->json(['items' => $items]);
    }

    public function buscarModelosModal(Request $request): JsonResponse
    {
        $queryTexto = trim((string) $request->input('q', ''));
        $marcaId = (int) $request->input('marca_id', 0);

        $query = $this->modeloService->getModel()->newQuery()
            ->from('modelos')
            ->leftJoin('marcas as marcas_modelo', 'marcas_modelo.id', '=', 'modelos.marca_id')
            ->leftJoin('segmentos', 'segmentos.id', '=', 'modelos.segmento_id')
            ->select([
                'modelos.id',
                'modelos.nome',
                'modelos.codigo_fipe',
                'modelos.nome_fipe',
                'modelos.ano_modelo',
                'modelos.marca_id',
                'marcas_modelo.nome as marca_nome',
                'segmentos.nome as segmento_nome',
            ])
            ->where('modelos.ativo', 'S');

        if ($marcaId > 0) {
            $query->where('modelos.marca_id', $marcaId);
        }

        if ($queryTexto !== '') {
            $query->where(function ($sub) use ($queryTexto) {
                $sub->where('modelos.nome', 'like', '%' . $queryTexto . '%')
                    ->orWhere('marcas_modelo.nome', 'like', '%' . $queryTexto . '%')
                    ->orWhere('segmentos.nome', 'like', '%' . $queryTexto . '%')
                    ->orWhere('modelos.codigo_fipe', 'like', '%' . $queryTexto . '%')
                    ->orWhere('modelos.nome_fipe', 'like', '%' . $queryTexto . '%');
            });
        }

        $items = $query
            ->orderBy('modelos.nome')
            ->limit(50)
            ->get()
            ->map(function ($modelo) {
                $partes = [];
                if (!empty($modelo->marca_nome)) {
                    $partes[] = 'Marca: ' . $modelo->marca_nome;
                }
                if (!empty($modelo->ano_modelo)) {
                    $partes[] = 'Ano modelo: ' . $modelo->ano_modelo;
                }
                if (!empty($modelo->segmento_nome)) {
                    $partes[] = 'Segmento: ' . $modelo->segmento_nome;
                }
                if (!empty($modelo->codigo_fipe)) {
                    $partes[] = 'Código FIPE: ' . $modelo->codigo_fipe;
                }

                return [
                    'id' => $modelo->id,
                    'nome' => $modelo->nome,
                    'marca_id' => $modelo->marca_id,
                    'marca_nome' => $modelo->marca_nome,
                    'ano_modelo' => $modelo->ano_modelo,
                    'extra' => implode(' • ', $partes),
                ];
            })
            ->values();

        return response()->json(['items' => $items]);
    }

    public function setOrdenacao(Request $request)
    {
        $campo  = $request->input('orderBy', 'id');
        $dir    = $request->input('orderDir', 'asc');
        $allowed = ['id','nome','placa_licenca','renavam','ano_fabricacao','kms','created_at'];
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
            ->get(['id', 'nome', 'ano_modelo']);

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

        if ($empresaId && SchemaCache::hasColumn('veiculos', 'empresa_id')) {
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
            $query->whereHas('modelo', fn($m) => $m->where('ano_modelo', '>=', $v));
        }
        if ($v = (int) $request->input('ano_ate', 0)) {
            $query->whereHas('modelo', fn($m) => $m->where('ano_modelo', '<=', $v));
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
            'ano_mod'     => $v->modelo?->ano_modelo ?? '',
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
            $empresaId = $empresaContext->empresaId() ?? Auth::user()?->empresa_id;
        }

        if ($empresaId && SchemaCache::hasTable('empresas') && SchemaCache::hasColumn('empresas', 'reserva_bloqueio_pos_reserva_horas')) {
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
                ->whereIn('status_reserva_id', [StatusReservaEnum::Pendente->value, StatusReservaEnum::Confirmada->value, StatusReservaEnum::Checkout->value, StatusReservaEnum::NoShow->value])
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

        $estacoes = $this->estacaoService->getAllAtivos('id', 'nome', 'nome', 'asc');

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
        if ($empresaId !== null && SchemaCache::hasColumn($model->getTable(), 'empresa_id')) {
            $query->where($model->getTable() . '.empresa_id', $empresaId);
        }

        if (SchemaCache::hasColumn($model->getTable(), 'ativo')) {
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

    private function normalizarTextoFipe(string $valor): string
    {
        return Str::slug(trim($valor), '');
    }

    private function normalizarValorFipe(mixed $valor): ?float
    {
        if (!is_string($valor) || trim($valor) === '') {
            return null;
        }

        $limpo = trim(str_replace(['R$', ' '], '', $valor));
        $limpo = str_replace('.', '', $limpo);
        $limpo = str_replace(',', '.', $limpo);

        return is_numeric($limpo) ? round((float) $limpo, 2) : null;
    }
}
