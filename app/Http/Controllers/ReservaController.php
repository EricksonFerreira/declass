<?php

namespace App\Http\Controllers;

use App\DTO\BreadCrumbItem;
use App\Enums\StatusReservaEnum;
use App\Exceptions\CartaoNaoCadastradoException;
use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\Reserva\AdicionarDanoVeiculoRequest;
use App\Http\Requests\Reserva\AssinarContratoRequest;
use App\Http\Requests\Reserva\CriarContratoCheckInRequest;
use App\Http\Requests\Reserva\FinalizarCheckoutVistoriaRequest;
use App\Http\Requests\Calcao\AutorizarCalcaoRequest;
use App\Http\Requests\Calcao\CapturarCalcaoRequest;
use App\Http\Requests\Reserva\StoreReservaRequest;
use App\Http\Requests\Reserva\UpdateReservaRequest;
use App\Models\Calcao;
use App\Models\ContratoCheckout;
use App\Models\Estacao;
use App\Models\Feriado;
use App\Models\Multa;
use App\Models\Transacao;
use App\Services\CalcaoService;
use App\Services\UpgradeVeiculoService;
use App\Services\BreadcrumbService;
use App\Services\CategoriaDanoService;
use App\Services\ClienteStripeService;
use App\Services\ContratoCheckinService;
use App\Services\EstacaoService;
use App\Services\GrupoFrotaService;
use App\Services\MarcaService;
use App\Services\ModeloService;
use App\Services\MovimentacaoService;
use App\Services\NfseService;
use App\Services\OpcionalService;
use App\Services\Pagamentos\GatewayEmpresaConfigService;
use App\Services\Pagamentos\GerenciadorDePagamentos;
use App\Services\PdfService;
use App\Services\PaisService;
use App\Services\ReservaService;
use App\Services\RegraService;
use App\Services\StatusReservaService;
use App\Services\TipoOpcionalService;
use App\Services\UsuarioService;
use App\Services\VeiculoDanoService;
use App\Services\VeiculoService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\LogTrait;
use App\Models\Reserva;
use App\Models\Veiculo;
use DB;
use Illuminate\Http\Request;
use App\Traits\FiltroTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use App\Support\SchemaCache;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ReservaController extends BaseController implements FiltroInterface, ResourceInterface
{
    use FiltroTrait;
    use AtualizaFiltrosTrait;

    protected $reservaService;
    protected $breadcrumbService;
    protected UpgradeVeiculoService $upgradeVeiculoService;
    protected CalcaoService $calcaoService;
    protected $userService;
    protected $veiculoService;
    protected $estacaoService;
    protected $grupoFrotaService;
    protected $tipoOpcionalService;
    protected $opcionalService;
    protected $clienteStripeService;
    protected $categoriaDanoService;
    protected $marcaService;
    protected $veiculoDanoService;
    protected $modeloService;
    protected $contratoCheckinService;
    protected $movimentacaoService;
    protected $pdfService;
    protected $gerenciadorDePagamentos;
    protected $gatewayEmpresaConfigService;
    protected $regraService;
    protected $paisService;
    protected $nfseService;
    protected $statusReservaService;

    public function __construct(
        ReservaService $reservaService,
        BreadcrumbService $breadcrumbService,
        UsuarioService $userService,
        VeiculoService $veiculoService,
        EstacaoService $estacaoService,
        GrupoFrotaService $grupoFrotaService,
        TipoOpcionalService $tipoOpcionalService,
        OpcionalService $opcionalService,
        ModeloService $modeloService,
        ClienteStripeService $clienteStripeService,
        CategoriaDanoService $categoriaDanoService,
        MarcaService $marcaService,
        VeiculoDanoService $veiculoDanoService,
        ContratoCheckinService $contratoCheckinService,
        MovimentacaoService $movimentacaoService,
        PdfService $pdfService,
        GerenciadorDePagamentos $gerenciadorDePagamentos,
        GatewayEmpresaConfigService $gatewayEmpresaConfigService,
        RegraService $regraService,
        PaisService $paisService,
        NfseService $nfseService,
        StatusReservaService $statusReservaService,
        UpgradeVeiculoService $upgradeVeiculoService,
        CalcaoService $calcaoService
    ) {
        $this->titulo = "Reservas";
        $this->nomeFiltro = 'reserva';
        $this->reservaService = $reservaService;
        $this->breadcrumbService = $breadcrumbService;
        $this->userService = $userService;
        $this->veiculoService = $veiculoService;
        $this->estacaoService = $estacaoService;
        $this->grupoFrotaService = $grupoFrotaService;
        $this->tipoOpcionalService = $tipoOpcionalService;
        $this->modeloService = $modeloService;
        $this->opcionalService = $opcionalService;
        $this->clienteStripeService = $clienteStripeService;
        $this->categoriaDanoService = $categoriaDanoService;
        $this->marcaService = $marcaService;
        $this->veiculoDanoService = $veiculoDanoService;
        $this->contratoCheckinService = $contratoCheckinService;
        $this->movimentacaoService = $movimentacaoService;
        $this->pdfService = $pdfService;
        $this->gerenciadorDePagamentos = $gerenciadorDePagamentos;
        $this->gatewayEmpresaConfigService = $gatewayEmpresaConfigService;
        $this->regraService = $regraService;
        $this->paisService = $paisService;
        $this->nfseService = $nfseService;
        $this->statusReservaService = $statusReservaService;
        $this->upgradeVeiculoService = $upgradeVeiculoService;
        $this->calcaoService = $calcaoService;
    }

    public function index()
    {
        $this->configurarSessao();

        $user = Auth::user();
        if ($user && !$user->isAdministrador() && $this->isCliente($user)) {
            $query = Reserva::query()
                ->with(['estacaoRetirada', 'estacaoDevolucao', 'grupoFrota', 'modelos', 'statusReserva', 'nfseAtual'])
                ->where('user_id', $user->id)
                ->orderBy('id', 'desc');

            if (SchemaCache::hasColumn('reservas', 'empresa_id') && $user->empresa_id) {
                $query->where('empresa_id', $user->empresa_id);
            }

            $reservas = $query->paginate(10);
            $multas = $this->multasDoCliente($user);
            $multasPendentesCount = $this->multasPendentesCount($user);

            return view('reserva.index-cliente', [
                'reservas' => $reservas,
                'multas' => $multas,
                'multasPendentesCount' => $multasPendentesCount,
                'titulo' => 'Minhas reservas',
            ]);
        }

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $reservas = $this->reservaService->getAllWithFiltersAndPagination(
            $itensFiltered,
            $perPage,
            ['opcionais', 'cupomDesconto'],
            'id',
            'desc'
        );

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Reservas');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];

        return view('reserva.index', compact(
            'reservas',
            'breadcrumb',
            'filters',
            'itensFiltered',
            'additionalFilters',
            'perPages',
            'perPage'
        ))->with('titulo', $this->titulo);
    }

    public function minhas()
    {
        $user    = Auth::user();
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $query = Reserva::query()
            ->with(['opcionais', 'cupomDesconto', 'usuario', 'estacaoRetirada', 'estacaoDevolucao', 'grupoFrota', 'modelos', 'statusReserva', 'nfseAtual'])
            ->where('user_id', $user->id)
            ->orderBy('id', 'desc');

        if (SchemaCache::hasColumn('reservas', 'empresa_id') && $user->empresa_id) {
            $query->where('empresa_id', $user->empresa_id);
        }

        $reservas = $query->paginate($perPage);
        $multas   = $this->multasDoCliente($user);
        $multasPendentesCount = $this->multasPendentesCount($user);

        $breadcrumb        = $this->breadcrumbService->prepararBreadcrumbIndex('Minhas Reservas');
        $perPages          = [5, 10, 15, 25, 50, 100];
        $filters           = [];
        $additionalFilters = [];
        $itensFiltered     = [];

        return view('reserva.index', compact(
            'reservas',
            'breadcrumb',
            'filters',
            'itensFiltered',
            'additionalFilters',
            'perPages',
            'perPage',
            'multasPendentesCount'
        ))->with('titulo', 'Minhas Reservas');
    }

    private function isCliente($user): bool
    {
        $roleName = strtolower(trim((string) ($user?->tipoUsuario?->name ?? '')));
        return $roleName === 'cliente';
    }

    private function multasDoCliente($user)
    {
        if (!$user) {
            return collect();
        }

        $query = Multa::query()
            ->with(['reserva.usuario', 'veiculo.modelo', 'cliente'])
            ->whereNull('pago_em')
            ->orderByRaw('CASE WHEN data_vencimento IS NULL THEN 1 ELSE 0 END')
            ->orderBy('data_vencimento')
            ->orderByDesc('id');

        if (SchemaCache::hasColumn('multas', 'empresa_id') && !empty($user->empresa_id)) {
            $query->where('empresa_id', (int) $user->empresa_id);
        }

        $query->where(function ($subQuery) use ($user): void {
            $subQuery->where('cliente_id', $user->id)
                ->orWhereHas('reserva', function ($reservaQuery) use ($user): void {
                    $reservaQuery->where('user_id', $user->id);
                });
        });

        return $query->limit(12)->get();
    }

    private function multasPendentesCount($user): int
    {
        if (!$user) {
            return 0;
        }

        $query = Multa::query()->whereNull('pago_em');

        if (SchemaCache::hasColumn('multas', 'empresa_id') && !empty($user->empresa_id)) {
            $query->where('empresa_id', (int) $user->empresa_id);
        }

        $query->where(function ($subQuery) use ($user): void {
            $subQuery->where('cliente_id', $user->id)
                ->orWhereHas('reserva', function ($reservaQuery) use ($user): void {
                    $reservaQuery->where('user_id', $user->id);
                });
        });

        return (int) $query->count();
    }

    public function create()
    {
        $tituloFormulario = 'Cadastro de Reservas';
        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo'
        ];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Reservas', 'reserva.index', 'Criar');
        $usuarioAutenticado = Auth::user()?->loadMissing(['estacoesPermitidas']);
        $estacoesPermitidasUsuario = $usuarioAutenticado && ! $usuarioAutenticado->hasAcessoTodasEstacoes()
            ? $usuarioAutenticado->estacoesPermitidas->sortBy('nome')->values()
            : collect();

        // Consultando as variáveis necessárias
        $users = $this->userService->getAllForReservaSelect();
        $estacoesRetirada = $this->estacaoService->getEstacoesParaRetiradaSelect();
        $estacoesDevolucao = $this->estacaoService->getEstacoesParaDevolucaoSelect();
        $gruposVeiculo = $this->grupoFrotaService->getAllAtivosForSelectSubText('id', 'nome', 'valor_diario', null, 'asc');
        $modelosAtivos = $this->modeloService->getAllAtivos();
        $estacoes = $this->unirEstacoesReserva($estacoesRetirada, $estacoesDevolucao);
        $resumoVeiculosEstacoes = $this->montarResumoVeiculosPorEstacao($estacoes);
        $preRequisitosReserva = $this->montarPreRequisitosReserva(
            $estacoes,
            $gruposVeiculo,
            $modelosAtivos,
            $resumoVeiculosEstacoes
        );
        if (($preRequisitosReserva['qtd_estacoes'] ?? 0) <= 0 && $estacoesPermitidasUsuario->isNotEmpty()) {
            $preRequisitosReserva['faltas'] = array_values(array_filter(
                $preRequisitosReserva['faltas'],
                static fn (string $falta): bool => !str_contains(mb_strtolower($falta), 'estacao ativa para iniciar reservas')
            ));
            $preRequisitosReserva['faltas'][] = 'Você possui estações no perfil, mas nenhuma está habilitada para retirada/devolução nesta reserva.';
        }
        $moedaEmpresa = 'BRL';
        $tiposUsuario = $this->regraService->getAllTiposUsuarios();
        $paises = $this->paisService->getAllForSelectSigla();
        $empresaAtual = $this->gatewayEmpresaConfigService->empresaAtual();
        if ($empresaAtual) {
            $moedaEmpresa = $this->gatewayEmpresaConfigService->moeda($empresaAtual);
        }
        $simboloMoedaEmpresa = $this->simboloMoeda($moedaEmpresa);

        $tiposOpcionais = $this->tipoOpcionalService->getTipoOpcionais('nome', 'asc');

        [$metodo, $rota] = $this->getFormMethodAndRoute();
        $usuarioAutenticado = Auth::user()?->loadMissing(['estacoesPermitidas']);
        $estacoesPermitidasUsuario = $usuarioAutenticado && ! $usuarioAutenticado->hasAcessoTodasEstacoes()
            ? $usuarioAutenticado->estacoesPermitidas->sortBy('nome')->values()
            : collect();
        $estacoesBloqueadasReserva = $this->montarEstacoesBloqueadasReserva($estacoesPermitidasUsuario, array_keys($estacoes));
        $resetWizardStep = Auth::user()?->isAdministrador() ? true : false;
        $reservaSelecionarModelo = ($empresaAtual?->reserva_selecionar_modelo ?? 'N') === 'S';

        return view('reserva.add-edit', compact(
            'breadcrumb',
            'status',
            'metodo',
            'rota',
            'users',
            'gruposVeiculo',
            'estacoesRetirada',
            'estacoesDevolucao',
            'estacoesPermitidasUsuario',
            'estacoesBloqueadasReserva',
            'preRequisitosReserva',
            'moedaEmpresa',
            'simboloMoedaEmpresa',
            'tiposOpcionais',
            'tiposUsuario',
            'paises',
            'resetWizardStep',
            'reservaSelecionarModelo'
        ))
            ->with('titulo', $tituloFormulario);
    }

    public function store(StoreReservaRequest $request)
    {
        if ($redirect = $this->validarPreRequisitosReservaOuRedirecionar('create')) {
            return $redirect;
        }

        // Recebe os dados da reserva em formato JSON
        $reservaData = json_decode($request->reservaHidden);

        // Processa os dados da reserva
        $data = $this->reservaService->processarDadosReserva($reservaData);

        // Cria a reserva com os dados processados
        $reserva = $this->reservaService->create($data);

        // Se o serviço retornou um RedirectResponse (validação falhou dentro do serviço), retornar direto
        if ($reserva instanceof \Illuminate\Http\RedirectResponse) {
            return $reserva;
        }

        if (!$reserva) {
            return redirect()->back()->with('error', 'Falha ao criar a reserva.');
        }

        // Adiciona um log da ação realizada
        $json = json_encode([
            'acao' => 'Reserva criada',
            'data' => $data,
        ]);
        $this->reservaService->adicionaLog($reserva->id, auth()->user()->id, $json);

        return redirect()->route('reserva.show', $reserva->id)
            ->with('success', 'Reserva criada com sucesso.');
    }



    public function show($id)
    {
        $titulo = "Detalhes da Reserva";
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Reservas', route('reserva.index')),
            new BreadCrumbItem('Detalhes da Reserva Nº' . $id)
        ]);
        $reserva = $this->reservaService->getById($id, ['contratoCheckin', 'contratoCheckout']);
        if (!$reserva) {
            return redirect()->route('reserva.index')->with('error', 'Reserva nao encontrada.');
        }

        $reserva->load([
            'criadoPor',
            'nfseAtual',
            'estacaoRetirada',
            'estacaoDevolucao',
            'grupoFrota',
            'cupomDesconto',
            'opcionais',
            'transacoes' => function ($query) {
                $query->with(['user', 'pagoPor'])->orderByDesc('id');
            },
            'contratoCheckin.grupoFrotaOriginal',
            'contratoCheckin.veiculo.marca',
            'contratoCheckin.usuarioUpgrade',
            'contratoCheckout',
            'calcao',
            'veiculoDano' => fn ($q) => $q->with('imagem')->orderByDesc('id')->take(10),
            'avariaCobrancas' => fn ($q) => $q->orderByDesc('id')->take(10),
            'multas' => fn ($q) => $q->orderByDesc('id')->take(5),
        ]);

        // Calcular taxas fora de expediente se não estiverem salvas na reserva
        $taxaColeta  = (float) ($reserva->taxa_coleta  ?? 0);
        $taxaEntrega = (float) ($reserva->taxa_entrega ?? 0);

        if ($taxaColeta == 0 && $reserva->estacaoRetirada && $reserva->data_inicio) {
            $avaliacao = $this->reservaService->avaliarFuncionamentoEstacaoPublico(
                (int) $reserva->estacao_retirada_id,
                $reserva->data_inicio,
                'retirada'
            );
            if (($avaliacao['taxa_extra'] ?? false) && ($avaliacao['ok'] ?? false)) {
                $taxaColeta = (float) ($reserva->estacaoRetirada->valor_taxa_coleta ?? 0);
            }
        }

        if ($taxaEntrega == 0 && $reserva->estacaoDevolucao && $reserva->data_fim) {
            $avaliacao = $this->reservaService->avaliarFuncionamentoEstacaoPublico(
                (int) $reserva->estacao_devolucao_id,
                $reserva->data_fim,
                'devolucao'
            );
            if (($avaliacao['taxa_extra'] ?? false) && ($avaliacao['ok'] ?? false)) {
                $taxaEntrega = (float) ($reserva->estacaoDevolucao->valor_taxa_entrega ?? 0);
            }
        }

        // Se percentual_desconto não foi salvo mas cupom existe, recuperar do cupom
        if ((float) ($reserva->percentual_desconto ?? 0) == 0 && $reserva->cupom_desconto_id && $reserva->cupomDesconto) {
            $reserva->percentual_desconto = (float) ($reserva->cupomDesconto->valor ?? 0);
        }

        // Verificar módulos do plano da empresa
        $empresa = \App\Models\Empresa::with('planoAtivo.plano')->find($reserva->empresa_id);
        $moduloNfse   = (bool) ($empresa?->planoAtivo?->plano?->modulo_nfse   ?? false);
        $moduloMultas = (bool) ($empresa?->planoAtivo?->plano?->modulo_multas ?? false);

        return view('reserva.show', compact('reserva', 'breadcrumb', 'titulo', 'taxaColeta', 'taxaEntrega', 'moduloNfse', 'moduloMultas'));
    }

    public function emitirNfse($id)
    {
        $reserva = $this->reservaService->getById($id);
        if (!$reserva) {
            if (request()->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Reserva nao encontrada.',
                ], 404);
            }

            return redirect()->route('reserva.index')->with('error', 'Reserva nao encontrada.');
        }

        $nfse = $this->nfseService->emitirParaReserva($reserva);
        $nfseWarning = null;
        if ($nfse === null) {
            $nfseWarning = 'Emissao de NFS-e nao configurada para esta empresa.';
        } elseif (($nfse->status ?? '') === 'erro') {
            $nfseWarning = $this->nfseService->mensagemErro($nfse) ?? 'Falha ao emitir NFS-e.';
        }

        if (request()->expectsJson()) {
            return response()->json([
                'status' => $nfseWarning ? 'warning' : 'success',
                'message' => $nfseWarning ? $nfseWarning : 'NFS-e emitida com sucesso.',
                'nfse' => [
                    'status' => $nfse->status ?? null,
                    'numero' => $nfse->numero ?? null,
                    'ambiente' => $nfse->ambiente ?? null,
                ],
            ]);
        }

        return redirect()
            ->route('reserva.show', $id)
            ->with('success', $nfseWarning ? 'Tentativa de emissao finalizada.' : 'NFS-e emitida com sucesso.')
            ->with('warning', $nfseWarning);
    }

    public function edit($id)
    {
        $tituloFormulario = 'Edição de Reserva';
        $reserva = $this->reservaService->getById($id, ['opcionais', 'cupomDesconto', 'usuario']);

        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo'
        ];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Reservas', 'reserva.index', "Editar: $reserva->id");
        [$metodo, $rota] = $this->getFormMethodAndRoute($reserva);
        $usuarioAutenticado = Auth::user()?->loadMissing(['estacoesPermitidas']);
        $estacoesPermitidasUsuario = $usuarioAutenticado && ! $usuarioAutenticado->hasAcessoTodasEstacoes()
            ? $usuarioAutenticado->estacoesPermitidas->sortBy('nome')->values()
            : collect();

        // Consultando as variáveis necessárias
        $users = $this->userService->getAllForReservaSelect();
        $estacoesRetirada = $this->estacaoService->getEstacoesParaRetiradaSelect();
        $estacoesDevolucao = $this->estacaoService->getEstacoesParaDevolucaoSelect();
        $gruposVeiculo = $this->grupoFrotaService->getAllAtivosForSelectSubText('id', 'nome', 'valor_diario', null, 'asc');
        $modelosAtivos = $this->modeloService->getAllAtivos();
        $estacoes = $this->unirEstacoesReserva($estacoesRetirada, $estacoesDevolucao);
        $resumoVeiculosEstacoes = $this->montarResumoVeiculosPorEstacao($estacoes);
        $preRequisitosReserva = $this->montarPreRequisitosReserva(
            $estacoes,
            $gruposVeiculo,
            $modelosAtivos,
            $resumoVeiculosEstacoes
        );
        if (($preRequisitosReserva['qtd_estacoes'] ?? 0) <= 0 && $estacoesPermitidasUsuario->isNotEmpty()) {
            $preRequisitosReserva['faltas'] = array_values(array_filter(
                $preRequisitosReserva['faltas'],
                static fn (string $falta): bool => !str_contains(mb_strtolower($falta), 'estacao ativa para iniciar reservas')
            ));
            $preRequisitosReserva['faltas'][] = 'Você possui estações no perfil, mas nenhuma está habilitada para retirada/devolução nesta reserva.';
        }
        $moedaEmpresa = 'BRL';
        $tiposUsuario = $this->regraService->getAllTiposUsuarios();
        $paises = $this->paisService->getAllForSelectSigla();
        $empresaAtual = $this->gatewayEmpresaConfigService->empresaAtual();
        if ($empresaAtual) {
            $moedaEmpresa = $this->gatewayEmpresaConfigService->moeda($empresaAtual);
        }
        $simboloMoedaEmpresa = $this->simboloMoeda($moedaEmpresa);

        $tiposOpcionais = $this->tipoOpcionalService->getTipoOpcionais('nome', 'asc');

        $modelos = $reserva->modelos?->pluck('nome', 'id') ?? [];
        $marcas = $reserva->modelos->marcas ?? [];
        $segmentos = $reserva->modelos->segmentos ?? [];

        $opcionaisSelecionados = $reserva->opcionais?->pluck('id')->toArray() ?? [];
        $usuarioAutenticado = Auth::user()?->loadMissing(['estacoesPermitidas']);
        $estacoesPermitidasUsuario = $usuarioAutenticado && ! $usuarioAutenticado->hasAcessoTodasEstacoes()
            ? $usuarioAutenticado->estacoesPermitidas->sortBy('nome')->values()
            : collect();
        $estacoesBloqueadasReserva = $this->montarEstacoesBloqueadasReserva($estacoesPermitidasUsuario, array_keys($estacoes));
        $reservaSelecionarModelo = ($empresaAtual?->reserva_selecionar_modelo ?? 'N') === 'S';

        return view('reserva.add-edit', compact(
            'opcionaisSelecionados',
            'users',
            'estacoesRetirada',
            'estacoesDevolucao',
            'estacoesPermitidasUsuario',
            'estacoesBloqueadasReserva',
            'gruposVeiculo',
            'preRequisitosReserva',
            'moedaEmpresa',
            'simboloMoedaEmpresa',
            'tiposOpcionais',
            'tiposUsuario',
            'paises',
            'modelos',
            'marcas',
            'segmentos',
            'reserva',
            'breadcrumb',
            'status',
            'metodo',
            'rota',
            'reservaSelecionarModelo'
        ))->with('titulo', $tituloFormulario);
    }

    private function montarPreRequisitosReserva(
        $estacoes,
        $gruposVeiculo,
        $modelosAtivos,
        array $resumoVeiculosEstacoes = []
    ): array
    {
        $qtdEstacoes = $this->contarItens($estacoes);
        $qtdGrupos = $this->contarItens($gruposVeiculo);
        $qtdModelos = $this->contarItens($modelosAtivos);
        $qtdVeiculosTotal = (int) ($resumoVeiculosEstacoes['total'] ?? 0);
        $qtdEstacoesComVeiculo = (int) ($resumoVeiculosEstacoes['estacoes_com_veiculo'] ?? 0);
        $detalhesVeiculosPorEstacao = $resumoVeiculosEstacoes['detalhes'] ?? [];

        $faltas = [];
        $user = Auth::user();
        $semPermissaoDeEstacao = $user && !$user->hasAcessoTodasEstacoes() && empty($user->estacoesPermitidasIds());

        if ($semPermissaoDeEstacao) {
            $faltas[] = 'Seu usuario nao possui estacoes permitidas. Solicite ao administrador acesso a pelo menos uma estacao.';
        } elseif ($qtdEstacoes <= 0) {
            $faltas[] = 'Cadastre pelo menos uma estacao ativa para iniciar reservas.';
        }

        if ($qtdEstacoes > 0 && $qtdGrupos <= 0) {
            $faltas[] = 'Cadastre pelo menos um grupo de veiculos ativo.';
        }

        if ($qtdEstacoes > 0 && $qtdGrupos > 0 && $qtdModelos <= 0) {
            $faltas[] = 'Cadastre pelo menos um modelo ativo para os grupos de veiculos.';
        }

        if ($qtdEstacoes > 0 && $qtdGrupos > 0 && $qtdModelos > 0 && $qtdVeiculosTotal <= 0) {
            $faltas[] = 'Nao ha veiculos ativos nas estacoes permitidas. Cadastre ou libere pelo menos um veiculo para reservar.';
        }

        return [
            'qtd_estacoes' => $qtdEstacoes,
            'qtd_grupos' => $qtdGrupos,
            'qtd_modelos' => $qtdModelos,
            'qtd_veiculos_total' => $qtdVeiculosTotal,
            'qtd_estacoes_com_veiculo' => $qtdEstacoesComVeiculo,
            'detalhes_veiculos_estacao' => $detalhesVeiculosPorEstacao,
            'faltas' => $faltas,
            'pode_reservar' => empty($faltas),
        ];
    }

    private function simboloMoeda(string $moeda): string
    {
        return match (strtoupper($moeda)) {
            'USD' => '$',
            'EUR' => '€',
            default => 'R$',
        };
    }

    private function contarItens($itens): int
    {
        if (is_array($itens)) {
            return count($itens);
        }

        if (is_object($itens) && method_exists($itens, 'count')) {
            return (int) $itens->count();
        }

        if ($itens instanceof \Countable) {
            return count($itens);
        }

        return empty($itens) ? 0 : 1;
    }

    private function unirEstacoesReserva(array $estacoesRetirada, array $estacoesDevolucao): array
    {
        $unificadas = [];

        foreach ([$estacoesRetirada, $estacoesDevolucao] as $lista) {
            foreach ($lista as $id => $nome) {
                $unificadas[(int) $id] = (string) $nome;
            }
        }

        return $unificadas;
    }

    private function montarEstacoesBloqueadasReserva($estacoesPermitidasUsuario, array $estacoesDisponiveisIds): array
    {
        if (empty($estacoesPermitidasUsuario) || !method_exists($estacoesPermitidasUsuario, 'filter')) {
            return [];
        }

        $idsDisponiveis = array_map('intval', $estacoesDisponiveisIds);

        return $estacoesPermitidasUsuario
            ->filter(static function ($estacao) use ($idsDisponiveis): bool {
                return !in_array((int) ($estacao->id ?? 0), $idsDisponiveis, true);
            })
            ->map(function ($estacao): array {
                return [
                    'id' => (int) ($estacao->id ?? 0),
                    'nome' => (string) ($estacao->nome ?? 'Estação'),
                    'motivo' => $this->descreverMotivoBloqueioEstacaoReserva($estacao),
                ];
            })
            ->values()
            ->all();
    }

    private function descreverMotivoBloqueioEstacaoReserva($estacao): string
    {
        $motivos = [];

        if (($estacao->ativo ?? 'S') !== 'S') {
            $motivos[] = 'está inativa';
        }

        $checkin = ($estacao->checkin ?? 'N') === 'S';
        $checkout = ($estacao->checkout ?? 'N') === 'S';

        if (!$checkin && !$checkout) {
            $motivos[] = 'não está habilitada para retirada nem devolução';
        } elseif (!$checkin) {
            $motivos[] = 'não está habilitada para retirada';
        } elseif (!$checkout) {
            $motivos[] = 'não está habilitada para devolução';
        }

        if (empty($motivos)) {
            return 'não aparece nas opções da reserva.';
        }

        return implode(' e ', $motivos) . '.';
    }

    private function montarResumoVeiculosPorEstacao($estacoes): array
    {
        $estacoesArray = [];
        if (is_array($estacoes)) {
            $estacoesArray = $estacoes;
        } elseif (is_object($estacoes) && method_exists($estacoes, 'toArray')) {
            $estacoesArray = (array) $estacoes->toArray();
        } elseif ($estacoes instanceof \Traversable) {
            $estacoesArray = iterator_to_array($estacoes);
        }

        $estacaoIds = array_map('intval', array_keys($estacoesArray));
        $quantidadesPorEstacao = $this->veiculoService->getQuantidadeVeiculosAtivosPorEstacao($estacaoIds);

        $detalhes = [];
        foreach ($estacoesArray as $estacaoId => $nomeEstacao) {
            $id = (int) $estacaoId;
            $detalhes[] = [
                'label' => (string) $nomeEstacao,
                'value' => (int) ($quantidadesPorEstacao[$id] ?? 0),
            ];
        }

        $total = array_sum(array_map('intval', $quantidadesPorEstacao));
        $estacoesComVeiculo = count(array_filter(
            $quantidadesPorEstacao,
            static fn ($quantidade): bool => (int) $quantidade > 0
        ));

        return [
            'quantidades' => $quantidadesPorEstacao,
            'detalhes' => $detalhes,
            'total' => (int) $total,
            'estacoes_com_veiculo' => (int) $estacoesComVeiculo,
        ];
    }

    public function update(UpdateReservaRequest $request, $id)
    {
        if ($redirect = $this->validarPreRequisitosReservaOuRedirecionar('edit', (int) $id)) {
            return $redirect;
        }

        $reservaAntes = $this->reservaService->getById((int) $id, ['opcionais']);
        $antes = $reservaAntes ? $reservaAntes->only([
            'user_id',
            'modelo_id',
            'estacao_retirada_id',
            'estacao_devolucao_id',
            'data_inicio',
            'data_fim',
            'observacoes',
            'grupo_frota_id',
            'tarifa_base',
            'quantidade_dias',
            'valor_total_final',
        ]) : [];
        if ($reservaAntes) {
            $antes['opcionais'] = $reservaAntes->opcionais->map(fn ($o) => [
                'id' => (int) $o->id,
                'quantidade' => (int) ($o->pivot->quantidade ?? 0),
                'preco_total' => (float) ($o->pivot->preco_total ?? 0),
            ])->values()->all();
        }

        // Recebe os dados da reserva em formato JSON
        $reservaData = json_decode($request->reservaHidden);

        // Processa os dados da reserva
        $data = $this->reservaService->processarDadosReserva($reservaData);

        // Atualiza a reserva com os dados processados
        $this->reservaService->update($id, $data);

        $reservaDepois = $this->reservaService->getById((int) $id, ['opcionais']);
        $depois = $reservaDepois ? $reservaDepois->only([
            'user_id',
            'modelo_id',
            'estacao_retirada_id',
            'estacao_devolucao_id',
            'data_inicio',
            'data_fim',
            'observacoes',
            'grupo_frota_id',
            'tarifa_base',
            'quantidade_dias',
            'valor_total_final',
        ]) : [];
        if ($reservaDepois) {
            $depois['opcionais'] = $reservaDepois->opcionais->map(fn ($o) => [
                'id' => (int) $o->id,
                'quantidade' => (int) ($o->pivot->quantidade ?? 0),
                'preco_total' => (float) ($o->pivot->preco_total ?? 0),
            ])->values()->all();
        }

        // Adiciona um log da ação realizada
        $json = json_encode([
            'acao' => 'Reserva atualizada',
            'antes' => $antes,
            'depois' => $depois,
            'payload' => $data,
        ]);
        $this->reservaService->adicionaLog($id, auth()->user()->id, json: $json);

        return redirect()->route('reserva.show', $id)->with('success', 'Reserva atualizada com sucesso.');
    }

    public function destroy($id)
    {
        // $this->reservaService->delete($id);
        // return $this->redirecionaRotaRedirect($this->nomeFiltro);
        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Reserva não pode ser excluida', 'error');
    }


    /**
     * Tela de vistoria de devolução (check-out). A reserva só é concluída após finalizar aqui.
     */
    public function checkoutVistoria(Request $request, $reserva_id)
    {
        $reserva = $this->reservaService->getById($reserva_id, ['modelos', 'usuario', 'veiculo', 'contratoCheckin', 'calcao']);

        if (($reserva->pagamento_efetuado ?? 'N') !== 'S') {
            return redirect()
                ->route('reserva.show', $reserva_id)
                ->with('warning', 'O pagamento deve ser efetuado antes de realizar o check-out.');
        }

        if (!$reserva->contratoCheckin || $reserva->contratoCheckin->assinado !== 'S') {
            return redirect()
                ->route('reserva.show', $reserva_id)
                ->with('warning', 'O check-in deve ser realizado e o contrato assinado antes do check-out.');
        }

        if ($mensagemCalcao = $this->validarCalcaoParaCheckout($reserva)) {
            return redirect()
                ->route('reserva.show', $reserva_id)
                ->with('error', $mensagemCalcao);
        }

        if (!$reserva->veiculo_id) {
            return redirect()
                ->route('reserva.show', $reserva_id)
                ->with('error', 'Associe um veículo à reserva antes do check-out.');
        }

        $categoriaDanos = $this->categoriaDanoService->getAllForSelect();

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Reservas', route('reserva.index')),
            new BreadCrumbItem('Detalhes da Reserva Nº' . $reserva_id, route('reserva.show', $reserva_id)),
            new BreadCrumbItem('Check-out / Vistoria'),
        ]);

        return view('reserva.checkout', compact('reserva', 'categoriaDanos', 'breadcrumb'))
            ->with('titulo', 'Check-out (vistoria) — reserva Nº ' . $reserva->id);
    }

    public function finalizarCheckoutVistoria(FinalizarCheckoutVistoriaRequest $request, $reserva_id)
    {
        $reserva = $this->reservaService->getById($reserva_id, ['veiculo', 'calcao', 'contratoCheckin']);

        if (!$reserva->podeFazerCheckout()) {
            return response()->json(['success' => false, 'message' => 'Reserva não está em status válido para check-out.'], 422);
        }

        if ($mensagemCalcao = $this->validarCalcaoParaCheckout($reserva)) {
            return response()->json(['success' => false, 'message' => $mensagemCalcao], 422);
        }

        if (ContratoCheckout::where('reserva_id', $reserva_id)->exists()) {
            return response()->json(['success' => false, 'message' => 'Check-out desta reserva já foi registrado.'], 422);
        }

        if (!$reserva->veiculo_id) {
            return response()->json(['success' => false, 'message' => 'Veículo não vinculado à reserva.'], 422);
        }

        $dados = $request->validated();

        // Quilometragem: devolucao deve ser >= quilometragem inicial (check-in)
        $kmInicial = $reserva->contratoCheckin?->quilometragem_atual;
        if ($kmInicial !== null) {
            $kmDevolucao = (float) ($dados['quilometragem_devolucao'] ?? 0);
            if ($kmDevolucao < (float) $kmInicial) {
                return response()->json([
                    'success' => false,
                    'message' => 'A quilometragem na devolucao deve ser maior ou igual a quilometragem inicial do check-in (KM ' . $kmInicial . ').',
                ], 422);
            }
        }

        try {
            DB::beginTransaction();

            $dir = 'checkout/reserva_' . (int) $reserva_id;
            $mapFotosCheckout = [
                'foto_direita_co' => 'foto_direita_path',
                'foto_esquerda_co' => 'foto_esquerda_path',
                'foto_frente_co' => 'foto_frente_path',
                'foto_tras_co' => 'foto_tras_path',
                'foto_velocimetro_co' => 'foto_velocimetro_path',
                'foto_combustivel_co' => 'foto_combustivel_path',
            ];

            $destDir = storage_path('app/public/' . $dir);
            if (!is_dir($destDir)) {
                mkdir($destDir, 0775, true);
            }

            $dadosFotosCheckout = [];
            foreach ($mapFotosCheckout as $input => $col) {
                if ($request->hasFile($input) && $request->file($input)->isValid()) {
                    $file = $request->file($input);
                    $extension = $file->getClientOriginalExtension() ?: 'jpg';
                    $filename = \Illuminate\Support\Str::random(40) . '.' . $extension;
                    $file->move($destDir, $filename);
                    $dadosFotosCheckout[$col] = $dir . '/' . $filename;
                }
            }

            ContratoCheckout::create([
                'reserva_id' => (int) $reserva_id,
                'veiculo_id' => (int) $reserva->veiculo_id,
                'empresa_id' => $reserva->empresa_id ?? null,
                'quilometragem_devolucao' => $dados['quilometragem_devolucao'],
                'nivel_combustivel_devolucao' => $dados['nivel_combustivel_devolucao'],
                'data_devolucao' => $dados['data_devolucao'],
                'hora_devolucao' => $dados['hora_devolucao'],
                'notas_devolucao' => $dados['notas_devolucao'] ?? null,
                'observacoes_devolucao' => $dados['observacoes_devolucao'] ?? null,
                ...$dadosFotosCheckout,
            ]);

            $this->movimentacaoService->realizarCheckOut(
                (int) $reserva->veiculo_id,
                (int) $reserva->estacao_devolucao_id,
                (int) $reserva_id,
                'Check-out com vistoria de devolução'
            );

            $this->reservaService->checkOutRealizado($reserva_id);
            $this->liberarCalcaoAposCheckout($reserva);
            $this->reservaService->adicionaLog(
                $reserva_id,
                Auth::id(),
                json_encode(['acao' => 'Check-out com vistoria concluído'])
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Check-out concluído e reserva finalizada.',
                'redirect' => route('reserva.show', $reserva_id),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Erro ao finalizar check-out: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function salvarSatisfacao(Request $request, $reserva_id)
    {
        $reserva = $this->reservaService->getById($reserva_id);
        if (!$reserva) {
            return response()->json(['message' => 'Reserva não encontrada.'], 404);
        }

        $validated = $request->validate([
            'satisfacao_geral'   => ['required', 'integer', 'min:1', 'max:5'],
            'facilidade_reserva' => ['required', 'integer', 'min:1', 'max:5'],
            'nps_nota'           => ['required', 'integer', 'min:0', 'max:10'],
            'comentario'         => ['nullable', 'string', 'max:1000'],
        ]);

        \App\Models\ReservaSatisfacao::updateOrCreate(
            ['reserva_id' => (int) $reserva_id],
            [
                'empresa_id'          => session('empresa_id') ?? $reserva->empresa_id ?? null,
                'respondente_user_id' => Auth::id(),
                'satisfacao_geral'    => (int) $validated['satisfacao_geral'],
                'facilidade_reserva'  => (int) $validated['facilidade_reserva'],
                'nps_nota'            => (int) $validated['nps_nota'],
                'comentario'          => trim((string) ($validated['comentario'] ?? '')) ?: null,
            ]
        );

        return response()->json(['success' => true, 'message' => 'Avaliação registrada com sucesso!']);
    }

    private function validarCalcaoParaCheckout(Reserva $reserva): ?string
    {
        $calculo = $this->calcaoService->calcularValor($reserva);
        $valorCalculado = (float) ($calculo['valor_calculado'] ?? 0);

        if ($valorCalculado <= 0) {
            return null; // não exige calção
        }

        $calcao = $reserva->calcao;
        if (!$calcao) {
            return 'Para fazer o checkout é obrigatório registrar o pagamento do calção primeiro.';
        }

        $ok = [
            Calcao::STATUS_AUTORIZADO,
            Calcao::STATUS_CAPTURADO,
            Calcao::STATUS_PARCIALMENTE_CAPTURADO,
        ];

        if (in_array((string) $calcao->status, $ok, true)) {
            return null;
        }

        return 'Para fazer o checkout é obrigatório registrar o calção. Status atual do calção: ' . (string) ($calcao->label_status ?? $calcao->status);
    }

    private function liberarCalcaoAposCheckout(Reserva $reserva): void
    {
        $calcao = $reserva->calcao;
        if (!$calcao) {
            return;
        }

        if (!empty($calcao->payment_intent_id) && $calcao->podeLiberar()) {
            $calcaoLiberado = $this->calcaoService->cancelar(
                $calcao,
                'Calção liberado automaticamente após o checkout.'
            );

            $this->reservaService->adicionaLog($reserva->id, Auth::id(), json_encode([
                'acao' => 'Calção liberado automaticamente no checkout',
                'calcao_id' => $calcaoLiberado->id,
            ]));

            return;
        }

        if (!empty($calcao->payment_intent_id) || $calcao->status === Calcao::STATUS_CANCELADO) {
            return;
        }

        $valor = (float) ($calcao->valor_capturado ?: $calcao->valor_autorizado ?: $calcao->valor_calculado ?: 0);
        if ($valor <= 0) {
            return;
        }

        $metodo = (string) (($calcao->stripe_response['metodo'] ?? null) ?: 'presencial');

        $calcao->update([
            'status' => Calcao::STATUS_CANCELADO,
            'data_hora_cancelamento' => now(),
            'usuario_responsavel_id' => Auth::id(),
            'observacao' => 'Calção presencial devolvido automaticamente após o checkout.',
        ]);

        $payload = [
            'user_id' => $reserva->user_id,
            'pago_por_user_id' => Auth::id(),
            'reserva_id' => $reserva->id,
            'valor' => -1 * abs($valor),
            'moeda' => 'BRL',
            'metodo_pagamento' => $metodo,
            'status' => 'refunded',
            'info_pagamento' => [
                'tipo' => 'calcao',
                'acao' => 'devolvido_automaticamente_checkout',
                'calcao_id' => $calcao->id,
            ],
        ];

        if (SchemaCache::hasColumn('transacoes', 'confirmado_em')) {
            $payload['confirmado_em'] = now();
        }
        if (SchemaCache::hasColumn('transacoes', 'confirmado_por_user_id')) {
            $payload['confirmado_por_user_id'] = Auth::id();
        }
        if (SchemaCache::hasColumn('transacoes', 'empresa_id')) {
            $payload['empresa_id'] = $reserva->empresa_id ?? null;
        }

        Transacao::create($payload);

        $this->reservaService->adicionaLog($reserva->id, Auth::id(), json_encode([
            'acao' => 'Calção presencial devolvido automaticamente no checkout',
            'calcao_id' => $calcao->id,
            'valor' => $valor,
        ]));
    }

    public function cancelar($id)
    {
        $reservaAntes = $this->reservaService->getById((int) $id);
        $this->reservaService->cancelar($id);
        $reserva = $this->reservaService->getById($id);
        if ($reserva) {
            $this->nfseService->cancelarPorReserva($reserva);
            $this->cancelarTransferenciasReserva($reserva);
        }
        $cancelamentoMensagem = null;
        if ($reserva) {
            $cancelamentoMensagem = $this->cancelarTransacaoGateway($reserva);
            $this->registrarCancelamentoTransacao($reserva);

            $this->reservaService->adicionaLog((int) $id, Auth::id(), json_encode([
                'acao' => 'Reserva cancelada',
                'antes' => [
                    'status_reserva_id' => $reservaAntes?->status_reserva_id,
                    'veiculo_id' => $reservaAntes?->veiculo_id,
                    'data_cancelamento' => $reservaAntes?->data_cancelamento ?? null,
                ],
                'depois' => [
                    'status_reserva_id' => $reserva->status_reserva_id,
                    'veiculo_id' => $reserva->veiculo_id,
                    'data_cancelamento' => $reserva->data_cancelamento ?? null,
                ],
            ]));
        }

        $redirect = redirect()->route('reserva.index');
        if ($cancelamentoMensagem) {
            $redirect->with('warning', $cancelamentoMensagem);
        }

        return $redirect;
    }

    private function registrarCancelamentoTransacao(Reserva $reserva): void
    {
        if (!SchemaCache::hasTable('transacoes')) {
            return;
        }

        $existeCancelamento = DB::table('transacoes')
            ->where('reserva_id', $reserva->id)
            ->where('valor', '<', 0)
            ->whereIn(DB::raw("LOWER(COALESCE(status, ''))"), ['cancelado', 'cancelada', 'cancelled', 'canceled', 'refunded', 'estorno'])
            ->exists();

        if ($existeCancelamento) {
            return;
        }

        $transacaoBase = Transacao::query()
            ->where('reserva_id', $reserva->id)
            ->orderByDesc('id')
            ->first();

        $valorBase = (float) ($transacaoBase?->valor ?? $reserva->valor_total_final ?? 0);
        if ($valorBase <= 0) {
            return;
        }

        $gatewayTransactionId = (string) ($transacaoBase?->gateway_transaction_id ?? '');
        if ($gatewayTransactionId !== '') {
            $gatewayTransactionId .= ':cancel';
        }

        $payload = [
            'user_id' => $transacaoBase?->user_id ?? $reserva->user_id,
            'pago_por_user_id' => $transacaoBase?->pago_por_user_id ?? auth()->id(),
            'reserva_id' => $reserva->id,
            'valor' => -1 * abs($valorBase),
            'moeda' => $transacaoBase?->moeda ?? 'BRL',
            'metodo_pagamento' => $transacaoBase?->metodo_pagamento ?? null,
            'gateway_transaction_id' => $gatewayTransactionId,
            'status' => 'cancelado',
            'info_pagamento' => json_encode([
                'cancelamento' => true,
                'origem_transacao_id' => $transacaoBase?->id,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (SchemaCache::hasColumn('transacoes', 'metodo_pagamento_id')) {
            $metodoId = $transacaoBase?->metodo_pagamento_id;
            if (!$metodoId && SchemaCache::hasTable('metodo_pagamentos')) {
                $metodoId = DB::table('metodo_pagamentos')->value('id');
            }
            if (!$metodoId && SchemaCache::hasTable('metodos_pagamento')) {
                $metodoId = DB::table('metodos_pagamento')->value('id');
            }
            $payload['metodo_pagamento_id'] = $metodoId ?: null;
        }

        if (SchemaCache::hasColumn('transacoes', 'tipo_pagamento_id')) {
            $tipoId = $transacaoBase?->tipo_pagamento_id;
            if (!$tipoId && SchemaCache::hasTable('tipo_pagamentos')) {
                $tipoId = DB::table('tipo_pagamentos')->value('id');
            }
            if (!$tipoId && SchemaCache::hasTable('tipos_pagamento')) {
                $tipoId = DB::table('tipos_pagamento')->value('id');
            }
            $payload['tipo_pagamento_id'] = $tipoId ?: null;
        }

        if (SchemaCache::hasColumn('transacoes', 'empresa_id')) {
            $payload['empresa_id'] = $reserva->empresa_id ?? null;
        }

        DB::table('transacoes')->insert($payload);
    }

    private function cancelarTransferenciasReserva(Reserva $reserva): void
    {
        if (!SchemaCache::hasTable('movimentacoes') || !SchemaCache::hasColumn('movimentacoes', 'reserva_id')) {
            return;
        }

        $cancelStatusId = 3;
        if (SchemaCache::hasTable('status_movimentacao')) {
            $cancelStatusId = (int) (DB::table('status_movimentacao')
                ->whereIn(DB::raw('LOWER(COALESCE(nome, ""))'), ['cancelado', 'cancelada', 'cancelamento', 'cancelled', 'canceled'])
                ->value('id') ?? 3);
        }

        $query = DB::table('movimentacoes')->where('reserva_id', $reserva->id);

        if (SchemaCache::hasColumn('movimentacoes', 'tipo_movimentacao_id')) {
            $query->where('tipo_movimentacao_id', 10);
        }

        if (SchemaCache::hasColumn('movimentacoes', 'status_movimentacao_id')) {
            $query->update([
                'status_movimentacao_id' => $cancelStatusId,
                'updated_at' => now(),
            ]);
            return;
        }

        if (SchemaCache::hasColumn('movimentacoes', 'updated_at')) {
            $query->update(['updated_at' => now()]);
        }
    }

    private function cancelarTransacaoGateway(Reserva $reserva): ?string
    {
        $transacao = Transacao::query()
            ->where('reserva_id', $reserva->id)
            ->orderByDesc('id')
            ->first();

        if (!$transacao) {
            return null;
        }

        $statusAtual = strtolower((string) ($transacao->status ?? ''));
        if (in_array($statusAtual, ['refunded', 'refund', 'estorno', 'cancelled', 'canceled'], true)) {
            $this->criarTransacaoEstorno($transacao, [
                'status' => 'success',
                'action' => $transacao->status ?? 'refunded',
                'message' => 'Estorno registrado a partir do status atual.',
            ], $reserva);
            return null;
        }

        $empresa = $this->gatewayEmpresaConfigService->empresaPorId((int) $reserva->empresa_id);
        if (!$empresa) {
            return 'Pagamento nao foi cancelado: empresa nao encontrada para o gateway.';
        }

        $gateway = $this->gatewayEmpresaConfigService->gateway($empresa);
        $credenciais = $this->gatewayEmpresaConfigService->credenciais($empresa, $gateway);

        $provider = $this->gerenciadorDePagamentos->resolver($gateway);
        if (empty($transacao->gateway_transaction_id)) {
            $transacao->status = $transacao->status ?: 'cancelled';
            $info = is_array($transacao->info_pagamento ?? null) ? $transacao->info_pagamento : [];
            $info['cancelamento'] = [
                'status' => 'error',
                'message' => 'Transacao sem identificador do gateway.',
            ];
            $transacao->info_pagamento = $info;
            $transacao->save();
            return 'Pagamento nao foi cancelado: transacao sem identificador no gateway.';
        }

        $resultado = $provider->cancelarPagamento((string) $transacao->gateway_transaction_id, $credenciais);

        $info = is_array($transacao->info_pagamento ?? null) ? $transacao->info_pagamento : [];
        $info['cancelamento'] = $resultado;

        if (($resultado['status'] ?? 'error') === 'success') {
            $transacao->status = (string) ($resultado['raw_status'] ?? $resultado['action'] ?? 'cancelled');
            $transacao->info_pagamento = $info;
            $transacao->save();
            $this->criarTransacaoEstorno($transacao, $resultado, $reserva);
            return null;
        }

        $resultadoReembolso = $provider->reembolsarPagamento((string) $transacao->gateway_transaction_id, $credenciais);
        $info['reembolso'] = $resultadoReembolso;

        if (($resultadoReembolso['status'] ?? 'error') === 'success') {
            $transacao->status = (string) ($resultadoReembolso['raw_status'] ?? $resultadoReembolso['action'] ?? 'refunded');
            $transacao->info_pagamento = $info;
            $transacao->save();
            $this->criarTransacaoEstorno($transacao, $resultadoReembolso, $reserva);
            return null;
        }

        $transacao->status = $transacao->status ?: 'cancel_failed';
        $transacao->info_pagamento = $info;
        $transacao->save();

        return $resultado['message'] ?? $resultadoReembolso['message'] ?? 'Pagamento nao foi cancelado no gateway.';
    }

    private function criarTransacaoEstorno(Transacao $transacao, array $resultado, Reserva $reserva): void
    {
        if (!SchemaCache::hasTable('transacoes')) {
            return;
        }

        $existeEstorno = DB::table('transacoes')
            ->where('reserva_id', $reserva->id)
            ->whereIn(DB::raw("LOWER(COALESCE(status, ''))"), ['refunded', 'refund', 'estorno', 'cancelled', 'canceled'])
            ->where('valor', '<', 0)
            ->exists();

        if ($existeEstorno) {
            return;
        }

        $valor = -1 * abs((float) ($transacao->valor ?? 0));
        $gatewayTransactionId = (string) ($resultado['transaction_id'] ?? $resultado['id'] ?? '');
        if ($gatewayTransactionId === '' && !empty($transacao->gateway_transaction_id)) {
            $gatewayTransactionId = $transacao->gateway_transaction_id . ':refund';
        }

        $payload = [
            'user_id' => $transacao->user_id,
            'pago_por_user_id' => $transacao->pago_por_user_id ?? auth()->id(),
            'reserva_id' => $reserva->id,
            'valor' => $valor,
            'moeda' => $transacao->moeda ?? 'BRL',
            'metodo_pagamento' => $transacao->metodo_pagamento ?? null,
            'gateway_transaction_id' => $gatewayTransactionId,
            'status' => (string) ($resultado['raw_status'] ?? $resultado['action'] ?? 'refunded'),
            'info_pagamento' => json_encode([
                'estorno' => true,
                'origem_transacao_id' => $transacao->id,
                'resultado' => $resultado,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (SchemaCache::hasColumn('transacoes', 'metodo_pagamento_id')) {
            $payload['metodo_pagamento_id'] = $transacao->metodo_pagamento_id ?? 1;
        }

        if (SchemaCache::hasColumn('transacoes', 'tipo_pagamento_id')) {
            $payload['tipo_pagamento_id'] = $transacao->tipo_pagamento_id ?? 1;
        }

        if (SchemaCache::hasColumn('transacoes', 'empresa_id')) {
            $payload['empresa_id'] = $reserva->empresa_id ?? null;
        }

        DB::table('transacoes')->insert($payload);
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
        session()->put("$this->nomeFiltro.urlPadrao", 'reserva');
        session()->put("$this->nomeFiltro.rotaRedirect", 'reserva.index');
    }

    public function obterFiltrosPrincipais()
    {
        $statusReserva = $this->statusReservaService->getAllForSelect('id', 'nome');
        $dadosStatusReserva = FiltroTrait::formatOptions($statusReserva, 'Selecione o status');

        $usuarios = $this->userService->getAllForSelect('id', 'nome');
        $dadosUsuarios = FiltroTrait::formatOptions($usuarios, 'Selecione o cliente');

        $dadosPagamento = [
            ['value' => '', 'label' => 'Pagamento', 'selected' => false],
            ['value' => 'S', 'label' => 'Pago', 'selected' => false],
            ['value' => 'N', 'label' => 'Pendente', 'selected' => false],
        ];

        return [
            ['name' => 'id', 'label' => 'Reserva', 'type' => 'number', 'typeWhere' => 'where'],
            ['name' => 'user_id', 'label' => 'Cliente', 'type' => 'select', 'typeWhere' => 'where', 'options' => $dadosUsuarios],
            ['name' => 'status_reserva_id', 'label' => 'Status', 'type' => 'select', 'typeWhere' => 'where', 'options' => $dadosStatusReserva],
            ['name' => 'pagamento_efetuado', 'label' => 'Pagamento', 'type' => 'select', 'typeWhere' => 'where', 'options' => $dadosPagamento],
            ['name' => 'data_inicio', 'label' => 'Data retirada', 'type' => 'date', 'typeWhere' => 'like'],
        ];
    }

    public function obterFiltrosSecundarios()
    {
        $estacoes = $this->estacaoService->getAllAtivos();
        $dadosEstacoes = FiltroTrait::formatOptions($estacoes, 'Selecione a estacao');

        $gruposFrota = $this->grupoFrotaService->getAllForSelect('id', 'nome');
        $dadosGrupos = FiltroTrait::formatOptions($gruposFrota, 'Selecione o grupo');

        $modelos = $this->modeloService->getAllForSelect('id', 'nome');
        $dadosModelos = FiltroTrait::formatOptions($modelos, 'Selecione o modelo');

        return [
            ['name' => 'data_fim', 'label' => 'Data devolucao', 'type' => 'date', 'typeWhere' => 'like'],
            ['name' => 'estacao_retirada_id', 'label' => 'Estacao retirada', 'type' => 'select', 'typeWhere' => 'where', 'options' => $dadosEstacoes],
            ['name' => 'estacao_devolucao_id', 'label' => 'Estacao devolucao', 'type' => 'select', 'typeWhere' => 'where', 'options' => $dadosEstacoes],
            ['name' => 'grupo_frota_id', 'label' => 'Grupo de frota', 'type' => 'select', 'typeWhere' => 'where', 'options' => $dadosGrupos],
            ['name' => 'modelo_id', 'label' => 'Modelo', 'type' => 'select', 'typeWhere' => 'where', 'options' => $dadosModelos],
        ];
    }

    /**
     * Função para obter o método e a rota para o formulário.
     *
     * @param  Reserva|null  $reserva
     * @return array
     */
    private function getFormMethodAndRoute($reserva = null)
    {
        if ($reserva) {
            return [
                'PUT',
                route('reserva.update', $reserva->id),
            ];
        }

        return [
            'POST',
            route('reserva.store'),
        ];
    }

    private function validarPreRequisitosReservaOuRedirecionar(string $contexto, ?int $reservaId = null)
    {
        $estacoes = $this->estacaoService->getAllAtivos();
        $gruposVeiculo = $this->grupoFrotaService->getAllAtivosForSelectSubText('id', 'nome', 'valor_diario', null, 'asc');
        $modelosAtivos = $this->modeloService->getAllAtivos();
        $resumoVeiculosEstacoes = $this->montarResumoVeiculosPorEstacao($estacoes);
        $preRequisitosReserva = $this->montarPreRequisitosReserva(
            $estacoes,
            $gruposVeiculo,
            $modelosAtivos,
            $resumoVeiculosEstacoes
        );
        if ($preRequisitosReserva['pode_reservar']) {
            return null;
        }

        $faltas = implode(' ', $preRequisitosReserva['faltas']);
        if ($contexto === 'edit' && $reservaId) {
            return redirect()
                ->route('reserva.edit', $reservaId)
                ->withInput()
                ->withErrors(['reserva' => trim('Nao foi possivel salvar a reserva. ' . $faltas)]);
        }

        return redirect()
            ->route('reserva.create')
            ->withInput()
            ->withErrors(['reserva' => trim('Nao foi possivel criar a reserva. ' . $faltas)]);
    }


    public function atualizaStatusReservas()
    {
        // Rotina de atraso das reservas confirmadas e nao retiradas
        $this->reservaService->atualizarStatusReservasConfirmadasNaoRetiradasParaAtrasadas();

        // Rotina de expiracao das reservas atrasadas
        $this->reservaService->atualizarStatusReservasAtrasadasParaExpiradas();

        // Rotina de expiracao de reservascriadas
        $this->reservaService->atualizarStatusReservasCriadasParaExpiradas();

        return response()->json(['message' => 'Status das reservas atualizados com sucesso']);
        // $this->reservaService->atualizarStatusReservasPagasNaoRetiradasParaAtrasadas();
        // $this->reservaService->cancelarReservasAguardandoPagamentoExpiradas();
    }

    public function obterOpcionais(Request $request)
    {

        $opcionaisSelecionados = isset($request->opcionais) ? $request->opcionais : [];
        // dd($opcionaisSelecionados);

        $quantidade_dias = $this->reservaService->calculaQuantidadeDias(
            $request->data_inicio,
            $request->data_fim
        );

        $opcionais = $this->grupoFrotaService->getTiposOpcionaisComOpcionais($request->grupo_frota_id);
        // dd($opcionais);

        return view('reserva.components.opcionais', ['tiposOpcionais' => $opcionais, 'quantidade_dias' => $quantidade_dias, 'opcionaisSelecionados' => $opcionaisSelecionados]);
    }

    public function veiculosDisponiveis(Request $request)
    {
        if (empty($request->data_inicio) || empty($request->data_fim) || empty($request->grupo_frota_id)) {
            return response()->json([
                'grupo_frota'         => null,
                'modelos'             => [],
                'modelos_vinculados'  => [],
                'tipo_opcional'       => [],
                'qtd_veiculos_estacao'=> 0,
                'aviso_modelos'       => 'Preencha as datas e estacoes para verificar disponibilidade.',
            ]);
        }

        $grupoFrota = $this->grupoFrotaService->getById($request->grupo_frota_id);
        $qtdVeiculosEstacao = $this->veiculoService->getQuantidadeVeiculosDisponiveisPorEstacaoEPeriodo(
            (int) $request->estacao_retirada_id,
            (string) $request->data_inicio,
            (string) $request->data_fim
        );

        $modelos = $this->veiculoService->getModelosComVeiculosDisponiveis(
            $request->grupo_frota_id,
            $request->estacao_retirada_id,
            $request->data_inicio,
            $request->data_fim
        );
        $modelosVinculados = collect($this->grupoFrotaService->getModelosPorGrupoFrota($request->grupo_frota_id));
        $opcionais = $this->grupoFrotaService->getTiposOpcionaisComOpcionais($request->grupo_frota_id);
        $avisoModelos = null;

        if ($modelos->isEmpty()) {
            $avisoModelos = $modelosVinculados->isNotEmpty()
                ? 'Este grupo possui modelos vinculados, mas nao ha veiculos disponiveis para a estacao/data selecionadas.'
                : 'Este grupo de veiculos ainda nao possui modelos vinculados.';
        }


        return response()->json([
            'grupo_frota' => $grupoFrota,
            'modelo' => $modelos,
            'tipo_opcional' => $opcionais,
            'aviso_modelos' => $avisoModelos,
            'qtd_veiculos_estacao' => $qtdVeiculosEstacao,
        ]);
    }

    /**
     * Retorna estacoes com veiculos disponiveis no periodo (opcionalmente filtrado por grupo/modelo).
     */
    public function estacoesDisponiveisNoPeriodo(Request $request)
    {
        $request->validate([
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after:data_inicio',
            'grupo_frota_id' => 'nullable|integer',
            'modelo_id' => 'nullable|integer',
            'reserva_id' => 'nullable|integer',
            'tipo' => 'nullable|string|max:20',
        ]);

        $ids = $this->veiculoService->getEstacaoIdsComVeiculosDisponiveisNoPeriodo(
            (string) $request->input('data_inicio'),
            (string) $request->input('data_fim'),
            $request->filled('grupo_frota_id') ? (int) $request->input('grupo_frota_id') : null,
            $request->filled('modelo_id') ? (int) $request->input('modelo_id') : null,
            $request->filled('reserva_id') ? (int) $request->input('reserva_id') : null,
        );

        return response()->json([
            'estacao_ids' => $ids,
        ]);
    }

    public function estacoesDisponiveisNaDataHora(Request $request)
    {
        $request->validate([
            'data_hora' => 'required|date',
            'tipo' => 'nullable|string|max:20',
            'grupo_frota_id' => 'nullable|integer',
            'modelo_id' => 'nullable|integer',
            'reserva_id' => 'nullable|integer',
        ]);

        $dataHora = (string) $request->input('data_hora');
        $tipo = (string) ($request->input('tipo') ?: 'retirada');
        $dataFimApoio = Carbon::parse($dataHora)->addMinute()->format('Y-m-d\TH:i');

        $ids = $this->veiculoService->getEstacaoIdsComVeiculosDisponiveisNoPeriodo(
            $dataHora,
            $dataFimApoio,
            $request->filled('grupo_frota_id') ? (int) $request->input('grupo_frota_id') : null,
            $request->filled('modelo_id') ? (int) $request->input('modelo_id') : null,
            $request->filled('reserva_id') ? (int) $request->input('reserva_id') : null,
        );

        $idsFiltrados = [];
        foreach ($ids as $estacaoId) {
            $msg = $this->reservaService->validarFuncionamentoEstacaoParaApi((int) $estacaoId, $dataHora, $tipo);
            if ($msg === null) {
                $idsFiltrados[] = (int) $estacaoId;
            }
        }

        if ($tipo === 'retirada') {
            $estacoesRetirada = array_keys($this->estacaoService->getEstacoesParaRetiradaSelect());
            $quantidadesAtivas = $this->veiculoService->getQuantidadeVeiculosAtivosPorEstacao($estacoesRetirada);

            foreach ($quantidadesAtivas as $estacaoId => $totalAtivos) {
                if ((int) $totalAtivos <= 0 && !in_array((int) $estacaoId, $idsFiltrados, true)) {
                    $idsFiltrados[] = (int) $estacaoId;
                }
            }
        }

        return response()->json([
            'estacao_ids' => $idsFiltrados,
        ]);
    }

    public function datasRetiradaDisponiveisPorEstacao(Request $request)
    {
        $request->validate([
            'estacao_id' => 'required|integer|exists:estacoes,id',
            'data_base' => 'nullable|date',
            'limite' => 'nullable|integer|min:1|max:60',
        ]);

        $datas = $this->reservaService->obterDatasRetiradaDisponiveisParaEstacao(
            (int) $request->input('estacao_id'),
            $request->filled('data_base') ? (string) $request->input('data_base') : null,
            $request->filled('limite') ? (int) $request->input('limite') : 14,
        );

        return response()->json([
            'datas' => $datas,
        ]);
    }

    /**
     * Retorna datas (YYYY-MM-DD) nos próximos N dias onde a estação está fechada OU sem veículos.
     * Lógica:
     *  1. Carregar tipos de dia (Dias da Semana, Sábado, Domingo, Feriados) da empresa via horarios_diarios.
     *  2. Verificar quais tipos o horario da estação tem funciona=S.
     *  3. Se a estação não trabalha em feriados, pré-carregar feriados do período e bloqueá-los.
     *  4. Para dias em que a estação opera, bloquear os que tiverem 0 veículos disponíveis.
     */
    public function pesquisarGruposVeiculos(Request $request)
    {
        $request->validate([
            'grupo'               => 'nullable|string|max:100',
            'modelo'              => 'nullable|string|max:100',
            'marca'               => 'nullable|string|max:100',
            'segmento'            => 'nullable|string|max:100',
            'valor_min'           => 'nullable|numeric|min:0',
            'valor_max'           => 'nullable|numeric|min:0',
            'por_pagina'          => 'nullable|integer|min:1|max:50',
            'estacao_retirada_id' => 'nullable|integer|exists:estacoes,id',
            'data_inicio'         => 'nullable|string',
            'data_fim'            => 'nullable|string',
        ]);

        $filtros   = $request->only(['grupo', 'modelo', 'marca', 'segmento', 'valor_min', 'valor_max']);
        $porPagina = (int) ($request->input('por_pagina') ?: 12);

        $estacaoId  = $request->input('estacao_retirada_id');
        $dataInicio = $request->input('data_inicio');
        $dataFim    = $request->input('data_fim');
        $verificarDisponibilidade = $estacaoId && $dataInicio && $dataFim;

        $paginator = $this->grupoFrotaService->pesquisarComFiltros($filtros, $porPagina);

        $items = $paginator->getCollection()->map(function ($grupo) use ($verificarDisponibilidade, $estacaoId, $dataInicio, $dataFim) {
            $modelos = $grupo->modelos->map(fn($m) => [
                'id'       => $m->id,
                'nome'     => $m->nome,
                'marca'    => $m->marcas?->nome,
                'segmento' => $m->segmentos?->nome,
                'imagem'   => $m->imagem_url,
            ]);

            $disponivel         = null;
            $qtdModelosDisponiveis = null;

            if ($verificarDisponibilidade) {
                $modelosDisponiveis = $this->veiculoService->getModelosComVeiculosDisponiveis(
                    (int) $grupo->id,
                    (int) $estacaoId,
                    (string) $dataInicio,
                    (string) $dataFim
                );
                $qtdModelosDisponiveis = $modelosDisponiveis->count();
                $disponivel = $qtdModelosDisponiveis > 0;
            }

            return [
                'id'                     => $grupo->id,
                'nome'                   => $grupo->nome,
                'valor_diario'           => (float) $grupo->valor_diario,
                'imagem_url'             => $grupo->imagem_url,
                'modelos'                => $modelos,
                'marcas'                 => $modelos->pluck('marca')->filter()->unique()->values(),
                'segmentos'              => $modelos->pluck('segmento')->filter()->unique()->values(),
                'disponivel'             => $disponivel,
                'qtd_modelos_disponiveis'=> $qtdModelosDisponiveis,
            ];
        });

        return response()->json([
            'data'          => $items,
            'total'         => $paginator->total(),
            'per_page'      => $paginator->perPage(),
            'current_page'  => $paginator->currentPage(),
            'last_page'     => $paginator->lastPage(),
        ]);
    }

    public function diasSemDisponibilidade(Request $request)
{
    $request->validate([
        'estacao_id' => 'required|integer|exists:estacoes,id',
        'dias'       => 'nullable|integer|min:1|max:180',
        'tipo'       => 'nullable|string|max:20',
    ]);

    $estacaoId     = (int) $request->input('estacao_id');
    $dias          = (int) ($request->input('dias') ?: 90);
    $tipo          = strtolower(trim((string) ($request->input('tipo') ?: 'retirada')));
    $isDevolucao   = $tipo === 'devolucao';
    $hoje          = Carbon::today();
    $mensagem      = null;

    $estacao = Estacao::with(['diasUteis'])->findOrFail($estacaoId);
    $quantidadeVeiculosAtivos = $this->veiculoService->getQuantidadeVeiculosAtivosPorEstacao([$estacaoId]);

    // Para retirada: só mostra dias se há veículos ativos na estação
    if (!$isDevolucao) {
        if ((int) ($quantidadeVeiculosAtivos[$estacaoId] ?? 0) <= 0) {
            return response()->json([
                'estacao_id'      => $estacao->id,
                'dias_disponiveis' => [],
                'mensagem'        => 'A estação selecionada não possui veículos ativos para retirada.',
            ]);
        }
    }

    // 1. dias em que a estação funciona (baseado no horário/escala)
    $proximosDias = $estacao->proximosDiasFuncionamento($dias, $hoje);

    // 2. remover feriados, caso não opere em feriado
    $proximosDias = $this->removerFeriados(
        $proximosDias,
        $estacao->id,
        $estacao->opera_feriado
    );

    if (empty($proximosDias)) {
        $mensagem = $isDevolucao
            ? 'A estação selecionada não possui agenda de devolução configurada para o período.'
            : 'A estação selecionada não possui agenda de retirada configurada para o período.';
    }

    // 3. para retirada: filtrar apenas dias com veículo disponível
    //    para devolução: todos os dias de funcionamento são válidos
    $diasDisponiveis = $isDevolucao
        ? $proximosDias
        : $estacao->filtrarDiasComVeiculoDisponivel($proximosDias);

    if (!$isDevolucao && empty($diasDisponiveis) && (int) ($quantidadeVeiculosAtivos[$estacaoId] ?? 0) > 0) {
        $mensagem = 'A estação possui veículos, mas nenhum dia ficou disponível no período selecionado.';
    }

    if ($isDevolucao && empty($diasDisponiveis) && empty($mensagem)) {
        $mensagem = 'A estação selecionada não possui dias de devolução disponíveis no período.';
    }

    return response()->json([
        'estacao_id'      => $estacao->id,
        'dias_disponiveis' => $diasDisponiveis,
        'mensagem'        => $mensagem,
    ]);
}

    /**
     * Sinalize que precisa fazer!!!
     * Aqui seria o local para remover os feriados do array de proximos dias, caso a estação não opere em feriados.
     * 
     * @param array $dados Array de dias com ['data' => 'YYYY-MM-DD', 'dia_semana' => 0..6]
     * @return array Mesmos dados, mas com os feriados removidos (caso a estação não opere em feriados).
     */
    private function removerFeriados($dados,$estacao_id,$operaFeriado)
    {
        if ($operaFeriado) {
            return $dados;
        }

        if (empty($dados)) {
            return $dados;
        }

        $dataInicial = $dados[0]['data'];
        $dataFinal = end($dados)['data'];

        $feriados = Feriado::where('estacao_id', $estacao_id)
            ->whereBetween('data', [
                $dataInicial,
                $dataFinal
            ])
            ->where('ativo','S')
            ->pluck('data')
            ->map(fn ($data) => Carbon::parse($data)->toDateString())
            ->flip()
            ->all();

        $dados = array_values(array_filter($dados, fn ($d) => !isset($feriados[$d['data']])));
        
        return $dados;
    }

private function montarDiasSemanaFlags($diasUteis): array
{
    $diasPadrao = [
        '0' => false,
        '1' => false,
        '2'   => false,
        '3'  => false,
        '4'  => false,
        '5'  => false,
        '6'  => false,
    ];

    $mapa = [
        1 => '0',
        2 => '1',
        3 => '2',
        4 => '3',
        5 => '4',
        6 => '5',
        7 => '6',
    ];

    foreach ($diasUteis as $diaUtil) {
        $numeroDia = (int) $diaUtil->dia;

        if (isset($mapa[$numeroDia])) {
            $diasPadrao[$mapa[$numeroDia]] = true;
        }
    }

    return $diasPadrao;
}



    public function agendaDisponivelPorEstacao(Request $request)
    {
        $request->validate([
            'estacao_id' => 'required|integer|exists:estacoes,id',
            'tipo' => 'nullable|string|max:20',
            'data_base' => 'nullable|date',
            'limite' => 'nullable|integer|min:1|max:60',
            'reserva_id' => 'nullable|integer',
        ]);

        $agenda = $this->reservaService->obterAgendaDisponivelPorEstacao(
            (int) $request->input('estacao_id'),
            (string) ($request->input('tipo') ?: 'retirada'),
            $request->filled('data_base') ? (string) $request->input('data_base') : null,
            $request->filled('limite') ? (int) $request->input('limite') : 14,
            $request->filled('reserva_id') ? (int) $request->input('reserva_id') : null,
        );

        return response()->json([
            'agenda' => $agenda,
        ]);
    }

    public function validarFuncionamentoEstacao(Request $request)
    {
        $request->validate([
            'estacao_id' => 'required|integer|exists:estacoes,id',
            'data_hora'  => 'required|date',
            'tipo'       => 'nullable|string|max:20',
        ]);

        $tipo = (string) ($request->input('tipo') ?: 'retirada');
        $msg = $this->reservaService->validarFuncionamentoEstacaoParaApi(
            (int) $request->input('estacao_id'),
            (string) $request->input('data_hora'),
            $tipo
        );

        return response()->json([
            'ok' => $msg === null,
            'message' => $msg,
        ]);
    }

    public function efetuarPagamento($id)
    {

        $reserva = $this->reservaService->getById($id, ['opcionais', 'cupomDesconto', 'estacaoRetirada', 'estacaoDevolucao', 'grupoFrota', 'modelos']);
        if ($reserva->status_reserva_id != StatusReservaEnum::Pendente->value) {
            return redirect()->route('reserva.show', $id)->with('error', 'Reserva não pode ser paga');
        }

        // Taxas fora de expediente (igual ao show)
        $taxaColeta  = (float) ($reserva->taxa_coleta  ?? 0);
        $taxaEntrega = (float) ($reserva->taxa_entrega ?? 0);
        if ($taxaColeta == 0 && $reserva->estacaoRetirada && $reserva->data_inicio) {
            $av = $this->reservaService->avaliarFuncionamentoEstacaoPublico((int) $reserva->estacao_retirada_id, $reserva->data_inicio, 'retirada');
            if (($av['taxa_extra'] ?? false) && ($av['ok'] ?? false)) {
                $taxaColeta = (float) ($reserva->estacaoRetirada->valor_taxa_coleta ?? 0);
            }
        }
        if ($taxaEntrega == 0 && $reserva->estacaoDevolucao && $reserva->data_fim) {
            $av = $this->reservaService->avaliarFuncionamentoEstacaoPublico((int) $reserva->estacao_devolucao_id, $reserva->data_fim, 'devolucao');
            if (($av['taxa_extra'] ?? false) && ($av['ok'] ?? false)) {
                $taxaEntrega = (float) ($reserva->estacaoDevolucao->valor_taxa_entrega ?? 0);
            }
        }
        // Desconto: recuperar do cupom se não salvo
        if ((float) ($reserva->percentual_desconto ?? 0) == 0 && $reserva->cupom_desconto_id && $reserva->cupomDesconto) {
            $reserva->percentual_desconto = (float) ($reserva->cupomDesconto->valor ?? 0);
        }

        $empresa = $this->gatewayEmpresaConfigService->empresaPorId((int) $reserva->empresa_id);
        if (!$empresa) {
            return redirect()->route('reserva.show', $id)->with('error', 'Empresa da reserva não encontrada para configuração de pagamento.');
        }

        $gatewayPadrao = $this->gatewayEmpresaConfigService->gateway($empresa);
        $moedaPadrao = $this->gatewayEmpresaConfigService->moeda($empresa);
        $gatewayNome = [
            'stripe' => 'Stripe',
            'mercado_livre' => 'Mercado Livre',
            'paypal' => 'PayPal',
        ][$gatewayPadrao] ?? strtoupper($gatewayPadrao);

        $titulo = "Efetuar Pagamento da Reserva";

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Reservas', route('reserva.index')),
            new BreadCrumbItem('Efetuar Pagamento da Reserva Nº' . $id)
        ]);

        $user = $this->userService->getById($reserva->user_id);
        $metodosPagamento = [];
        $stripePublicKey = null;
        if ($gatewayPadrao === 'stripe' && !empty($user->stripe_id)) {
            $stripePublicKey = (string) ($empresa->stripe_public_key ?? config('services.stripe.key'));
            $stripeSecret = (string) ($empresa->stripe_secret ?? config('services.stripe.secret'));
            if ($stripeSecret !== '') {
                try {
                    $metodosPagamento = $this->clienteStripeService->getPaymentMethods($user->stripe_id, $stripeSecret);
                } catch (\Stripe\Exception\InvalidRequestException $e) {
                    \Log::warning('Stripe customer invalid or not found for user when loading payment methods - attempting reconcile', [
                        'user_id' => $user->id ?? null,
                        'stripe_id' => $user->stripe_id ?? null,
                        'exception' => $e->getMessage(),
                    ]);

                    try {
                        $novoStripeId = $this->clienteStripeService->garantirClienteValido($user->stripe_id, $user->email ?? '', $user->nome ?? 'Cliente', $stripeSecret);
                        if (!empty($novoStripeId) && $novoStripeId !== ($user->stripe_id ?? '')) {
                            // Atualiza o usuário local para apontar para o cliente correto no Stripe
                            $this->userService->updateUser($user->id, ['stripe_id' => $novoStripeId]);
                            $user = $this->userService->getById($user->id);
                        }

                        // Tenta novamente obter métodos com o id reconciliado
                        $metodosPagamento = $this->clienteStripeService->getPaymentMethods($user->stripe_id, $stripeSecret);
                    } catch (\Exception $e2) {
                        \Log::warning('Erro ao reconciliar cliente Stripe ou ao listar métodos após reconcile', ['exception' => $e2->getMessage()]);
                        session()->flash('warning', 'Não foi possível carregar os métodos de pagamento salvos. Remova e adicione novamente o cartão do cliente ou entre em contato com o suporte.');
                        $metodosPagamento = [];
                    }
                } catch (\Exception $e) {
                    \Log::error('Erro ao consultar métodos de pagamento Stripe', ['exception' => $e->getMessage()]);
                    session()->flash('error', 'Erro ao conectar com o provedor de pagamento. Tente novamente mais tarde.');
                    $metodosPagamento = [];
                }
            }
        }

        return view(
            'reserva.efetuar-pagamento',
            compact('reserva', 'breadcrumb', 'titulo', 'metodosPagamento', 'gatewayPadrao', 'gatewayNome', 'moedaPadrao', 'stripePublicKey', 'taxaColeta', 'taxaEntrega')
        );
    }

    public function processarPagamento(Request $request, $id)
    {
        $reserva = $this->reservaService->getById($id);
        $valorTotal = (float) $reserva->valor_total_final;

        $empresa = $this->gatewayEmpresaConfigService->empresaPorId((int) $reserva->empresa_id);
        if (!$empresa) {
            return redirect()->route('reserva.show', $id)->with('error', 'Empresa da reserva não encontrada para configuração de pagamento.');
        }

        $userReserva = $this->userService->getById($reserva->user_id);
        $gateway = $this->gatewayEmpresaConfigService->gateway($empresa);
        $currency = $this->gatewayEmpresaConfigService->moeda($empresa);
        $credenciais = $this->gatewayEmpresaConfigService->credenciais($empresa, $gateway);

        if ($gateway === 'stripe' && empty($request->payment_method)) {
            return redirect()->back()->with('error', 'Selecione um método de pagamento para Stripe.');
        }

        $payload = array_merge([
            'reserva_id' => $reserva->id,
            'valor' => $valorTotal,
            'currency' => $currency,
            'payment_method' => (string) $request->input('payment_method', ''),
            'customer_id' => (string) ($userReserva->stripe_id ?? ''),
            'descricao' => 'Pagamento da reserva #' . $reserva->id,
            'payer_email' => (string) ($userReserva->email ?? ''),
        ], $credenciais);

        try {
            $resultado = $this->gerenciadorDePagamentos
                ->resolver($gateway)
                ->cobrarPagamento($payload);
        } catch (\Throwable $e) {
            \Log::error('Falha ao processar pagamento da reserva', [
                'reserva_id' => (int) $id,
                'gateway' => (string) $gateway,
                'message' => $e->getMessage(),
                'exception' => get_class($e),
            ]);
            return redirect()
                ->route('reserva.show', $id)
                ->with('error', 'Falha ao processar o pagamento. Tente novamente.');
        }

        if (($resultado['status'] ?? 'error') !== 'success') {
            \Log::warning('Pagamento nao efetuado para reserva', [
                'reserva_id' => (int) $id,
                'gateway' => (string) $gateway,
                'status' => (string) ($resultado['status'] ?? 'error'),
                'raw_status' => (string) ($resultado['raw_status'] ?? ''),
            ]);
            return redirect()->route('reserva.show', $id)->with('error', $resultado['message'] ?? 'Pagamento não efetuado');
        }

        DB::table('reservas')->where('id', $id)->update(['status_reserva_id' => StatusReservaEnum::Confirmada->value, 'pagamento_efetuado' => 'S']);

        Transacao::create([
            'user_id' => $reserva->user_id,
            'pago_por_user_id' => Auth::id(),
            'reserva_id' => $reserva->id,
            'valor' => $valorTotal,
            'moeda' => strtoupper((string) ($resultado['currency'] ?? $currency)),
            'metodo_pagamento' => $gateway,
            'gateway_transaction_id' => $resultado['transaction_id'] ?? null,
            'status' => $resultado['raw_status'] ?? 'success',
            'info_pagamento' => $resultado,
        ]);

        $json = json_encode([
            'acao' => 'Pagamento efetuado',
            'valor_total' => $valorTotal,
            'gateway' => $gateway,
            'moeda' => strtoupper((string) ($resultado['currency'] ?? $currency)),
            'usuario_pagador_id' => Auth::id(),
            'customer_id' => $resultado['customer_id'] ?? null,
            'id_pagamento' => $resultado['transaction_id'] ?? null,
            'status' => $resultado['raw_status'] ?? 'success',
            'data_pagamento' => date('Y-m-d H:i:s'),
        ]);
        $this->reservaService->adicionaLog($id, (int) Auth::id(), $json);

        $nfse = $this->nfseService->emitirParaReserva($reserva);
        $nfseWarning = null;
        if ($nfse === null) {
            $nfseWarning = 'Emissao de NFS-e nao configurada para esta empresa.';
        } elseif (($nfse->status ?? '') === 'erro') {
            $nfseWarning = $this->nfseService->mensagemErro($nfse) ?? 'Falha ao emitir NFS-e.';
        }

        return redirect()
            ->route('reserva.show', $id)
            ->with('success', 'Pagamento efetuado com sucesso')
            ->with('warning', $nfseWarning);
    }
    public function checkin(Request $request, $reserva_id)
    {
        $reserva = $this->reservaService->getById($reserva_id, ['modelos']);

        if (($reserva->pagamento_efetuado ?? 'N') !== 'S') {
            return redirect()
                ->route('reserva.show', $reserva_id)
                ->with('warning', 'O pagamento deve ser efetuado antes de realizar o check-in.');
        }

        $categoriaDanos = $this->categoriaDanoService->getAllForSelect();

        $modeloIdsNoGrupo = $this->veiculoService->getModeloIdsDeVeiculosNoGrupo((int) $reserva->grupo_frota_id);
        $modelos = $this->modeloService->getAtivosPorIds($modeloIdsNoGrupo);

        $marcaIds = $modelos->pluck('marca_id')->unique()->filter()->map(static fn ($id) => (int) $id)->values()->all();
        $marcas = $this->marcaService->getModel()->newQuery()
            ->whereIn('id', $marcaIds)
            ->where('ativo', 'S')
            ->orderBy('nome')
            ->get();

        $checkinModelosJson = $modelos->map(static fn ($m) => [
            'id' => $m->id,
            'nome' => $m->nome,
            'marca_id' => (int) $m->marca_id,
        ])->values();

        $veiculos = collect();

        // Contrato existente (edição) — pré-preenche os campos
        $contratoExistente = $this->contratoCheckinService->getModel()->newQuery()
            ->with('veiculo')
            ->where('reserva_id', $reserva_id)
            ->first();

        // Contrato já assinado não pode ser editado — redireciona para a reserva
        if ($contratoExistente && $contratoExistente->assinado === 'S') {
            return redirect()->route('reserva.show', $reserva_id)
                ->with('warning', 'O contrato de check-in já foi assinado e não pode ser editado. Para criar um novo, exclua o contrato atual.');
        }

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Reservas', route('reserva.index')),
            new BreadCrumbItem('Detalhes da Reserva Nº' . $reserva_id, route('reserva.show', $reserva_id)),
            new BreadCrumbItem($contratoExistente ? 'Editar Check-in' : 'Check-in')
        ]);

        return view(
            'reserva.checkin',
            compact(
                'reserva',
                'marcas',
                'modelos',
                'veiculos',
                'breadcrumb',
                'categoriaDanos',
                'checkinModelosJson',
                'contratoExistente'
            )
        )
            ->with('titulo', ($contratoExistente ? 'Editar Check-in' : 'Check-in') . ' da reserva Nº ' . $reserva->id);
    }

    /**
     * Veículos disponíveis na estação de retirada, no período da reserva, para marca + modelo escolhidos.
     */
    public function veiculosDisponiveisCheckin(Request $request, $reserva_id)
    {
        $request->validate([
            'modelo_id' => 'required|integer|exists:modelos,id',
            'marca_id' => 'required|integer|exists:marcas,id',
        ]);

        $reserva = $this->reservaService->getById($reserva_id, ['modelos']);

        $permitidos = $this->veiculoService->getModeloIdsDeVeiculosNoGrupo((int) $reserva->grupo_frota_id);
        $modeloId = (int) $request->modelo_id;
        if (! in_array($modeloId, $permitidos, true)) {
            return response()->json(['message' => 'Este modelo não possui veículos cadastrados neste grupo de frota.'], 422);
        }

        $modelo = $this->modeloService->getById($modeloId);
        if (! $modelo || (int) $modelo->marca_id !== (int) $request->marca_id) {
            return response()->json(['message' => 'Marca e modelo não conferem.'], 422);
        }

        $dataInicio = Carbon::parse($reserva->data_inicio)->format('Y-m-d H:i:s');
        $dataFim = Carbon::parse($reserva->data_fim)->format('Y-m-d H:i:s');

        $veiculos = $this->veiculoService->getVeiculosDisponiveisPorModeloEEstacaoEIntervaloDatas(
            $modeloId,
            (int) $reserva->estacao_retirada_id,
            $dataInicio,
            $dataFim,
            (int) $reserva_id
        );

        return response()->json(
            $veiculos->map(static fn ($v) => [
                'id' => $v->id,
                'nome' => $v->nome,
            ])->values()
        );
    }

    /**
     * Retorna veículos disponíveis para o checkin via modal de busca avançada.
     *
     * Comportamento sem filtros ativos:
     *  1. Tenta retornar apenas veículos da marca/modelo da reserva.
     *  2. Se não houver, retorna todos os disponíveis no período/estação.
     *
     * Com filtros aplicados pelo usuário: usa apenas os filtros informados.
     */
    public function veiculosModalCheckin(Request $request, $reserva_id)
    {
        $reserva = $this->reservaService->getById($reserva_id, ['modelos']);

        $dataInicio = Carbon::parse($reserva->data_inicio)->format('Y-m-d H:i:s');
        $dataFim    = Carbon::parse($reserva->data_fim)->format('Y-m-d H:i:s');
        $estacaoId  = (int) $reserva->estacao_retirada_id;

        $filtros = $request->only(['placa', 'nome', 'chassi', 'cor', 'modelo', 'marca_id', 'grupo_frota_id', 'tipo_frota_id', 'ativo', 'ano_de', 'ano_ate', 'kms_ate']);
        $filtrosVazios = collect($filtros)->filter(fn($v) => $v !== null && $v !== '')->isEmpty();

        if ($filtrosVazios) {
            $filtrosGrupo = [];
            if (!empty($reserva->grupo_frota_id)) {
                $filtrosGrupo['grupo_frota_id'] = $reserva->grupo_frota_id;
            }

            $veiculos = $this->veiculoService->buscarDisponiveisCheckin(
                $estacaoId, $dataInicio, $dataFim, (int) $reserva_id, $filtrosGrupo
            );

            if ($veiculos->isEmpty()) {
                $veiculos = $this->veiculoService->buscarDisponiveisCheckin(
                    $estacaoId, $dataInicio, $dataFim, (int) $reserva_id, []
                );
            }
        } else {
            $veiculos = $this->veiculoService->buscarDisponiveisCheckin(
                $estacaoId, $dataInicio, $dataFim, (int) $reserva_id, $filtros
            );
        }

        $toArray = static fn ($v) => [
            'id'              => $v->id,
            'nome'            => $v->nome ?? '',
            'placa'           => $v->placa_licenca ?? '',
            'marca'           => $v->marca?->nome ?? '',
            'modelo'          => $v->modelo?->nome ?? '',
            'grupo'           => $v->grupoFrota?->nome ?? '',
            'tipo'            => $v->tipoFrota?->nome ?? '',
            'status'          => $v->statusVeiculo?->nome ?? '',
            'ano_fab'         => $v->ano_fabricacao ?? '',
            'ano_mod'         => $v->modelo?->ano_modelo ?? '',
            'cor'             => $v->cor ?? '',
            'kms'             => $v->kms ?? '',
            'ativo'           => $v->ativo === 'S' ? 'Sim' : 'Não',
            'estacao'         => '',
            'nivel_combustivel' => '',
        ];

        return response()->json(['veiculos' => $veiculos->map($toArray)->values()]);
    }

    public function diagnosticoDisponibilidadeCheckin(Request $request, $reserva_id)
    {
        $reserva = $this->reservaService->getById($reserva_id, ['modelos', 'estacaoRetirada']);
        $dataInicio = Carbon::parse($reserva->data_inicio)->format('Y-m-d H:i:s');
        $dataFim    = Carbon::parse($reserva->data_fim)->format('Y-m-d H:i:s');
        $estacaoId  = (int) $reserva->estacao_retirada_id;
        $agora = now()->format('Y-m-d H:i:s');
        $limite = $dataInicio > $agora ? $dataInicio : $agora;

        $veiculos = \App\Models\Veiculo::with([
            'marca', 'modelo', 'grupoFrota', 'tipoFrota', 'statusVeiculo', 'localDisponibilidade',
            'movimentacoes' => fn($q) => $q->with(['reserva', 'statusMovimentacao', 'localOrigem', 'localDestino']),
        ])
        ->where(function ($q) use ($estacaoId) {
            $q->where('local_disponibilidade', $estacaoId)->orWhereNull('local_disponibilidade');
        })
        ->get()
        ->map(function ($v) use ($reserva, $dataInicio, $dataFim, $estacaoId, $agora, $reserva_id, $limite) {
            $regras = [];

            // 1. Estação
            $veicEstacao = $v->local_disponibilidade ? (int) $v->local_disponibilidade : 0;
            $veicEstacaoNome = $v->localDisponibilidade?->nome ?: "Estação #{$veicEstacao}";
            $reservaEstacaoNome = $estacaoId > 0 ? ($reserva->estacaoRetirada?->nome ?? "Estação #{$estacaoId}") : 'nenhuma';
            $regras[] = [
                'nome' => 'Estação',
                'ok' => $veicEstacao === $estacaoId || ($veicEstacao === 0 && $estacaoId === 0),
                'info' => $estacaoId > 0
                    ? "Veículo na <strong>{$veicEstacaoNome}</strong>, reserva requer <strong>{$reservaEstacaoNome}</strong>"
                    : 'Reserva sem estação definida',
                'solucao' => $veicEstacao !== $estacaoId && $estacaoId > 0
                    ? "Mova o veículo para a estação <strong>{$reservaEstacaoNome}</strong> ou altere a estação de retirada da reserva."
                    : null,
            ];

            // 2. Disponibilidade (data_hora_disponibilidade)
            $dispDt = $v->data_hora_disponibilidade ? Carbon::parse($v->data_hora_disponibilidade) : null;
            $regras[] = [
                'nome' => 'Disponibilidade',
                'ok' => is_null($dispDt) || $dispDt->lte(Carbon::parse($limite)),
                'info' => is_null($dispDt)
                    ? 'Sem restrição — veículo disponível agora'
                    : "Disponível a partir de " . $dispDt->format('d/m/Y H:i') . ". Limite atual: " . Carbon::parse($limite)->format('d/m/Y H:i'),
                'solucao' => $dispDt && $dispDt->gt(Carbon::parse($limite))
                    ? "Aguarde até " . $dispDt->format('d/m/Y H:i') . " ou antecipe a data de disponibilidade no cadastro do veículo."
                    : null,
            ];

            // 3. Status do veículo
            $statusDisponiveis = $this->veiculoService->getStatusVeiculoDisponivelIds();
            $statusOk = in_array((int) $v->status_veiculo_id, $statusDisponiveis);
            $regras[] = [
                'nome' => 'Status',
                'ok' => $statusOk,
                'info' => "Status atual: <strong>{$v->statusVeiculo?->nome}</strong>",
                'solucao' => !$statusOk
                    ? "Altere o status do veículo para um dos disponíveis."
                    : null,
            ];

            // 4. Reservas conflitantes
            $movConflitantes = $v->movimentacoes->filter(function ($m) use ($reserva, $dataInicio, $dataFim, $reserva_id) {
                if ((int) $m->reserva_id === (int) $reserva_id) return false;
                $r = $m->reserva;
                if (!$r) return false;
                $statusPendentes = [
                    StatusReservaEnum::Pendente->value,
                    StatusReservaEnum::Confirmada->value,
                    StatusReservaEnum::Checkout->value,
                    StatusReservaEnum::NoShow->value,
                ];
                if (!in_array((int) $r->status_reserva_id, $statusPendentes)) return false;
                return $r->data_inicio < $dataFim && $r->data_fim > $dataInicio;
            })->values();
            $regras[] = [
                'nome' => 'Reservas',
                'ok' => $movConflitantes->isEmpty(),
                'info' => $movConflitantes->isEmpty() ? 'Nenhuma reserva conflitante' : $movConflitantes->count() . ' reserva(s) conflitante(s)',
                'solucao' => !$movConflitantes->isEmpty()
                    ? 'Finalize ou cancele as reservas conflitantes antes de prosseguir.'
                    : null,
                'itens' => $movConflitantes->map(fn($m) => [
                    'tipo' => 'reserva',
                    'id' => $m->reserva_id,
                    'label' => 'Reserva #' . $m->reserva_id . ' — ' . ($m->reserva?->statusReserva?->nome ?? ''),
                    'url' => route('reserva.show', $m->reserva_id),
                ]),
            ];

            // 5. Movimentações em aberto
            $movAbertas = $v->movimentacoes->filter(function ($m) use ($dataFim) {
                if ($m->data_chegada_real) return false;
                return is_null($m->data_saida) || $m->data_saida < $dataFim;
            })->values();
            $regras[] = [
                'nome' => 'Movimentações',
                'ok' => $movAbertas->isEmpty(),
                'info' => $movAbertas->isEmpty() ? 'Sem movimentações em aberto' : $movAbertas->count() . ' movimentação(ões) em aberto',
                'solucao' => !$movAbertas->isEmpty()
                    ? 'Finalize as movimentações pendentes antes de prosseguir.'
                    : null,
                'itens' => $movAbertas->map(fn($m) => [
                    'tipo' => 'movimentacao',
                    'id' => $m->id,
                    'label' => 'Mov. #' . $m->id . ' — ' . ($m->localOrigem?->nome ?? '?') . ' → ' . ($m->localDestino?->nome ?? '?'),
                    'url' => url('/admin/movimentacao/' . $m->id),
                ]),
            ];

            return [
                'id' => $v->id,
                'nome' => $v->nome ?: '#' . $v->id,
                'placa' => $v->placa_licenca ?: '—',
                'cor' => $v->cor ?: '—',
                'modelo' => $v->modelo?->nome ?: '—',
                'status_nome' => $v->statusVeiculo?->nome ?: '—',
                'disponivel' => collect($regras)->every(fn($r) => $r['ok']),
                'regras' => $regras,
            ];
        });

        $html = view('reserva.partials.diagnostico', [
            'veiculos' => $veiculos,
            'reserva_info' => [
                'id' => $reserva->id,
                'estacao_retirada_id' => $estacaoId,
                'data_inicio' => $dataInicio,
                'data_fim' => $dataFim,
                'url' => route('reserva.show', $reserva->id),
            ]
        ])->render();

        return response()->json(['html' => $html]);
    }

    public function verContratoCheckin(Request $request, $reserva_id){

        // Carrega a reserva com todos os relacionamentos necessários
    $reserva = $this->reservaService->getById($reserva_id, [
        'usuario',
        'empresa',
        'veiculo',
        'grupoFrota',
        'estacaoRetirada',
        'estacaoDevolucao',
        'opcionais',
        'contratoCheckin',
        'modelos',
        'statusReserva',
        'cupomDesconto',
        'transacoes.user',
        'transacoes.pagoPor',
        'contratoCheckin.marca',
        'contratoCheckin.modelo',
        'contratoCheckin.veiculo.marca',
        'contratoCheckin.veiculo.modelo',
        'contratoCheckin.veiculo.grupoFrota',
        'contratoCheckin.grupoFrotaOriginal',
        'contratoCheckin.veiculoPrevisto',
        'contratoCheckin.usuarioUpgrade',
        'contratoCheckin.calcao',
        'contratoCheckout',
        'calcao',
        'veiculoDano.dano.categoria',
        'veiculoDano.imagem',
        'veiculoDano.usuarioCorrecao',
        'avariaCobrancas',
        'multas',
        'nfseAtual',
    ]);

    if (!$reserva || !$reserva->contratoCheckin) {
        return redirect()->route('reserva.show', $reserva_id)
            ->with('error', 'Contrato de check-in nao encontrado para esta reserva.');
    }

    $money = fn ($valor) => 'R$ ' . number_format((float) ($valor ?? 0), 2, ',', '.');
    $dateTime = fn ($valor) => $valor ? Carbon::parse($valor)->format('d/m/Y H:i') : '-';
    $dateOnly = fn ($valor) => $valor ? Carbon::parse($valor)->format('d/m/Y') : '-';
    $safe = fn ($valor, $fallback = '-') => filled($valor) ? $valor : $fallback;

    $diasReserva = (int) ($reserva->quantidade_dias ?? 0);
    $tarifaBase = (float) ($reserva->tarifa_base ?? 0);
    $valorTarifaBase = (float) ($reserva->valor_total_tarifa_base ?? 0);
    if ($valorTarifaBase <= 0) {
        $valorTarifaBase = $tarifaBase * $diasReserva;
    }

    $valorOpcionais = (float) ($reserva->valor_opcionais ?? 0);
    if ($valorOpcionais <= 0 && $reserva->opcionais?->isNotEmpty()) {
        $valorOpcionais = (float) $reserva->opcionais->sum(fn ($opcional) => (float) ($opcional->pivot->preco_total ?? 0));
    }

    $taxaColeta = (float) ($reserva->taxa_coleta ?? 0);
    $taxaEntrega = (float) ($reserva->taxa_entrega ?? 0);
    if ($taxaColeta == 0 && $reserva->estacaoRetirada && $reserva->data_inicio) {
        $avaliacao = $this->reservaService->avaliarFuncionamentoEstacaoPublico(
            (int) $reserva->estacao_retirada_id,
            $reserva->data_inicio,
            'retirada'
        );
        if (($avaliacao['taxa_extra'] ?? false) && ($avaliacao['ok'] ?? false)) {
            $taxaColeta = (float) ($reserva->estacaoRetirada->valor_taxa_coleta ?? 0);
        }
    }
    if ($taxaEntrega == 0 && $reserva->estacaoDevolucao && $reserva->data_fim) {
        $avaliacao = $this->reservaService->avaliarFuncionamentoEstacaoPublico(
            (int) $reserva->estacao_devolucao_id,
            $reserva->data_fim,
            'devolucao'
        );
        if (($avaliacao['taxa_extra'] ?? false) && ($avaliacao['ok'] ?? false)) {
            $taxaEntrega = (float) ($reserva->estacaoDevolucao->valor_taxa_entrega ?? 0);
        }
    }
    $percentualDesconto = (float) ($reserva->percentual_desconto ?? 0);
    if ($percentualDesconto <= 0 && $reserva->cupomDesconto) {
        $percentualDesconto = (float) ($reserva->cupomDesconto->valor ?? 0);
    }

    $subtotal = $valorTarifaBase + $valorOpcionais + $taxaColeta + $taxaEntrega;
    $valorDesconto = $subtotal * ($percentualDesconto / 100);
    $valorTotal = max(0, $subtotal - $valorDesconto);

    $logoUrl = null;
    $logoPath = is_string($reserva->empresa?->logo_path ?? null) ? trim($reserva->empresa->logo_path) : '';
    if ($logoPath !== '') {
        $logoUrl = 'https://decars.com.br/public/storage/' . $logoPath;
    }

    $danosDetalhados = $reserva->veiculoDano
        ->sortByDesc('id')
        ->map(function ($dano) use ($money, $dateTime, $safe) {
            return [
                'id' => $dano->id,
                'localizacao' => strtoupper((string) ($dano->local_dano ?? $dano->localizacao_problema ?? '-')),
                'categoria' => $dano->dano?->categoria?->nome ?? '-',
                'localizacao_problema' => $safe($dano->localizacao_problema),
                'tipo_dano' => $safe($dano->tipo_dano),
                'observacoes' => $safe($dano->observacoes),
                'quilometragem' => $dano->quilometragem_veiculo ? number_format((float) $dano->quilometragem_veiculo, 0, ',', '.') . ' km' : '-',
                'custo_estimado' => $money($dano->custo_estimado),
                'status' => $dano->correcao_data ? 'Corrigido' : 'Pendente',
                'correcao' => $safe($dano->correcao_descricao),
                'correcao_data' => $dano->correcao_data ? $dateTime($dano->correcao_data) : '-',
                'correcao_usuario' => $dano->usuarioCorrecao?->nome ?? '-',
                'registrado_em' => $dateTime($dano->created_at),
                'imagens' => $dano->imagem->map(function ($img) {
                    return $img->caminho_imagem
                        ? Storage::disk('public')->url($img->caminho_imagem)
                        : null;
                })->filter()->values()->toArray(),
            ];
        })
        ->values()
        ->toArray();

    $data = [
        'documento' => [
            'codigo_interno' => 'ARCS-' . date('y'),
            'atcud' => 'JJNXXNNC-' . date('y'),
            'data_emissao' => now()->format('d/m/Y H:i:s'),
            'empresa' => [
                'nome' => $reserva->empresa->nome ?? config('app.empresa.nome'),
                'endereco' => config('app.empresa.endereco'),
                'site' => config('app.empresa.site'),
                'instagram' => config('app.empresa.instagram'),
                'logo_url' => $reserva->empresa->logo_path,
            ],
        ],
        'contrato' => [
            'numero' => '#' . str_pad($reserva->id, 5, '0', STR_PAD_LEFT),
            'titulo' => 'CONTRATO DE ALUGUEL',
            'subtitulo' => 'CONDIÇÕES PARTICULARES DO CONTRATO DE ALUGUEL',
        ],
        'cliente' => [
            // 'nif' => $reserva->user->nif,
            'nome' => $reserva->usuario->nome,
            'endereco' => $reserva->usuario->endereco,
            'pais' => $reserva->usuario->pais,
            'apolice' => $reserva->usuario->apolice,
        
            'nif' => $reserva->usuario->nif,
            'nome' => $reserva->usuario->nome,
            'email' => $reserva->usuario->email,
            'telefone' => $reserva->usuario->telefone,
            'passaporte' => $reserva->usuario->passaporte ?? '<br>',
            'local_emissao' => $reserva->usuario->local_emissao,
            'data_nascimento' => $dateOnly($reserva->usuario->data_nascimento),
            'local_nascimento' => $reserva->usuario->local_nascimento,
            'carta_conducao' => [
                'pais' => $reserva->usuario->pais_emissao,
                'numero' => $reserva->usuario->numero_carta_conducao,
                'data_emissao' => $dateOnly($reserva->usuario->data_emissao_carta_conducao),
                'validade' => $dateOnly($reserva->usuario->data_validade_carta_conducao),
            ],
        ],
        'veiculo' => [
            'matricula' => $safe($reserva->contratoCheckin->veiculo?->placa_licenca, '#' . $reserva->contratoCheckin->veiculo_id),
            'marca' => $safe($reserva->contratoCheckin->marca?->nome ?? $reserva->contratoCheckin->veiculo?->marca?->nome),
            'modelo' => $safe($reserva->contratoCheckin->modelo?->nome ?? $reserva->contratoCheckin->veiculo?->modelo?->nome),
            'grupo' => $safe($reserva->contratoCheckin->veiculo?->grupoFrota?->nome ?? $reserva->grupoFrota?->nome),
            'nome' => $safe($reserva->contratoCheckin->veiculo?->nome),
            'quilometros' => $reserva->contratoCheckin->quilometragem_atual,
            'combustivel' => $safe($reserva->contratoCheckin->nivel_combustivel),
            'taxas' => [
                // 'combustivel_em_falta' => '€' . number_format($reserva->taxa_combustivel, 2) . ' + IVA / litro',
                // 'taxa_reabastecimento' => '€' . number_format($reserva->taxa_reabastecimento, 2) . ' + IVA',
                'combustivel_em_falta' => '€ 0',
                'taxa_reabastecimento' => '€ 0',
            ],
            // 'franquia' => '€' . number_format($reserva->franquia, 2),
            // 'kms_permitidos' => $reserva->kms_permitidos ?: 'ilimitado',
            'franquia' => '€ 0',
            'kms_permitidos' => $reserva->kms_permitidos ?? $reserva->contratoCheckin->veiculo?->limite_kms ?? 'ilimitado',
        ],
        'reserva' => [
            'id' => $reserva->id,
            'status' => $safe($reserva->statusReserva?->nome),
            'origem' => $safe($reserva->label_origem_cadastro),
            'criada_em' => $dateTime($reserva->created_at),
            'pagamento_efetuado' => ($reserva->pagamento_efetuado ?? 'N') === 'S' ? 'Sim' : 'Nao',
            'grupo_reservado' => $safe($reserva->grupoFrota?->nome),
            'modelo_reservado' => $safe($reserva->modelos?->nome),
            'observacoes' => $safe($reserva->observacoes),
            'alugado_em' => [
                'local' => $safe($reserva->estacaoRetirada?->nome),
                'data' => $dateOnly($reserva->data_inicio),
                'hora' => $reserva->data_inicio ? Carbon::parse($reserva->data_inicio)->format('H:i') : '-',
            ],
            'devolver_em' => [
                'local' => $safe($reserva->estacaoDevolucao?->nome),
                'data' => $dateOnly($reserva->data_fim),
                'hora' => $reserva->data_fim ? Carbon::parse($reserva->data_fim)->format('H:i') : '-',
            ],
        ],
        'faturacao' => [
            'dias_unidades' => $reserva->quantidade_dias,
            'tarifa_diaria' => $money($tarifaBase),
            'valor_tarifa_base' => $money($valorTarifaBase),
            'valor_opcionais' => $money($valorOpcionais),
            'taxa_coleta' => $money($taxaColeta),
            'taxa_entrega' => $money($taxaEntrega),
            'subtotal' => $money($subtotal),
            'percentual_desconto' => number_format($percentualDesconto, 2, ',', '.') . '%',
            'cupom' => $reserva->cupomDesconto?->codigo,
            'valor_desconto' => $money($valorDesconto),
            'iva_total' => $money(0),
            'valor_total' => $money($valorTotal),
        ],
        'estado_viatura' => [
            'itens' => $reserva->veiculoDano->map(function ($dano) {
                $correcao = '';
                if ($dano->correcao_data) {
                    $correcao = ($dano->correcao_descricao ?? '') . ' — ' . $dano->correcao_data->format('d/m/Y H:i');
                }

                return [
                    'local' => ucfirst((string) $dano->local_dano) . ':',
                    'localizacao' => $dano->localizacao_problema ?? '',
                    'tipo_dano' => '(' . ($dano->tipo_dano ?? '') . ')',
                    'observacoes' => $dano->observacoes ?? '',
                    'correcao' => $correcao,
                ];
            })->toArray(),
        ],
        'funcionario' => [
            'nome' => Auth::user()->nome ?? Auth::user()->name ?? 'Sistema',
        ],
        'opcionais' => $reserva->opcionais->map(function ($opcional) {
                return [
                    'nome' => $opcional->nome,
                    'quantidade' => $opcional->pivot->quantidade,
                    'valor_total' => "R$ " . number_format((float) $opcional->pivot->preco_total, 2, ',', '.'),
                    'preco_unitario' => $opcional->preco_por_dia ? "R$ " . number_format((float) $opcional->preco_por_dia, 2, ',', '.') : "R$ " . number_format((float) $opcional->preco_integral, 2, ',', '.'),
                ];
            })->toArray(),
            'assinatura'=> $reserva->contratoCheckin->assinatura,
            'financeiro_detalhado' => [
                'grupo' => $safe($reserva->grupoFrota?->nome),
                'tarifa_diaria' => $money($tarifaBase),
                'dias_label' => $diasReserva . ' ' . ($diasReserva === 1 ? 'dia' : 'dias') . ' x tarifa',
                'valor_tarifa_base' => $money($valorTarifaBase),
                'opcionais' => $money($valorOpcionais),
                'taxa_coleta' => $money($taxaColeta),
                'taxa_entrega' => $money($taxaEntrega),
                'subtotal' => $money($subtotal),
                'desconto_label' => number_format($percentualDesconto, 2, ',', '.') . '%' . ($reserva->cupomDesconto?->codigo ? ' - ' . $reserva->cupomDesconto->codigo : ''),
                'desconto_valor' => $money($valorDesconto),
                'total' => $money($valorTotal),
            ],
            'pagamentos' => $reserva->transacoes->sortByDesc('id')->map(function ($transacao) use ($money, $dateTime) {
                return [
                    'data' => $dateTime($transacao->created_at),
                    'valor' => $money($transacao->valor),
                    'metodo' => $transacao->metodo_pagamento ?? '-',
                    'status' => $transacao->status ?? '-',
                    'confirmado_em' => $dateTime($transacao->confirmado_em),
                    'pago_por' => $transacao->pagoPor?->nome ?? $transacao->user?->nome ?? '-',
                ];
            })->values()->toArray(),
            'danos_detalhados' => $danosDetalhados,
            'calcao' => $reserva->calcao ? [
                'status' => $reserva->calcao->status ?? '-',
                'valor_calculado' => $money($reserva->calcao->valor_calculado ?? 0),
                'valor_autorizado' => $money($reserva->calcao->valor_autorizado ?? 0),
                'valor_capturado' => $money($reserva->calcao->valor_capturado ?? 0),
                'metodo' => $reserva->calcao->metodo ?? '-',
            ] : null,
            'cobrancas_avaria' => $reserva->avariaCobrancas->sortByDesc('id')->map(function ($cobranca) use ($money, $dateTime) {
                return [
                    'descricao' => $cobranca->descricao ?? ('Cobranca #' . $cobranca->id),
                    'valor' => $money($cobranca->valor ?? 0),
                    'status' => $cobranca->status ?? '-',
                    'data' => $dateTime($cobranca->created_at),
                ];
            })->values()->toArray(),
    ];


    // dd($data);
    // Percorrer todos os atributos de data e caso não tenha valor, substituir por <br>
    $this->tratarCamposData($data);

       $this->pdfService->gerarCheckinPdf($data);
    }
    
    public function assinarContratoCheckin(Request $request, $reserva_id)
    {
       
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Reservas', route('reserva.index')),
            new BreadCrumbItem('Detalhes da Reserva Nº' . $reserva_id, route('reserva.show', $reserva_id)),
            new BreadCrumbItem('Assinar Contrato de Check-in')
        ]);

        $reserva = $this->reservaService->getById($reserva_id,['contratoCheckin']);
        return view('reserva.assinar-contrato-checkin', compact('reserva','breadcrumb'))->with('titulo', 'Assinar contrato de check-in da reserva Nº ' . $reserva->id);
    }

    public function salvarAssinarContratoCheckIn(AssinarContratoRequest $request, $reserva_id)
    {
        $dados = $request->validated();
        $dados['reserva_id'] = $reserva_id;
        
        $contrato = $this->contratoCheckinService->getById($dados['contrato_id']);
        
        if ($contrato) {
            try {
                $reserva = $this->reservaService->getById($reserva_id);
                $this->contratoCheckinService->assinarDocumento($dados['contrato_id'], $dados['assinatura']);
                $this->reservaService->checkInRealizado($reserva_id, $contrato->veiculo_id);
                $this->movimentacaoService->realizarCheckIn($dados['veiculo_id'], $reserva->estacao_retirada_id, $reserva_id);
                $this->reservaService->adicionaLog(
                    $reserva_id, Auth::id(),
                    ['veiculo' => $contrato->veiculo?->nome ?? "ID {$contrato->veiculo_id}"],
                    'Contrato de check-in assinado', 'success'
                );
                
                return response()->json(['success' => true, 'message' => 'Contrato assinado com sucesso']);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Erro ao assinar contrato: ' . $e->getMessage()], 500);
            }
        }
        
        return response()->json(['success' => false, 'message' => 'Contrato de check-in não encontrado'], 404);
    }

    public function criarContratoCheckIn(CriarContratoCheckInRequest $request,$reserva_id)
    {
        $dados = $request->validated();
        $dados['reserva_id'] = $reserva_id;
        $dados['danos'] = $request->input('danos');

        // Deriva marca_id e modelo_id do veículo selecionado quando não enviados pelo formulário
        if (empty($dados['marca_id']) || empty($dados['modelo_id'])) {
            $veiculo = $this->veiculoService->getById((int) $dados['veiculo_id']);
            $dados['marca_id']  = $dados['marca_id']  ?: (int) $veiculo->marca_id;
            $dados['modelo_id'] = $dados['modelo_id'] ?: (int) $veiculo->modelo_id;
        }

        // Verifica se já existe contrato para esta reserva (modo edição)
        $contratoExistente = $this->contratoCheckinService->getModel()->newQuery()
            ->where('reserva_id', $reserva_id)
            ->first();

        try{
            // Salva as fotos do check-in (passo 2) no storage e registra os paths no contrato.
            $dir = 'checkin/reserva_' . (int) $reserva_id;
            $map = [
                'foto_direita' => 'foto_direita_path',
                'foto_esquerda' => 'foto_esquerda_path',
                'foto_frente' => 'foto_frente_path',
                'foto_tras' => 'foto_tras_path',
                'foto_velocimetro' => 'foto_velocimetro_path',
                'foto_combustivel' => 'foto_combustivel_path',
            ];

            $destDir = storage_path('app/public/' . $dir);
            if (!is_dir($destDir)) {
                mkdir($destDir, 0775, true);
            }

            foreach ($map as $input => $col) {
                if ($request->hasFile($input) && $request->file($input)->isValid()) {
                    // Remove foto antiga se existir
                    if ($contratoExistente && !empty($contratoExistente->$col)) {
                        $oldPath = storage_path('app/public/' . $contratoExistente->$col);
                        if (file_exists($oldPath)) {
                            @unlink($oldPath);
                        }
                    }
                    $file      = $request->file($input);
                    $extension = $file->getClientOriginalExtension() ?: 'jpg';
                    $filename  = \Illuminate\Support\Str::random(40) . '.' . $extension;
                    $file->move($destDir, $filename);
                    $dados[$col] = $dir . '/' . $filename;
                } elseif ($contratoExistente) {
                    // Mantém a foto existente se nenhuma nova foi enviada
                    $dados[$col] = $contratoExistente->$col;
                }
            }

            // Detecta e registra upgrade de categoria automaticamente
            $this->resolverDadosUpgrade($dados, (int) $reserva_id);

            if ($contratoExistente) {
                // Atualiza contrato existente
                $contratoExistente->update($dados);
                $this->reservaService->adicionaLog($reserva_id, Auth::id(), [
                    'houve_upgrade'  => $dados['houve_upgrade_categoria'] ?? false,
                    'motivo_upgrade' => $dados['motivo_upgrade'] ?? null,
                    'veiculo_id'     => $dados['veiculo_id'] ?? null,
                ], 'Contrato de check-in editado', 'info');
                return response()->json($contratoExistente->fresh());
            } else {
                // Cria novo contrato
                $this->reservaService->adicionaLog($reserva_id, Auth::id(), [
                    'houve_upgrade'  => $dados['houve_upgrade_categoria'] ?? false,
                    'motivo_upgrade' => $dados['motivo_upgrade'] ?? null,
                    'veiculo_id'     => $dados['veiculo_id'] ?? null,
                ], 'Contrato de check-in criado', 'success');
                $contrato = $this->contratoCheckinService->create($dados);

                // Salva danos coletados (enviados como JSON no campo "danos")
                if (!empty($dados['danos'])) {
                    $danosData = is_string($dados['danos']) ? json_decode($dados['danos'], true) : $dados['danos'];
                    if (is_array($danosData)) {
                        $fotoIdx = 0;
                        foreach ($danosData as $item) {
                            $item['veiculo_id'] = $dados['veiculo_id'];
                            $item['reserva_id'] = $reserva_id;
                            $item['contrato_id'] = $contrato->id;
                            $dano = $this->veiculoDanoService->create($item);
                            // Salva foto se houver
                            $fotoInput = 'danos_fotos_' . $fotoIdx;
                            if ($dano && $request->hasFile($fotoInput)) {
                                $dir = 'danos/reserva_' . (int) $reserva_id . '/dano_' . (int) $dano->id;
                                $path = \App\Support\ArquivoAnexoHelper::armazenar($request->file($fotoInput), $dir);
                                if ($path) {
                                    $dano->imagem()->create([
                                        'caminho_imagem' => $path,
                                        'nome_imagem'    => basename($path),
                                    ]);
                                }
                            }
                            $fotoIdx++;
                        }
                    }
                }

                return response()->json($contrato);
            }

        }catch(\Exception $e){
            \Log::error('criarContratoCheckIn falhou', [
                'reserva_id' => $reserva_id,
                'message'    => $e->getMessage(),
                'file'       => $e->getFile(),
                'line'       => $e->getLine(),
            ]);
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function adicionarDanoVeiculo(AdicionarDanoVeiculoRequest $request,$reserva_id)
    {
        $dados = $request->validated();
        $dados['reserva_id'] = $reserva_id;

        $fotos = $request->file('fotos', []);
        unset($dados['fotos']);

        try {
            DB::beginTransaction();

            $dano = $this->veiculoDanoService->create($dados);

            if ($dano && !empty($fotos)) {
                $dir = 'danos/reserva_' . (int) $reserva_id . '/dano_' . (int) $dano->id;

                foreach ($fotos as $foto) {
                    $path = \App\Support\ArquivoAnexoHelper::armazenar($foto, $dir);
                    if (!$path) continue;

                    $dano->imagem()->create([
                        'caminho_imagem' => $path,
                        'nome_imagem'    => basename($path),
                    ]);
                }
            }

            $this->reservaService->adicionaLog($reserva_id, Auth::id(), json_encode([
                'acao'        => 'Dano de veiculo adicionado',
                'dano_id'     => $dano?->id,
                'veiculo_id'  => $dados['veiculo_id'] ?? null,
                'categoria_id'=> $dados['categoria_id'] ?? null,
                'local_dano'  => $dados['local_dano'] ?? null,
                'tipo_dano'   => $dados['tipo_dano'] ?? null,
                'fotos'       => is_array($fotos) ? count($fotos) : 0,
            ]));

            DB::commit();

            return response()->json($dano?->load('imagem'));
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => 'Erro ao salvar dano: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Percorre recursivamente o array e substitui valores nulos em campos de data por <br>
     * 
     * @param array &$data Array de dados a ser processado
     */
    private function tratarCamposData(&$data) 
    {
        foreach ($data as $key => &$value) {
            if (is_array($value)) {
                $this->tratarCamposData($value);
            } else {
                $value = $this->convertBrCasoValorSejaNulo($value);
            }
        }
    }
    private function convertBrCasoValorSejaNulo($valor){
        return !isset($valor) || is_null($valor) ? '<br>' : $valor;
    }

    // ══════════════════════════════════════════════════════════════
    // FEATURE 1: Upgrade de categoria no check-in
    // ══════════════════════════════════════════════════════════════

    /**
     * API: verifica disponibilidade na categoria original e retorna
     * veículos elegíveis para upgrade, se necessário.
     */
    public function checkinStatusUpgrade(Request $request, $reserva_id)
    {
        $reserva = $this->reservaService->getById($reserva_id, ['grupoFrota', 'modelos']);

        $temDisponibilidade = $this->upgradeVeiculoService->categoriaOriginalTemDisponibilidade($reserva);

        $payload = [
            'tem_disponibilidade_categoria_original' => $temDisponibilidade,
            'grupo_original' => [
                'id'   => $reserva->grupoFrota?->id,
                'nome' => $reserva->grupoFrota?->nome,
            ],
        ];

        if (!$temDisponibilidade) {
            $grupos = $this->upgradeVeiculoService->veiculosDisponiveisParaUpgrade($reserva);
            $payload['grupos_upgrade'] = collect($grupos)->map(fn ($item) => [
                'grupo' => [
                    'id'    => $item['grupo']->id,
                    'nome'  => $item['grupo']->nome,
                    'ordem' => $item['grupo']->ordem,
                ],
                'veiculos' => $item['veiculos']->map(fn ($v) => [
                    'id'   => $v->id,
                    'nome' => $v->nome,
                    'placa' => $v->placa_licenca,
                ])->values(),
            ])->values();

            $todos = $this->upgradeVeiculoService->veiculosDisponiveisNoPeriodo($reserva);
            $payload['veiculos_periodo'] = $todos->map(function ($v) use ($reserva) {
                $grupo = $v->grupoFrota ?? null;
                return [
                    'id'   => $v->id,
                    'nome' => $v->nome,
                    'placa' => $v->placa_licenca,
                    'grupo' => [
                        'id'    => $grupo?->id,
                        'nome'  => $grupo?->nome,
                        'ordem' => $grupo?->ordem,
                    ],
                    'elegivel_upgrade' => $this->upgradeVeiculoService->veiculoElegivelParaUpgrade($v, $reserva),
                ];
            })->values();
        }

        return response()->json($payload);
    }

    /**
     * Modifica criarContratoCheckIn para registrar upgrade quando detectado.
     * Lógica extra injetada no método existente via método auxiliar.
     */
    private function resolverDadosUpgrade(array &$dados, int $reservaId): void
    {
        $reserva = $this->reservaService->getById($reservaId, ['grupoFrota']);
        if (!$reserva || !$reserva->grupo_frota_id) {
            return;
        }

        $veiculoId = (int) ($dados['veiculo_id'] ?? 0);
        if (!$veiculoId) {
            return;
        }

        $veiculo = $this->veiculoService->getById($veiculoId);
        if (!$veiculo) {
            return;
        }

        // Registra grupo original sempre
        $dados['grupo_frota_original_id'] = $reserva->grupo_frota_id;

        // Registra veiculo previsto, se existir na reserva
        if (!empty($reserva->veiculo_id)) {
            $dados['veiculo_previsto_id'] = (int) $reserva->veiculo_id;
        }

        // Blindagem: veiculo deve estar na estacao de retirada (quando aplicavel)
        if (!empty($reserva->estacao_retirada_id) && !empty($veiculo->local_disponibilidade)) {
            if ((int) $veiculo->local_disponibilidade !== (int) $reserva->estacao_retirada_id) {
                throw ValidationException::withMessages([
                    'veiculo_id' => 'Veiculo selecionado nao esta disponivel na estacao de retirada desta reserva.',
                ]);
            }
        }

        // Mesmo grupo: sem upgrade
        if ((int) $veiculo->grupo_frota_id === (int) $reserva->grupo_frota_id) {
            return;
        }

        // Categoria original ainda tem disponibilidade → aplica regras de upgrade/downgrade
        $categoriaDisponivel = $this->upgradeVeiculoService->categoriaOriginalTemDisponibilidade($reserva);

        if ($categoriaDisponivel) {
            // Há veículos na categoria original: só permite upgrade (nunca downgrade)
            $ehUpgrade = $this->upgradeVeiculoService->veiculoElegivelParaUpgrade($veiculo, $reserva);
            if (!$ehUpgrade) {
                throw ValidationException::withMessages([
                    'veiculo_id' => 'Nao e permitido selecionar veiculo de categoria inferior a reservada (downgrade bloqueado).',
                ]);
            }
        }

        // Categoria original sem disponibilidade (ou é upgrade): registra como upgrade
        $motivo = trim((string) ($dados['motivo_upgrade'] ?? ''));

        $dados['houve_upgrade_categoria'] = true;
        $dados['motivo_upgrade']          = $motivo !== '' ? $motivo : 'Indisponibilidade na categoria original';
        $dados['usuario_upgrade_id']      = Auth::id();
        $dados['data_hora_upgrade']       = now();
    }

    // ══════════════════════════════════════════════════════════════
    // FEATURE 2: Editar reserva no show
    // ══════════════════════════════════════════════════════════════

    /**
     * Exclui o contrato de check-in e redireciona para recriação.
     * Se assinado, o fato é registrado no log com nível danger.
     */
    public function excluirContratoCheckin(Request $request, $reserva_id)
    {
        $reserva  = $this->reservaService->getById($reserva_id, ['contratoCheckin', 'contratoCheckout']);
        $contrato = $reserva->contratoCheckin;

        if (!$contrato) {
            return redirect()->route('reserva.show', $reserva_id)
                ->with('warning', 'Nenhum contrato de check-in encontrado.');
        }

        if ($reserva->contratoCheckout) {
            return redirect()->route('reserva.show', $reserva_id)
                ->with('warning', 'Após o checkout, o contrato de check-in não pode mais ser excluído. Apenas a visualização fica disponível.');
        }

        $foiAssinado = $contrato->assinado === 'S';
        $veiculoId   = $contrato->veiculo_id;
        $veiculoNome = $contrato->veiculo?->nome ?? "ID {$veiculoId}";

        // Remove arquivos de fotos do storage
        $fotoCols = [
            'foto_direita_path', 'foto_esquerda_path', 'foto_frente_path',
            'foto_tras_path',    'foto_velocimetro_path', 'foto_combustivel_path',
        ];
        foreach ($fotoCols as $col) {
            if (!empty($contrato->$col)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($contrato->$col);
            }
        }

        // Remove arquivo de assinatura
        if ($foiAssinado && !empty($contrato->assinatura)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($contrato->assinatura);
        }

        $contrato->delete();

        // Remove danos vinculados a este contrato (e suas imagens)
        $danos = \App\Models\VeiculoDanos::where('contrato_id', $contrato->id)->get();
        foreach ($danos as $dano) {
            $dano->imagem()->delete();
            $dano->delete();
        }

        // Cancela movimentacoes vinculadas (check-in/checkout) para liberar disponibilidade do veiculo
        $movimentacoesCanceladas = $this->movimentacaoService->liberarBloqueioDeDisponibilidadePorReserva(
            (int) $reserva_id,
            [2, 3],
            'Cancelada por exclusao do contrato de check-in'
        );

        // Volta o status da reserva para "Confirmada" e remove vinculo do veiculo para bloquear checkout.
        $updates = [
            'status_reserva_id' => StatusReservaEnum::Confirmada->value,
        ];
        if (SchemaCache::hasColumn('reservas', 'veiculo_id')) {
            $updates['veiculo_id'] = null;
        }
        $reserva->update($updates);

        $this->reservaService->adicionaLog(
            $reserva_id,
            Auth::id(),
            json_encode([
                'acao'         => 'Contrato de check-in excluído',
                'foi_assinado' => $foiAssinado,
                'veiculo'      => $veiculoNome,
                'movimentacoes_canceladas' => $movimentacoesCanceladas ?? 0,
                'excluido_por' => Auth::user()?->name ?? Auth::user()?->nome ?? 'Usuário',
                'status_alterado_para' => $statusConfirmada?->nome ?? 'não alterado',
            ]),
            'Contrato de check-in excluído' . ($foiAssinado ? ' (estava assinado)' : '') . ' — status voltou para Confirmada',
            $foiAssinado ? 'danger' : 'warning'
        );

        return redirect()->route('reserva.checkin', $reserva_id)
            ->with('warning', $foiAssinado
                ? 'Contrato assinado excluído. Status da reserva voltou para Confirmada. Crie um novo contrato.'
                : 'Contrato excluído. Status da reserva voltou para Confirmada. Crie um novo contrato.'
            );
    }

    /**
     * Retorna o histórico de logs da reserva em JSON (para carregamento via AJAX).
     */
    public function historicoReserva(Request $request, $reserva_id)
    {
        $logs = \App\Models\ReservaLog::where('reserva_id', $reserva_id)
            ->with('usuario:id,nome')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($log) {
                $detalhes = [];
                if ($log->detalhes) {
                    $decoded = json_decode($log->detalhes, true);
                    $detalhes = is_array($decoded) ? $decoded : ['mensagem' => $log->detalhes];
                }
                // Remove chave 'acao' dos detalhes pois já está em $log->acao
                unset($detalhes['acao']);

                $nomeUsuario = $log->usuario?->name ?? $log->usuario?->nome ?? 'Sistema';

                return [
                    'id'         => $log->id,
                    'acao'       => $log->acao ?: ($detalhes['acao'] ?? 'Ação registrada'),
                    'nivel'      => $log->nivel ?? 'info',
                    'usuario'    => $nomeUsuario,
                    'data'       => $log->created_at?->format('d/m/Y H:i:s'),
                    'detalhes'   => $detalhes,
                ];
            });

        return response()->json(['logs' => $logs]);
    }

    // ══════════════════════════════════════════════════════════════

    /**
     * Retorna os dados da reserva + opcionais em JSON para edição inline no show.
     */
    public function dadosParaEdicaoNoShow(Request $request, $id)
    {
        $this->authorize('reserva.editar_no_show');

        $reserva = $this->reservaService->getById($id, ['opcionais', 'grupoFrota', 'estacaoRetirada', 'estacaoDevolucao']);
        if (!$reserva) {
            return response()->json(['error' => 'Reserva nao encontrada.'], 404);
        }

        $estacoes     = $this->estacaoService->getAllAtivos();
        $gruposVeiculo = $this->grupoFrotaService->getAllAtivosForSelectSubText('id', 'nome', 'valor_diario', null, 'asc');
        $tiposOpcionais = $this->tipoOpcionalService->getTipoOpcionais('nome', 'asc');

        return response()->json([
            'reserva'        => $reserva->only([
                'id', 'data_inicio', 'data_fim', 'estacao_retirada_id', 'estacao_devolucao_id',
                'grupo_frota_id', 'modelo_id', 'observacoes',
                'tarifa_base', 'valor_total_final', 'quantidade_dias',
            ]),
            'opcionais_ids'  => $reserva->opcionais->pluck('id')->toArray(),
            'opcionais_pivot' => $reserva->opcionais->map(fn ($o) => [
                'id'          => $o->id,
                'nome'        => $o->nome,
                'quantidade'  => $o->pivot->quantidade,
                'preco_total' => $o->pivot->preco_total,
            ]),
            'estacoes'       => $estacoes,
            'grupos_veiculo' => $gruposVeiculo,
            'tipos_opcionais' => $tiposOpcionais,
        ]);
    }

    /**
     * Atualiza datas, estações, observações e opcionais da reserva via show.
     */
    public function atualizarNoShow(Request $request, $id)
    {
        $this->authorize('reserva.editar_no_show');

        $reserva = $this->reservaService->getById($id, ['opcionais']);
        if (!$reserva) {
            return response()->json(['error' => 'Reserva nao encontrada.'], 404);
        }

        // Campos editáveis bloqueados após certos status
        $statusBloqueados = [3, 4, 5]; // cancelada, em andamento, concluída
        if (in_array((int) $reserva->status_reserva_id, $statusBloqueados, true)) {
            return response()->json([
                'error' => 'Reserva no status atual nao pode ser editada.',
            ], 422);
        }

        $request->validate([
            'data_inicio'            => 'nullable|date',
            'data_fim'               => 'nullable|date|after_or_equal:data_inicio',
            'estacao_retirada_id'    => 'nullable|integer|exists:estacoes,id',
            'estacao_devolucao_id'   => 'nullable|integer|exists:estacoes,id',
            'observacoes'            => 'nullable|string|max:1000',
            'opcionais'              => 'nullable|array',
            'opcionais.*.id'         => 'required|integer|exists:opcionais,id',
            'opcionais.*.quantidade' => 'required|integer|min:1',
            'opcionais.*.preco_total' => 'required|numeric|min:0',
        ]);

        $camposAntes = $reserva->only([
            'data_inicio', 'data_fim', 'estacao_retirada_id', 'estacao_devolucao_id', 'observacoes',
        ]);

        $dados = $request->only([
            'data_inicio', 'data_fim', 'estacao_retirada_id', 'estacao_devolucao_id', 'observacoes',
        ]);

        $dados = array_filter($dados, fn ($v) => $v !== null);
        $reserva->update($dados);

        // Atualiza opcionais se enviados
        if ($request->has('opcionais')) {
            $this->authorize('reserva.gerenciar_opcionais');
            $sync = [];
            foreach ($request->input('opcionais', []) as $opc) {
                $sync[$opc['id']] = [
                    'quantidade'  => $opc['quantidade'],
                    'preco_total' => $opc['preco_total'],
                ];
            }
            $reserva->opcionais()->sync($sync);
        }

        $this->reservaService->adicionaLog($id, Auth::id(), json_encode([
            'acao'   => 'Edicao no show',
            'antes'  => $camposAntes,
            'depois' => $dados,
        ]));

        return response()->json(['success' => true, 'message' => 'Reserva atualizada com sucesso.']);
    }

    // ══════════════════════════════════════════════════════════════
    // FEATURE 4: Calção no cartão (Stripe)
    // ══════════════════════════════════════════════════════════════

    /**
     * Exibe a tela de calção após assinatura do contrato.
     */
    public function calcaoCheckin(Request $request, $reserva_id)
    {
        $this->authorize('calcao.autorizar');

        $reserva = $this->reservaService->getById($reserva_id, ['contratoCheckin', 'grupoFrota', 'modelos', 'usuario']);
        if (!$reserva || !$reserva->contratoCheckin || $reserva->contratoCheckin->assinado !== 'S') {
            return redirect()->route('reserva.show', $reserva_id)
                ->with('error', 'O contrato de check-in deve ser assinado antes de registrar o calcao.');
        }

        $calcaoExistente = $reserva->calcao;
        $calculo         = $this->calcaoService->calcularValor($reserva);

        $empresa       = $this->gatewayEmpresaConfigService->empresaAtual();
        $stripePublicKey = $empresa?->stripe_public_key ?? '';
        $metodosPagamento = [];
        $user = $reserva->usuario;
        if ($empresa && !empty($user?->stripe_id) && $empresa->stripe_secret) {
            try {
                $metodosPagamento = $this->clienteStripeService->getPaymentMethods(
                    $user->stripe_id,
                    $empresa->stripe_secret
                );
            } catch (\Stripe\Exception\InvalidRequestException $e) {
                \Log::warning('Stripe customer invalid or not found for user when loading calcao payment methods - attempting reconcile', [
                    'user_id' => $user->id ?? null,
                    'stripe_id' => $user->stripe_id ?? null,
                    'exception' => $e->getMessage(),
                ]);

                try {
                    $novoStripeId = $this->clienteStripeService->garantirClienteValido($user->stripe_id, $user->email ?? '', $user->nome ?? 'Cliente', $empresa->stripe_secret);
                    if (!empty($novoStripeId) && $novoStripeId !== ($user->stripe_id ?? '')) {
                        $this->userService->updateUser($user->id, ['stripe_id' => $novoStripeId]);
                        $user = $this->userService->getById($user->id);
                    }

                    $metodosPagamento = $this->clienteStripeService->getPaymentMethods($user->stripe_id, $empresa->stripe_secret);
                } catch (\Exception $e2) {
                    \Log::warning('Erro ao reconciliar cliente Stripe ou ao listar métodos após reconcile (calcao)', ['exception' => $e2->getMessage()]);
                    $metodosPagamento = [];
                }
            } catch (\Exception $e) {
                \Log::error('Erro ao listar métodos de pagamento para calcao', ['exception' => $e->getMessage()]);
                $metodosPagamento = [];
            }
        }

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Reservas', route('reserva.index')),
            new BreadCrumbItem('Detalhes da Reserva Nº' . $reserva_id, route('reserva.show', $reserva_id)),
            new BreadCrumbItem('Calcao'),
        ]);

        return view('reserva.calcao', compact(
            'reserva', 'calcaoExistente', 'calculo',
            'stripePublicKey', 'metodosPagamento', 'breadcrumb'
        ))->with('titulo', 'Calcao — Reserva Nº ' . $reserva->id);
    }

    /**
     * Cria pré-autorização no Stripe e persiste o calção.
     */
    public function autorizarCalcao(AutorizarCalcaoRequest $request, $reserva_id)
    {
        $this->authorize('calcao.autorizar');

        $reserva = $this->reservaService->getById($reserva_id, ['contratoCheckin', 'usuario']);
        if (!$reserva || !$reserva->contratoCheckin) {
            return response()->json(['error' => 'Contrato de check-in nao encontrado.'], 404);
        }

        if ($reserva->contratoCheckin->assinado !== 'S') {
            return response()->json(['error' => 'Contrato deve estar assinado antes do calcao.'], 422);
        }

        // Idempotência: não duplicar calcão autorizado
        $calcaoExistente = $reserva->calcao;
        if ($calcaoExistente && $calcaoExistente->status === Calcao::STATUS_AUTORIZADO) {
            return response()->json(['success' => true, 'calcao_id' => $calcaoExistente->id, 'message' => 'Calcao ja autorizado.']);
        }

        try {
            $calcao = $this->calcaoService->autorizarCalcao(
                $reserva,
                $reserva->contratoCheckin,
                $request->input('payment_method_id')
            );

            $this->reservaService->adicionaLog($reserva_id, Auth::id(), json_encode([
                'acao'        => 'Calcao autorizado',
                'calcao_id'   => $calcao->id,
                'valor'       => $calcao->valor_autorizado,
                'payment_intent' => $calcao->payment_intent_id,
            ]));

            return response()->json([
                'success'   => true,
                'calcao_id' => $calcao->id,
                'message'   => 'Calcao autorizado com sucesso. Valor pre-autorizado: R$ ' . number_format((float) $calcao->valor_autorizado, 2, ',', '.'),
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Falha inesperada ao autorizar calcao.'], 500);
        }
    }

    /**
     * Vincula um PaymentMethod (já criado no Stripe via JS) ao cliente da reserva.
     * Chamado após stripe.createPaymentMethod() no front — persiste o cartão no Stripe.
     */
    public function salvarCartaoCalcao(Request $request, $reserva_id)
    {
        $this->authorize('calcao.autorizar');

        $request->validate(['payment_method_id' => 'required|string']);

        $reserva = $this->reservaService->getById($reserva_id, ['usuario']);
        if (!$reserva || !$reserva->usuario) {
            return response()->json(['error' => 'Reserva ou cliente não encontrado.'], 404);
        }

        $empresa = $this->gatewayEmpresaConfigService->empresaAtual();
        if (!$empresa || !$empresa->stripe_secret) {
            return response()->json(['error' => 'Stripe não configurado.'], 422);
        }

        $user = $reserva->usuario;

        try {
            // Garante que o cliente existe no Stripe
            $stripeId = $this->clienteStripeService->garantirClienteValido(
                $user->stripe_id ?? null,
                $user->email ?? '',
                $user->nome ?? 'Cliente',
                $empresa->stripe_secret
            );

            if (empty($user->stripe_id) || $user->stripe_id !== $stripeId) {
                $this->userService->updateUser($user->id, ['stripe_id' => $stripeId]);
            }

            // Vincula o PaymentMethod (já criado via Stripe.js) ao cliente
            $pm = $this->clienteStripeService->attachPaymentMethod(
                $request->input('payment_method_id'),
                $stripeId,
                $empresa->stripe_secret
            );

            return response()->json([
                'success' => true,
                'pm' => [
                    'id'        => $pm->id,
                    'brand'     => $pm->card->brand ?? '',
                    'last4'     => $pm->card->last4 ?? '',
                    'exp_month' => $pm->card->exp_month ?? '',
                    'exp_year'  => $pm->card->exp_year ?? '',
                ],
            ]);
        } catch (\Throwable $e) {
            \Log::error('Erro ao salvar cartão no calção', ['exception' => $e->getMessage()]);
            return response()->json(['error' => 'Não foi possível salvar o cartão: ' . $e->getMessage()], 422);
        }
    }

    /**
     * Captura (total ou parcial) o valor do calção no checkout.
     */
    public function capturarCalcao(CapturarCalcaoRequest $request, $reserva_id)
    {
        $this->authorize('calcao.capturar');

        $reserva = $this->reservaService->getById($reserva_id);
        $calcao  = $reserva?->calcao;

        if (!$calcao) {
            return response()->json(['error' => 'Calcao nao encontrado para esta reserva.'], 404);
        }

        try {
            $calcao = $this->calcaoService->capturar(
                $calcao,
                (float) $request->input('valor_captura'),
                $request->input('justificativa')
            );

            $this->reservaService->adicionaLog($reserva_id, Auth::id(), json_encode([
                'acao'           => 'Calcao capturado',
                'calcao_id'      => $calcao->id,
                'valor_capturado' => $calcao->valor_capturado,
            ]));

            return response()->json(['success' => true, 'message' => 'Calcao capturado com sucesso.', 'calcao' => $calcao->only(['id', 'status', 'valor_capturado'])]);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Libera (cancela) o hold do calção no checkout.
     */
    public function liberarCalcao(Request $request, $reserva_id)
    {
        $this->authorize('calcao.cancelar');

        $request->validate(['observacao' => 'nullable|string|max:500']);

        $reserva = $this->reservaService->getById($reserva_id);
        $calcao  = $reserva?->calcao;

        if (!$calcao) {
            return response()->json(['error' => 'Calcao nao encontrado para esta reserva.'], 404);
        }

        try {
            $calcao = $this->calcaoService->cancelar($calcao, $request->input('observacao', ''));

            $this->reservaService->adicionaLog($reserva_id, Auth::id(), json_encode([
                'acao'      => 'Calcao liberado (cancelado)',
                'calcao_id' => $calcao->id,
            ]));

            return response()->json(['success' => true, 'message' => 'Calcao liberado com sucesso.']);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Registra calção pago presencialmente (dinheiro/pix/transferência), sem Stripe.
     * Cria registro em `calcoes` e uma `transacao` financeira positiva vinculada à reserva.
     */
    public function registrarCalcaoPresencial(Request $request, $reserva_id)
    {
        $this->authorize('calcao.autorizar');

        $request->validate([
            'metodo' => 'required|string|max:50',
            'valor'  => 'nullable|numeric|min:0.01',
            'observacao' => 'nullable|string|max:500',
        ]);

        $reserva = $this->reservaService->getById((int) $reserva_id, ['contratoCheckin', 'usuario', 'calcao']);
        if (!$reserva || !$reserva->contratoCheckin || $reserva->contratoCheckin->assinado !== 'S') {
            return response()->json(['error' => 'O contrato de check-in deve estar assinado antes do calção.'], 422);
        }

        if ($reserva->calcao) {
            return response()->json(['error' => 'Já existe um calção registrado para esta reserva.'], 422);
        }

        $calculo = $this->calcaoService->calcularValor($reserva);
        $valorCalculado = (float) ($calculo['valor_calculado'] ?? 0);
        if ($valorCalculado <= 0) {
            return response()->json(['error' => 'Esta reserva não requer calção.'], 422);
        }

        $valorPago = (float) ($request->input('valor') ?: $valorCalculado);
        $metodo = trim((string) $request->input('metodo'));
        $observacao = trim((string) $request->input('observacao', ''));

        DB::beginTransaction();
        try {
            $calcao = Calcao::create([
                'empresa_id' => $reserva->empresa_id ?? null,
                'reserva_id' => (int) $reserva_id,
                'contrato_checkin_id' => $reserva->contratoCheckin->id,
                'cliente_id' => $reserva->user_id,
                'valor_calculado' => $valorCalculado,
                'valor_capturado' => $valorPago,
                'status' => Calcao::STATUS_CAPTURADO,
                'data_hora_captura' => now(),
                'usuario_responsavel_id' => Auth::id(),
                'observacao' => $observacao !== '' ? $observacao : ('Calção pago presencialmente via ' . $metodo),
                'tipo_regra_aplicada' => $calculo['tipo_regra_aplicada'] ?? null,
                'origem_regra_aplicada' => $calculo['origem_regra_aplicada'] ?? null,
                'configuracao_calcao_id' => $calculo['configuracao_calcao_id'] ?? null,
                'stripe_response' => [
                    'origem' => 'presencial',
                    'metodo' => $metodo,
                ],
            ]);

            // Registra movimentação financeira em `transacoes`
            $transacaoPayload = [
                'user_id' => $reserva->user_id,
                'pago_por_user_id' => Auth::id(),
                'reserva_id' => $reserva->id,
                'valor' => $valorPago,
                'moeda' => 'BRL',
                'metodo_pagamento' => $metodo,
                'status' => 'approved',
                'info_pagamento' => [
                    'tipo' => 'calcao',
                    'acao' => 'recebido_presencial',
                    'calcao_id' => $calcao->id,
                ],
            ];
            if (SchemaCache::hasColumn('transacoes', 'confirmado_em')) {
                $transacaoPayload['confirmado_em'] = now();
            }
            if (SchemaCache::hasColumn('transacoes', 'confirmado_por_user_id')) {
                $transacaoPayload['confirmado_por_user_id'] = Auth::id();
            }
            if (SchemaCache::hasColumn('transacoes', 'empresa_id')) {
                $transacaoPayload['empresa_id'] = $reserva->empresa_id ?? null;
            }

            Transacao::create($transacaoPayload);

            $this->reservaService->adicionaLog((int) $reserva_id, Auth::id(), json_encode([
                'acao' => 'Calção presencial registrado',
                'calcao_id' => $calcao->id,
                'metodo' => $metodo,
                'valor' => $valorPago,
            ]));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Calção presencial registrado com sucesso.',
                'calcao' => $calcao->only(['id', 'status', 'valor_calculado', 'valor_capturado']),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => 'Falha ao registrar calção presencial: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Devolve calção presencial (estorno/saída de caixa) e registra transação negativa.
     */
    public function devolverCalcaoPresencial(Request $request, $reserva_id)
    {
        $this->authorize('calcao.cancelar');

        $request->validate([
            'observacao' => 'nullable|string|max:500',
        ]);

        $reserva = $this->reservaService->getById((int) $reserva_id, ['calcao']);
        $calcao = $reserva?->calcao;

        if (!$reserva || !$calcao) {
            return response()->json(['error' => 'Calção não encontrado para esta reserva.'], 404);
        }

        // Somente calção presencial (sem PaymentIntent) pode ser devolvido por aqui.
        if (!empty($calcao->payment_intent_id)) {
            return response()->json(['error' => 'Este calção foi registrado via Stripe. Use "Liberar Hold" ou fluxo de captura/liberação.'], 422);
        }

        if ($calcao->status === Calcao::STATUS_CANCELADO) {
            return response()->json(['error' => 'Calção já devolvido.'], 422);
        }

        $valor = (float) ($calcao->valor_capturado ?: $calcao->valor_autorizado ?: $calcao->valor_calculado ?: 0);
        if ($valor <= 0) {
            return response()->json(['error' => 'Valor inválido para devolução.'], 422);
        }

        $observacao = trim((string) $request->input('observacao', 'Devolução do calção presencial no checkout'));
        $metodo = (string) (($calcao->stripe_response['metodo'] ?? null) ?: 'presencial');

        DB::beginTransaction();
        try {
            $calcao->update([
                'status' => Calcao::STATUS_CANCELADO,
                'data_hora_cancelamento' => now(),
                'usuario_responsavel_id' => Auth::id(),
                'observacao' => $observacao,
            ]);

            $payload = [
                'user_id' => $reserva->user_id,
                'pago_por_user_id' => Auth::id(),
                'reserva_id' => $reserva->id,
                'valor' => -1 * abs($valor),
                'moeda' => 'BRL',
                'metodo_pagamento' => $metodo,
                'status' => 'refunded',
                'info_pagamento' => [
                    'tipo' => 'calcao',
                    'acao' => 'devolvido_presencial',
                    'calcao_id' => $calcao->id,
                ],
            ];
            if (SchemaCache::hasColumn('transacoes', 'confirmado_em')) {
                $payload['confirmado_em'] = now();
            }
            if (SchemaCache::hasColumn('transacoes', 'confirmado_por_user_id')) {
                $payload['confirmado_por_user_id'] = Auth::id();
            }
            if (SchemaCache::hasColumn('transacoes', 'empresa_id')) {
                $payload['empresa_id'] = $reserva->empresa_id ?? null;
            }
            Transacao::create($payload);

            $this->reservaService->adicionaLog((int) $reserva_id, Auth::id(), json_encode([
                'acao' => 'Calção presencial devolvido',
                'calcao_id' => $calcao->id,
                'valor' => $valor,
            ]));

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Calção devolvido com sucesso.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => 'Falha ao devolver calção: ' . $e->getMessage()], 500);
        }
    }

    // ══════════════════════════════════════════════════════════════
    // Confirmar pagamento em dinheiro
    // ══════════════════════════════════════════════════════════════

    public function confirmarPagamentoDinheiro(Request $request, $reserva_id)
    {
        $transacaoId = $request->input('transacao_id');
        $transacao = Transacao::where('reserva_id', $reserva_id)->where('id', $transacaoId)->firstOrFail();

        if ($transacao->confirmado_em) {
            return response()->json(['error' => 'Pagamento já confirmado.'], 422);
        }

        $transacao->update([
            'status'                 => 'approved',
            'confirmado_em'          => now(),
            'confirmado_por_user_id' => Auth::id(),
        ]);

        $this->reservaService->adicionaLog($reserva_id, Auth::id(), json_encode([
            'acao'        => 'Pagamento em dinheiro confirmado',
            'transacao_id'=> $transacao->id,
            'valor'       => $transacao->valor,
        ]), 'success');

        return response()->json(['success' => true, 'message' => 'Pagamento confirmado com sucesso.']);
    }
}
