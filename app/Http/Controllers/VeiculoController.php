<?php

namespace App\Http\Controllers;

use App\Actions\Veiculo\CreateVeiculoAction;
use App\Actions\Veiculo\AdicionarDisponibilidadeVeiculoAction;
use App\Actions\Veiculo\EditVeiculoAction;
use App\Actions\Veiculo\FiltroVeiculoAction;
use App\Actions\Veiculo\IndexVeiculoAction;
use App\Actions\Veiculo\StoreVeiculoAction;
use App\DTO\BreadCrumbItem;
use App\Enums\StatusReservaEnum;
use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\Veiculo\StoreVeiculoRequest;
use App\Http\Requests\Veiculo\AdicionarDisponibilidadeVeiculoRequest;
use App\Http\Requests\Veiculo\UpdateVeiculoRequest;
use App\Models\GrupoFrota;
use App\Models\Marca;
use App\Models\Modelo;
use App\Models\Veiculo;
use App\Models\Movimentacao;
use App\Models\Reserva;
use App\Models\StatusVeiculo;
use App\Models\TipoFrota;
use App\Services\BreadcrumbService;
use App\Services\DisponibilidadeVeiculoService;
use App\Services\EstacaoService;
use App\Services\FipeCatalogService;
use App\Services\GrupoFrotaService;
use App\Services\MarcaService;
use App\Services\ModeloService;
use App\Services\SegmentoService;
use App\Services\StatusVeiculoService;
use App\Services\TipoFrotaService;
use App\Services\VeiculoService;
use App\Support\EmpresaContext;
use App\Support\SchemaCache;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class VeiculoController extends BaseController implements FiltroInterface, ResourceInterface
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    protected $veiculoService;

    protected $breadcrumbService;

    protected $tipoFrotaService;

    protected $grupoFrotaService;

    protected $marcaService;

    protected $modeloService;

    protected $segmentoService;

    protected $statusVeiculoService;

    protected $estacaoService;

    protected $disponibilidadeVeiculoService;

    protected $fipeCatalogService;

    protected $indexVeiculoAction;

    protected $createVeiculoAction;

    protected $storeVeiculoAction;

    protected $adicionarDisponibilidadeVeiculoAction;

    protected $editVeiculoAction;

    protected $filtroVeiculoAction;

    public function __construct(
        VeiculoService $veiculoService,
        BreadcrumbService $breadcrumbService,
        TipoFrotaService $tipoFrotaService,
        GrupoFrotaService $grupoFrotaService,
        MarcaService $marcaService,
        ModeloService $modeloService,
        SegmentoService $segmentoService,
        StatusVeiculoService $statusVeiculoService,
        EstacaoService $estacaoService,
        DisponibilidadeVeiculoService $disponibilidadeVeiculoService,
        FipeCatalogService $fipeCatalogService,
        IndexVeiculoAction $indexVeiculoAction,
        CreateVeiculoAction $createVeiculoAction,
        StoreVeiculoAction $storeVeiculoAction,
        AdicionarDisponibilidadeVeiculoAction $adicionarDisponibilidadeVeiculoAction,
        EditVeiculoAction $editVeiculoAction,
        FiltroVeiculoAction $filtroVeiculoAction
    ) {
        $this->titulo = 'Veiculos';
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
        $this->disponibilidadeVeiculoService = $disponibilidadeVeiculoService;
        $this->fipeCatalogService = $fipeCatalogService;
        $this->indexVeiculoAction = $indexVeiculoAction;
        $this->createVeiculoAction = $createVeiculoAction;
        $this->storeVeiculoAction = $storeVeiculoAction;
        $this->adicionarDisponibilidadeVeiculoAction = $adicionarDisponibilidadeVeiculoAction;
        $this->editVeiculoAction = $editVeiculoAction;
        $this->filtroVeiculoAction = $filtroVeiculoAction;
    }

    public function index()
    {
        return view('veiculo.index', $this->indexVeiculoAction->handle($this))
            ->with('titulo', $this->titulo);
    }

    public function create()
    {
        return view('veiculo.add-edit', $this->createVeiculoAction->handle($this))
            ->with('titulo', $this->titulo);
    }

    public function store(StoreVeiculoRequest $request)
    {
        return $this->storeVeiculoAction->handle($request, $this->nomeFiltro);
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
            'ano_modelo' => ['nullable', 'integer', 'min:1900', 'max:'.(now()->year + 2)],
            'ano_fabricacao' => ['nullable', 'integer', 'min:1900', 'max:'.(now()->year + 2)],
        ]);

        $marca = Marca::query()->find($dados['marca_id']);
        $modelo = Modelo::query()->find($dados['modelo_id']);

        if (! $marca || ! $modelo) {
            return response()->json(['message' => 'Marca ou modelo não encontrado no cadastro local.'], 422);
        }

        $anoModelo = (int) ($dados['ano_fabricacao'] ?? $dados['ano_modelo'] ?? 0);
        if ($anoModelo <= 0) {
            $anoModelo = (int) ($modelo->ano_modelo ?? 0);
        }

        if ($anoModelo <= 0) {
            return response()->json(['message' => 'Informe o ano do veiculo para consultar a FIPE.'], 422);
        }

        $vehicleType = (string) ($modelo->vehicle_type ?? $marca->vehicle_type ?? config('services.fipe.vehicle_type', 'cars'));

        try {
            $marcas = $this->fipeCatalogService->fetchJsonForVehicleType($vehicleType, 'brands');
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

            if (! $marcaApi) {
                return response()->json(['message' => 'Nao foi possivel localizar a marca na FIPE.'], 422);
            }

            $codigoMarcaApi = (string) ($marcaApi['code'] ?? $marcaApi['codigo'] ?? '');
            $modelosResponse = $this->fipeCatalogService->fetchJsonForVehicleType(
                $vehicleType,
                'brands/'.$codigoMarcaApi.'/models'
            );

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

            if (! $modeloApi) {
                return response()->json(['message' => 'Nao foi possivel localizar o modelo na FIPE.'], 422);
            }

            $codigoModeloApi = (string) ($modeloApi['code'] ?? $modeloApi['codigo'] ?? '');
            $anosResponse = $this->fipeCatalogService->fetchJsonForVehicleType(
                $vehicleType,
                'brands/'.$codigoMarcaApi.'/models/'.$codigoModeloApi.'/years'
            );

            $anoApi = collect($anosResponse)->first(function (array $item) use ($anoModelo) {
                $descricaoAno = (string) ($item['name'] ?? $item['nome'] ?? '');

                return Str::contains($descricaoAno, (string) $anoModelo);
            });

            if (! $anoApi) {
                return response()->json(['message' => 'Nao foi possivel localizar o ano/modelo na FIPE.'], 422);
            }
            $detalhe = $this->fipeCatalogService->fetchJsonForVehicleType(
                $vehicleType,
                'brands/'.$codigoMarcaApi.'/models/'.$codigoModeloApi.'/years/'.$anoApi['code']
            );

            $veiculo = Veiculo::query()->find((int) $request->route('veiculo'));
            if ($veiculo) {
                $veiculo->fill([
                    'valor_fipe' => $this->normalizarValorFipe($detalhe['price'] ?? $detalhe['Valor'] ?? null),
                    'codigo_fipe' => $detalhe['codeFipe'] ?? $detalhe['code'] ?? $detalhe['CodigoFipe'] ?? null,
                    'referencia_fipe' => $detalhe['referenceMonth'] ?? $detalhe['reference'] ?? $detalhe['MesReferencia'] ?? null,
                    'data_consulta_fipe' => now()->toDateString(),
                ]);
                $veiculo->save();
                $veiculo->refresh();
            }

            $valorFipe = $veiculo?->valor_fipe !== null
                ? (float) $veiculo->valor_fipe
                : $this->normalizarValorFipe($detalhe['price'] ?? $detalhe['Valor'] ?? null);

            return response()->json([
                'marca' => $marcaApi['name'] ?? $marcaApi['nome'] ?? $marca->nome,
                'modelo' => $modeloApi['name'] ?? $modeloApi['nome'] ?? $modelo->nome,
                'ano_modelo' => $anoModelo,
                'codigo_fipe' => $veiculo?->codigo_fipe ?? ($detalhe['codeFipe'] ?? $detalhe['code'] ?? $detalhe['CodigoFipe'] ?? null),
                'referencia_fipe' => $veiculo?->referencia_fipe ?? ($detalhe['referenceMonth'] ?? $detalhe['reference'] ?? $detalhe['MesReferencia'] ?? null),
                'valor_fipe' => $valorFipe,
                'valor_fipe_formatado' => $detalhe['price'] ?? $detalhe['Valor'] ?? null,
                'combustivel' => $detalhe['fuel'] ?? $detalhe['Combustivel'] ?? null,
                'ano_fipe' => $detalhe['modelYear'] ?? $detalhe['AnoModelo'] ?? null,
                'data_consulta_fipe' => $veiculo?->data_consulta_fipe ? $veiculo->data_consulta_fipe->format('Y-m-d') : now()->format('Y-m-d'),
                'valor_compra' => $veiculo?->valor_compra,
                'valor_atual_estimado' => $veiculo?->valor_atual_estimado,
                'valor_depreciacao_acumulada' => $veiculo?->valor_depreciacao_acumulada,
                'taxa_depreciacao_anual' => $veiculo?->taxa_depreciacao_anual,
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
            'reservas.codigoReserva',
            'veiculoDanos.dano.categoria',
            'veiculoDanos.reservaCodigo',
            'veiculoDanos.reserva.codigoReserva',
            'veiculoDanos.contratoCheckin.reserva.codigoReserva',
            'ordensServico',
            'manutencoesPreventivas' => function ($query) {
                $query->with(['item', 'ordemServico:id,numero,status'])->orderByDesc('data_execucao')->orderByDesc('id');
            },
            'pendenciasManutencaoPreventiva' => function ($query) {
                $query->with('item')->orderByDesc('id');
            },
            'documentacoesVeiculo' => function ($query) {
                $query->with('categoriaFinanceira')->orderByDesc('id');
            },
            'multas' => function ($query) {
                $query->with(['reserva.codigoReserva', 'responsavelLancamento:id,nome'])
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
                    'reserva.codigoReserva',
                    'ordemServico:id,numero,status',
                ])->orderByDesc('id');
            },
        ]);

        if (! $veiculo) {
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

        if (
            $empresaId
            && SchemaCache::hasTable('empresas')
            && SchemaCache::hasColumn('empresas', 'reserva_bloqueio_pos_reserva_horas')
        ) {
            $reservaBloqueioHoras = (int) (
                DB::table('empresas')
                    ->where('id', (int) $empresaId)
                    ->value('reserva_bloqueio_pos_reserva_horas') ?? 0
            );

            if ($reservaBloqueioHoras < 0) {
                $reservaBloqueioHoras = 0;
            }
        }

        $dispDiasNoMes = (int) cal_days_in_month(CAL_GREGORIAN, $dispMes, $dispAno);
        $dispFirstDow = (int) Carbon::create($dispAno, $dispMes, 1)->dayOfWeekIso;

        /*
 * CorreÃƒÂ§ÃƒÂ£o:
 * A tela show agora usa o mesmo service da grade mensal.
 * Assim, index, show e disponibilidade seguem a mesma regra.
 */
        $resultadoDisponibilidadeShow = $this->disponibilidadeVeiculoService->montarGradeMensal(
            collect([$veiculo]),
            $dispAno,
            $dispMes
        );

        $dispGradeCompleta = $resultadoDisponibilidadeShow['grade'] ?? [];
        $dispResumoVeiculos = $resultadoDisponibilidadeShow['resumoVeiculos'] ?? [];
        $dispCards = $resultadoDisponibilidadeShow['cards'] ?? [];

        $dispGrade = $dispGradeCompleta[$veiculo->id] ?? [];
        $dispResumo = $dispResumoVeiculos[$veiculo->id] ?? [
            'percentual' => 0,
            'minutos_total' => 0,
            'minutos_disponiveis' => 0,
            'status_mes' => 'off',
            'dias_parciais' => 0,
            'dias_indisponiveis' => 0,
        ];

        $dispStats = [
            'ok' => 0,
            'partial' => 0,
            'reserved' => 0,
            'overdue' => 0,
            'off' => 0,
            'pending' => 0,
        ];

        foreach ($dispGrade as $cell) {
            $tipo = $cell['tipo'] ?? 'off';

            if (! array_key_exists($tipo, $dispStats)) {
                $dispStats[$tipo] = 0;
            }

            $dispStats[$tipo]++;
        }

        $dispInicioMes = Carbon::create($dispAno, $dispMes, 1)->startOfDay();
        $dispFimMesExclusivo = $dispInicioMes->copy()->addMonth()->startOfDay();
        $dispInicioMesComFolga = $dispInicioMes->copy()->subHours($reservaBloqueioHoras);
        $planoManutencao = $veiculo->planoManutencaoPreventiva;

        $dispReservas = Reserva::query()
            ->select(['id', 'veiculo_id', 'data_inicio', 'data_fim', 'status_reserva_id', 'user_id'])
            ->with(['statusReserva:id,nome'])
            ->where('veiculo_id', (int) $veiculo->id)
            ->whereIn('status_reserva_id', [
                StatusReservaEnum::Pendente->value,
                StatusReservaEnum::Confirmada->value,
                StatusReservaEnum::Checkin->value,
                StatusReservaEnum::Checkout->value,
                StatusReservaEnum::NoShow->value,
            ])
            ->where('data_inicio', '<', $dispFimMesExclusivo->toDateTimeString())
            ->where('data_fim', '>', $dispInicioMesComFolga->toDateTimeString())
            ->orderBy('data_inicio')
            ->get();

        $dispMovimentacoes = Movimentacao::query()
            ->with([
                'usuario:id,nome',
                'tipoMovimentacao:id,nome',
                'statusMovimentacao:id,nome,cor',
                'localOrigem:id,nome',
                'localDestino:id,nome',
                'reserva:id',
                'reserva.codigoReserva:id,reserva_id,codigo',
                'ordemServico:id,numero,status',
            ])
            ->select([
                'id',
                'nome',
                'veiculo_id',
                'tipo_movimentacao_id',
                'local_origem_id',
                'local_destino_id',
                'ordem_servico_id',
                'data_saida',
                'data_registro',
                'data_chegada_prevista',
                'data_chegada_real',
                'km_saida',
                'km_chegada',
                'status_movimentacao_id',
                'reserva_id',
                'observacoes',
                'user_id',
            ])
            ->where('veiculo_id', (int) $veiculo->id)
            ->whereIn('status_movimentacao_id', [1, 2, 5, 6, 7])
            ->where(function ($query) use ($dispFimMesExclusivo) {
                $query->whereNull('data_saida')
                    ->orWhere('data_saida', '<', $dispFimMesExclusivo->toDateTimeString());
            })
            ->where(function ($query) use ($dispInicioMes) {
                $query->whereNull('data_chegada_real')
                    ->orWhere('data_chegada_real', '>=', $dispInicioMes->toDateTimeString());
            })
            ->orderByDesc('id')
            ->get();

        $dispEstacoes = $this->estacaoService->getAllAtivos('id', 'nome', 'nome', 'asc');

        // --- Financeiro ---
        $finReceita = (float) $veiculo->reservas->sum('valor_total_final');
        $finCustoMultas = (float) $veiculo->multas->where('destino_cobranca', 'empresa')->sum('valor_final');
        $finCustoSinistros = (float) $veiculo->sinistros->sum('valor_custo_empresa');
        $finLucroLiquido = $finReceita - $finCustoMultas - $finCustoSinistros;

        $finValorCompra = (float) ($veiculo->valor_compra ?? 0);
        $finValorFipe = (float) ($veiculo->valor_fipe ?? 0);
        $finValorAtualEstimado = $veiculo->valor_atual_estimado !== null ? (float) $veiculo->valor_atual_estimado : null;
        $finDepreciacaoAcumulada = $veiculo->valor_depreciacao_acumulada !== null ? (float) $veiculo->valor_depreciacao_acumulada : null;
        $finDiferencaFipeCompra = $veiculo->diferenca_fipe_compra !== null ? (float) $veiculo->diferenca_fipe_compra : null;
        $finPctDepreciacao = $veiculo->percentual_depreciacao_acumulada !== null ? (float) $veiculo->percentual_depreciacao_acumulada : null;
        $finTemProtecaoDepreciacao = (bool) $veiculo->tem_protecao_depreciacao;
        $finValorProtegidoDepreciacao = $veiculo->valor_protegido_depreciacao !== null ? (float) $veiculo->valor_protegido_depreciacao : null;
        $finRentabilidade = ($finValorCompra > 0) ? ($finLucroLiquido / $finValorCompra * 100) : null;

        // Payback: meses desde a compra e receita lÃƒÂ­quida mÃƒÂ©dia mensal
        $finPaybackMeses = null;
        if ($finValorCompra > 0 && ! empty($veiculo->data_compra)) {
            $mesesUso = (int) Carbon::parse($veiculo->data_compra)->diffInMonths(Carbon::now());
            if ($mesesUso > 0 && $finLucroLiquido > 0) {
                $receitaMediaMensal = $finLucroLiquido / $mesesUso;
                $finPaybackMeses = (int) ceil($finValorCompra / $receitaMediaMensal);
            }
        }
        // ---

        $this->garantirRegistroInicialManutencaoPreventiva($veiculo);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Veiculos', route('veiculo.index')),
            new BreadCrumbItem($veiculo->nome ?? 'Detalhes'),
        ]);

        $titulo = 'Veículo: '.($veiculo->nome ?? '#'.$veiculo->id);

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
            'dispResumo',
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
            'finPaybackMeses',
            'planoManutencao'
        ));
    }

    public function concluirManutencaoPreventiva(Request $request, \App\Models\Veiculo $veiculo)
    {
        $dados = $request->validate([
            'item_id' => ['required', 'integer'],
        ]);

        $plano = \App\Models\PlanoManutencaoPreventiva::query()
            ->with('itens')
            ->where('grupo_frota_id', $veiculo->grupo_frota_id)
            ->firstOrFail();

        $planoItem = $plano->itens->firstWhere('item_id', (int) $dados['item_id']);
        if (! $planoItem) {
            return back()->with('error', 'Item não pertence ao plano deste grupo de frota.');
        }

        $pendencia = \App\Models\PendenciaManutencaoPreventiva::query()
            ->where('veiculo_id', $veiculo->id)
            ->where('item_id', (int) $dados['item_id'])
            ->whereIn('status', ['pendente', 'atrasado'])
            ->orderByDesc('id')
            ->first();

        if ($pendencia) {
            $pendencia->update(['status' => 'concluida']);
        }

        $kmAtual = (int) ($veiculo->kms ?? 0);
        $kmComPreventiva = $planoItem->frequencia_km !== null
            ? $kmAtual + (int) $planoItem->frequencia_km
            : $kmAtual;

        \App\Models\ExecucaoManutencaoPreventiva::create([
            'empresa_id' => $veiculo->empresa_id,
            'veiculo_id' => $veiculo->id,
            'item_id' => (int) $dados['item_id'],
            'ordem_servico_id' => null,
            'data_execucao' => now()->toDateString(),
            'km_execucao' => $kmComPreventiva,
        ]);

        if ($kmComPreventiva !== $kmAtual) {
            $veiculo->forceFill(['kms' => $kmComPreventiva])->save();
        }

        \App\Models\PendenciaManutencaoPreventiva::create([
            'empresa_id' => $veiculo->empresa_id,
            'veiculo_id' => $veiculo->id,
            'item_id' => (int) $dados['item_id'],
            'data_prevista' => $planoItem->frequencia_meses ? now()->addMonths((int) $planoItem->frequencia_meses)->toDateString() : null,
            'km_previsto' => $planoItem->frequencia_km ? ((int) ($veiculo->kms ?? 0) + (int) $planoItem->frequencia_km) : null,
            'status' => 'pendente',
        ]);

        return back()->with('success', 'ManutenÃƒÂ§ÃƒÂ£o marcada como concluÃƒÂ­da.');
    }

    private function garantirRegistroInicialManutencaoPreventiva(\App\Models\Veiculo $veiculo): void
    {
        if (! $veiculo->grupo_frota_id) {
            return;
        }

        $plano = \App\Models\PlanoManutencaoPreventiva::query()
            ->with('itens')
            ->where('grupo_frota_id', $veiculo->grupo_frota_id)
            ->first();

        if (! $plano || $plano->itens->isEmpty()) {
            return;
        }

        $veiculo->loadMissing(['manutencoesPreventivas', 'pendenciasManutencaoPreventiva']);

        foreach ($plano->itens as $planoItem) {
            $itemId = (int) $planoItem->item_id;

            $jaTemExecucao = $veiculo->manutencoesPreventivas->where('item_id', $itemId)->isNotEmpty();
            if ($jaTemExecucao) {
                continue;
            }

            \App\Models\ExecucaoManutencaoPreventiva::create([
                'empresa_id' => $veiculo->empresa_id,
                'veiculo_id' => $veiculo->id,
                'item_id' => $itemId,
                'ordem_servico_id' => null,
                'data_execucao' => now()->toDateString(),
                'km_execucao' => (int) ($veiculo->kms ?? 0),
            ]);

            $jaTemPendencia = $veiculo->pendenciasManutencaoPreventiva->where('item_id', $itemId)->isNotEmpty();
            if ($jaTemPendencia) {
                continue;
            }

            \App\Models\PendenciaManutencaoPreventiva::create([
                'empresa_id' => $veiculo->empresa_id,
                'veiculo_id' => $veiculo->id,
                'item_id' => $itemId,
                'data_prevista' => $planoItem->frequencia_meses ? now()->addMonths((int) $planoItem->frequencia_meses)->toDateString() : null,
                'km_previsto' => $planoItem->frequencia_km ? ((int) ($veiculo->kms ?? 0) + (int) $planoItem->frequencia_km) : null,
                'status' => 'pendente',
            ]);
        }
    }

    public function edit($id)
    {
        return view('veiculo.add-edit', $this->editVeiculoAction->handle($this, $id))
            ->with('titulo', $this->titulo);
    }

    public function update(UpdateVeiculoRequest $request, $id)
    {
        $veiculo = $this->veiculoService->update($id, $request->all());

        if ($request->expectsJson()) {
            if (! $veiculo) {
                return response()->json(['message' => 'Veiculo nao encontrado.'], 404);
            }

            $veiculo->refresh();

            return response()->json([
                'message' => 'Veiculo atualizado com sucesso.',
                'payload' => [
                    'valor_compra' => $veiculo->valor_compra,
                    'data_compra' => $veiculo->data_compra ? $veiculo->data_compra->format('Y-m-d') : null,
                    'taxa_depreciacao_anual' => $veiculo->taxa_depreciacao_anual,
                    'valor_fipe' => $veiculo->valor_fipe,
                    'codigo_fipe' => $veiculo->codigo_fipe,
                    'referencia_fipe' => $veiculo->referencia_fipe,
                    'data_consulta_fipe' => $veiculo->data_consulta_fipe ? $veiculo->data_consulta_fipe->format('Y-m-d') : null,
                    'valor_atual_estimado' => $veiculo->valor_atual_estimado,
                    'valor_depreciacao_acumulada' => $veiculo->valor_depreciacao_acumulada,
                ],
            ]);
        }

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function alterarDepreciacao(Request $request, $veiculo)
    {
        $dados = $request->validate([
            'valor_compra' => ['nullable', 'numeric', 'min:0'],
            'data_compra' => ['nullable', 'date'],
            'taxa_depreciacao_anual' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $veiculoAtualizado = $this->veiculoService->update($veiculo, $dados);

        if (! $veiculoAtualizado) {
            return response()->json(['message' => 'Veiculo nao encontrado.'], 404);
        }

        $veiculoAtualizado->refresh();

        return response()->json([
            'message' => 'Depreciacao atualizada com sucesso.',
            'payload' => [
                'valor_compra' => $veiculoAtualizado->valor_compra,
                'data_compra' => $veiculoAtualizado->data_compra
                    ? Carbon::parse($veiculoAtualizado->data_compra)->format('Y-m-d')
                    : null,
                'taxa_depreciacao_anual' => $veiculoAtualizado->taxa_depreciacao_anual,
                'valor_atual_estimado' => $veiculoAtualizado->valor_atual_estimado,
                'valor_depreciacao_acumulada' => $veiculoAtualizado->valor_depreciacao_acumulada,
            ],
        ]);
    }

    public function adicionarDisponibilidade(AdicionarDisponibilidadeVeiculoRequest $request, $veiculo): JsonResponse
    {
        $resultado = $this->adicionarDisponibilidadeVeiculoAction->handle((int) $veiculo, $request->validated());

        return response()->json([
            'message' => $resultado['message'],
            'payload' => $resultado['payload'] ?? [],
        ], $resultado['status'] ?? 200);
    }

    public function destroy($id)
    {
        $veiculo = $this->veiculoService->getById($id);

        if (! $veiculo) {
            return redirect()
                ->route('veiculo.index')
                ->with('error', 'Veículo não encontrado.');
        }

        $reservasBloqueadoras = Reserva::query()
            ->where('veiculo_id', (int) $veiculo->id)
            ->whereIn('status_reserva_id', [
                StatusReservaEnum::Pendente->value,
                StatusReservaEnum::Confirmada->value,
                StatusReservaEnum::Checkin->value,
                StatusReservaEnum::Checkout->value,
            ])
            ->where(function ($query) {
                $query->whereNull('data_fim')
                    ->orWhere('data_fim', '>=', now());
            })
            ->with('codigoReserva:id,reserva_id,codigo')
            ->orderBy('data_inicio')
            ->get();

        if ($reservasBloqueadoras->isNotEmpty()) {
            $codigos = $reservasBloqueadoras
                ->map(fn ($reserva) => $reserva->codigoReserva?->codigo ?? ('#'.$reserva->id))
                ->implode(', ');

            return redirect()
                ->route('veiculo.index')
                ->with(
                    'error',
                    'O veiculo não pode ser arquivado porque possui reservas futuras ou em andamento: '.$codigos.'. Troque o veiculo ou finalize/cancele essas reservas primeiro.'
                );
        }

        DB::transaction(function () use ($veiculo) {
            $dadosAtualizacao = [];

            if (SchemaCache::hasColumn('veiculos', 'ativo')) {
                $dadosAtualizacao['ativo'] = 0;
            }

            if (SchemaCache::hasColumn('veiculos', 'arquivado_em')) {
                $dadosAtualizacao['arquivado_em'] = now();
            }

            if (SchemaCache::hasColumn('veiculos', 'arquivado_por')) {
                $dadosAtualizacao['arquivado_por'] = Auth::id();
            }

            if (SchemaCache::hasColumn('veiculos', 'situacao_cadastral')) {
                $dadosAtualizacao['situacao_cadastral'] = 'arquivado';
            }

            if (SchemaCache::hasColumn('veiculos', 'status_veiculo_id')) {
                $statusArquivadoId = StatusVeiculo::query()
                    ->where(function ($query) {
                        $query->whereRaw('LOWER(nome) = ?', ['arquivado'])
                            ->orWhereRaw('LOWER(nome) = ?', ['inativo']);
                    })
                    ->value('id');

                if ($statusArquivadoId) {
                    $dadosAtualizacao['status_veiculo_id'] = $statusArquivadoId;
                }
            }

            if (! empty($dadosAtualizacao)) {
                DB::table('veiculos')
                    ->where('id', (int) $veiculo->id)
                    ->update($dadosAtualizacao);
            }

            if (SchemaCache::hasTable('movimentacoes')) {
                $movimentacao = [
                    'veiculo_id' => (int) $veiculo->id,
                ];

                if (SchemaCache::hasColumn('movimentacoes', 'nome')) {
                    $movimentacao['nome'] = 'Arquivamento do veiculo';
                }

                if (SchemaCache::hasColumn('movimentacoes', 'observacoes')) {
                    $movimentacao['observacoes'] = 'Veículo arquivado e retirado da operaÃƒÂ§ÃƒÂ£o pelo usuÃƒÂ¡rio '.(Auth::user()?->nome ?? Auth::id() ?? 'não identificado').'.';
                }

                if (SchemaCache::hasColumn('movimentacoes', 'user_id')) {
                    $movimentacao['user_id'] = Auth::id();
                }

                if (SchemaCache::hasColumn('movimentacoes', 'data_registro')) {
                    $movimentacao['data_registro'] = now();
                }

                if (SchemaCache::hasColumn('movimentacoes', 'created_at')) {
                    $movimentacao['created_at'] = now();
                }

                if (SchemaCache::hasColumn('movimentacoes', 'updated_at')) {
                    $movimentacao['updated_at'] = now();
                }

                DB::table('movimentacoes')->insert($movimentacao);
            }
        });

        return redirect()
            ->route('veiculo.index')
            ->with('success', 'Veículo arquivado com sucesso. Todo o histÃƒÂ³rico foi preservado.');
    }

    public function gruposPorTipo(Request $request)
    {
        $tipoId = (int) $request->input('tipo_frota_id');
        $grupos = \App\Models\GrupoFrota::where('ativo', 'S')
            ->when($tipoId, fn ($q) => $q->where('tipo_frota_id', $tipoId))
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
            $query->where('grupos_frota.nome', 'like', '%'.$queryTexto.'%');
        }

        $items = $query
            ->orderBy('grupos_frota.nome')
            ->limit(50)
            ->get()
            ->map(function ($grupo) {
                $partes = [];
                if (! empty($grupo->tipo_frota_nome)) {
                    $partes[] = 'Tipo: '.$grupo->tipo_frota_nome;
                }
                if ($grupo->valor_diario !== null) {
                    $partes[] = 'Diária: '.number_format((float) $grupo->valor_diario, 2, ',', '.');
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
                $sub->where('marcas.nome', 'like', '%'.$queryTexto.'%')
                    ->orWhere('marcas.nome_fipe', 'like', '%'.$queryTexto.'%')
                    ->orWhere('marcas.codigo_fipe', 'like', '%'.$queryTexto.'%');
            });
        }

        $items = $query
            ->orderBy('marcas.nome')
            ->limit(50)
            ->get()
            ->map(function ($marca) {
                $partes = [];
                if (! empty($marca->codigo_fipe)) {
                    $partes[] = 'Código FIPE: '.$marca->codigo_fipe;
                }
                if (! empty($marca->nome_fipe) && $marca->nome_fipe !== $marca->nome) {
                    $partes[] = 'FIPE: '.$marca->nome_fipe;
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
        $grupoFrotaId = (int) $request->input('grupo_frota_id', 0);

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

        if ($grupoFrotaId > 0) {
            $query->whereHas('gruposFrota', function ($sub) use ($grupoFrotaId) {
                $sub->where('grupos_frota.id', $grupoFrotaId);
            });
        }

        if ($queryTexto !== '') {
            $query->where(function ($sub) use ($queryTexto) {
                $sub->where('modelos.nome', 'like', '%'.$queryTexto.'%')
                    ->orWhere('marcas_modelo.nome', 'like', '%'.$queryTexto.'%')
                    ->orWhere('segmentos.nome', 'like', '%'.$queryTexto.'%')
                    ->orWhere('modelos.codigo_fipe', 'like', '%'.$queryTexto.'%')
                    ->orWhere('modelos.nome_fipe', 'like', '%'.$queryTexto.'%');
            });
        }

        $items = $query
            ->orderBy('modelos.nome')
            ->limit(50)
            ->get()
            ->map(function ($modelo) {
                $partes = [];
                if (! empty($modelo->marca_nome)) {
                    $partes[] = 'Marca: '.$modelo->marca_nome;
                }
                if (! empty($modelo->ano_modelo)) {
                    $partes[] = 'Ano modelo: '.$modelo->ano_modelo;
                }
                if (! empty($modelo->segmento_nome)) {
                    $partes[] = 'Segmento: '.$modelo->segmento_nome;
                }
                if (! empty($modelo->codigo_fipe)) {
                    $partes[] = 'Código FIPE: '.$modelo->codigo_fipe;
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
        $campo = $request->input('orderBy', 'id');
        $dir = $request->input('orderDir', 'asc');
        $allowed = ['id', 'nome', 'placa_licenca', 'renavam', 'ano_fabricacao', 'kms', 'created_at'];
        if (! in_array($campo, $allowed)) {
            $campo = 'id';
        }
        if (! in_array($dir, ['asc', 'desc'])) {
            $dir = 'asc';
        }
        session(['veiculo.orderBy' => $campo, 'veiculo.orderDir' => $dir]);

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function modelosPorMarca(Request $request)
    {
        $marcaId = (int) $request->input('marca_id');
        $modelos = \App\Models\Modelo::where('ativo', 'S')
            ->when($marcaId, fn ($q) => $q->where('marca_id', $marcaId))
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
            $query->whereHas('modelo', fn ($m) => $m->where('ano_modelo', '>=', $v));
        }
        if ($v = (int) $request->input('ano_ate', 0)) {
            $query->whereHas('modelo', fn ($m) => $m->where('ano_modelo', '<=', $v));
        }
        if ($v = (int) $request->input('kms_ate', 0)) {
            $query->where('veiculos.kms', '<=', $v);
        }

        $veiculos = $query->orderBy('veiculos.placa_licenca')->limit(80)->get()->map(fn ($v) => [
            'id' => $v->id,
            'nome' => $v->nome ?? ('Veiculo #'.$v->id),
            'placa' => $v->placa_licenca ?? '',
            'chassi' => $v->chassi ?? '',
            'marca' => $v->marca?->nome ?? '',
            'modelo' => $v->modelo?->nome ?? '',
            'grupo' => $v->grupoFrota?->nome ?? '',
            'tipo' => $v->tipoFrota?->nome ?? '',
            'status' => $v->statusVeiculo?->nome ?? '',
            'ano_fab' => $v->ano_fabricacao ?? '',
            'ano_mod' => $v->modelo?->ano_modelo ?? '',
            'cor' => $v->cor ?? '',
            'kms' => $v->kms !== null ? number_format((float) $v->kms, 0, ',', '.') : '',
            'ativo' => $v->ativo ? 'Sim' : 'não',
            'estacao' => $v->localDisponibilidade?->nome ?? '',
        ]);

        return response()->json(['veiculos' => $veiculos]);
    }

    public function disponibilidade(Request $request)
    {
        $titulo = 'Disponibilidade de Veiculos';
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex($titulo);

        $anos = range(2024, date('Y') + 1);

        $meses = [
            1 => 'Janeiro',
            2 => 'Fevereiro',
            3 => 'MarÃƒÂ§o',
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

        $mes = (int) ($request->input('mes') ?? date('n'));
        $ano = (int) ($request->input('ano') ?? date('Y'));

        if ($mes < 1 || $mes > 12) {
            $mes = (int) date('n');
        }

        if ($ano < 2024 || $ano > ((int) date('Y') + 1)) {
            $ano = (int) date('Y');
        }

        $estacaoFiltro = $request->input('estacao_id');

        $veiculos = $this->veiculoService->getVeiculosDisponiveisPorUltimaMovimentacao($estacaoFiltro, 1000);

        $veiculosCollection = method_exists($veiculos, 'getCollection')
            ? $veiculos->getCollection()
            : collect($veiculos);

        $resultadoDisponibilidade = $this->disponibilidadeVeiculoService->montarGradeMensal(
            $veiculosCollection,
            $ano,
            $mes
        );

        $diasDoMes = $resultadoDisponibilidade['diasDoMes'];
        $grade = $resultadoDisponibilidade['grade'];
        $resumoVeiculos = $resultadoDisponibilidade['resumoVeiculos'];
        $reservaBloqueioHoras = $resultadoDisponibilidade['reservaBloqueioHoras'];

        $cards = $resultadoDisponibilidade['cards'];

        $totalVeiculos = $cards['total'];
        $veiculosDisponiveis = $cards['disponiveis'];
        $veiculosParciais = $cards['parciais'];
        $veiculosIndisponiveis = $cards['indisponiveis'];

        $estacoes = $this->estacaoService->getAllAtivos('id', 'nome', 'nome', 'asc');

        return view('veiculo.disponibilidade', compact(
            'totalVeiculos',
            'veiculosDisponiveis',
            'veiculosParciais',
            'veiculosIndisponiveis',
            'diasDoMes',
            'veiculos',
            'grade',
            'resumoVeiculos',
            'reservaBloqueioHoras',
            'anos',
            'meses',
            'estacoes',
            'titulo',
            'breadcrumb',
            'mes',
            'ano'
        ));
    }

    public function documentacao()
    {
        $titulo = 'Documentação de Veículos';
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex($titulo);

        $itens = [
            ['titulo' => 'Documento do veículo', 'descricao' => 'Cadastro principal com placa, RENAVAM, chassi, motor e datas de validade.'],
            ['titulo' => 'Licenciamento', 'descricao' => 'Controle da placa/licença e sua validade.'],
            ['titulo' => 'Seguro', 'descricao' => 'Cadastro de apÃƒÂ³lice, vigÃƒÂªncia e status.'],
            ['titulo' => 'IPVA', 'descricao' => 'Controle de vencimento e situaÃƒÂ§ÃƒÂ£o do imposto.'],
            ['titulo' => 'PendÃƒÂªncias', 'descricao' => 'SinalizaÃƒÂ§ÃƒÂ£o de qualquer documento vencido ou ausente.'],
        ];

        return view('veiculo.documentacao', compact('titulo', 'breadcrumb', 'itens'));
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
        // Salva o valor de paginaÃƒÂ§ÃƒÂ£o na sessÃƒÂ£o
        $request->session()->put("$this->nomeFiltro.perPage", $request->input('perPage'));

        // Redireciona de volta para a pÃƒÂ¡gina de listagem
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function configurarSessao()
    {
        session()->put("$this->nomeFiltro.urlPadrao", 'veiculo');
        session()->put("$this->nomeFiltro.rotaRedirect", 'veiculo.index');
    }

    public function getNomeFiltro(): string
    {
        return $this->nomeFiltro;
    }

    public function getTitulo(): string
    {
        return $this->titulo;
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
            // Adicione filtros secundÃƒÂ¡rios aqui, se necessÃƒÂ¡rio
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
            $query->where($model->getTable().'.empresa_id', $empresaId);
        }

        if (SchemaCache::hasColumn($model->getTable(), 'ativo')) {
            $query->where($model->getTable().'.ativo', 'S');
        }

        $items = $query->orderBy('nome')->pluck('nome', 'id')->toArray();

        return FiltroTrait::formatOptions($items, $label);
    }

    /**
     * FunÃƒÂ§ÃƒÂ£o para obter o mÃƒÂ©todo e a rota para o formulÃƒÂ¡rio.
     *
     * @param  Veiculo|null  $veiculo
     * @return array
     */
    public function getFormMethodAndRoute($veiculo = null)
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
        if (! is_string($valor) || trim($valor) === '') {
            return null;
        }

        $limpo = trim(str_replace(['R$', ' '], '', $valor));
        $limpo = str_replace('.', '', $limpo);
        $limpo = str_replace(',', '.', $limpo);

        return is_numeric($limpo) ? round((float) $limpo, 2) : null;
    }
}
