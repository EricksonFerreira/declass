<?php

namespace App\Http\Controllers\Reserva;

use App\Actions\Reserva\Checkin\Contrato\Exibir\ExibirContratoCheckinAction;
use App\Actions\Reserva\Checkin\Finalizar\FinalizarCheckinAction;
use App\Actions\Reserva\Checkout\Contrato\AssinarContratoCheckoutAction;
use App\Actions\Reserva\Pagamento\Calcao\AutorizarCalcaoAction;
use App\Actions\Reserva\Pagamento\Calcao\CapturarCalcaoAction;
use App\Actions\Reserva\Pagamento\Calcao\DevolverCalcaoPresencialAction;
use App\Actions\Reserva\Pagamento\Calcao\ExibirCalcaoCheckinAction;
use App\Actions\Reserva\Pagamento\Calcao\LiberarCalcaoAction;
use App\Actions\Reserva\Pagamento\Calcao\RegistrarCalcaoPresencialAction;
use App\Actions\Reserva\Pagamento\Calcao\SalvarCartaoCalcaoAction;
use App\Actions\Reserva\Pagamento\ConfirmarPagamentoDinheiroAction;
use App\Actions\Reserva\Pagamento\ExibirFormularioPagamentoAction;
use App\Actions\Reserva\Pagamento\RealizarPagamentoAction;
use App\Actions\Reserva\Exibir\ExibirReservaComumAction;
use App\Actions\Reserva\Renovacao\RenovarReservaComumAction;
use App\DTO\BreadCrumbItem;
use App\Enums\StatusReservaEnum;
use App\Exceptions\ReservaNaoEncontradaException;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\Calcao\AutorizarCalcaoRequest;
use App\Http\Requests\Calcao\CapturarCalcaoRequest;
use App\Http\Requests\Reserva\AdicionarDanoVeiculoRequest;
use App\Http\Requests\Reserva\AssinarContratoRequest;
use App\Http\Requests\Reserva\CriarContratoCheckInRequest;
use App\Http\Requests\Reserva\FinalizarCheckoutVistoriaRequest;
use App\Http\Requests\Reserva\StoreReservaRequest;
use App\Http\Requests\Reserva\UpdateReservaRequest;
use App\Models\Calcao;
use App\Models\ContratoCheckin;
use App\Models\ContratoCheckout;
use App\Models\Cupom;
use App\Models\Estacao;
use App\Models\Feriado;
use App\Models\Multa;
use App\Models\Plano;
use App\Models\Reserva;
use App\Models\ReservaCodigo;
use App\Models\ReservaMotoristaApp;
use App\Models\ReservaMotoristaAppPagamento;
use App\Models\TermoConsentimento;
use App\Models\Transacao;
use App\Models\Veiculo;
use App\Services\BreadcrumbService;
use App\Services\CalcaoService;
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
use App\Services\PaisService;
use App\Services\PdfService;
use App\Services\RegraService;
use App\Services\RenovacaoService;
use App\Services\Reserva\ReservaResolver;
use App\Services\ReservaCodigoService;
use App\Services\ReservaService;
use App\Services\StatusReservaService;
use App\Services\TipoOpcionalService;
use App\Services\UpgradeVeiculoService;
use App\Services\UsuarioService;
use App\Services\VeiculoDanoService;
use App\Services\VeiculoService;
use App\Support\EmpresaContext;
use App\Support\SchemaCache;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Carbon\Carbon;
use DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ReservaController extends BaseController implements FiltroInterface, ResourceInterface
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    protected $reservaService;

    protected $breadcrumbService;

    protected $upgradeVeiculoService;

    protected $calcaoService;

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

    protected $renovacaoService;

    protected $realizarPagamentoAction;

    protected $exibirFormularioPagamentoAction;

    protected $autorizarCalcaoAction;

    protected $capturarCalcaoAction;

    protected $liberarCalcaoAction;

    protected $registrarCalcaoPresencialAction;

    protected $devolverCalcaoPresencialAction;

    protected $confirmarPagamentoDinheiroAction;

    protected $exibirCalcaoCheckinAction;

    protected $salvarCartaoCalcaoAction;

    protected $renovarReservaComumAction;

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
        CalcaoService $calcaoService,
        RealizarPagamentoAction $realizarPagamentoAction,
        ExibirFormularioPagamentoAction $exibirFormularioPagamentoAction,
        AutorizarCalcaoAction $autorizarCalcaoAction,
        CapturarCalcaoAction $capturarCalcaoAction,
        LiberarCalcaoAction $liberarCalcaoAction,
        RegistrarCalcaoPresencialAction $registrarCalcaoPresencialAction,
        DevolverCalcaoPresencialAction $devolverCalcaoPresencialAction,
        ConfirmarPagamentoDinheiroAction $confirmarPagamentoDinheiroAction,
        ExibirCalcaoCheckinAction $exibirCalcaoCheckinAction,
        SalvarCartaoCalcaoAction $salvarCartaoCalcaoAction,
        RenovacaoService $renovacaoService,
        RenovarReservaComumAction $renovarReservaComumAction
    ) {
        $this->titulo = 'Reservas';
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
        $this->renovacaoService = $renovacaoService;
        $this->regraService = $regraService;
        $this->paisService = $paisService;
        $this->renovacaoService = $renovacaoService;
        $this->nfseService = $nfseService;
        $this->statusReservaService = $statusReservaService;
        $this->renovacaoService = $renovacaoService;
        $this->upgradeVeiculoService = $upgradeVeiculoService;
        $this->calcaoService = $calcaoService;
        $this->realizarPagamentoAction = $realizarPagamentoAction;
        $this->exibirFormularioPagamentoAction = $exibirFormularioPagamentoAction;
        $this->autorizarCalcaoAction = $autorizarCalcaoAction;
        $this->capturarCalcaoAction = $capturarCalcaoAction;
        $this->liberarCalcaoAction = $liberarCalcaoAction;
        $this->registrarCalcaoPresencialAction = $registrarCalcaoPresencialAction;
        $this->devolverCalcaoPresencialAction = $devolverCalcaoPresencialAction;
        $this->confirmarPagamentoDinheiroAction = $confirmarPagamentoDinheiroAction;
        $this->exibirCalcaoCheckinAction = $exibirCalcaoCheckinAction;
        $this->salvarCartaoCalcaoAction = $salvarCartaoCalcaoAction;
        $this->renovarReservaComumAction = $renovarReservaComumAction;
    }

    public function index()
    {
        $this->configurarSessao();

        $user = Auth::user();
        if ($user && ! $user->isAdministrador() && $this->isCliente($user)) {
            $reservas = $this->minhasReservasUnificadas($user, 10);
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

        $reservas = $this->reservasUnificadasAdmin($user, $perPage);

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
        $user = Auth::user();
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $reservas = $this->minhasReservasUnificadas($user, $perPage);
        $multas = $this->multasDoCliente($user, false);
        $multasPendentesCount = $this->multasPendentesCount($user);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Minhas Reservas');
        $perPages = [5, 10, 15, 25, 50, 100];
        $filters = [];
        $additionalFilters = [];
        $itensFiltered = [];

        return view('reserva.index-cliente', compact(
            'reservas',
            'multas',
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

    private function empresaIdParaMinhasReservas($user): ?int
    {
        $contextEmpresaId = app(EmpresaContext::class)->empresaId();
        if ($contextEmpresaId !== null && (int) $contextEmpresaId > 0) {
            return (int) $contextEmpresaId;
        }

        $sessionEmpresaId = (int) session('empresa_context_id', 0);
        if ($sessionEmpresaId > 0) {
            return $sessionEmpresaId;
        }

        $userEmpresaId = (int) ($user?->empresa_id ?? 0);

        return $userEmpresaId > 0 ? $userEmpresaId : null;
    }

    private function reservaCodigoPorIdentificador(string|int $identificador): ?ReservaCodigo
    {
        $codigo = strtoupper(trim((string) $identificador));

        if ($codigo === '' || ctype_digit($codigo)) {
            return null;
        }

        return ReservaCodigo::query()
            ->whereRaw('UPPER(codigo) = ?', [$codigo])
            ->first();
    }

    private function dadosCodigoReservaPorIdentificador(string|int $identificador): ?object
    {
        $codigo = strtoupper(trim((string) $identificador));

        if ($codigo === '' || ctype_digit($codigo) || ! Schema::hasTable('reserva_codigos')) {
            return null;
        }

        return DB::table('reserva_codigos')
            ->whereRaw('UPPER(codigo) = ?', [$codigo])
            ->first();
    }

    private function identificadorReservaPublico($item): string
    {
        return (string) ($item->codigo_reserva ?? $item->codigoReserva?->codigo ?? $item->id);
    }

    private function idReservaNumericoDoCodigoLegado(string|int $identificador, string $prefixo): ?int
    {
        $codigo = strtoupper(trim((string) $identificador));
        $pattern = '/^'.preg_quote(strtoupper($prefixo), '/').'-\d{6}-(\d{6,})$/';

        if (! preg_match($pattern, $codigo, $matches)) {
            return null;
        }

        return (int) ltrim($matches[1], '0') ?: null;
    }

    private function idReservaPorIdentificador(string|int $identificador, string $prefixo, ?object $codigoReserva, string $campoCodigo): string|int
    {
        if ($codigoReserva && (int) ($codigoReserva->{$campoCodigo} ?? 0) > 0) {
            return (int) $codigoReserva->{$campoCodigo};
        }

        if (! ctype_digit((string) $identificador)) {
            return $this->idReservaNumericoDoCodigoLegado($identificador, $prefixo) ?? $identificador;
        }

        return $identificador;
    }

    private function identificadorComecaComPrefixo(string|int $identificador, string $prefixo): bool
    {
        return str_starts_with(strtoupper(trim((string) $identificador)), strtoupper($prefixo).'-');
    }

    private function minhasReservasUnificadas($user, int $perPage)
    {
        $empresaId = $this->empresaIdParaMinhasReservas($user);

        $reservasQuery = DB::table('reservas')
            ->selectRaw("
                'reserva' as origem_tipo,
                reservas.id as id,
                reservas.empresa_id as empresa_id,
                reservas.user_id as user_id,
                rc.codigo as codigo_reserva,
                reservas.data_inicio as data_inicio,
                reservas.data_fim as data_fim,
                reservas.quantidade_dias as quantidade_dias,
                reservas.tarifa_base as tarifa_base,
                reservas.valor_total_tarifa_base as valor_total_tarifa_base,
                reservas.valor_total_com_desconto as valor_total_com_desconto,
                reservas.valor_opcionais as valor_opcionais,
                reservas.valor_total_final as valor_total_final,
                reservas.pagamento_efetuado as pagamento_efetuado,
                NULL as status_pagamento,
                usuarios.nome as user_nome,
                usuarios.email as user_email,
                status_reserva.nome as status_nome,
                status_reserva.cor as status_cor,
                status_reserva.descricao as status_descricao,
                grupos_frota.nome as grupo_nome,
                modelos.nome as modelo_nome,
                estacao_retirada.nome as estacao_retirada_nome,
                estacao_devolucao.nome as estacao_devolucao_nome,
                reservas.created_at as created_at,
                reservas.updated_at as updated_at
            ")
            ->leftJoin('status_reserva', 'status_reserva.id', '=', 'reservas.status_reserva_id')
            ->leftJoin('grupos_frota', 'grupos_frota.id', '=', 'reservas.grupo_frota_id')
            ->leftJoin('modelos', 'modelos.id', '=', 'reservas.modelo_id')
            ->leftJoin('estacoes as estacao_retirada', 'estacao_retirada.id', '=', 'reservas.estacao_retirada_id')
            ->leftJoin('estacoes as estacao_devolucao', 'estacao_devolucao.id', '=', 'reservas.estacao_devolucao_id')
            ->leftJoin('reserva_codigos as rc', 'rc.reserva_id', '=', 'reservas.id')
            ->leftJoin('users as usuarios', 'usuarios.id', '=', 'reservas.user_id')
            ->where('reservas.user_id', (int) $user->id);

        if (SchemaCache::hasColumn('reservas', 'empresa_id') && $empresaId !== null) {
            $reservasQuery->where('reservas.empresa_id', $empresaId);
        }

        $motoristaQuery = DB::table('reservas_motorista_app')
            ->selectRaw("
                'motorista_app' as origem_tipo,
                reservas_motorista_app.id as id,
                reservas_motorista_app.empresa_id as empresa_id,
                reservas_motorista_app.user_id as user_id,
                rc_motorista.codigo as codigo_reserva,
                reservas_motorista_app.data_inicio as data_inicio,
                reservas_motorista_app.data_fim as data_fim,
                reservas_motorista_app.quantidade_dias as quantidade_dias,
                reservas_motorista_app.valor_plano as tarifa_base,
                reservas_motorista_app.valor_plano as valor_total_tarifa_base,
                reservas_motorista_app.valor_plano as valor_total_com_desconto,
                reservas_motorista_app.valor_opcionais as valor_opcionais,
                reservas_motorista_app.valor_total_final as valor_total_final,
                reservas_motorista_app.pagamento_efetuado as pagamento_efetuado,
                reservas_motorista_app.status_pagamento as status_pagamento,
                usuarios_motorista.nome as user_nome,
                usuarios_motorista.email as user_email,
                reservas_motorista_app.status as status_nome,
                CASE
                    WHEN reservas_motorista_app.status = 'pendente_pagamento' THEN 'amber'
                    WHEN reservas_motorista_app.status = 'pendente_analise' THEN 'slate'
                    WHEN reservas_motorista_app.status = 'ativa' THEN 'green'
                    WHEN reservas_motorista_app.status = 'cancelada' THEN 'red'
                    ELSE 'slate'
                END as status_cor,
                CASE
                    WHEN reservas_motorista_app.status = 'pendente_pagamento' THEN 'Pendente de pagamento'
                    WHEN reservas_motorista_app.status = 'pendente_analise' THEN 'Pendente de análise'
                    WHEN reservas_motorista_app.status = 'ativa' THEN 'Ativa'
                    WHEN reservas_motorista_app.status = 'cancelada' THEN 'Cancelada'
                    ELSE 'Motorista de app'
                END as status_descricao,
                'Motorista de app' as grupo_nome,
                planos_motorista_app.nome as modelo_nome,
                estacoes.nome as estacao_retirada_nome,
                NULL as estacao_devolucao_nome,
                reservas_motorista_app.created_at as created_at,
                reservas_motorista_app.updated_at as updated_at
            ")
            ->leftJoin('planos_motorista_app', 'planos_motorista_app.id', '=', 'reservas_motorista_app.plano_motorista_app_id')
            ->leftJoin('estacoes', 'estacoes.id', '=', 'reservas_motorista_app.estacao_retirada_id')
            ->leftJoin('reserva_codigos as rc_motorista', 'rc_motorista.reserva_motorista_app_id', '=', 'reservas_motorista_app.id')
            ->leftJoin('users as usuarios_motorista', 'usuarios_motorista.id', '=', 'reservas_motorista_app.user_id')
            ->where('reservas_motorista_app.user_id', (int) $user->id);

        if (SchemaCache::hasColumn('reservas_motorista_app', 'empresa_id') && $empresaId !== null) {
            $motoristaQuery->where('reservas_motorista_app.empresa_id', $empresaId);
        }

        $unified = DB::query()
            ->fromSub($reservasQuery->unionAll($motoristaQuery), 'reservas_unificadas')
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        $paginator = $unified->paginate($perPage);

        $paginator->getCollection()->transform(function ($item) {
            $item->origem_tipo = (string) ($item->origem_tipo ?? 'reserva');
            $item->is_motorista_app = $item->origem_tipo === 'motorista_app';
            $item->status_pagamento = strtolower(trim((string) ($item->status_pagamento ?? '')));
            $item->pagamento_pendente = $item->is_motorista_app
                ? in_array($item->status_pagamento, ['pendente', 'aguardando', 'vencido', 'atrasado'], true) || ! in_array($item->pagamento_efetuado, [true, 1, '1', 'S', 's', 'sim', 'SIM'], true)
                : ! in_array($item->pagamento_efetuado, [true, 1, '1', 'S', 's', 'sim', 'SIM'], true);
            $statusNome = strtolower(trim((string) ($item->status_nome ?? '')));
            if ($item->is_motorista_app) {
                $item->status_exibicao = match ($statusNome) {
                    'cancelada' => 'Cancelada',
                    'ativa' => 'Ativa',
                    'pendente_analise' => 'Criada',
                    'pendente_pagamento', 'pendente', 'criada' => 'Criada',
                    default => 'Criada',
                };
            } else {
                $item->status_exibicao = $item->status_nome ?: 'Status';
            }
            $item->status_card_class = match ($statusNome) {
                'confirmada', 'ativa' => 'rc-status--green',
                'cancelada' => 'rc-status--red',
                'pendente_analise' => 'rc-status--slate',
                'pendente_pagamento', 'pendente', 'criada' => 'rc-status--amber',
                default => 'rc-status--slate',
            };
            $item->tipo_label = $item->is_motorista_app ? 'Motorista APP' : 'Reservas';
            $item->statusReserva = (object) [
                'nome' => (string) ($item->status_nome ?? '-'),
                'cor' => (string) ($item->status_cor ?? 'secondary'),
                'descricao' => (string) ($item->status_descricao ?? ''),
            ];
            $item->estacaoRetirada = (object) [
                'nome' => (string) ($item->estacao_retirada_nome ?? '-'),
            ];
            $item->estacaoDevolucao = (object) [
                'nome' => (string) ($item->estacao_devolucao_nome ?? '-'),
            ];
            $item->grupoFrota = (object) [
                'nome' => (string) ($item->grupo_nome ?? '-'),
            ];
            $item->modelos = (object) [
                'nome' => (string) ($item->modelo_nome ?? '-'),
            ];
            $item->usuario = (object) [
                'nome' => (string) ($item->user_nome ?? '-'),
                'email' => (string) ($item->user_email ?? '-'),
            ];
            $item->nfseAtual = null;
            $item->codigo_exibicao = (string) ($item->codigo_reserva ?? ('#'.$item->id));
            $identificadorPublico = $this->identificadorReservaPublico($item);
            $item->url_detalhes = route('reserva.minhas.show', ['id' => $identificadorPublico]);

            return $item;
        });

        return $paginator;
    }

    private function multasDoCliente($user, bool $apenasPendentes = true)
    {
        if (! $user) {
            return collect();
        }

        $empresaId = $this->empresaIdParaMinhasReservas($user);

        $query = Multa::query()
            ->with(['reserva.usuario', 'veiculo.modelo', 'cliente'])
            ->orderByRaw('CASE WHEN data_vencimento IS NULL THEN 1 ELSE 0 END')
            ->orderBy('data_vencimento')
            ->orderByDesc('id');

        if ($apenasPendentes) {
            $query->whereNull('pago_em');
        }

        if (SchemaCache::hasColumn('multas', 'empresa_id') && $empresaId !== null) {
            $query->where('empresa_id', $empresaId);
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
        if (! $user) {
            return 0;
        }

        $empresaId = $this->empresaIdParaMinhasReservas($user);

        $query = Multa::query()->whereNull('pago_em');

        if (SchemaCache::hasColumn('multas', 'empresa_id') && $empresaId !== null) {
            $query->where('empresa_id', $empresaId);
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
            'N' => 'Inativo',
        ];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Reservas', 'reserva.index', 'Criar');
        $usuarioAutenticado = Auth::user()?->loadMissing(['estacoesPermitidas']);
        $estacoesPermitidasUsuario = $usuarioAutenticado && ! $usuarioAutenticado->hasAcessoTodasEstacoes()
            ? $usuarioAutenticado->estacoesPermitidas->sortBy('nome')->values()
            : collect();

        // Consultando as variÃƒÂ¡veis necessÃƒÂ¡rias
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
                static fn (string $falta): bool => ! str_contains(mb_strtolower($falta), 'estacao ativa para iniciar reservas')
            ));
            $preRequisitosReserva['faltas'][] = 'VocÃƒÂª possui estaçÃƒÂµes no perfil, mas nenhuma estÃƒÂ¡ habilitada para retirada/devolução nesta reserva.';
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

        if (! $reserva) {
            return redirect()->back()->with('error', 'Falha ao criar a reserva.');
        }

        // Adiciona um log da ação realizada
        $json = json_encode([
            'acao' => 'Reserva criada',
            'data' => $data,
        ]);
        $this->reservaService->adicionaLog($reserva->id, auth()->user()->id, $json);

        return redirect()->route('reserva.show', $reserva->codigoReserva?->codigo ?? $reserva->id)
            ->with('success', 'Reserva criada com sucesso.');
    }

    public function renovacao($codigo)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        $codigoReserva = $this->dadosCodigoReservaPorIdentificador($codigo);

        if (($codigoReserva && $codigoReserva->tipo_origem === 'motorista_app')
            || (! $codigoReserva && $this->identificadorComecaComPrefixo($codigo, 'MAP'))
        ) {
            $idMotoristaApp = $this->idReservaPorIdentificador(
                $codigo,
                'MAP',
                $codigoReserva,
                'reserva_motorista_app_id'
            );

            $reservaMotoristaApp = ReservaMotoristaApp::withoutGlobalScopes()
                ->with(['user', 'empresa', 'veiculo.marca', 'veiculo.modelo', 'planoMotoristaApp', 'solicitacao.planoMotoristaApp', 'pagamentos'])
                ->find($idMotoristaApp);

            // Obter opcionais atuais e calcular valor total deles
            $opcionaisAtuaisValor = (float) ($reservaMotoristaApp->valor_opcionais ?? 0);

            if (! $reservaMotoristaApp) {
                return redirect()->route('reserva.index')->with('error', 'Reserva não encontrada.');
            }

            $plano = $reservaMotoristaApp->planoMotoristaApp ?? $reservaMotoristaApp->solicitacao?->planoMotoristaApp;
            $valorPeriodoAtual = (float) (
                $plano?->valor_periodo
                ?? $reservaMotoristaApp->solicitacao?->valor_periodo
                ?? $reservaMotoristaApp->pagamentos?->first()?->valor_unitario
                ?? $reservaMotoristaApp->pagamentos?->first()?->valor_total
                ?? 0
            );
            $valorPeriodoContratado = (float) (
                $reservaMotoristaApp->valor_periodo_contrato
                ?: ($reservaMotoristaApp->valor_plano
                ?: $valorPeriodoAtual)
            );

            // Vigências
            $inicioVigencia = $reservaMotoristaApp->data_inicio;
            $fimVigencia = $reservaMotoristaApp->data_fim;

            // Se existirem pagamentos, pegar a vigência do mais recente
            $ultimoPgto = collect($reservaMotoristaApp->pagamentos ?? [])
                ->filter(fn ($p) => strtolower((string) ($p->status ?? '')) !== 'cancelado')
                ->sortByDesc('id')
                ->first();

            if ($ultimoPgto && ! empty($ultimoPgto->vigencia_fim)) {
                $inicioVigencia = $ultimoPgto->vigencia_inicio;
                $fimVigencia = $ultimoPgto->vigencia_fim;
            }

            $inicioVigenciaFormatado = $inicioVigencia ? \Carbon\Carbon::parse($inicioVigencia)->format('d/m/Y') : '-';
            $fimVigenciaFormatado = $fimVigencia ? \Carbon\Carbon::parse($fimVigencia)->format('d/m/Y') : '-';

            // Buscar todos os planos disponíveis para possível troca
            $planos = \App\Models\PlanoMotoristaApp::where('empresa_id', $reservaMotoristaApp->empresa_id)
                ->where('ativo', 'S')
                ->orderBy('ordem')
                ->orderBy('nome')
                ->get();

            $breadcrumb = $this->breadcrumbService->prepararBreadcrumbShow('Reservas', 'reserva.index', 'Renovação: '.$codigo);

            return view('reserva.renovacao', [
                'tipo' => 'MAP',
                'reserva' => $reservaMotoristaApp,
                'codigo' => $codigo,
                'plano' => $plano,
                'planos' => $planos,
                'valorPeriodoContratado' => $valorPeriodoContratado,
                'opcionaisAtuaisValor' => $opcionaisAtuaisValor,
                'inicioVigenciaFormatado' => $inicioVigenciaFormatado,
                'fimVigenciaFormatado' => $fimVigenciaFormatado,
                'titulo' => 'Renovação de Plano de Motorista — '.$codigo,
                'breadcrumb' => $breadcrumb,
            ]);

        }

        // Reserva Comum
        $reservaId = $this->idReservaPorIdentificador($codigo, 'RES', $codigoReserva, 'reserva_id');
        $reserva = Reserva::withoutGlobalScopes()
            ->with(['usuario', 'veiculo.marca', 'veiculo.modelo', 'estacaoDevolucao'])
            ->find($reservaId);

        if (! $reserva) {
            return redirect()->route('reserva.index')->with('error', 'Reserva não encontrada.');
        }

        $estacoes = \App\Models\Estacao::where('ativo', true)->get();
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbShow('Reservas', 'reserva.index', 'Renovação: '.$codigo);

        return view('reserva.renovacao', [
            'tipo' => 'RES',
            'reserva' => $reserva,
            'codigo' => $codigo,
            'estacoes' => $estacoes,
            'titulo' => 'Renovação de Reserva — #'.$reserva->id,
            'breadcrumb' => $breadcrumb,
        ]);
    }

    public function renovarComum(Request $request, $id)
    {
        $reserva = Reserva::withoutGlobalScopes()
            ->with(['empresa', 'codigoReserva'])
            ->find((int) $id);

        if (! $reserva) {
            return back()->with('error', 'Reserva não encontrada.');
        }

        try {
            $reserva = $this->renovarReservaComumAction->handle($request, $reserva);

            $novaDataHora = \Carbon\Carbon::parse($reserva->data_fim);
            $valorAdicional = round(
                max(1, (int) ceil($novaDataHora->diffInDays(\Carbon\Carbon::parse($request->input('data_fim_nova')))))
                * (float) $reserva->tarifa_base,
                2
            );

            return redirect()
                ->route('reserva.show', $reserva->codigoReserva?->codigo ?? $reserva->id)
                ->with('success', 'Reserva renovada com sucesso! Um pagamento adicional de R$ '.number_format($valorAdicional, 2, ',', '.').' foi gerado.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Falha ao renovar reserva: '.$e->getMessage());
        }
    }

    public function show($reserva)
    {
        $user = Auth::user();
        $identificadorOriginal = $reserva;

        if (ctype_digit((string) $identificadorOriginal)) {
            $codigoPublico = DB::table('reserva_codigos')
                ->where('reserva_id', (int) $identificadorOriginal)
                ->value('codigo');

            if ($codigoPublico) {
                return redirect()->route('reserva.show', $codigoPublico);
            }

            return redirect()->route('reserva.index')
                ->with('error', 'Esta reserva nao possui codigo publico para acesso.');
        }

        $codigoReserva = $this->dadosCodigoReservaPorIdentificador($identificadorOriginal);

        if (($codigoReserva && $codigoReserva->tipo_origem === 'motorista_app')
            || (! $codigoReserva && $this->identificadorComecaComPrefixo($identificadorOriginal, 'MAP'))
        ) {
            $idMotoristaApp = $this->idReservaPorIdentificador(
                $identificadorOriginal,
                'MAP',
                $codigoReserva,
                'reserva_motorista_app_id'
            );

            $reservaMotoristaApp = ReservaMotoristaApp::withoutGlobalScopes()
                ->with([
                    'empresa',
                    'user',
                    'solicitacao.user',
                    'solicitacao.planoMotoristaApp',
                    'planoMotoristaApp.opcionais',
                    'estacaoRetirada',
                    'veiculo.marca',
                    'veiculo.modelo',
                    'codigoReserva',
                    'pagamentos',
                    'multas',
                    'sinistros',
                ])
                ->find($idMotoristaApp);

            if ($user && $reservaMotoristaApp && (
                (int) $reservaMotoristaApp->user_id === (int) $user->id
                || $user->isAdministrador()
                || $user->hasPermissao('reserva.show')
            )) {
                $this->garantirPrimeiroPagamentoMotoristaApp($reservaMotoristaApp);
                $reservaMotoristaApp->load('pagamentos');
                $checkinDocs = is_array($reservaMotoristaApp->documentos_json ?? null)
                    ? data_get($reservaMotoristaApp->documentos_json, 'checkin', [])
                    : [];
                $reservaOperacionalCheckinId = (int) data_get($checkinDocs, 'reserva_operacional_id', 0);
                if ($reservaOperacionalCheckinId <= 0) {
                    $codigoMap = $reservaMotoristaApp->codigoReserva?->codigo ?? '';
                    if ($codigoMap) {
                        $reservaOperacionalCheckinId = (int) \App\Models\Reserva::withoutGlobalScopes()
                            ->where('observacoes', 'like', '%'.$codigoMap.'%')
                            ->latest('id')
                            ->value('id');
                    }
                }
                $contratoCheckinMotoristaApp = null;

                if ((int) data_get($checkinDocs, 'contrato_checkin_id', 0) > 0) {
                    $contratoCheckinMotoristaApp = ContratoCheckin::with(['reserva.codigoReserva', 'veiculo.marca', 'veiculo.modelo', 'marca', 'modelo'])
                        ->find((int) data_get($checkinDocs, 'contrato_checkin_id'));
                }

                if (! $contratoCheckinMotoristaApp && $reservaOperacionalCheckinId > 0) {
                    $contratoCheckinMotoristaApp = ContratoCheckin::with(['reserva.codigoReserva', 'veiculo.marca', 'veiculo.modelo', 'marca', 'modelo'])
                        ->where('reserva_id', $reservaOperacionalCheckinId)
                        ->latest('id')
                        ->first();
                }

                if (! $reservaOperacionalCheckinId && $contratoCheckinMotoristaApp) {
                    $reservaOperacionalCheckinId = (int) $contratoCheckinMotoristaApp->reserva_id;
                }

                return view('reserva.show-motorista-app', [
                    'registro' => $reservaMotoristaApp,
                    'empresa' => $reservaMotoristaApp->empresa ?? $user->empresa,
                    'veiculosDisponiveis' => $this->veiculoService->listarAtivosParaOrdemServico(300),
                    'contratoCheckinMotoristaApp' => $contratoCheckinMotoristaApp,
                    'reservaOperacionalCheckinId' => $reservaOperacionalCheckinId,
                    'titulo' => $user->isAdministrador()
                        ? 'Reserva de motorista de app'
                        : 'Minha reserva de motorista de app',
                    'termoAceite' => $this->obterTermoAceiteReserva($reservaMotoristaApp),
                ]);
            }

            Log::warning('Reserva motorista app administrativa nao encontrada ao abrir show.', [
                'identificador_original' => $identificadorOriginal,
                'id_resolvido' => $idMotoristaApp,
                'codigo_reserva' => $codigoReserva ? (array) $codigoReserva : null,
                'url' => request()->fullUrl(),
                'user_id' => $user?->id,
            ]);

            return redirect()->route('reserva.index')->with('error', 'Reserva nao encontrada.');
        }

        $id = $this->idReservaPorIdentificador($identificadorOriginal, 'RES', $codigoReserva, 'reserva_id');

        $titulo = 'Detalhes da Reserva';

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Reservas', route('reserva.index')),
            new BreadCrumbItem('Detalhes da Reserva Nº'.($codigoReserva->codigo ?? $id)),
        ]);
        $reserva = Reserva::withoutGlobalScopes()
            ->with(['contratoCheckin', 'contratoCheckout', 'codigoReserva'])
            ->find($id);
        if (! $reserva) {
            Log::warning('Reserva administrativa nao encontrada ao abrir show.', [
                'identificador_original' => $identificadorOriginal,
                'id_resolvido' => $id,
                'codigo_reserva' => $codigoReserva ? (array) $codigoReserva : null,
                'url' => request()->fullUrl(),
                'user_id' => $user?->id,
            ]);

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
                $query
                    ->with(['user', 'pagoPor'])
                    ->orderByDesc('id');
            },

            'contratoCheckin.grupoFrotaOriginal',
            'contratoCheckin.veiculo.marca',
            'contratoCheckin.usuarioUpgrade',
            'contratoCheckout',
            'calcao',

            'veiculoDanos' => fn ($query) => $query
                ->with(['imagens', 'dano.categoria', 'usuarioCorrecao'])
                ->orderByDesc('veiculo_danos.id')
                ->take(50),

            'avariaCobrancas' => fn ($query) => $query
                ->orderByDesc('avaria_cobrancas.id')
                ->take(10),

            'multas' => fn ($query) => $query
                ->orderByDesc('id')
                ->take(5),

            'sinistros' => fn ($query) => $query
                ->orderByDesc('id')
                ->take(5),
        ]);

        // Calcular taxas fora de expediente se nÃƒÂ£o estiverem salvas na reserva
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

        // Se percentual_desconto nÃƒÂ£o foi salvo mas cupom existe, recuperar do cupom
        if ((float) ($reserva->percentual_desconto ?? 0) == 0 && $reserva->cupom_desconto_id && $reserva->cupomDesconto) {
            $reserva->percentual_desconto = (float) ($reserva->cupomDesconto->valor ?? 0);
        }

        // Verificar mÃƒÂ³dulos do plano da empresa
        $empresa = \App\Models\Empresa::with('planoAtivo.plano')->find($reserva->empresa_id);
        $moduloNfse = (bool) ($empresa?->planoAtivo?->plano?->modulo_nfse ?? false);
        $moduloMultas = (bool) ($empresa?->planoAtivo?->plano?->modulo_multas ?? false);

        $user = Auth::user();
        if ($user && $this->isCliente($user)) {
            if ((int) $reserva->user_id !== (int) $user->id) {
                return redirect()->route('reserva.minhas')->with('error', 'VocÃƒÂª nÃƒÂ£o tem permissÃƒÂ£o para ver essa reserva.');
            }

            return redirect()->route('reserva.minhas.show', $codigoReserva?->codigo ?? $reserva->id);
        }

        if ($user && ! $user->isAdministrador() && ! $user->hasPermissao('reserva.show')) {
            abort(403, 'Sem permissao para acessar esta acao.');
        }

        return app(ExibirReservaComumAction::class)->handle(
            app(ReservaResolver::class)->resolver($codigoReserva->codigo ?? $reserva->id),
            request()
        );
    }

    private function reservasUnificadasAdmin($user, int $perPage)
    {
        $empresaId = $this->empresaIdParaMinhasReservas($user);

        $reservasQuery = DB::table('reservas')
            ->selectRaw("
                'reserva' as origem_tipo,
                reservas.id as id,
                reservas.empresa_id as empresa_id,
                reservas.user_id as user_id,
                rc.codigo as codigo_reserva,
                reservas.data_inicio as data_inicio,
                reservas.data_fim as data_fim,
                reservas.quantidade_dias as quantidade_dias,
                reservas.tarifa_base as tarifa_base,
                reservas.valor_total_tarifa_base as valor_total_tarifa_base,
                reservas.valor_total_com_desconto as valor_total_com_desconto,
                reservas.valor_opcionais as valor_opcionais,
                reservas.valor_total_final as valor_total_final,
                reservas.pagamento_efetuado as pagamento_efetuado,
                NULL as status_pagamento,
                usuarios.nome as user_nome,
                usuarios.email as user_email,
                status_reserva.nome as status_nome,
                status_reserva.cor as status_cor,
                status_reserva.descricao as status_descricao,
                grupos_frota.nome as grupo_nome,
                modelos.nome as modelo_nome,
                estacao_retirada.nome as estacao_retirada_nome,
                estacao_devolucao.nome as estacao_devolucao_nome,
                reservas.created_at as created_at,
                reservas.updated_at as updated_at
            ")
            ->leftJoin('status_reserva', 'status_reserva.id', '=', 'reservas.status_reserva_id')
            ->leftJoin('grupos_frota', 'grupos_frota.id', '=', 'reservas.grupo_frota_id')
            ->leftJoin('modelos', 'modelos.id', '=', 'reservas.modelo_id')
            ->leftJoin('estacoes as estacao_retirada', 'estacao_retirada.id', '=', 'reservas.estacao_retirada_id')
            ->leftJoin('estacoes as estacao_devolucao', 'estacao_devolucao.id', '=', 'reservas.estacao_devolucao_id')
            ->leftJoin('reserva_codigos as rc', 'rc.reserva_id', '=', 'reservas.id')
            ->leftJoin('users as usuarios', 'usuarios.id', '=', 'reservas.user_id');

        $motoristaQuery = DB::table('reservas_motorista_app')
            ->selectRaw("
                'motorista_app' as origem_tipo,
                reservas_motorista_app.id as id,
                reservas_motorista_app.empresa_id as empresa_id,
                reservas_motorista_app.user_id as user_id,
                rc_motorista.codigo as codigo_reserva,
                reservas_motorista_app.data_inicio as data_inicio,
                reservas_motorista_app.data_fim as data_fim,
                reservas_motorista_app.quantidade_dias as quantidade_dias,
                reservas_motorista_app.valor_plano as tarifa_base,
                reservas_motorista_app.valor_plano as valor_total_tarifa_base,
                reservas_motorista_app.valor_plano as valor_total_com_desconto,
                reservas_motorista_app.valor_opcionais as valor_opcionais,
                reservas_motorista_app.valor_total_final as valor_total_final,
                reservas_motorista_app.pagamento_efetuado as pagamento_efetuado,
                reservas_motorista_app.status_pagamento as status_pagamento,
                usuarios_motorista.nome as user_nome,
                usuarios_motorista.email as user_email,
                reservas_motorista_app.status as status_nome,
                CASE
                    WHEN reservas_motorista_app.status = 'pendente_pagamento' THEN 'amber'
                    WHEN reservas_motorista_app.status = 'pendente_analise' THEN 'slate'
                    WHEN reservas_motorista_app.status = 'ativa' THEN 'green'
                    WHEN reservas_motorista_app.status = 'cancelada' THEN 'red'
                    ELSE 'slate'
                END as status_cor,
                CASE
                    WHEN reservas_motorista_app.status = 'pendente_pagamento' THEN 'Criada'
                    WHEN reservas_motorista_app.status = 'pendente_analise' THEN 'Criada'
                    WHEN reservas_motorista_app.status = 'ativa' THEN 'Ativa'
                    WHEN reservas_motorista_app.status = 'cancelada' THEN 'Cancelada'
                    ELSE 'Criada'
                END as status_descricao,
                'Motorista APP' as grupo_nome,
                planos_motorista_app.nome as modelo_nome,
                estacoes.nome as estacao_retirada_nome,
                NULL as estacao_devolucao_nome,
                reservas_motorista_app.created_at as created_at,
                reservas_motorista_app.updated_at as updated_at
            ")
            ->leftJoin('planos_motorista_app', 'planos_motorista_app.id', '=', 'reservas_motorista_app.plano_motorista_app_id')
            ->leftJoin('estacoes', 'estacoes.id', '=', 'reservas_motorista_app.estacao_retirada_id')
            ->leftJoin('reserva_codigos as rc_motorista', 'rc_motorista.reserva_motorista_app_id', '=', 'reservas_motorista_app.id')
            ->leftJoin('users as usuarios_motorista', 'usuarios_motorista.id', '=', 'reservas_motorista_app.user_id');

        if ($empresaId !== null) {
            if (SchemaCache::hasColumn('reservas', 'empresa_id')) {
                $reservasQuery->where('reservas.empresa_id', $empresaId);
            }

            if (SchemaCache::hasColumn('reservas_motorista_app', 'empresa_id')) {
                $motoristaQuery->where('reservas_motorista_app.empresa_id', $empresaId);
            }
        }

        $unified = DB::query()
            ->fromSub($reservasQuery->unionAll($motoristaQuery), 'reservas_unificadas')
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        $paginator = $unified->paginate($perPage);

        $paginator->getCollection()->transform(function ($item) {
            $item->origem_tipo = (string) ($item->origem_tipo ?? 'reserva');
            $item->is_motorista_app = $item->origem_tipo === 'motorista_app';
            $item->status_pagamento = strtolower(trim((string) ($item->status_pagamento ?? '')));
            $item->pagamento_pendente = $item->is_motorista_app
                ? in_array($item->status_pagamento, ['pendente', 'aguardando', 'vencido', 'atrasado'], true) || ! in_array($item->pagamento_efetuado, [true, 1, '1', 'S', 's', 'sim', 'SIM'], true)
                : ! in_array($item->pagamento_efetuado, [true, 1, '1', 'S', 's', 'sim', 'SIM'], true);
            $statusNome = strtolower(trim((string) ($item->status_nome ?? '')));
            $item->status_exibicao = $item->is_motorista_app
                ? match ($statusNome) {
                    'cancelada' => 'Cancelada',
                    'ativa' => 'Ativa',
                    'pendente_analise', 'pendente_pagamento', 'pendente', 'criada' => 'Criada',
                    default => 'Criada',
                }
            : ((string) ($item->status_nome ?? 'Status'));
            $item->status_card_class = match ($statusNome) {
                'confirmada', 'ativa' => 'rc-status--green',
                'cancelada' => 'rc-status--red',
                'pendente_analise' => 'rc-status--slate',
                'pendente_pagamento', 'pendente', 'criada' => 'rc-status--amber',
                default => 'rc-status--slate',
            };
            $item->tipo_label = $item->is_motorista_app ? 'Motorista APP' : 'Reservas';
            $item->statusReserva = (object) [
                'nome' => (string) ($item->status_nome ?? '-'),
                'cor' => (string) ($item->status_cor ?? 'secondary'),
                'descricao' => (string) ($item->status_descricao ?? ''),
            ];
            $item->estacaoRetirada = (object) [
                'nome' => (string) ($item->estacao_retirada_nome ?? '-'),
            ];
            $item->estacaoDevolucao = (object) [
                'nome' => (string) ($item->estacao_devolucao_nome ?? '-'),
            ];
            $item->grupoFrota = (object) [
                'nome' => (string) ($item->grupo_nome ?? '-'),
            ];
            $item->modelos = (object) [
                'nome' => (string) ($item->modelo_nome ?? '-'),
            ];
            $item->usuario = (object) [
                'nome' => (string) ($item->user_nome ?? '-'),
                'email' => (string) ($item->user_email ?? '-'),
            ];
            $item->nfseAtual = null;
            $item->codigo_exibicao = (string) ($item->codigo_reserva ?? ('#'.$item->id));
            $identificadorPublico = $this->identificadorReservaPublico($item);
            $item->url_detalhes = route('reserva.show', ['reserva' => $identificadorPublico]);

            return $item;
        });

        return $paginator;
    }

    public function showMinhas($id)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        $identificadorOriginal = $id;
        $codigoReserva = $this->dadosCodigoReservaPorIdentificador($identificadorOriginal);
        if ($codigoReserva && $codigoReserva->tipo_origem === 'motorista_app') {
            return $this->showMinhasMotoristaApp($codigoReserva->codigo);
        }

        $id = $this->idReservaPorIdentificador($identificadorOriginal, 'RES', $codigoReserva, 'reserva_id');

        $titulo = 'Detalhes da Reserva';
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Minhas Reservas', route('reserva.minhas')),
            new BreadCrumbItem('Detalhes da Reserva Nº'.$codigoReserva->codigo),
        ]);
        $reserva = Reserva::withoutGlobalScopes()
            ->with(['contratoCheckin', 'contratoCheckout', 'codigoReserva'])
            ->find($id);
        if (! $reserva) {
            Log::warning('Reserva do cliente nao encontrada ao abrir show.', [
                'identificador_original' => $identificadorOriginal,
                'id_resolvido' => $id,
                'codigo_reserva' => $codigoReserva ? (array) $codigoReserva : null,
                'url' => request()->fullUrl(),
                'user_id' => $user?->id,
            ]);

            return redirect()->route('reserva.minhas')->with('error', 'Reserva nao encontrada.');
        }

        if ((int) $reserva->user_id !== (int) $user->id) {
            return redirect()->route('reserva.minhas')->with('error', 'VocÃƒÂª nÃƒÂ£o tem permissÃƒÂ£o para ver essa reserva.');
        }

        $empresaId = $this->empresaIdParaMinhasReservas($user);
        if ($empresaId !== null && (int) ($reserva->empresa_id ?? 0) !== $empresaId) {
            return redirect()->route('reserva.minhas')->with('error', 'Reserva nao encontrada nesta empresa.');
        }

        $reserva->load([
            'codigoReserva',
            'criadoPor',
            'nfseAtual',
            'estacaoRetirada',
            'estacaoDevolucao',
            'grupoFrota',
            'veiculo.veiculoDanos' => fn ($query) => $query->with(['dano.categoria', 'imagens', 'usuarioCorrecao'])->orderByDesc('id'),
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
            'veiculoDanos' => fn ($q) => $q->with('imagem')->orderByDesc('id')->take(10),
            'avariaCobrancas' => fn ($q) => $q->orderByDesc('id')->take(10),
            'multas' => fn ($q) => $q->orderByDesc('id')->take(5),
        ]);

        $multas = $this->multasDoCliente($user);

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

        if ((float) ($reserva->percentual_desconto ?? 0) == 0 && $reserva->cupom_desconto_id && $reserva->cupomDesconto) {
            $reserva->percentual_desconto = (float) ($reserva->cupomDesconto->valor ?? 0);
        }

        $empresa = \App\Models\Empresa::with('planoAtivo.plano')->find($reserva->empresa_id);
        $moduloNfse = (bool) ($empresa?->planoAtivo?->plano?->modulo_nfse ?? false);
        $moduloMultas = (bool) ($empresa?->planoAtivo?->plano?->modulo_multas ?? false);

        return view('reserva.show-cliente', compact('reserva', 'breadcrumb', 'titulo', 'taxaColeta', 'taxaEntrega', 'moduloNfse', 'moduloMultas'));
    }



    public function verContratoCheckoutMinhaReserva($codigo, ReservaResolver $reservaResolver, \App\Actions\Reserva\Checkout\TermoDevolucao\Exibir\ExibirTermoDevolucaoAction $exibirTermoDevolucaoAction)
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        try {
            $reservaResolvida = $reservaResolver->resolver((string) $codigo);
        } catch (\Throwable $e) {
            return redirect()
                ->route('reserva.minhas')
                ->with('error', 'Reserva não encontrada.');
        }

        /** @var \App\Models\Reserva|\App\Models\ReservaMotoristaApp $reserva */
        $reserva = $reservaResolvida->registro;

        $ehTitularDaReserva = in_array((int) $user->id, [
            (int) ($reserva->user_id ?? 0),
            (int) ($reserva->criado_por_user_id ?? 0),
            (int) ($reserva->usuario?->id ?? 0),
        ], true);

        if (! $ehTitularDaReserva) {
            return redirect()
                ->route('reserva.minhas.show', ['id' => $reserva->codigoReserva?->codigo ?? $reserva->id])
                ->with('error', 'Você não tem permissão para visualizar este contrato.');
        }

        $pdfPath = $reservaResolvida->ehMotoristaApp()
            ? (data_get($reserva->documentos_json, 'checkout.pdf_path') ?: ($reserva->contratoCheckout?->pdf_path ?? null))
            : ($reserva->contratoCheckout?->pdf_path ?? null);

        if ($pdfPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($pdfPath)) {
            $fileContent = \Illuminate\Support\Facades\Storage::disk('public')->get($pdfPath);

            return response($fileContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="contrato-checkout-'.$codigo.'.pdf"',
            ]);
        }

        $response = $exibirTermoDevolucaoAction->handle((string) $codigo);
        if ($response instanceof \Symfony\Component\HttpFoundation\Response) {
            return $response;
        }

        return redirect()
            ->route('reserva.minhas.show', ['id' => $reserva->codigoReserva?->codigo ?? $reserva->id])
            ->with('warning', 'Não foi possível exibir o contrato de devolução agora.');
    }

    public function showMinhasMotoristaApp($id)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        $codigoReserva = $this->dadosCodigoReservaPorIdentificador($id);
        if ($codigoReserva && $codigoReserva->tipo_origem === 'reserva') {
            return redirect()->route('reserva.minhas.show', ['id' => $codigoReserva->codigo]);
        }
        if ($codigoReserva && $codigoReserva->tipo_origem === 'motorista_app') {
            $id = $this->idReservaPorIdentificador($id, 'MAP', $codigoReserva, 'reserva_motorista_app_id');
        } elseif (! ctype_digit((string) $id)) {
            $id = $this->idReservaNumericoDoCodigoLegado($id, 'MAP') ?? $id;
        }

        $reservaMotoristaApp = \App\Models\ReservaMotoristaApp::withoutGlobalScopes()
            ->with([
                'empresa',
                'user',
                'solicitacao.user',
                'solicitacao.planoMotoristaApp',
                'planoMotoristaApp.opcionais',
                'estacaoRetirada',
                'veiculo.marca',
                'veiculo.modelo',
                'codigoReserva',
                'pagamentos',
                'multas',
                'sinistros',
            ])
            ->where('user_id', (int) $user->id)
            ->find($id);

        if (! $reservaMotoristaApp) {
            return redirect()->route('reserva.minhas')->with('error', 'Reserva nao encontrada.');
        }

        $empresaId = $this->empresaIdParaMinhasReservas($user);
        if ($empresaId !== null && (int) ($reservaMotoristaApp->empresa_id ?? 0) !== $empresaId) {
            return redirect()->route('reserva.minhas')->with('error', 'Reserva nao encontrada nesta empresa.');
        }

        $this->garantirPrimeiroPagamentoMotoristaApp($reservaMotoristaApp);
        $reservaMotoristaApp->load('pagamentos');

        $codigo = $reservaMotoristaApp->codigoReserva?->codigo ?? ('MAP-'.str_pad((string) $reservaMotoristaApp->id, 6, '0', STR_PAD_LEFT));
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new \App\DTO\BreadCrumbItem('Home', route('home')),
            new \App\DTO\BreadCrumbItem('Minhas Reservas', route('reserva.minhas')),
            new \App\DTO\BreadCrumbItem('Reserva Nº '.$codigo),
        ]);

        return view('reserva.show-cliente-motorista-app', [
            'registro' => $reservaMotoristaApp,
            'breadcrumb' => $breadcrumb,
            'empresa' => $reservaMotoristaApp->empresa ?? $user->empresa,
            'veiculosDisponiveis' => $this->veiculoService->listarAtivosParaOrdemServico(300),
            'titulo' => 'Minha reserva de motorista de app',
            'termoAceite' => $this->obterTermoAceiteReserva($reservaMotoristaApp),
        ]);
    }

    private function obterTermoAceiteReserva($reserva): ?TermoConsentimento
    {
        $empresaId = (int) ($reserva->empresa_id ?? 0);
        if ($empresaId <= 0) {
            return null;
        }

        $tipoTermo = $reserva instanceof ReservaMotoristaApp
            ? 'aceito-motorista-app'
            : 'aceito-reserva';

        return TermoConsentimento::query()
            ->where('empresa_id', $empresaId)
            ->where(function ($query) {
                $query->whereNull('ativo')->orWhere('ativo', 'S');
            })
            ->where('tipo', $tipoTermo)
            ->orderBy('ordem')
            ->orderBy('id')
            ->first();
    }

    private function garantirPrimeiroPagamentoMotoristaApp(ReservaMotoristaApp $reserva): void
    {
        $plano = $reserva->planoMotoristaApp;
        $periodoContrato = (string) ($reserva->periodo_contrato ?: ($plano?->periodo ?? 'semana'));
        $valorUnitario = $this->valorUnitarioMotoristaApp($reserva);
        $dataInicio = $reserva->data_inicio ? Carbon::parse($reserva->data_inicio) : now();
        $dataFim = $reserva->data_fim ? Carbon::parse($reserva->data_fim) : $this->aplicarPeriodoMotoristaApp($dataInicio->copy(), $periodoContrato, 1);

        $pagamentoPendenteZerado = $reserva->pagamentos()
            ->where('status', 'pendente')
            ->where(function ($query) {
                $query->whereNull('valor_total')->orWhere('valor_total', '<=', 0);
            })
            ->orderByDesc('id')
            ->first();

        $valorOpcionais = (float) ($reserva->valor_opcionais ?? 0);

        if ($pagamentoPendenteZerado && $valorUnitario > 0) {
            $periodosPendente = max(1, (int) ($pagamentoPendenteZerado->periodos ?? 1));
            $pagamentoPendenteZerado->update([
                'periodos' => $periodosPendente,
                'periodo_unidade' => $pagamentoPendenteZerado->periodo_unidade ?: $this->rotuloPeriodoMotoristaApp($periodoContrato),
                'valor_unitario' => $valorUnitario,
                'valor_total' => round(($valorUnitario * $periodosPendente) + $valorOpcionais, 2),
                'data_inicio_prevista' => $pagamentoPendenteZerado->data_inicio_prevista ?: $dataInicio,
                'data_fim_prevista' => $pagamentoPendenteZerado->data_fim_prevista ?: $dataFim,
            ]);

            return;
        }

        if ($reserva->pagamentos()->exists()) {
            return;
        }

        ReservaMotoristaAppPagamento::create([
            'reserva_motorista_app_id' => (int) $reserva->id,
            'empresa_id' => $reserva->empresa_id,
            'user_id' => (int) $reserva->user_id,
            'periodos' => 1,
            'periodo_unidade' => $this->rotuloPeriodoMotoristaApp($periodoContrato),
            'valor_unitario' => $valorUnitario,
            'valor_total' => round($valorUnitario + $valorOpcionais, 2),
            'data_inicio_prevista' => $dataInicio,
            'data_fim_prevista' => $dataFim,
            'status' => 'pendente',
            'observacoes' => 'Primeiro ciclo criado automaticamente na abertura da reserva.',
            'metadados' => [
                'tipo' => 'primeiro_ciclo',
                'plano_id' => (int) ($reserva->plano_motorista_app_id ?? 0),
                'valor_contrato' => $valorUnitario,
            ],
        ]);
    }

    private function valorUnitarioMotoristaApp(ReservaMotoristaApp $reserva): float
    {
        $plano = $reserva->relationLoaded('planoMotoristaApp')
            ? $reserva->planoMotoristaApp
            : $reserva->planoMotoristaApp()->first();

        foreach (
            [
                $reserva->valor_periodo_contrato ?? null,
                $reserva->valor_plano ?? null,
                $plano?->valor_periodo ?? null,
            ] as $valor
        ) {
            $valorFloat = (float) $valor;
            if ($valorFloat > 0) {
                if ($reserva->exists && ((float) $reserva->valor_periodo_contrato <= 0 || (float) $reserva->valor_plano <= 0)) {
                    $reserva->update([
                        'valor_periodo_contrato' => (float) $reserva->valor_periodo_contrato > 0 ? $reserva->valor_periodo_contrato : $valorFloat,
                        'valor_plano' => (float) $reserva->valor_plano > 0 ? $reserva->valor_plano : $valorFloat,
                    ]);
                }

                return $valorFloat;
            }
        }

        return 0.0;
    }

    public function renovarMotoristaApp(Request $request, $id)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }
        $ehAdmin = (bool) ($user->isAdministrador() ?? false);

        $reserva = ReservaMotoristaApp::query()
            ->with(['planoMotoristaApp', 'empresa', 'veiculo', 'pagamentos'])
            ->find($id);
        if ($reserva && ! $ehAdmin && (int) $reserva->user_id !== (int) $user->id) {
            $reserva = null;
        }

        if (! $reserva) {
            return redirect()->route('reserva.minhas')->with('error', 'Reserva nao encontrada.');
        }

        if (strtolower((string) ($reserva->status ?? '')) === 'cancelada') {
            return back()->with('error', 'Nao e possivel renovar uma reserva cancelada.');
        }

        if (strtolower((string) ($reserva->status ?? '')) === 'cancelada') {
            return back()->with('error', 'A renovação sÃƒÂ³ pode ser feita apÃƒÂ³s a efetivação da locação.');
        }

        if (! empty($reserva->checkout_realizado_em)) {
            return back()->with('error', 'Nao e possivel renovar o plano apos o checkout da locacao.');
        }

        if (empty(data_get($reserva->contrato_snapshot_json, 'assinatura'))) {
            return back()->with('error', 'A renovacao so fica disponivel apos o check-in e a assinatura do contrato.');
        }

        $validated = $request->validate([
            'periodos' => ['required', 'integer', 'min:1', 'max:120'],
            'observacoes' => ['nullable', 'string', 'max:2000'],
        ]);

        $plano = $reserva->planoMotoristaApp;
        if (! $plano) {
            return back()->with('error', 'Plano nao encontrado para esta reserva.');
        }

        $valorUnitario = $this->valorUnitarioMotoristaApp($reserva);
        $periodoContrato = (string) ($reserva->periodo_contrato ?: $plano->periodo ?: 'semana');
        $inicioBase = $reserva->data_fim ? Carbon::parse($reserva->data_fim) : Carbon::now();
        if (empty($reserva->data_fim)) {
            return back()->with('error', 'NÃƒÂ£o foi possível identificar o fim do ciclo atual.');
        }

        $periodoUnidade = $this->rotuloPeriodoMotoristaApp($periodoContrato);
        $novaVigenciaFim = $this->aplicarPeriodoMotoristaApp($inicioBase->copy(), $periodoContrato, (int) $validated['periodos']);
        $quantidadeDias = max(1, $inicioBase->diffInDays($novaVigenciaFim));
        $valorTotal = round($valorUnitario * (int) $validated['periodos'], 2);

        $pagamento = ReservaMotoristaAppPagamento::create([
            'reserva_motorista_app_id' => (int) $reserva->id,
            'empresa_id' => $reserva->empresa_id ?? $user->empresa_id ?? null,
            'user_id' => (int) $user->id,
            'periodos' => (int) $validated['periodos'],
            'periodo_unidade' => $periodoUnidade,
            'valor_unitario' => $valorUnitario,
            'valor_total' => $valorTotal,
            'data_inicio_prevista' => $inicioBase->copy(),
            'data_fim_prevista' => $novaVigenciaFim,
            'status' => 'pendente',
            'observacoes' => $validated['observacoes'] ?? null,
            'metadados' => [
                'plano_id' => (int) $plano->id,
                'valor_plano_atual' => $valorUnitario,
                'quantidade_dias_prevista' => $quantidadeDias,
            ],
        ]);

        return redirect()
            ->route('reserva.minhas.motorista-app.show', ['id' => $reserva->id])
            ->with('success', 'Renovacao criada. Agora basta efetuar o pagamento para confirmar o novo periodo.')
            ->with('pagamento', $pagamento->id);
    }

    public function efetivarMotoristaApp(Request $request, $id)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }
        $ehAdmin = (bool) ($user->isAdministrador() ?? false);

        $reserva = ReservaMotoristaApp::query()
            ->with(['empresa', 'user', 'solicitacao.user', 'planoMotoristaApp', 'veiculo', 'pagamentos'])
            ->find($id);
        if ($reserva && ! $ehAdmin && (int) $reserva->user_id !== (int) $user->id) {
            $reserva = null;
        }

        if (! $reserva) {
            return redirect()->route('reserva.minhas')->with('error', 'Reserva nao encontrada.');
        }

        if (strtolower((string) ($reserva->status ?? '')) === 'ativa' && ! empty($reserva->contrato_efetivado_em)) {
            return back()->with('success', 'Esta locação jÃƒÂ¡ foi efetivada.');
        }

        $validated = $request->validate([
            'veiculo_id' => ['required', 'integer', 'exists:veiculos,id'],
            'quilometragem_inicial' => ['required', 'integer', 'min:0'],
            'nivel_combustivel_inicial' => ['required', 'string', 'in:Reserva,1/8,1/4,3/8,1/2,5/8,3/4,7/8,Cheio'],
            'data_retirada_real' => ['nullable', 'date'],
        ]);

        $plano = $reserva->planoMotoristaApp;
        if (! $plano) {
            return back()->with('error', 'Plano nao encontrado para esta reserva.');
        }

        $dataRetiradaReal = ! empty($validated['data_retirada_real'])
            ? Carbon::parse($validated['data_retirada_real'])
            : now();

        $periodoContrato = (string) ($plano->periodo ?: 'semana');
        $valorUnitario = (float) ($plano->valor_periodo ?? 0);
        $dataFimPrimeiroCiclo = $this->aplicarPeriodoMotoristaApp($dataRetiradaReal->copy(), $periodoContrato, 1);
        $quantidadeDias = max(1, $dataRetiradaReal->diffInDays($dataFimPrimeiroCiclo));

        $snapshot = $this->montarSnapshotContratoMotoristaApp(
            $reserva,
            (int) $validated['veiculo_id'],
            (int) $validated['quilometragem_inicial'],
            (string) $validated['nivel_combustivel_inicial'],
            $dataRetiradaReal,
            $dataFimPrimeiroCiclo,
            $valorUnitario,
            $periodoContrato
        );

        $arquivoContrato = 'contratos/motorista-app/reserva-'.(int) $reserva->id.'-'.time().'.pdf';
        Storage::disk('public')->put(
            $arquivoContrato,
            $this->pdfService->gerarCheckinPdfContent($snapshot)
        );

        $pagamento = $reserva->pagamentos()->orderByDesc('id')->first();
        if (! $pagamento || strtolower((string) ($pagamento->status ?? '')) !== 'pendente' || (int) ($pagamento->periodos ?? 0) !== 1) {
            $pagamento = ReservaMotoristaAppPagamento::create([
                'reserva_motorista_app_id' => (int) $reserva->id,
                'empresa_id' => $reserva->empresa_id ?? $user->empresa_id ?? null,
                'user_id' => (int) $user->id,
                'periodos' => 1,
                'periodo_unidade' => $this->rotuloPeriodoMotoristaApp($periodoContrato),
                'valor_unitario' => $valorUnitario,
                'valor_total' => $valorUnitario,
                'data_inicio_prevista' => $dataRetiradaReal->copy(),
                'data_fim_prevista' => $dataFimPrimeiroCiclo,
                'status' => 'pendente',
                'observacoes' => 'Primeiro ciclo criado na efetivacao da locacao.',
                'metadados' => [
                    'tipo' => 'primeiro_ciclo',
                    'valor_contrato' => $valorUnitario,
                ],
            ]);
        } else {
            $pagamento->update([
                'periodos' => 1,
                'periodo_unidade' => $this->rotuloPeriodoMotoristaApp($periodoContrato),
                'valor_unitario' => $valorUnitario,
                'valor_total' => $valorUnitario,
                'data_inicio_prevista' => $dataRetiradaReal->copy(),
                'data_fim_prevista' => $dataFimPrimeiroCiclo,
                'status' => 'pendente',
                'metadados' => array_merge((array) ($pagamento->metadados ?? []), [
                    'tipo' => 'primeiro_ciclo',
                    'valor_contrato' => $valorUnitario,
                ]),
            ]);
        }

        $reserva->fill([
            'veiculo_id' => (int) $validated['veiculo_id'],
            'quilometragem_inicial' => (int) $validated['quilometragem_inicial'],
            'nivel_combustivel_inicial' => (string) $validated['nivel_combustivel_inicial'],
            'data_retirada_real' => $dataRetiradaReal,
            'contrato_efetivado_em' => now(),
            'checkin_realizado_em' => null,
            'checkout_realizado_em' => null,
            'periodo_contrato' => $periodoContrato,
            'valor_periodo_contrato' => $valorUnitario,
            'data_inicio' => $dataRetiradaReal,
            'data_fim' => $dataFimPrimeiroCiclo,
            'quantidade_dias' => $quantidadeDias,
            'status' => 'ativa',
            'status_pagamento' => 'pendente',
            'pagamento_efetuado' => false,
            'contrato_snapshot_json' => $snapshot,
            'contrato_pdf_path' => $arquivoContrato,
            'valor_plano' => $valorUnitario,
            'valor_total_final' => round($valorUnitario + (float) ($reserva->valor_opcionais ?? 0), 2),
        ])->save();

        app(ReservaCodigoService::class)->sincronizar($reserva->fresh(), 'motorista_app', [
            'empresa_id' => $reserva->empresa_id,
            'status_label' => $reserva->status,
            'data_inicio' => $reserva->data_inicio,
            'data_fim' => $reserva->data_fim,
            'valor_total' => $reserva->valor_total_final,
        ]);

        $this->reservaService->adicionaLog(
            $reserva->id,
            Auth::id(),
            json_encode([
                'acao' => 'Locacao efetivada',
                'veiculo_id' => $reserva->veiculo_id,
                'quilometragem_inicial' => $reserva->quilometragem_inicial,
                'nivel_combustivel_inicial' => $reserva->nivel_combustivel_inicial,
                'data_retirada_real' => optional($dataRetiradaReal)->format('d/m/Y H:i'),
                'data_fim' => optional($dataFimPrimeiroCiclo)->format('d/m/Y H:i'),
                'contrato_pdf_path' => $arquivoContrato,
            ])
        );

        return redirect()
            ->route('reserva.minhas.motorista-app.show', ['id' => $reserva->id])
            ->with('success', 'Locação efetivada com sucesso. O contrato foi gerado e o primeiro ciclo financeiro foi criado.');
    }

    public function aceitarTermoConfirmacaoMotoristaApp(Request $request, $id)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }
        $ehAdmin = (bool) ($user->isAdministrador() ?? false);

        $reserva = ReservaMotoristaApp::query()
            ->with(['codigoReserva'])
            ->find($id);
        if ($reserva && ! $ehAdmin && (int) $reserva->user_id !== (int) $user->id) {
            $reserva = null;
        }

        if (! $reserva) {
            return redirect()->route('reserva.minhas')->with('error', 'Reserva nao encontrada.');
        }

        if (! $reserva->pagamento_efetuado) {
            return back()->with('error', 'Confirme o pagamento antes de aceitar o termo da reserva.');
        }

        if (! empty($reserva->checkin_realizado_em)) {
            return back()->with('success', 'Termo ja aceito e check-in ja iniciado.');
        }

        $request->validate([
            'aceite_termo' => ['accepted'],
        ]);

        $empresaId = (int) ($reserva->empresa_id ?? 0);
        $termoConsentimento = null;
        if ($empresaId > 0) {
            $termoConsentimento = TermoConsentimento::query()
                ->where('empresa_id', $empresaId)
                ->where(function ($query) {
                    $query->whereNull('ativo')->orWhere('ativo', 'S');
                })
                ->where('tipo', 'aceito-motorista-app')
                ->orderBy('ordem')
                ->orderBy('id')
                ->first();
        }

        $documentos = (array) ($reserva->documentos_json ?? []);
        data_set($documentos, 'termo_confirmacao.aceito', true);
        data_set($documentos, 'termo_confirmacao.aceito_em', now()->format('Y-m-d H:i:s'));
        data_set($documentos, 'termo_confirmacao.aceito_por_user_id', Auth::id());
        data_set($documentos, 'termo_confirmacao.ip', $request->ip());
        data_set($documentos, 'termo_confirmacao.user_agent', substr((string) $request->userAgent(), 0, 500));
        data_set($documentos, 'termo_confirmacao.titulo', $termoConsentimento?->titulo ?? 'Termo de confirmação da reserva');
        data_set($documentos, 'termo_confirmacao.texto', $termoConsentimento?->texto ?? 'Este termo confirma a reserva e autoriza a preparação posterior do contrato de locação no check-in.');

        $reserva->update([
            'status' => 'confirmada',
            'documentos_json' => $documentos,
        ]);

        $this->reservaService->adicionaLog($reserva->id, Auth::id(), json_encode([
            'acao' => 'Termo de confirmacao da reserva motorista app aceito',
            'data' => now()->format('d/m/Y H:i'),
        ]));

        return back()->with('success', 'Termo de confirmacao aceito. O check-in ja pode ser realizado.');
    }

    public function checkinMotoristaApp($id)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }
        $ehAdmin = (bool) ($user->isAdministrador() ?? false);

        $registro = ReservaMotoristaApp::query()
            ->with(['empresa', 'user', 'planoMotoristaApp', 'estacaoRetirada', 'veiculo.marca', 'veiculo.modelo', 'codigoReserva', 'pagamentos'])
            ->find($id);
        if ($registro && ! $ehAdmin && (int) $registro->user_id !== (int) $user->id) {
            $registro = null;
        }

        if (! $registro) {
            return redirect()->route('reserva.minhas')->with('error', 'Reserva nao encontrada.');
        }

        $this->normalizarPagamentoMotoristaAppQuitado($registro);

        if (! $registro->pagamento_efetuado) {
            return redirect()->route('reserva.show', $registro->codigoReserva?->codigo ?? $registro->id)
                ->with('error', 'Confirme o pagamento antes de abrir o check-in.');
        }

        if (empty(data_get($registro->documentos_json, 'termo_confirmacao.aceito_em'))) {
            return redirect()->route('reserva.show', $registro->codigoReserva?->codigo ?? $registro->id)
                ->with('error', 'Aceite o termo de confirmacao da reserva antes de abrir o check-in.');
        }

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Reservas', route('reserva.index')),
            new BreadCrumbItem('Reserva motorista APP #'.$registro->id, route('reserva.show', $registro->codigoReserva?->codigo ?? $registro->id)),
            new BreadCrumbItem('Check-in'),
        ]);

        $veiculosDisponiveis = $this->veiculoService->listarAtivosParaOrdemServico(300);

        return view('reserva.checkin-motorista-app', compact('registro', 'breadcrumb', 'veiculosDisponiveis'))
            ->with('titulo', 'Check-in da reserva motorista APP #'.$registro->id);
    }

    public function registrarCheckinMotoristaApp(Request $request, $id)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }
        $ehAdmin = (bool) ($user->isAdministrador() ?? false);

        $reserva = ReservaMotoristaApp::query()
            ->with(['pagamentos', 'codigoReserva'])
            ->find($id);
        if ($reserva && ! $ehAdmin && (int) $reserva->user_id !== (int) $user->id) {
            $reserva = null;
        }

        if (! $reserva) {
            return redirect()->route('reserva.minhas')->with('error', 'Reserva nao encontrada.');
        }

        $this->normalizarPagamentoMotoristaAppQuitado($reserva);

        if (! $reserva->pagamento_efetuado) {
            return back()->with('error', 'Confirme o pagamento antes de registrar o check-in.');
        }

        if (empty(data_get($reserva->documentos_json, 'termo_confirmacao.aceito_em'))) {
            return back()->with('error', 'Aceite o termo de confirmacao da reserva antes de registrar o check-in.');
        }

        if (false && (empty($reserva->contrato_efetivado_em) || empty($reserva->veiculo_id))) {
            return back()->with('error', 'Efetive a locação antes de registrar o check-in.');
        }

        if (false && empty(data_get($reserva->contrato_snapshot_json, 'assinatura'))) {
            return back()->with('error', 'Assine o contrato antes de registrar o check-in.');
        }

        if (! empty($reserva->checkin_realizado_em)) {
            return back()->with('success', 'O check-in desta locação jÃƒÂ¡ foi registrado.');
        }

        $validated = $request->validate([
            'veiculo_id' => ['required', 'integer', 'exists:veiculos,id'],
            'quilometragem_inicial' => ['required', 'integer', 'min:0'],
            'nivel_combustivel_inicial' => ['required', 'string', 'in:Reserva,1/8,1/4,3/8,1/2,5/8,3/4,7/8,Cheio'],
            'data_retirada_real' => ['nullable', 'date'],
            'foto_direita' => ['required', 'image', 'max:8192'],
            'foto_esquerda' => ['required', 'image', 'max:8192'],
            'foto_frente' => ['required', 'image', 'max:8192'],
            'foto_tras' => ['required', 'image', 'max:8192'],
            'foto_velocimetro' => ['required', 'image', 'max:8192'],
            'foto_combustivel' => ['required', 'image', 'max:8192'],
            'notas_saida' => ['nullable', 'string', 'max:3000'],
            'observacoes_saida' => ['nullable', 'string', 'max:3000'],
        ]);

        $reserva->load(['planoMotoristaApp', 'empresa', 'user', 'veiculo']);
        $plano = $reserva->planoMotoristaApp;
        if (! $plano) {
            return back()->with('error', 'Plano nao encontrado para esta reserva.');
        }

        $dataRetiradaReal = ! empty($validated['data_retirada_real'])
            ? Carbon::parse($validated['data_retirada_real'])
            : now();
        $periodoContrato = (string) ($reserva->periodo_contrato ?: $plano->periodo ?: 'semana');
        $valorUnitario = $this->valorUnitarioMotoristaApp($reserva);
        $dataFimPrimeiroCiclo = $this->aplicarPeriodoMotoristaApp($dataRetiradaReal->copy(), $periodoContrato, 1);
        $quantidadeDias = max(1, $dataRetiradaReal->diffInDays($dataFimPrimeiroCiclo));

        $snapshot = $this->montarSnapshotContratoMotoristaApp(
            $reserva,
            (int) $validated['veiculo_id'],
            (int) $validated['quilometragem_inicial'],
            (string) $validated['nivel_combustivel_inicial'],
            $dataRetiradaReal,
            $dataFimPrimeiroCiclo,
            $valorUnitario,
            $periodoContrato
        );

        $arquivoContrato = $reserva->contrato_pdf_path ?: 'contratos/motorista-app/reserva-'.(int) $reserva->id.'-'.time().'.pdf';
        Storage::disk('public')->put(
            $arquivoContrato,
            $this->pdfService->gerarCheckinPdfContent($snapshot)
        );

        $documentos = (array) ($reserva->documentos_json ?? []);
        $fotos = [];
        $dir = 'motorista-app/checkin/reserva_'.(int) $reserva->id;
        foreach (['foto_direita', 'foto_esquerda', 'foto_frente', 'foto_tras', 'foto_velocimetro', 'foto_combustivel'] as $input) {
            if ($request->hasFile($input)) {
                $fotos[$input] = \App\Support\ArquivoAnexoHelper::armazenar($request->file($input), $dir);
            }
        }

        data_set($documentos, 'checkin.fotos', $fotos);
        data_set($documentos, 'checkin.notas_saida', $validated['notas_saida'] ?? null);
        data_set($documentos, 'checkin.observacoes_saida', $validated['observacoes_saida'] ?? null);
        data_set($documentos, 'checkin.registrado_em', now()->format('Y-m-d H:i:s'));

        $reserva->update([
            'veiculo_id' => (int) $validated['veiculo_id'],
            'quilometragem_inicial' => (int) $validated['quilometragem_inicial'],
            'nivel_combustivel_inicial' => (string) $validated['nivel_combustivel_inicial'],
            'data_retirada_real' => $dataRetiradaReal,
            'contrato_efetivado_em' => now(),
            'checkin_realizado_em' => now(),
            'periodo_contrato' => $periodoContrato,
            'valor_periodo_contrato' => $valorUnitario,
            'data_inicio' => $dataRetiradaReal,
            'data_fim' => $dataFimPrimeiroCiclo,
            'quantidade_dias' => $quantidadeDias,
            'status' => 'confirmada',
            'contrato_snapshot_json' => $snapshot,
            'contrato_pdf_path' => $arquivoContrato,
            'documentos_json' => $documentos,
        ]);

        $this->reservaService->adicionaLog($reserva->id, Auth::id(), json_encode([
            'acao' => 'Check-in registrado',
            'data' => now()->format('d/m/Y H:i'),
        ]));

        return redirect()
            ->route('reserva.show', $reserva->codigoReserva?->codigo ?? $reserva->id)
            ->with('success', 'Check-in registrado. Agora assine o contrato para ativar a locacao.');
    }

    public function checkoutMotoristaApp($id)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }
        $ehAdmin = (bool) ($user->isAdministrador() ?? false);

        $registro = ReservaMotoristaApp::query()
            ->with(['empresa', 'user', 'planoMotoristaApp', 'estacaoRetirada', 'veiculo.marca', 'veiculo.modelo', 'codigoReserva'])
            ->find($id);
        if ($registro && ! $ehAdmin && (int) $registro->user_id !== (int) $user->id) {
            $registro = null;
        }

        if (! $registro) {
            return redirect()->route('reserva.minhas')->with('error', 'Reserva nao encontrada.');
        }

        if (empty($registro->checkin_realizado_em)) {
            return redirect()->route('reserva.show', $registro->codigoReserva?->codigo ?? $registro->id)
                ->with('error', 'O check-in precisa ser registrado antes do checkout.');
        }

        if (! $this->checkinMotoristaAppAssinado($registro)) {
            return redirect()->route('reserva.show', $registro->codigoReserva?->codigo ?? $registro->id)
                ->with('error', 'Assine o contrato de check-in antes de fazer checkout.');
        }

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Reservas', route('reserva.index')),
            new BreadCrumbItem('Reserva motorista APP #'.$registro->id, route('reserva.show', $registro->codigoReserva?->codigo ?? $registro->id)),
            new BreadCrumbItem('Check-out'),
        ]);

        return view('reserva.checkout-motorista-app', compact('registro', 'breadcrumb'))
            ->with('titulo', 'Check-out da reserva motorista APP #'.$registro->id);
    }

    public function registrarCheckoutMotoristaApp(Request $request, $id)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }
        $ehAdmin = (bool) ($user->isAdministrador() ?? false);

        $reserva = ReservaMotoristaApp::query()
            ->find($id);
        if ($reserva && ! $ehAdmin && (int) $reserva->user_id !== (int) $user->id) {
            $reserva = null;
        }

        if (! $reserva) {
            return redirect()->route('reserva.minhas')->with('error', 'Reserva nao encontrada.');
        }

        if (empty($reserva->checkin_realizado_em)) {
            return back()->with('error', 'O check-in precisa ser registrado antes do checkout.');
        }

        if (! $this->checkinMotoristaAppAssinado($reserva)) {
            return back()->with('error', 'Assine o contrato de check-in antes de fazer checkout.');
        }

        $validated = $request->validate([
            'quilometragem_final' => ['required', 'integer', 'min:'.(int) ($reserva->quilometragem_inicial ?? 0)],
            'nivel_combustivel_final' => ['required', 'string', 'in:Reserva,1/8,1/4,3/8,1/2,5/8,3/4,7/8,Cheio'],
            'foto_direita_co' => ['nullable', 'image', 'max:8192'],
            'foto_esquerda_co' => ['nullable', 'image', 'max:8192'],
            'foto_frente_co' => ['nullable', 'image', 'max:8192'],
            'foto_tras_co' => ['nullable', 'image', 'max:8192'],
            'foto_velocimetro_co' => ['nullable', 'image', 'max:8192'],
            'foto_combustivel_co' => ['nullable', 'image', 'max:8192'],
            'notas_devolucao' => ['nullable', 'string', 'max:3000'],
            'observacoes_devolucao' => ['nullable', 'string', 'max:3000'],
        ]);

        $documentos = (array) ($reserva->documentos_json ?? []);
        $fotos = [];
        $dir = 'motorista-app/checkout/reserva_'.(int) $reserva->id;
        foreach (['foto_direita_co', 'foto_esquerda_co', 'foto_frente_co', 'foto_tras_co', 'foto_velocimetro_co', 'foto_combustivel_co'] as $input) {
            if ($request->hasFile($input)) {
                $fotos[$input] = \App\Support\ArquivoAnexoHelper::armazenar($request->file($input), $dir);
            }
        }

        data_set($documentos, 'checkout.fotos', $fotos);
        data_set($documentos, 'checkout.notas_devolucao', $validated['notas_devolucao'] ?? null);
        data_set($documentos, 'checkout.observacoes_devolucao', $validated['observacoes_devolucao'] ?? null);
        data_set($documentos, 'checkout.registrado_em', now()->format('Y-m-d H:i:s'));

        $reserva->update([
            'quilometragem_final' => (int) $validated['quilometragem_final'],
            'nivel_combustivel_final' => (string) $validated['nivel_combustivel_final'],
            'checkout_realizado_em' => now(),
            'status' => 'encerrada',
            'documentos_json' => $documentos,
        ]);

        $this->reservaService->adicionaLog($reserva->id, Auth::id(), json_encode([
            'acao' => 'Checkout registrado',
            'quilometragem_final' => $validated['quilometragem_final'],
            'nivel_combustivel_final' => $validated['nivel_combustivel_final'],
            'data' => now()->format('d/m/Y H:i'),
        ]));

        return back()->with('success', 'Checkout registrado com sucesso.');
    }

    public function verContratoMotoristaApp($id)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }
        $ehAdmin = (bool) ($user->isAdministrador() ?? false);

        $reserva = ReservaMotoristaApp::query()->find($id);
        if ($reserva && ! $ehAdmin && (int) $reserva->user_id !== (int) $user->id) {
            $reserva = null;
        }

        if (! $reserva || empty($reserva->contrato_pdf_path)) {
            return back()->with('error', 'Contrato nao encontrado para esta reserva.');
        }

        if (! Storage::disk('public')->exists($reserva->contrato_pdf_path)) {
            return back()->with('error', 'Arquivo do contrato nao encontrado.');
        }

        return response()->file(storage_path('app/public/'.$reserva->contrato_pdf_path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="contrato-motorista-app-'.$reserva->id.'.pdf"',
        ]);
    }

    public function assinarContratoMotoristaApp(Request $request, $id)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }
        $ehAdmin = (bool) ($user->isAdministrador() ?? false);

        $reserva = ReservaMotoristaApp::query()
            ->with(['empresa', 'user', 'planoMotoristaApp', 'veiculo.marca', 'veiculo.modelo', 'solicitacao'])
            ->find($id);
        if ($reserva && ! $ehAdmin && (int) $reserva->user_id !== (int) $user->id) {
            $reserva = null;
        }

        if (! $reserva) {
            return redirect()->route('reserva.minhas')->with('error', 'Reserva nao encontrada.');
        }

        if (empty($reserva->contrato_efetivado_em) || empty($reserva->contrato_snapshot_json)) {
            return back()->with('error', 'Efetive a locacao antes de assinar o contrato.');
        }

        if (empty($reserva->checkin_realizado_em)) {
            return back()->with('error', 'Registre o check-in antes de assinar o contrato.');
        }

        $validated = $request->validate([
            'assinatura' => ['required', 'string'],
        ]);

        if (! preg_match('/^data:image\/png;base64,/', $validated['assinatura'])) {
            return back()->with('error', 'Assinatura invalida. Tente assinar novamente.');
        }

        $assinaturaBinaria = base64_decode(substr($validated['assinatura'], strpos($validated['assinatura'], ',') + 1), true);
        if ($assinaturaBinaria === false) {
            return back()->with('error', 'Nao foi possivel processar a assinatura.');
        }

        $assinaturaPath = 'assinaturas/motorista-app/reserva-'.(int) $reserva->id.'-'.time().'.png';
        Storage::disk('public')->put($assinaturaPath, $assinaturaBinaria);

        $snapshot = (array) $reserva->contrato_snapshot_json;
        $dataAssinatura = now()->format('d/m/Y H:i');
        data_set($snapshot, 'assinatura', $validated['assinatura']);
        data_set($snapshot, 'documento.data_assinatura', $dataAssinatura);
        data_set($snapshot, 'contrato.assinado_em', $dataAssinatura);
        data_set($snapshot, 'contrato.assinatura_path', $assinaturaPath);

        $arquivoContrato = $reserva->contrato_pdf_path ?: 'contratos/motorista-app/reserva-'.(int) $reserva->id.'-'.time().'.pdf';
        Storage::disk('public')->put(
            $arquivoContrato,
            $this->pdfService->gerarCheckinPdfContent($snapshot)
        );

        $reserva->update([
            'contrato_snapshot_json' => $snapshot,
            'contrato_pdf_path' => $arquivoContrato,
            'checkin_assinado_em' => now(),
            'status' => 'ativa',
        ]);

        $this->reservaService->adicionaLog($reserva->id, Auth::id(), json_encode([
            'acao' => 'Contrato motorista app assinado',
            'data' => now()->format('d/m/Y H:i'),
            'contrato_pdf_path' => $arquivoContrato,
        ]));

        return back()->with('success', 'Contrato assinado com sucesso.');
    }

    public function cancelarMotoristaApp(Request $request, $id)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }
        $ehAdmin = (bool) ($user->isAdministrador() ?? false);

        $reserva = ReservaMotoristaApp::query()
            ->with(['pagamentos'])
            ->find($id);
        if ($reserva && ! $ehAdmin && (int) $reserva->user_id !== (int) $user->id) {
            $reserva = null;
        }

        if (! $reserva) {
            return redirect()->route('reserva.minhas')->with('error', 'Reserva nao encontrada.');
        }

        if (strtolower((string) ($reserva->status ?? '')) === 'cancelada') {
            return back()->with('success', 'Esta reserva ja foi cancelada.');
        }

        if (! empty($reserva->contrato_efetivado_em) || strtolower((string) ($reserva->status ?? '')) === 'ativa') {
            return back()->with('error', 'Esta reserva ja foi efetivada e nao pode ser cancelada por aqui.');
        }

        $limiteCancelamento = $reserva->created_at ? \Carbon\Carbon::parse($reserva->created_at)->addHours(24) : null;
        if (! $limiteCancelamento || now()->greaterThan($limiteCancelamento)) {
            return back()->with('error', 'O cancelamento so pode ser feito ate 24 horas apos a criacao da reserva.');
        }

        foreach ($reserva->pagamentos as $pagamento) {
            if (strtolower((string) ($pagamento->status ?? '')) === 'pago') {
                continue;
            }

            $pagamento->update([
                'status' => 'cancelado',
            ]);
        }

        $reserva->update([
            'status' => 'cancelada',
            'status_pagamento' => 'cancelado',
            'pagamento_efetuado' => false,
        ]);

        $this->reservaService->adicionaLog($reserva->id, Auth::id(), json_encode([
            'acao' => 'Reserva de motorista app cancelada',
            'data' => now()->format('d/m/Y H:i'),
        ]));

        return redirect()
            ->route('reserva.minhas.motorista-app.show', ['id' => $reserva->id])
            ->with('success', 'Reserva cancelada com sucesso.');
    }

    public function confirmarPagamentoMotoristaApp(Request $request, $pagamentoId)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }
        $ehAdmin = (bool) ($user->isAdministrador() ?? false);

        $pagamento = ReservaMotoristaAppPagamento::query()
            ->with(['empresa', 'reservaMotoristaApp.empresa', 'reservaMotoristaApp.user', 'reservaMotoristaApp.planoMotoristaApp'])
            ->find($pagamentoId);

        if (! $pagamento || (! $ehAdmin && (int) ($pagamento->user_id ?? 0) !== (int) $user->id)) {
            return redirect()->route('reserva.minhas')->with('error', 'Pagamento nao encontrado.');
        }

        $validated = $request->validate([
            'metodo_pagamento' => ['nullable', 'string', 'max:30'],
            'referencia_pagamento' => ['nullable', 'string', 'max:100'],
            'payment_method' => ['nullable', 'string', 'max:120'],
            'installments' => ['nullable', 'integer', 'min:1', 'max:12'],
        ]);

        if (strtolower((string) $pagamento->status) === 'pago') {
            return back()->with('success', 'Este pagamento ja foi confirmado.');
        }

        $reserva = $pagamento->reservaMotoristaApp;
        if (! $reserva) {
            return back()->with('error', 'Reserva nao encontrada para este pagamento.');
        }

        $fimConfirmado = Carbon::parse($pagamento->data_fim_prevista ?? now());
        $inicioConfirmado = Carbon::parse($pagamento->data_inicio_prevista ?? $reserva->data_inicio ?? now());
        $diasAtualizados = max(1, $inicioConfirmado->diffInDays($fimConfirmado));
        $periodos = max(1, (int) ($pagamento->periodos ?? 1));
        $valorUnitario = (float) $pagamento->valor_unitario > 0
            ? (float) $pagamento->valor_unitario
            : $this->valorUnitarioMotoristaApp($reserva);
        $valorTotal = (float) ($pagamento->valor_total ?: ($valorUnitario * $periodos));

        if ($valorTotal <= 0 || $valorUnitario <= 0) {
            return back()->with('error', 'Nao foi possivel identificar o valor do plano para este pagamento.');
        }

        $metodoPagamento = $validated['metodo_pagamento'] ?? 'cartao_credito';
        $gatewayTransactionId = null;
        $gatewayResultado = null;

        if (! empty($validated['payment_method'])) {
            $empresa = $pagamento->empresa ?? $reserva->empresa ?? $this->gatewayEmpresaConfigService->empresaPorId((int) $reserva->empresa_id);
            if (! $empresa) {
                return back()->with('error', 'Empresa da reserva nao encontrada para configuracao de pagamento.');
            }

            $gateway = $this->gatewayEmpresaConfigService->gateway($empresa);
            if ($gateway !== 'stripe') {
                return back()->with('error', 'Pagamento por cartao nesta tela requer Stripe como gateway.');
            }

            $currency = $this->gatewayEmpresaConfigService->moeda($empresa);
            $credenciais = $this->gatewayEmpresaConfigService->credenciais($empresa, $gateway);

            $payload = array_merge([
                'reserva_motorista_app_id' => $reserva->id,
                'valor' => $valorTotal,
                'currency' => $currency,
                'payment_method' => (string) $validated['payment_method'],
                'customer_id' => (string) ($reserva->user?->stripe_id ?? ''),
                'descricao' => 'Pagamento da reserva motorista APP #'.$reserva->id,
                'payer_email' => (string) ($reserva->user?->email ?? ''),
                'installments' => (int) ($validated['installments'] ?? 1),
            ], $credenciais);

            try {
                $gatewayResultado = $this->gerenciadorDePagamentos
                    ->resolver($gateway)
                    ->cobrarPagamento($payload);
            } catch (\Throwable $e) {
                \Log::error('Falha ao processar pagamento motorista APP', [
                    'pagamento_id' => (int) $pagamento->id,
                    'reserva_motorista_app_id' => (int) $reserva->id,
                    'message' => $e->getMessage(),
                ]);

                return back()->with('error', 'Falha ao processar o pagamento no cartao. Tente novamente.');
            }

            if (($gatewayResultado['status'] ?? 'error') !== 'success') {
                return back()->with('error', $gatewayResultado['message'] ?? 'Pagamento nao efetuado.');
            }

            $metodoPagamento = 'cartao_credito';
            $gatewayTransactionId = $gatewayResultado['transaction_id'] ?? null;
        }

        $pagamento->update([
            'status' => 'pago',
            'valor_unitario' => $valorUnitario,
            'valor_total' => $valorTotal,
            'metodo_pagamento' => $metodoPagamento,
            'pago_em' => now(),
            'referencia_pagamento' => $validated['referencia_pagamento'] ?? $gatewayTransactionId,
            'metadados' => array_merge((array) ($pagamento->metadados ?? []), [
                'gateway_transaction_id' => $gatewayTransactionId,
                'gateway_resultado' => $gatewayResultado,
            ]),
        ]);

        $contratoAssinado = ! empty(data_get($reserva->contrato_snapshot_json, 'assinatura'));

        $reserva->fill([
            'data_fim' => $fimConfirmado,
            'quantidade_dias' => $diasAtualizados,
            'valor_plano' => $valorUnitario,
            'valor_total_final' => $valorTotal + (float) ($reserva->valor_opcionais ?? 0),
            'status' => $contratoAssinado ? 'ativa' : 'confirmada',
            'status_pagamento' => 'pago',
            'pagamento_efetuado' => true,
        ])->save();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pagamento confirmado. Reserva confirmada e aguardando retirada.',
                'pagamento' => $pagamento->id,
            ]);
        }

        return back()
            ->with('success', 'Pagamento confirmado. Reserva confirmada e aguardando retirada.')
            ->with('pagamento', $pagamento->id);
    }

    public function efetuarPagamentoPorReserva($id)
    {
        $identificadorOriginal = (string) $id;
        if (ctype_digit($identificadorOriginal)) {
            $reservaComCodigo = Reserva::withoutGlobalScopes()
                ->with('codigoReserva')
                ->find($identificadorOriginal);

            if ($reservaComCodigo?->codigoReserva?->codigo) {
                return redirect()->route('reserva.pagamento.efetuar', [
                    'id' => $reservaComCodigo->codigoReserva->codigo,
                ]);
            }
        }

        $codigoReserva = $this->dadosCodigoReservaPorIdentificador($identificadorOriginal);

        if (($codigoReserva && $codigoReserva->tipo_origem === 'motorista_app')
            || (! $codigoReserva && $this->identificadorComecaComPrefixo($identificadorOriginal, 'MAP'))
        ) {
            $idMotoristaApp = $this->idReservaPorIdentificador(
                $identificadorOriginal,
                'MAP',
                $codigoReserva,
                'reserva_motorista_app_id'
            );

            $reserva = ReservaMotoristaApp::withoutGlobalScopes()
                ->with(['planoMotoristaApp', 'codigoReserva', 'pagamentos'])
                ->find($idMotoristaApp);

            if (! $reserva) {
                return redirect()->route('reserva.index')->with('error', 'Reserva motorista APP nao encontrada.');
            }

            $this->garantirPrimeiroPagamentoMotoristaApp($reserva);
            $reserva->load('pagamentos');

            $pagamento = $reserva->pagamentos
                ->filter(fn ($item) => strtolower((string) ($item->status ?? '')) === 'pendente')
                ->sortByDesc('id')
                ->first();

            if (! $pagamento) {
                return redirect()
                    ->route('reserva.show', $reserva->codigoReserva?->codigo ?? $reserva->id)
                    ->with('success', 'Nao ha pagamento pendente para esta reserva.');
            }

            return $this->efetuarPagamentoMotoristaApp($pagamento->id);
        }

        $idReserva = $this->idReservaPorIdentificador($identificadorOriginal, 'RES', $codigoReserva, 'reserva_id');

        return $this->efetuarPagamento($idReserva);
    }

    public function efetuarPagamentoMotoristaApp($pagamentoId)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }
        $ehAdmin = (bool) ($user->isAdministrador() ?? false);

        $pagamento = ReservaMotoristaAppPagamento::query()
            ->with([
                'empresa',
                'user',
                'reservaMotoristaApp.codigoReserva',
                'reservaMotoristaApp.empresa',
                'reservaMotoristaApp.user',
                'reservaMotoristaApp.planoMotoristaApp',
                'reservaMotoristaApp.estacaoRetirada',
            ])
            ->find($pagamentoId);

        if (! $pagamento || (! $ehAdmin && (int) ($pagamento->user_id ?? 0) !== (int) $user->id)) {
            return redirect()->route('reserva.minhas')->with('error', 'Pagamento nao encontrado.');
        }

        if (strtolower((string) ($pagamento->status ?? '')) === 'pago') {
            return redirect()
                ->route('reserva.minhas.show', ['id' => $pagamento->reservaMotoristaApp?->codigoReserva?->codigo ?? $pagamento->reserva_motorista_app_id])
                ->with('success', 'Este pagamento ja foi confirmado.');
        }

        $reservaMotoristaApp = $pagamento->reservaMotoristaApp;
        if (! $reservaMotoristaApp) {
            return redirect()->route('reserva.minhas')->with('error', 'Reserva nao encontrada para este pagamento.');
        }

        $plano = $reservaMotoristaApp->planoMotoristaApp;
        $periodos = max(1, (int) ($pagamento->periodos ?? 1));
        $valorUnitarioContrato = (float) ($reservaMotoristaApp->valor_periodo_contrato ?: ($reservaMotoristaApp->valor_plano ?: 0));
        $valorUnitarioPlano = (float) ($plano?->valor_periodo ?? 0);
        $valorUnitario = $valorUnitarioContrato > 0
            ? $valorUnitarioContrato
            : ($valorUnitarioPlano > 0 ? $valorUnitarioPlano : (float) ($pagamento->valor_unitario ?? 0));

        $pagamentosAnteriores = $reservaMotoristaApp->pagamentos()
            ->where('status', 'pago')
            ->orderByDesc('id')
            ->get();

        $pagamentoPaiId = data_get($pagamento->metadados, 'pagamento_pai_id');
        $valorOpcionaisReserva = (float) ($reservaMotoristaApp->valor_opcionais ?? 0);
        $valorTotalCicloOriginal = (float) (data_get($pagamento->metadados, 'valor_original_ciclo') ?: round(($valorUnitario * $periodos) + $valorOpcionaisReserva, 2));

        $totalPagoAcumulado = (float) $reservaMotoristaApp->pagamentos()
            ->where('status', 'pago')
            ->where('id', '!=', $pagamento->id)
            ->sum('valor_total');

        if ($totalPagoAcumulado > 0) {
            $saldoPendenteCalculado = max(0, round($valorTotalCicloOriginal - $totalPagoAcumulado, 2));
            $valorTotal = $saldoPendenteCalculado;
        } else {
            $valorTotal = (float) ($pagamento->valor_total ?: $valorTotalCicloOriginal);
        }

        if ($valorUnitario > 0 && ((float) ($pagamento->valor_unitario ?? 0) <= 0 || (float) ($pagamento->valor_total ?? 0) <= 0)) {
            $pagamento->update([
                'periodos' => $periodos,
                'valor_unitario' => $valorUnitario,
                'valor_total' => $valorTotal,
                'periodo_unidade' => $pagamento->periodo_unidade ?: $this->rotuloPeriodoMotoristaApp((string) ($reservaMotoristaApp->periodo_contrato ?: $plano?->periodo ?: 'semana')),
            ]);
            $pagamento->refresh();
        }

        $empresa = $pagamento->empresa ?? $reservaMotoristaApp->empresa ?? $user->empresa;
        $gatewayPadrao = $empresa ? $this->gatewayEmpresaConfigService->gateway($empresa) : 'manual';
        $moedaPadrao = $empresa ? $this->gatewayEmpresaConfigService->moeda($empresa) : 'BRL';
        $gatewayNome = [
            'stripe' => 'Stripe',
            'mercado_livre' => 'Mercado Livre',
            'paypal' => 'PayPal',
            'manual' => 'Manual',
        ][$gatewayPadrao] ?? strtoupper($gatewayPadrao);

        $clientePagamento = $reservaMotoristaApp->user ?? $pagamento->user ?? $user;
        $metodosPagamento = [];
        $stripePublicKey = null;
        if ($gatewayPadrao === 'stripe' && $empresa) {
            $stripePublicKey = (string) ($empresa->stripe_public_key ?? config('services.stripe.key'));
            $stripeSecret = (string) ($empresa->stripe_secret ?? config('services.stripe.secret'));
            if ($stripeSecret !== '' && ! empty($clientePagamento?->stripe_id)) {
                try {
                    $metodosPagamento = $this->clienteStripeService->getPaymentMethods($clientePagamento->stripe_id, $stripeSecret);
                } catch (\Throwable $e) {
                    \Log::warning('Erro ao carregar cartoes motorista APP', [
                        'user_id' => $clientePagamento->id ?? null,
                        'stripe_id' => $clientePagamento->stripe_id ?? null,
                        'message' => $e->getMessage(),
                    ]);
                    $metodosPagamento = [];
                }
            }
        }

        $titulo = 'Pagamento da Reserva Motorista APP';
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Minhas reservas', route('reserva.minhas')),
            new BreadCrumbItem('Reserva motorista APP #'.$reservaMotoristaApp->codigoReserva->codigo, route('reserva.minhas.show', ['id' => $reservaMotoristaApp->codigoReserva?->codigo ?? $reservaMotoristaApp->id])),
            new BreadCrumbItem('Pagamento'),
        ]);

        $viewPagamento = request()->routeIs('reserva.minhas.pagamento.efetuar')
            ? 'reserva.efetuar-pagamento-minhas-motorista-app'
            : 'reserva.efetuar-pagamento';

        return view($viewPagamento, compact(
            'pagamento',
            'reservaMotoristaApp',
            'breadcrumb',
            'titulo',
            'gatewayPadrao',
            'gatewayNome',
            'moedaPadrao',
            'metodosPagamento',
            'stripePublicKey',
            'pagamentosAnteriores'
        ));
    }

    private function calcularDuracaoMotoristaApp(?string $periodo, int $periodos): array
    {
        $periodos = max(1, $periodos);

        return match ($periodo) {
            'quinze' => [15 * $periodos, 'quinze_dias'],
            'mes' => [30 * $periodos, 'mes'],
            'trimestre' => [90 * $periodos, 'trimestre'],
            'semestre' => [180 * $periodos, 'semestre'],
            'ano' => [365 * $periodos, 'ano'],
            default => [7 * $periodos, 'semana'],
        };
    }

    private function aplicarPeriodoMotoristaApp(Carbon $inicio, ?string $periodo, int $periodos): Carbon
    {
        $periodos = max(1, $periodos);

        return match ($periodo) {
            'quinze' => $inicio->copy()->addDays(15 * $periodos),
            'mes' => $inicio->copy()->addMonthsNoOverflow($periodos),
            'trimestre' => $inicio->copy()->addMonthsNoOverflow(3 * $periodos),
            'semestre' => $inicio->copy()->addMonthsNoOverflow(6 * $periodos),
            'ano' => $inicio->copy()->addYearsNoOverflow($periodos),
            default => $inicio->copy()->addWeeks($periodos),
        };
    }

    private function rotuloPeriodoMotoristaApp(?string $periodo): string
    {
        return match ($periodo) {
            'quinze' => 'Quinze dias',
            'mes' => 'MÃƒÂªs',
            'trimestre' => 'Trimestre',
            'semestre' => 'Semestre',
            'ano' => 'Ano',
            default => 'Semana',
        };
    }

    private function montarSnapshotContratoMotoristaApp(
        ReservaMotoristaApp $reserva,
        int $veiculoId,
        int $quilometragemInicial,
        string $nivelCombustivelInicial,
        Carbon $dataRetiradaReal,
        Carbon $dataFimPrimeiroCiclo,
        float $valorUnitario,
        string $periodoContrato
    ): array {
        $reserva->loadMissing(['empresa', 'user', 'planoMotoristaApp', 'veiculo.marca', 'veiculo.modelo', 'solicitacao']);

        $veiculo = $reserva->veiculo;
        $plano = $reserva->planoMotoristaApp;

        $opcionais = collect($reserva->opcionais_json ?? [])
            ->map(function ($item) use ($plano) {
                $opcional = $plano?->opcionais?->firstWhere('id', (int) ($item['id'] ?? 0));

                return [
                    'id' => (int) ($item['id'] ?? 0),
                    'nome' => $opcional?->nome ?? ('Opcional #'.(int) ($item['id'] ?? 0)),
                    'quantidade' => (int) ($item['quantidade'] ?? 0),
                ];
            })
            ->values()
            ->all();

        return [
            'documento' => [
                'numero' => 'MAP-'.str_pad((string) $reserva->id, 6, '0', STR_PAD_LEFT),
                'titulo' => 'CONTRATO DE LOCAÃƒâ€¡ÃƒÆ’O MOTORISTA APP',
                'subtitulo' => 'CONDIÃƒâ€¡Ãƒâ€¢ES PARTICULARES DO CONTRATO',
                'data_emissao' => now()->format('d/m/Y H:i'),
                'empresa' => [
                    'nome' => $reserva->empresa?->nome ?? config('app.name'),
                    'endereco' => $reserva->empresa?->endereco ?? '-',
                    'logo_url' => null,
                ],
            ],
            'cliente' => [
                'nome' => $reserva->user?->nome ?? $reserva->user?->name ?? '-',
                'email' => $reserva->user?->email ?? '-',
                'telefone' => $reserva->user?->telefone ?? '-',
                'cpf' => $reserva->user?->cpf ?? '-',
                'cnh' => $reserva->user?->numero_carta_conducao ?? '-',
            ],
            'veiculo' => [
                'matricula' => $veiculo?->placa_licenca ?? ('#'.$veiculoId),
                'marca' => $veiculo?->marca?->nome ?? '-',
                'modelo' => $veiculo?->modelo?->nome ?? '-',
                'nome' => $veiculo?->nome ?? '-',
                'quilometros' => $quilometragemInicial,
                'combustivel' => $nivelCombustivelInicial,
            ],
            'contrato' => [
                'numero' => 'MAP-'.str_pad((string) $reserva->id, 6, '0', STR_PAD_LEFT),
                'titulo' => 'CONTRATO DE LOCAÃƒâ€¡ÃƒÆ’O MOTORISTA APP',
                'periodicidade' => $periodoContrato,
                'periodicidade_label' => $this->rotuloPeriodoMotoristaApp($periodoContrato),
                'assinatura' => null,
                'data_assinatura' => null,
            ],
            'reserva' => [
                'id' => $reserva->id,
                'status' => 'Ativa',
                'origem' => 'Motorista APP',
                'criada_em' => optional($reserva->created_at)->format('d/m/Y H:i') ?? '-',
                'pagamento_efetuado' => $reserva->pagamento_efetuado,
                'data_inicio' => $dataRetiradaReal->format('d/m/Y H:i'),
                'data_fim' => $dataFimPrimeiroCiclo->format('d/m/Y H:i'),
                'quantidade_dias' => max(1, $dataRetiradaReal->diffInDays($dataFimPrimeiroCiclo)),
            ],
            'valores' => [
                'valor_periodo' => $valorUnitario,
                'valor_opcionais' => (float) ($reserva->valor_opcionais ?? 0),
                'valor_total' => round($valorUnitario + (float) ($reserva->valor_opcionais ?? 0), 2),
                'caucao' => (float) ($reserva->valor_caucao ?? ($plano?->calcao ?? 0)),
            ],
            'opcionais' => $opcionais,
            'pagamentos' => $reserva->pagamentos->map(function ($pagamento) {
                return [
                    'status' => $pagamento->status ?? 'pendente',
                    'valor' => (float) ($pagamento->valor_total ?? 0),
                    'periodo_inicio' => optional($pagamento->data_inicio_prevista)->format('d/m/Y H:i'),
                    'periodo_fim' => optional($pagamento->data_fim_prevista)->format('d/m/Y H:i'),
                ];
            })->values()->all(),
            'caucao' => [
                'valor' => (float) ($reserva->valor_caucao ?? ($plano?->calcao ?? 0)),
            ],
            'danos_detalhados' => [],
            'cobrancas_avaria' => [],
            'condicoes_gerais' => [],
        ];
    }

    public function showMinhaMulta($id)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        $multa = Multa::query()
            ->with([
                'reserva.usuario',
                'veiculo.modelo',
                'veiculo.marca',
                'cliente',
                'responsavelLancamento',
            ])
            ->findOrFail($id);

        $pertenceAoUsuario = (int) ($multa->cliente_id ?? 0) === (int) $user->id
            || (int) ($multa->reserva?->user_id ?? 0) === (int) $user->id;

        if (! $pertenceAoUsuario) {
            return redirect()->route('reserva.minhas')->with('error', 'Voce nao tem permissao para ver essa multa.');
        }

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Minhas Reservas', route('reserva.minhas')),
            new BreadCrumbItem('Multa #'.$multa->id),
        ]);

        $valorMulta = (float) ($multa->valor_final ?? $multa->valor_original ?? 0);
        $statusMulta = strtolower((string) ($multa->status_multa ?? 'pendente'));
        $podePagar = empty($multa->pago_em);
        $urlPagamento = route('site.multa.pagamento', ['multa' => $multa->id]);

        return view('reserva.multa-cliente-show', compact(
            'multa',
            'breadcrumb',
            'valorMulta',
            'statusMulta',
            'podePagar',
            'urlPagamento'
        ))->with('titulo', 'Multa #'.$multa->id);
    }

    public function emitirNfse($id)
    {
        $reserva = $this->reservaService->getById($id);
        if (! $reserva) {
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
            $nfseWarning = 'Emissao de NFS-e nÃƒÂ£o configurada para esta empresa.';
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
            'N' => 'Inativo',
        ];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Reservas', 'reserva.index', "Editar: $reserva->id");
        [$metodo, $rota] = $this->getFormMethodAndRoute($reserva);
        $usuarioAutenticado = Auth::user()?->loadMissing(['estacoesPermitidas']);
        $estacoesPermitidasUsuario = $usuarioAutenticado && ! $usuarioAutenticado->hasAcessoTodasEstacoes()
            ? $usuarioAutenticado->estacoesPermitidas->sortBy('nome')->values()
            : collect();

        // Consultando as variÃƒÂ¡veis necessÃƒÂ¡rias
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
                static fn (string $falta): bool => ! str_contains(mb_strtolower($falta), 'estacao ativa para iniciar reservas')
            ));
            $preRequisitosReserva['faltas'][] = 'VocÃƒÂª possui estaçÃƒÂµes no perfil, mas nenhuma estÃƒÂ¡ habilitada para retirada/devolução nesta reserva.';
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
    ): array {
        $qtdEstacoes = $this->contarItens($estacoes);
        $qtdGrupos = $this->contarItens($gruposVeiculo);
        $qtdModelos = $this->contarItens($modelosAtivos);
        $qtdVeiculosTotal = (int) ($resumoVeiculosEstacoes['total'] ?? 0);
        $qtdEstacoesComVeiculo = (int) ($resumoVeiculosEstacoes['estacoes_com_veiculo'] ?? 0);
        $detalhesVeiculosPorEstacao = $resumoVeiculosEstacoes['detalhes'] ?? [];

        $faltas = [];
        $user = Auth::user();
        $semPermissaoDeEstacao = $user && ! $user->hasAcessoTodasEstacoes() && empty($user->estacoesPermitidasIds());

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
            'EUR' => 'Ã¢â€šÂ¬',
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
        if (empty($estacoesPermitidasUsuario) || ! method_exists($estacoesPermitidasUsuario, 'filter')) {
            return [];
        }

        $idsDisponiveis = array_map('intval', $estacoesDisponiveisIds);

        return $estacoesPermitidasUsuario
            ->filter(static function ($estacao) use ($idsDisponiveis): bool {
                return ! in_array((int) ($estacao->id ?? 0), $idsDisponiveis, true);
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
            $motivos[] = 'estÃƒÂ¡ inativa';
        }

        $checkin = ($estacao->checkin ?? 'N') === 'S';
        $checkout = ($estacao->checkout ?? 'N') === 'S';

        if (! $checkin && ! $checkout) {
            $motivos[] = 'nÃƒÂ£o estÃƒÂ¡ habilitada para retirada nem devolução';
        } elseif (! $checkin) {
            $motivos[] = 'nÃƒÂ£o estÃƒÂ¡ habilitada para retirada';
        } elseif (! $checkout) {
            $motivos[] = 'nÃƒÂ£o estÃƒÂ¡ habilitada para devolução';
        }

        if (empty($motivos)) {
            return 'nÃƒÂ£o aparece nas opçÃƒÂµes da reserva.';
        }

        return implode(' e ', $motivos).'.';
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
        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Reserva nÃƒÂ£o pode ser excluida', 'error');
    }

    /**
     * Tela de vistoria de devolução (check-out). A reserva sÃƒÂ³ ÃƒÂ© concluída apÃƒÂ³s finalizar aqui.
     */
    public function checkoutVistoria(Request $request, $reserva_id)
    {
        $reserva = Reserva::withoutGlobalScopes()
            ->with(['modelos', 'usuario', 'veiculo', 'contratoCheckin', 'calcao'])
            ->find($reserva_id);

        if (! $reserva) {
            return redirect()
                ->route('reserva.index')
                ->with('error', 'Reserva nao encontrada.');
        }
        $retorno = trim((string) $request->query('retorno', ''));
        $retornoMotoristaApp = null;
        if ($retorno !== '') {
            $codigoRetorno = $this->dadosCodigoReservaPorIdentificador($retorno);
            if ($codigoRetorno && $codigoRetorno->tipo_origem === 'motorista_app') {
                $retornoMotoristaApp = ReservaMotoristaApp::withoutGlobalScopes()
                    ->with(['codigoReserva', 'empresa', 'user', 'estacaoRetirada', 'planoMotoristaApp'])
                    ->find((int) ($codigoRetorno->reserva_motorista_app_id ?? 0));
            }
        }

        if (! $reserva->isPagamentoEfetuado()) {
            return redirect()
                ->route('reserva.show', $reserva_id)
                ->with('warning', 'O pagamento deve ser efetuado antes de realizar o check-out.');
        }

        if (! $reserva->contratoCheckin || $reserva->contratoCheckin->assinado !== 'S') {
            return redirect()
                ->route('reserva.show', $reserva_id)
                ->with('warning', 'O check-in deve ser realizado e o contrato assinado antes do check-out.');
        }

        if ($mensagemCalcao = $this->validarCalcaoParaCheckout($reserva)) {
            return redirect()
                ->route('reserva.show', $reserva_id)
                ->with('error', $mensagemCalcao);
        }

        if (! $reserva->veiculo_id) {
            return redirect()
                ->route('reserva.show', $reserva_id)
                ->with('error', 'Associe um veículo ÃƒÂ  reserva antes do check-out.');
        }

        $categoriaDanos = $this->categoriaDanoService->getAllForSelect();

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Reservas', route('reserva.index')),
            new BreadCrumbItem('Detalhes da Reserva Nº'.$reserva_id, route('reserva.show', $reserva_id)),
            new BreadCrumbItem('Check-out / Vistoria'),
        ]);

        return view('reserva.checkout', compact('reserva', 'categoriaDanos', 'breadcrumb'))
            ->with('titulo', 'Check-out (vistoria) Ã¢â‚¬â€ reserva Nº '.$reserva->id);
    }

    public function finalizarCheckoutVistoria(FinalizarCheckoutVistoriaRequest $request, $reserva_id)
    {
        $reserva = Reserva::withoutGlobalScopes()
            ->with(['veiculo', 'calcao', 'contratoCheckin'])
            ->find($reserva_id);

        if (! $reserva) {
            return response()->json(['success' => false, 'message' => 'Reserva nao encontrada.'], 404);
        }

        if (! $reserva->podeFazerCheckout()) {
            return response()->json(['success' => false, 'message' => 'Reserva nÃƒÂ£o estÃƒÂ¡ em status vÃƒÂ¡lido para check-out.'], 422);
        }

        if ($mensagemCalcao = $this->validarCalcaoParaCheckout($reserva)) {
            return response()->json(['success' => false, 'message' => $mensagemCalcao], 422);
        }

        if (ContratoCheckout::where('reserva_id', $reserva_id)->exists()) {
            return response()->json(['success' => false, 'message' => 'Check-out desta reserva jÃƒÂ¡ foi registrado.'], 422);
        }

        if (! $reserva->veiculo_id) {
            return response()->json(['success' => false, 'message' => 'Veículo nÃƒÂ£o vinculado ÃƒÂ  reserva.'], 422);
        }

        $dados = $request->validated();

        // Quilometragem: devolucao deve ser >= quilometragem inicial (check-in)
        $kmInicial = $reserva->contratoCheckin?->quilometragem_atual;
        if ($kmInicial !== null) {
            $kmDevolucao = (float) ($dados['quilometragem_devolucao'] ?? 0);
            if ($kmDevolucao < (float) $kmInicial) {
                return response()->json([
                    'success' => false,
                    'message' => 'A quilometragem na devolucao deve ser maior ou igual a quilometragem inicial do check-in (KM '.$kmInicial.').',
                ], 422);
            }
        }

        try {
            DB::beginTransaction();

            $dir = 'checkout';
            $mapFotosCheckout = [
                'foto_direita_co' => 'foto_direita_path',
                'foto_esquerda_co' => 'foto_esquerda_path',
                'foto_frente_co' => 'foto_frente_path',
                'foto_tras_co' => 'foto_tras_path',
                'foto_velocimetro_co' => 'foto_velocimetro_path',
                'foto_combustivel_co' => 'foto_combustivel_path',
            ];

            $dadosFotosCheckout = [];
            foreach ($mapFotosCheckout as $input => $col) {
                if ($request->hasFile($input) && $request->file($input)->isValid()) {
                    $file = $request->file($input);
                    $extension = $file->getClientOriginalExtension() ?: 'jpg';
                    $filename = \Illuminate\Support\Str::random(40).'.'.$extension;
                    $storedPath = Storage::disk('public')->putFileAs($dir, $file, $filename);
                    $dadosFotosCheckout[$col] = ltrim($storedPath, '/');
                }
            }

            $contratoCheckout = ContratoCheckout::create([
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

            $documentos = (array) ($reserva->documentos_json ?? []);
            data_set($documentos, 'checkout.fotos', array_merge(
                data_get($documentos, 'checkout.fotos', []),
                $dadosFotosCheckout
            ));
            data_set($documentos, 'checkout.notas_devolucao', $dados['notas_devolucao'] ?? null);
            data_set($documentos, 'checkout.observacoes_devolucao', $dados['observacoes_devolucao'] ?? null);
            data_set($documentos, 'checkout.registrado_em', now()->format('Y-m-d H:i:s'));

            $reserva->update([
                'documentos_json' => $documentos,
            ]);

            $this->movimentacaoService->realizarCheckOut(
                (int) $reserva->veiculo_id,
                (int) $reserva->estacao_devolucao_id,
                (int) $reserva_id,
                'Check-out com vistoria de devolução'
            );

            if (! empty($reserva->estacao_devolucao_id)) {
                $this->movimentacaoService->registrarChegadaNaEstacao(
                    (int) $reserva->veiculo_id,
                    (int) $reserva->estacao_devolucao_id,
                    'Chegada real registrada no check-out da reserva '.$reserva_id,
                    false
                );
            }

            $this->reservaService->checkOutRealizado($reserva_id);
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
                'message' => 'Erro ao finalizar check-out: '.$e->getMessage(),
            ], 500);
        }
    }

    public function assinarContratoCheckoutMinhaReserva(
        Request $request,
        $codigo,
        ReservaResolver $reservaResolver,
        AssinarContratoCheckoutAction $action,
    ) {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        try {
            $reservaResolvida = $reservaResolver->resolver((string) $codigo);
        } catch (\Throwable $e) {
            return redirect()
                ->route('reserva.minhas')
                ->with('error', 'Reserva não encontrada.');
        }

        /** @var \App\Models\Reserva $reserva */
        $reserva = $reservaResolvida->registro;

        $ehTitularDaReserva = in_array((int) $user->id, [
            (int) ($reserva->user_id ?? 0),
            (int) ($reserva->criado_por_user_id ?? 0),
            (int) ($reserva->usuario?->id ?? 0),
        ], true);

        if (! $ehTitularDaReserva) {
            return redirect()
                ->route('reserva.minhas.show', ['id' => $reserva->codigoReserva?->codigo ?? $reserva->id])
                ->with('error', 'Você não tem permissão para assinar este contrato.');
        }

        if (! $reserva->contratoCheckout) {
            return redirect()
                ->route('reserva.minhas.show', ['id' => $reserva->codigoReserva?->codigo ?? $reserva->id])
                ->with('warning', 'O contrato de devolução ainda não foi gerado.');
        }

        return $action->handle($request, (string) ($reserva->codigoReserva?->codigo ?? $reserva->id), $reservaResolver);
    }

    public function salvarSatisfacao(Request $request, $codigo, ReservaResolver $reservaResolver)
    {
        try {
            $reservaResolvida = $reservaResolver->resolver((string) $codigo);
            $reserva = $reservaResolvida->registro;
        } catch (\Throwable $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Reserva não encontrada.'], 404);
            }

            return redirect()->route('reserva.minhas')->with('error', 'Reserva não encontrada.');
        }

        if ($reserva->satisfacao()->exists()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Esta avaliação já foi enviada e não pode ser alterada.'], 409);
            }

            return redirect()
                ->route('reserva.minhas.show', ['id' => $codigo])
                ->with('error', 'Esta avaliação já foi enviada e não pode ser alterada.');
        }

        $validated = $request->validate([
            'satisfacao_geral' => ['required', 'integer', 'min:1', 'max:5'],
            'facilidade_reserva' => ['required', 'integer', 'min:1', 'max:5'],
            'nps_nota' => ['required', 'integer', 'min:0', 'max:10'],
            'comentario' => ['nullable', 'string', 'max:1000'],
        ]);

        \App\Models\ReservaSatisfacao::create([
            'empresa_id' => session('empresa_id') ?? $reserva->empresa_id ?? null,
            'reserva_id' => (int) $reserva->id,
            'respondente_user_id' => Auth::id(),
            'satisfacao_geral' => (int) $validated['satisfacao_geral'],
            'facilidade_reserva' => (int) $validated['facilidade_reserva'],
            'nps_nota' => (int) $validated['nps_nota'],
            'comentario' => trim((string) ($validated['comentario'] ?? '')) ?: null,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Avaliação registrada com sucesso!']);
        }

        return redirect()
            ->route('reserva.minhas.show', ['id' => $codigo])
            ->with('success', 'Avaliação registrada com sucesso!');
    }

    private function validarCalcaoParaCheckout(Reserva $reserva): ?string
    {
        $calculo = $this->calcaoService->calcularValor($reserva);
        $valorCalculado = (float) ($calculo['valor_calculado'] ?? 0);

        if ($valorCalculado <= 0) {
            return null; // nÃƒÂ£o exige calção
        }

        $calcao = $reserva->relationLoaded('calcao')
            ? $reserva->calcao
            : $reserva->calcao()->first();
        if (! $calcao) {
            return 'Para fazer o checkout ÃƒÂ© obrigatÃƒÂ³rio registrar o pagamento do calção primeiro.';
        }

        $ok = [
            Calcao::STATUS_AUTORIZADO,
            Calcao::STATUS_CAPTURADO,
            Calcao::STATUS_PARCIALMENTE_CAPTURADO,
        ];

        $statusCalcao = strtolower(trim((string) $calcao->status));

        if (in_array($statusCalcao, $ok, true)) {
            return null;
        }

        return 'Para fazer o checkout ÃƒÂ© obrigatÃƒÂ³rio registrar o calção. Status atual do calção: '.(string) ($calcao->label_status ?? $calcao->status);
    }

    private function liberarCalcaoAposCheckout(Reserva $reserva): void
    {
        $calcao = $reserva->calcao;
        if (! $calcao) {
            return;
        }

        if (! empty($calcao->payment_intent_id) && $calcao->podeLiberar()) {
            $calcaoLiberado = $this->calcaoService->cancelar(
                $calcao,
                'Calção liberado automaticamente apÃƒÂ³s o checkout.'
            );

            $this->reservaService->adicionaLog($reserva->id, Auth::id(), json_encode([
                'acao' => 'Calção liberado automaticamente no checkout',
                'calcao_id' => $calcaoLiberado->id,
            ]));

            return;
        }

        if (! empty($calcao->payment_intent_id) || $calcao->status === Calcao::STATUS_CANCELADO) {
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
            'observacao' => 'Calção presencial devolvido automaticamente apÃƒÂ³s o checkout.',
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

    public function cancelar(Request $request, $id)
    {
        $reservaAntes = $this->reservaService->getById((int) $id);
        if (! $reservaAntes) {
            $message = 'Reserva nao encontrada.';

            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $message], 404)
                : redirect()->route('reserva.minhas')->with('error', $message);
        }

        $user = Auth::user();
        $ehDonoDaReserva = $user && (int) $reservaAntes->user_id === (int) $user->id;
        $redirectRoute = $ehDonoDaReserva ? 'reserva.minhas' : 'reserva.index';

        $podeCancelar = $ehDonoDaReserva || $user->isAdministrador();
        if ($user && ! $podeCancelar) {
            $message = 'Sem permissao para cancelar esta reserva.';

            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $message], 403)
                : redirect()->route('reserva.minhas.show', $reservaAntes->id)->with('error', $message);
        }

        if (! $reservaAntes->podeFazerCancelamento()) {
            $message = 'Não é possível cancelar uma reserva após a realização do check-in ou em seu status atual.';

            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $message], 422)
                : redirect()->route('reserva.minhas.show', $reservaAntes->id)->with('error', $message);
        }

        if ($ehDonoDaReserva && ! $reservaAntes->podeCancelarComoCliente()) {
            $message = 'O cancelamento só pode ser feito até 24 horas após a criação da reserva.';

            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $message], 422)
                : redirect()->route('reserva.minhas.show', $reservaAntes->id)->with('error', $message);
        }

        $this->reservaService->cancelar($id);
        $reserva = $this->reservaService->getById($id);
        if ($reserva) {
            $this->nfseService->cancelarPorReserva($reserva);
            $this->cancelarTransferenciasReserva($reserva);
        }
        $cancelamentoMensagem = null;
        if ($reserva) {
            $cancelamentoMensagem = $this->cancelarTransacoesGatewayReserva($reserva);
            $this->registrarEstornosLancamentosReserva($reserva);
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

        if ($request->expectsJson()) {
            $message = $cancelamentoMensagem ?: 'Reserva cancelada com sucesso.';

            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect' => route($redirectRoute),
            ]);
        }

        $redirect = redirect()->route($redirectRoute);
        if ($cancelamentoMensagem) {
            $redirect->with('warning', $cancelamentoMensagem);
        }

        return $redirect;
    }

    private function registrarCancelamentoTransacao(Reserva $reserva): void
    {
        if (! SchemaCache::hasTable('transacoes')) {
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
            if (! $metodoId && SchemaCache::hasTable('metodo_pagamentos')) {
                $metodoId = DB::table('metodo_pagamentos')->value('id');
            }
            if (! $metodoId && SchemaCache::hasTable('metodos_pagamento')) {
                $metodoId = DB::table('metodos_pagamento')->value('id');
            }
            $payload['metodo_pagamento_id'] = $metodoId ?: null;
        }

        if (SchemaCache::hasColumn('transacoes', 'tipo_pagamento_id')) {
            $tipoId = $transacaoBase?->tipo_pagamento_id;
            if (! $tipoId && SchemaCache::hasTable('tipo_pagamentos')) {
                $tipoId = DB::table('tipo_pagamentos')->value('id');
            }
            if (! $tipoId && SchemaCache::hasTable('tipos_pagamento')) {
                $tipoId = DB::table('tipos_pagamento')->value('id');
            }
            $payload['tipo_pagamento_id'] = $tipoId ?: null;
        }

        if (SchemaCache::hasColumn('transacoes', 'empresa_id')) {
            $payload['empresa_id'] = $reserva->empresa_id ?? null;
        }

        DB::table('transacoes')->insert($payload);
    }

    private function registrarEstornosLancamentosReserva(Reserva $reserva): void
    {
        if (! SchemaCache::hasTable('lancamentos_financeiros')) {
            return;
        }

        $lancamentos = DB::table('lancamentos_financeiros')
            ->where('reserva_id', $reserva->id)
            ->where('valor', '>', 0)
            ->whereIn(DB::raw('LOWER(COALESCE(status, ""))'), ['pendente', 'pago'])
            ->get();

        foreach ($lancamentos as $lancamento) {
            $chave = 'estorno_cancelamento|reserva|'.$reserva->id.'|lancamento|'.$lancamento->id;
            $jaExiste = DB::table('lancamentos_financeiros')
                ->where('empresa_id', $reserva->empresa_id)
                ->where('valor', '<', 0)
                ->where('observacoes', 'like', '%'.$chave.'%')
                ->exists();

            if ($jaExiste) {
                continue;
            }

            $payload = [
                'empresa_id' => $reserva->empresa_id,
                'categoria_financeira_id' => $lancamento->categoria_financeira_id,
                'descricao' => 'Estorno do cancelamento - '.($lancamento->descricao ?? 'Reserva #'.$reserva->id),
                'tipo' => $lancamento->tipo === 'receita' ? 'despesa' : 'receita',
                'valor' => -1 * abs((float) $lancamento->valor),
                'status' => 'pago',
                'data_vencimento' => now()->toDateString(),
                'data_pagamento' => now()->toDateTimeString(),
                'metodo_pagamento' => $lancamento->metodo_pagamento ?? null,
                'reserva_id' => $reserva->id,
                'veiculo_id' => $lancamento->veiculo_id ?? $reserva->veiculo_id,
                'user_id' => $lancamento->user_id ?? $reserva->user_id,
                'observacoes' => 'Gerado automaticamente no cancelamento da reserva. Chave: '.$chave,
                'recorrente' => false,
                'recorrencia' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (SchemaCache::hasColumn('lancamentos_financeiros', 'pago_por_user_id')) {
                $payload['pago_por_user_id'] = auth()->id() ?? $lancamento->pago_por_user_id ?? $reserva->user_id;
            }
            if (SchemaCache::hasColumn('lancamentos_financeiros', 'categoria_financeira_id')) {
                $payload['categoria_financeira_id'] = $lancamento->categoria_financeira_id;
            }

            DB::table('lancamentos_financeiros')->insert($payload);
        }
    }

    private function cancelarTransferenciasReserva(Reserva $reserva): void
    {
        if (! SchemaCache::hasTable('movimentacoes') || ! SchemaCache::hasColumn('movimentacoes', 'reserva_id')) {
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

    private function cancelarTransacoesGatewayReserva(Reserva $reserva): ?string
    {
        $empresa = $this->gatewayEmpresaConfigService->empresaPorId((int) $reserva->empresa_id);
        if (! $empresa) {
            return 'Pagamento nao foi cancelado: empresa nao encontrada para o gateway.';
        }

        $gateway = $this->gatewayEmpresaConfigService->gateway($empresa);
        $credenciais = $this->gatewayEmpresaConfigService->credenciais($empresa, $gateway);

        $provider = $this->gerenciadorDePagamentos->resolver($gateway);
        if (! $provider) {
            return 'Pagamento nao foi cancelado: gateway indisponivel.';
        }

        $mensagens = [];
        $transacoes = Transacao::query()
            ->where('reserva_id', $reserva->id)
            ->whereNotNull('gateway_transaction_id')
            ->orderByDesc('id')
            ->get();

        foreach ($transacoes as $transacao) {
            $statusAtual = strtolower((string) ($transacao->status ?? ''));
            if (in_array($statusAtual, ['refunded', 'refund', 'estorno', 'cancelled', 'canceled'], true)) {
                continue;
            }

            $gatewayTransactionId = (string) ($transacao->gateway_transaction_id ?? '');
            if ($gatewayTransactionId === '') {
                continue;
            }

            $resultado = $provider->cancelarPagamento($gatewayTransactionId, $credenciais);
            $info = is_array($transacao->info_pagamento ?? null) ? $transacao->info_pagamento : [];
            $info['cancelamento'] = $resultado;

            if (($resultado['status'] ?? 'error') !== 'success') {
                $resultado = $provider->reembolsarPagamento($gatewayTransactionId, $credenciais);
                $info['reembolso'] = $resultado;
            }

            if (($resultado['status'] ?? 'error') === 'success') {
                $transacao->status = (string) ($resultado['raw_status'] ?? $resultado['action'] ?? 'refunded');
                $transacao->info_pagamento = $info;
                $transacao->save();
                $this->criarTransacaoEstorno($transacao, $resultado, $reserva);

                continue;
            }

            $transacao->status = $transacao->status ?: 'cancel_failed';
            $transacao->info_pagamento = $info;
            $transacao->save();
            $mensagens[] = $resultado['message'] ?? 'Pagamento nao foi cancelado no gateway.';
        }

        return $mensagens ? implode(' ', array_unique($mensagens)) : null;
    }

    private function criarTransacaoEstorno(Transacao $transacao, array $resultado, Reserva $reserva): void
    {
        if (! SchemaCache::hasTable('transacoes')) {
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
        if ($gatewayTransactionId === '' && ! empty($transacao->gateway_transaction_id)) {
            $gatewayTransactionId = $transacao->gateway_transaction_id.':refund';
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
        // Salva o valor de paginação na sessÃƒÂ£o
        $request->session()->put("$this->nomeFiltro.perPage", $request->input('perPage'));

        // Redireciona de volta para a pÃƒÂ¡gina de listagem
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
     * Função para obter o mÃƒÂ©todo e a rota para o formulÃƒÂ¡rio.
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
                ->withErrors(['reserva' => trim('Nao foi possivel salvar a reserva. '.$faltas)]);
        }

        return redirect()
            ->route('reserva.create')
            ->withInput()
            ->withErrors(['reserva' => trim('Nao foi possivel criar a reserva. '.$faltas)]);
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
                'grupo_frota' => null,
                'modelos' => [],
                'modelos_vinculados' => [],
                'tipo_opcional' => [],
                'qtd_veiculos_estacao' => 0,
                'aviso_modelos' => 'Preencha as datas e estacoes para verificar disponibilidade.',
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
                ? 'Nao ha veiculos disponiveis neste grupo de frota para a estacao e periodo selecionados.'
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
                if ((int) $totalAtivos <= 0 && ! in_array((int) $estacaoId, $idsFiltrados, true)) {
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
     * Retorna datas (YYYY-MM-DD) nos prÃƒÂ³ximos N dias onde a estação estÃƒÂ¡ fechada OU sem veículos.
     * LÃƒÂ³gica:
     *  1. Carregar tipos de dia (Dias da Semana, SÃƒÂ¡bado, Domingo, Feriados) da empresa via horarios_diarios.
     *  2. Verificar quais tipos o horario da estação tem funciona=S.
     *  3. Se a estação nÃƒÂ£o trabalha em feriados, prÃƒÂ©-carregar feriados do período e bloqueÃƒÂ¡-los.
     *  4. Para dias em que a estação opera, bloquear os que tiverem 0 veículos disponíveis.
     */
    public function pesquisarGruposVeiculos(Request $request)
    {
        $request->validate([
            'grupo' => 'nullable|string|max:100',
            'modelo' => 'nullable|string|max:100',
            'marca' => 'nullable|string|max:100',
            'segmento' => 'nullable|string|max:100',
            'valor_min' => 'nullable|numeric|min:0',
            'valor_max' => 'nullable|numeric|min:0',
            'por_pagina' => 'nullable|integer|min:1|max:50',
            'estacao_retirada_id' => 'nullable|integer|exists:estacoes,id',
            'data_inicio' => 'nullable|string',
            'data_fim' => 'nullable|string',
        ]);

        $filtros = $request->only(['grupo', 'modelo', 'marca', 'segmento', 'valor_min', 'valor_max']);
        $porPagina = (int) ($request->input('por_pagina') ?: 12);

        $estacaoId = $request->input('estacao_retirada_id');
        $dataInicio = $request->input('data_inicio');
        $dataFim = $request->input('data_fim');
        $verificarDisponibilidade = $estacaoId && $dataInicio && $dataFim;

        $paginator = $this->grupoFrotaService->pesquisarComFiltros($filtros, $porPagina);

        $items = $paginator->getCollection()->map(function ($grupo) use ($verificarDisponibilidade, $estacaoId, $dataInicio, $dataFim) {
            $modelos = $grupo->modelos->map(fn ($m) => [
                'id' => $m->id,
                'nome' => $m->nome,
                'marca' => $m->marcas?->nome,
                'segmento' => $m->segmentos?->nome,
                'imagem' => $m->imagem_url,
            ]);

            $disponivel = null;
            $qtdVeiculosDisponiveis = null;
            $qtdModelosDisponiveis = null;

            if ($verificarDisponibilidade) {
                $qtdVeiculosDisponiveis = $this->veiculoService->getQuantidadeVeiculosDisponiveisPorGrupoEEstacaoEPeriodo(
                    (int) $grupo->id,
                    (int) $estacaoId,
                    (string) $dataInicio,
                    (string) $dataFim
                );
                $modelosDisponiveis = $this->veiculoService->getModelosComVeiculosDisponiveis(
                    (int) $grupo->id,
                    (int) $estacaoId,
                    (string) $dataInicio,
                    (string) $dataFim
                );
                $qtdModelosDisponiveis = $modelosDisponiveis->count();
                $disponivel = $qtdVeiculosDisponiveis > 0;
            }

            return [
                'id' => $grupo->id,
                'nome' => $grupo->nome,
                'valor_diario' => (float) $grupo->valor_diario,
                'imagem_url' => $grupo->imagem_url,
                'modelos' => $modelos,
                'marcas' => $modelos->pluck('marca')->filter()->unique()->values(),
                'segmentos' => $modelos->pluck('segmento')->filter()->unique()->values(),
                'disponivel' => $disponivel,
                'qtd_veiculos_disponiveis' => $qtdVeiculosDisponiveis,
                'qtd_modelos_disponiveis' => $qtdModelosDisponiveis,
            ];
        });

        return response()->json([
            'data' => $items,
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
        ]);
    }

    public function diasSemDisponibilidade(Request $request)
    {
        $request->validate([
            'estacao_id' => 'required|integer|exists:estacoes,id',
            'dias' => 'nullable|integer|min:1|max:180',
            'tipo' => 'nullable|string|max:20',
        ]);

        $estacaoId = (int) $request->input('estacao_id');
        $dias = (int) ($request->input('dias') ?: 90);
        $tipo = strtolower(trim((string) ($request->input('tipo') ?: 'retirada')));
        $isDevolucao = $tipo === 'devolucao';
        $hoje = Carbon::today();
        $mensagem = null;

        $estacao = Estacao::with(['diasUteis'])->findOrFail($estacaoId);
        $quantidadeVeiculosAtivos = $this->veiculoService->getQuantidadeVeiculosAtivosPorEstacao([$estacaoId]);

        // Para retirada: sÃƒÂ³ mostra dias se hÃƒÂ¡ veículos ativos na estação
        if (! $isDevolucao) {
            if ((int) ($quantidadeVeiculosAtivos[$estacaoId] ?? 0) <= 0) {
                return response()->json([
                    'estacao_id' => $estacao->id,
                    'dias_disponiveis' => [],
                    'mensagem' => 'A estação selecionada nÃƒÂ£o possui veículos ativos para retirada.',
                ]);
            }
        }

        // 1. dias em que a estação funciona (baseado no horÃƒÂ¡rio/escala)
        $proximosDias = $estacao->proximosDiasFuncionamento($dias, $hoje);

        // 2. remover feriados, caso nÃƒÂ£o opere em feriado
        $proximosDias = $this->removerFeriados(
            $proximosDias,
            $estacao->id,
            $estacao->opera_feriado
        );

        if (empty($proximosDias)) {
            $mensagem = $isDevolucao
                ? 'A estação selecionada nÃƒÂ£o possui agenda de devolução configurada para o período.'
                : 'A estação selecionada nÃƒÂ£o possui agenda de retirada configurada para o período.';
        }

        // 3. para retirada: filtrar apenas dias com veículo disponível
        //    para devolução: todos os dias de funcionamento sÃƒÂ£o vÃƒÂ¡lidos
        $diasDisponiveis = $isDevolucao
            ? $proximosDias
            : $estacao->filtrarDiasComVeiculoDisponivel($proximosDias);

        if (! $isDevolucao && empty($diasDisponiveis) && (int) ($quantidadeVeiculosAtivos[$estacaoId] ?? 0) > 0) {
            $mensagem = 'A estação possui veículos, mas nenhum dia ficou disponível no período selecionado.';
        }

        if ($isDevolucao && empty($diasDisponiveis) && empty($mensagem)) {
            $mensagem = 'A estação selecionada nÃƒÂ£o possui dias de devolução disponíveis no período.';
        }

        return response()->json([
            'estacao_id' => $estacao->id,
            'dias_disponiveis' => $diasDisponiveis,
            'mensagem' => $mensagem,
        ]);
    }

    /**
     * Sinalize que precisa fazer!!!
     * Aqui seria o local para remover os feriados do array de proximos dias, caso a estação nÃƒÂ£o opere em feriados.
     *
     * @param  array  $dados  Array de dias com ['data' => 'YYYY-MM-DD', 'dia_semana' => 0..6]
     * @return array Mesmos dados, mas com os feriados removidos (caso a estação nÃƒÂ£o opere em feriados).
     */
    private function removerFeriados($dados, $estacao_id, $operaFeriado)
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
                $dataFinal,
            ])
            ->where('ativo', 'S')
            ->pluck('data')
            ->map(fn ($data) => Carbon::parse($data)->toDateString())
            ->flip()
            ->all();

        $dados = array_values(array_filter($dados, fn ($d) => ! isset($feriados[$d['data']])));

        return $dados;
    }

    private function montarDiasSemanaFlags($diasUteis): array
    {
        $diasPadrao = [
            '0' => false,
            '1' => false,
            '2' => false,
            '3' => false,
            '4' => false,
            '5' => false,
            '6' => false,
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
            'data_hora' => 'required|date',
            'tipo' => 'nullable|string|max:20',
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
        $reserva = Reserva::withoutGlobalScopes()
            ->with(['opcionais', 'cupomDesconto', 'estacaoRetirada', 'estacaoDevolucao', 'grupoFrota', 'modelos', 'transacoes'])
            ->find($id);

        if (! $reserva) {
            return redirect()->route('reserva.index')->with('error', 'Reserva nao encontrada.');
        }

        $data = $this->exibirFormularioPagamentoAction->handle($reserva);

        if ((int) $reserva->status_reserva_id !== StatusReservaEnum::Pendente->value && $data['saldoPendente'] <= 0.009) {
            return redirect()->route('reserva.show', $id)->with('warning', 'Esta reserva não possui saldo pendente para pagamento.');
        }

        if (! $data['gatewayPadrao']) {
            return redirect()->route('reserva.show', $id)->with('error', 'Empresa da reserva não encontrada para configuração de pagamento.');
        }

        $titulo = 'Efetuar Pagamento da Reserva';

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Reservas', route('reserva.index')),
            new BreadCrumbItem('Efetuar Pagamento da Reserva Nº'.$id),
        ]);

        return view('reserva.efetuar-pagamento', compact(
            'reserva', 'breadcrumb', 'titulo', 'data'
        ) + $data);
    }

    public function processarPagamento(Request $request, $id)
    {
        $reserva = Reserva::withoutGlobalScopes()
            ->with(['opcionais', 'cupomDesconto', 'estacaoRetirada', 'estacaoDevolucao', 'transacoes'])
            ->find($id);

        if (! $reserva) {
            return redirect()->route('reserva.index')->with('error', 'Reserva nao encontrada.');
        }

        try {
            $resultado = $this->realizarPagamentoAction->handle($reserva, $request);

            return redirect()
                ->route('reserva.show', $id)
                ->with('success', 'Pagamento efetuado com sucesso')
                ->with('warning', $resultado['warning'] ?? null);
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return redirect()->route('reserva.show', $id)->with('error', $e->getMessage());
        }
    }

    private function contextoCheckinPorCodigo(string|int $codigo): ?array
    {
        $codigoReserva = $this->dadosCodigoReservaPorIdentificador($codigo);

        if ($codigoReserva && $codigoReserva->tipo_origem === 'motorista_app') {
            $registro = ReservaMotoristaApp::withoutGlobalScopes()
                ->with(['codigoReserva', 'user', 'empresa', 'estacaoRetirada', 'planoMotoristaApp'])
                ->find((int) ($codigoReserva->reserva_motorista_app_id ?? 0));

            if (! $registro) {
                return null;
            }

            $documentosMotoristaApp = is_array($registro->documentos_json ?? null) ? $registro->documentos_json : [];
            $reservaOperacionalId = (int) data_get($documentosMotoristaApp, 'checkin.reserva_operacional_id', 0);

            if ($reservaOperacionalId <= 0) {
                $reservaOperacionalId = (int) (DB::table('reservas')
                    ->join('reserva_codigos', 'reserva_codigos.reserva_id', '=', 'reservas.id')
                    ->leftJoin('contrato_checkin', 'contrato_checkin.reserva_id', '=', 'reservas.id')
                    ->where('reservas.user_id', (int) $registro->user_id)
                    ->where('reservas.empresa_id', (int) $registro->empresa_id)
                    ->whereNull('contrato_checkin.id')
                    ->whereRaw('LOWER(COALESCE(reserva_codigos.status_label, "")) = ?', ['pendente'])
                    ->orderByDesc('reservas.id')
                    ->value('reservas.id') ?? 0);
            }

            $modeloIds = $this->modeloIdsDisponiveisMotoristaApp($registro);
            $modeloPrincipal = ! empty($modeloIds)
                ? $this->modeloService->getAtivosPorIds($modeloIds)->first()
                : null;

            $reserva = new Reserva();
            $reserva->id = $reservaOperacionalId;
            $reserva->exists = false;
            $reserva->empresa_id = $registro->empresa_id;
            $reserva->user_id = $registro->user_id;
            $reserva->estacao_retirada_id = $registro->estacao_retirada_id;
            $reserva->estacao_devolucao_id = $registro->estacao_retirada_id;
            $reserva->data_inicio = $registro->data_inicio;
            $reserva->data_fim = $registro->data_fim;
            $reserva->quantidade_dias = $registro->quantidade_dias;
            $statusMotoristaApp = strtolower((string) ($registro->status ?? ''));
            $reserva->pagamento_efetuado = ($registro->pagamento_efetuado || in_array($statusMotoristaApp, ['confirmada', 'ativa'], true)) ? 'S' : 'N';
            $reserva->valor_total_final = $registro->valor_total_final;
            $reserva->grupo_frota_id = null;
            $reserva->modelo_id = $modeloPrincipal?->id;
            $reserva->setRelation('usuario', $registro->user);
            $reserva->setRelation('empresa', $registro->empresa);
            $reserva->setRelation('estacaoRetirada', $registro->estacaoRetirada);
            $reserva->setRelation('modelos', $modeloPrincipal);
            $reserva->setRelation('codigoReserva', new ReservaCodigo([
                'codigo' => (string) $codigoReserva->codigo,
                'reserva_motorista_app_id' => (int) $registro->id,
            ]));

            return [
                'reserva' => $reserva,
                'codigo_reserva' => (string) $codigoReserva->codigo,
                'reserva_id' => $reservaOperacionalId,
                'motorista_app' => $registro,
                'is_motorista_app' => true,
                'modelo_ids' => $modeloIds,
                'grupo_label' => 'Motorista APP - '.($registro->planoMotoristaApp?->nome ?? 'Plano'),
            ];
        }

        $reservaId = $this->idReservaPorIdentificador($codigo, 'RES', $codigoReserva, 'reserva_id');
        $reserva = Reserva::withoutGlobalScopes()
            ->with(['modelos', 'codigoReserva', 'usuario', 'estacaoRetirada', 'grupoFrota'])
            ->find($reservaId);

        if (! $reserva) {
            return null;
        }

        return [
            'reserva' => $reserva,
            'codigo_reserva' => $codigoReserva?->codigo ?? $reserva->codigoReserva?->codigo ?? (string) $codigo,
            'reserva_id' => (int) $reserva->id,
            'motorista_app' => null,
            'is_motorista_app' => false,
            'modelo_ids' => $this->veiculoService->getModeloIdsDeVeiculosNoGrupo((int) $reserva->grupo_frota_id),
            'grupo_label' => $reserva->grupoFrota?->nome ?? ($reserva->grupo_frota_id ? 'Grupo #'.$reserva->grupo_frota_id : '---'),
        ];
    }

    private function modeloIdsDisponiveisMotoristaApp(ReservaMotoristaApp $registro): array
    {
        if (empty($registro->estacao_retirada_id) || empty($registro->data_inicio) || empty($registro->data_fim)) {
            return [];
        }

        return $this->veiculoService
            ->buscarDisponiveisCheckin(
                (int) $registro->estacao_retirada_id,
                Carbon::parse($registro->data_inicio)->format('Y-m-d H:i:s'),
                Carbon::parse($registro->data_fim)->format('Y-m-d H:i:s'),
                0,
                []
            )
            ->pluck('modelo_id')
            ->map(static fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function sincronizarCheckinMotoristaApp(array $contextoCheckin, ?ContratoCheckin $contrato, array $dados): void
    {
        if (empty($contextoCheckin['is_motorista_app']) || ! $contrato) {
            return;
        }

        /** @var ReservaMotoristaApp|null $registro */
        $registro = $contextoCheckin['motorista_app'] ?? null;
        if (! $registro) {
            return;
        }

        $documentos = is_array($registro->documentos_json ?? null) ? $registro->documentos_json : [];
        data_set($documentos, 'checkin.reserva_operacional_id', (int) $contextoCheckin['reserva_id']);
        data_set($documentos, 'checkin.reserva_operacional_codigo', $this->codigoReservaOperacionalPorId((int) $contextoCheckin['reserva_id']));
        data_set($documentos, 'checkin.contrato_checkin_id', (int) $contrato->id);
        data_set($documentos, 'checkin.veiculo_id', (int) ($dados['veiculo_id'] ?? $contrato->veiculo_id));
        data_set($documentos, 'checkin.quilometragem_inicial', $dados['quilometragem_atual'] ?? $contrato->quilometragem_atual);
        data_set($documentos, 'checkin.nivel_combustivel_inicial', $dados['nivel_combustivel'] ?? $contrato->nivel_combustivel);
        data_set($documentos, 'checkin.registrado_em', optional($contrato->created_at ?: now())->toDateTimeString());

        $checkinEm = $registro->checkin_realizado_em ?: now();
        $statusAtual = strtolower((string) ($registro->status ?? ''));

        $registro->forceFill([
            'veiculo_id' => (int) ($dados['veiculo_id'] ?? $contrato->veiculo_id),
            'checkin_realizado_em' => $checkinEm,
            'data_retirada_real' => $registro->data_retirada_real ?: $checkinEm,
            'quilometragem_inicial' => $dados['quilometragem_atual'] ?? $contrato->quilometragem_atual,
            'nivel_combustivel_inicial' => $dados['nivel_combustivel'] ?? $contrato->nivel_combustivel,
            'documentos_json' => $documentos,
            'status' => $statusAtual === 'cancelada' ? $registro->status : 'ativa',
        ])->save();
    }

    private function motoristaAppPorReservaOperacionalId(int $reservaId): ?ReservaMotoristaApp
    {
        if ($reservaId <= 0) {
            return null;
        }

        return ReservaMotoristaApp::withoutGlobalScopes()
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(documentos_json, '$.checkin.reserva_operacional_id')) = ?", [(string) $reservaId])
            ->first();
    }

    private function codigoReservaOperacionalPorId(int $reservaId): ?string
    {
        if ($reservaId <= 0) {
            return null;
        }

        return DB::table('reserva_codigos')
            ->where('reserva_id', $reservaId)
            ->value('codigo');
    }

    private function resolverReservaOperacionalPorCodigo(string|int $codigo): ?Reserva
    {
        $codigoReserva = $this->dadosCodigoReservaPorIdentificador($codigo);
        $reservaId = $this->idReservaPorIdentificador($codigo, 'RES', $codigoReserva, 'reserva_id');

        if (! is_numeric($reservaId)) {
            $reservaId = $this->idReservaNumericoDoCodigoLegado($codigo, 'MAP');
        }

        if (! is_numeric($reservaId)) {
            return null;
        }

        return Reserva::withoutGlobalScopes()
            ->with(['codigoReserva'])
            ->find((int) $reservaId);
    }

    private function checkinMotoristaAppAssinado(ReservaMotoristaApp $registro): bool
    {
        $documentos = is_array($registro->documentos_json ?? null) ? $registro->documentos_json : [];
        $contratoId = (int) data_get($documentos, 'checkin.contrato_checkin_id', 0);

        if ($contratoId <= 0) {
            return false;
        }

        return ContratoCheckin::where('id', $contratoId)
            ->where('assinado', 'S')
            ->exists();
    }

    public function checkin(Request $request, $codigo)
    {
        $contextoCheckin = $this->contextoCheckinPorCodigo($codigo);

        if (! $contextoCheckin) {
            return redirect()
                ->route('reserva.index')
                ->with('error', 'Reserva nao encontrada.');
        }

        $reserva = $contextoCheckin['reserva'];
        $reserva_id = (int) $contextoCheckin['reserva_id'];
        $codigoBreadcrumb = (string) $contextoCheckin['codigo_reserva'];
        $isMotoristaAppCheckin = (bool) $contextoCheckin['is_motorista_app'];
        $grupoCheckinLabel = (string) $contextoCheckin['grupo_label'];

        if (! $reserva->isPagamentoEfetuado()) {
            return redirect()
                ->route('reserva.show', $codigoBreadcrumb)
                ->with('warning', 'O pagamento deve ser efetuado antes de realizar o check-in.');
        }

        $categoriaDanos = $this->categoriaDanoService->getAllForSelect();

        $modeloIdsNoGrupo = $contextoCheckin['modelo_ids'];
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

        // Contrato existente (edição) Ã¢â‚¬â€ prÃƒÂ©-preenche os campos
        $contratoExistente = $this->contratoCheckinService->getModel()->newQuery()
            ->with('veiculo')
            ->where('reserva_id', $reserva_id)
            ->first();

        // Contrato jÃƒÂ¡ assinado nÃƒÂ£o pode ser editado Ã¢â‚¬â€ redireciona para a reserva
        if ($contratoExistente && $contratoExistente->assinado === 'S') {
            return redirect()->route('reserva.show', $codigoBreadcrumb)
                ->with('warning', 'O contrato de check-in jÃƒÂ¡ foi assinado e nÃƒÂ£o pode ser editado. Para criar um novo, exclua o contrato atual.');
        }

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Reservas', route('reserva.index')),
            new BreadCrumbItem('Detalhes da Reserva Nº'.$codigoBreadcrumb, route('reserva.show', $codigoBreadcrumb)),
            new BreadCrumbItem($contratoExistente ? 'Editar Check-in' : 'Check-in'),
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
                'contratoExistente',
                'codigoBreadcrumb',
                'isMotoristaAppCheckin',
                'grupoCheckinLabel'
            )
        )
            ->with('titulo', ($contratoExistente ? 'Editar Check-in' : 'Check-in').' da reserva Nº '.$codigoBreadcrumb);
    }

    /**
     * Veículos disponíveis na estação de retirada, no período da reserva, para marca + modelo escolhidos.
     */
    public function veiculosDisponiveisCheckin(Request $request, $codigo)
    {
        $request->validate([
            'modelo_id' => 'required|integer|exists:modelos,id',
            'marca_id' => 'required|integer|exists:marcas,id',
        ]);

        $contextoCheckin = $this->contextoCheckinPorCodigo($codigo);

        if (! $contextoCheckin) {
            return response()->json(['message' => 'Reserva nao encontrada.'], 404);
        }

        $reserva = $contextoCheckin['reserva'];
        $reserva_id = (int) $contextoCheckin['reserva_id'];
        $permitidos = $contextoCheckin['modelo_ids'];
        $modeloId = (int) $request->modelo_id;
        if (! in_array($modeloId, $permitidos, true)) {
            return response()->json(['message' => 'Este modelo nÃƒÂ£o possui veículos cadastrados neste grupo de frota.'], 422);
        }

        $modelo = $this->modeloService->getById($modeloId);
        if (! $modelo || (int) $modelo->marca_id !== (int) $request->marca_id) {
            return response()->json(['message' => 'Marca e modelo nÃƒÂ£o conferem.'], 422);
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
     *  2. Se nÃƒÂ£o houver, retorna todos os disponíveis no período/estação.
     *
     * Com filtros aplicados pelo usuÃƒÂ¡rio: usa apenas os filtros informados.
     */
    public function veiculosModalCheckin(Request $request, $codigo)
    {
        $contextoCheckin = $this->contextoCheckinPorCodigo($codigo);

        if (! $contextoCheckin) {
            return response()->json(['message' => 'Reserva nao encontrada.'], 404);
        }

        $reserva = $contextoCheckin['reserva'];
        $reserva_id = (int) $contextoCheckin['reserva_id'];
        $dataInicio = Carbon::parse($reserva->data_inicio)->format('Y-m-d H:i:s');
        $dataFim = Carbon::parse($reserva->data_fim)->format('Y-m-d H:i:s');
        $estacaoId = (int) $reserva->estacao_retirada_id;

        $filtros = $request->only(['placa', 'nome', 'chassi', 'cor', 'modelo', 'marca_id', 'grupo_frota_id', 'tipo_frota_id', 'ativo', 'ano_de', 'ano_ate', 'kms_ate']);
        if ($contextoCheckin['is_motorista_app']) {
            $filtros['modelo_ids'] = $contextoCheckin['modelo_ids'];
            unset($filtros['grupo_frota_id']);
        }
        $filtrosVazios = collect($filtros)->filter(fn ($v) => $v !== null && $v !== '')->isEmpty();

        if ($filtrosVazios) {
            $filtrosGrupo = [];
            if (! empty($reserva->grupo_frota_id)) {
                $filtrosGrupo['grupo_frota_id'] = $reserva->grupo_frota_id;
            }

            $veiculos = $this->veiculoService->buscarDisponiveisCheckin(
                $estacaoId,
                $dataInicio,
                $dataFim,
                (int) $reserva_id,
                $filtrosGrupo
            );

            if ($veiculos->isEmpty()) {
                $veiculos = $this->veiculoService->buscarDisponiveisCheckin(
                    $estacaoId,
                    $dataInicio,
                    $dataFim,
                    (int) $reserva_id,
                    []
                );
            }
        } else {
            $veiculos = $this->veiculoService->buscarDisponiveisCheckin(
                $estacaoId,
                $dataInicio,
                $dataFim,
                (int) $reserva_id,
                $filtros
            );
        }

        $toArray = static fn ($v) => [
            'id' => $v->id,
            'nome' => $v->nome ?? '',
            'placa' => $v->placa_licenca ?? '',
            'marca' => $v->marca?->nome ?? '',
            'modelo' => $v->modelo?->nome ?? '',
            'grupo' => $v->grupoFrota?->nome ?? '',
            'tipo' => $v->tipoFrota?->nome ?? '',
            'status' => $v->statusVeiculo?->nome ?? '',
            'ano_fab' => $v->ano_fabricacao ?? '',
            'ano_mod' => $v->modelo?->ano_modelo ?? '',
            'cor' => $v->cor ?? '',
            'kms' => $v->kms ?? '',
            'ativo' => $v->ativo === 'S' ? 'Sim' : 'NÃƒÂ£o',
            'estacao' => '',
            'nivel_combustivel' => '',
        ];

        return response()->json(['veiculos' => $veiculos->map($toArray)->values()]);
    }

    public function diagnosticoDisponibilidadeCheckin(Request $request, $codigo)
    {
        $contextoCheckin = $this->contextoCheckinPorCodigo($codigo);

        if (! $contextoCheckin) {
            return response()->json(['message' => 'Reserva nao encontrada.'], 404);
        }
        $reserva = $contextoCheckin['reserva'];
        $reserva_id = (int) $contextoCheckin['reserva_id'];
        $modeloIdsPermitidos = $contextoCheckin['modelo_ids'];
        $dataInicio = Carbon::parse($reserva->data_inicio)->format('Y-m-d H:i:s');
        $dataFim = Carbon::parse($reserva->data_fim)->format('Y-m-d H:i:s');
        $estacaoId = (int) $reserva->estacao_retirada_id;
        $agora = now()->format('Y-m-d H:i:s');
        $limite = $dataInicio > $agora ? $dataInicio : $agora;

        $veiculos = \App\Models\Veiculo::with([
            'marca',
            'modelo',
            'grupoFrota',
            'tipoFrota',
            'statusVeiculo',
            'localDisponibilidade',
            'movimentacoes' => fn ($q) => $q->with(['reserva', 'statusMovimentacao', 'localOrigem', 'localDestino']),
        ])
            ->where(function ($q) use ($estacaoId) {
                $q->where('local_disponibilidade', $estacaoId)->orWhereNull('local_disponibilidade');
            })
            ->when(! empty($modeloIdsPermitidos), fn ($q) => $q->whereIn('modelo_id', $modeloIdsPermitidos))
            ->get()
            ->map(function ($v) use ($reserva, $dataInicio, $dataFim, $estacaoId, $reserva_id, $limite) {
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
                        ? 'Sem restrição Ã¢â‚¬â€ veículo disponível agora'
                        : 'Disponível a partir de '.$dispDt->format('d/m/Y H:i').'. Limite atual: '.Carbon::parse($limite)->format('d/m/Y H:i'),
                    'solucao' => $dispDt && $dispDt->gt(Carbon::parse($limite))
                        ? 'Aguarde atÃƒÂ© '.$dispDt->format('d/m/Y H:i').' ou antecipe a data de disponibilidade no cadastro do veículo.'
                        : null,
                ];

                // 3. Status do veículo
                $statusDisponiveis = $this->veiculoService->getStatusVeiculoDisponivelIds();
                $statusOk = in_array((int) $v->status_veiculo_id, $statusDisponiveis);
                $regras[] = [
                    'nome' => 'Status',
                    'ok' => $statusOk,
                    'info' => "Status atual: <strong>{$v->statusVeiculo?->nome}</strong>",
                    'solucao' => ! $statusOk
                        ? 'Altere o status do veículo para um dos disponíveis.'
                        : null,
                ];

                // 4. Reservas conflitantes
                $movConflitantes = $v->movimentacoes->filter(function ($m) use ($dataInicio, $dataFim, $reserva_id) {
                    if ((int) $m->reserva_id === (int) $reserva_id) {
                        return false;
                    }
                    $r = $m->reserva;
                    if (! $r) {
                        return false;
                    }
                    $statusPendentes = [
                        StatusReservaEnum::Pendente->value,
                        StatusReservaEnum::Confirmada->value,
                        StatusReservaEnum::NoShow->value,
                    ];
                    if (! in_array((int) $r->status_reserva_id, $statusPendentes)) {
                        return false;
                    }

                    return $r->data_inicio < $dataFim && $r->data_fim > $dataInicio;
                })->values();
                $regras[] = [
                    'nome' => 'Reservas',
                    'ok' => $movConflitantes->isEmpty(),
                    'info' => $movConflitantes->isEmpty() ? 'Nenhuma reserva conflitante' : $movConflitantes->count().' reserva(s) conflitante(s)',
                    'solucao' => ! $movConflitantes->isEmpty()
                        ? 'Finalize ou cancele as reservas conflitantes antes de prosseguir.'
                        : null,
                    'itens' => $movConflitantes->map(fn ($m) => [
                        'tipo' => 'reserva',
                        'id' => $m->reserva_id,
                        'label' => 'Reserva #'.$m->reserva_id.' Ã¢â‚¬â€ '.($m->reserva?->statusReserva?->nome ?? ''),
                        'url' => route('reserva.show', $m->reserva_id),
                    ]),
                ];

                // 5. MovimentaçÃƒÂµes em aberto
                $movAbertas = $v->movimentacoes->filter(function ($m) use ($dataFim) {
                    if ($m->data_chegada_real) {
                        return false;
                    }

                    return is_null($m->data_saida) || $m->data_saida < $dataFim;
                })->values();
                $regras[] = [
                    'nome' => 'MovimentaçÃƒÂµes',
                    'ok' => $movAbertas->isEmpty(),
                    'info' => $movAbertas->isEmpty() ? 'Sem movimentaçÃƒÂµes em aberto' : $movAbertas->count().' movimentação(ÃƒÂµes) em aberto',
                    'solucao' => ! $movAbertas->isEmpty()
                        ? 'Finalize as movimentaçÃƒÂµes pendentes antes de prosseguir.'
                        : null,
                    'itens' => $movAbertas->map(fn ($m) => [
                        'tipo' => 'movimentacao',
                        'id' => $m->id,
                        'label' => 'Mov. #'.$m->id.' Ã¢â‚¬â€ '.($m->localOrigem?->nome ?? '?').' Ã¢â€ â€™ '.($m->localDestino?->nome ?? '?'),
                        'url' => url('/admin/movimentacao/'.$m->id),
                    ]),
                ];

                return [
                    'id' => $v->id,
                    'nome' => $v->nome ?: '#'.$v->id,
                    'placa' => $v->placa_licenca ?: 'Ã¢â‚¬â€',
                    'cor' => $v->cor ?: 'Ã¢â‚¬â€',
                    'modelo' => $v->modelo?->nome ?: 'Ã¢â‚¬â€',
                    'status_nome' => $v->statusVeiculo?->nome ?: 'Ã¢â‚¬â€',
                    'disponivel' => collect($regras)->every(fn ($r) => $r['ok']),
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
            ],
        ])->render();

        return response()->json(['html' => $html]);
    }

    public function verContratoCheckin(
        string $codigo,
        ReservaResolver $resolver,
        ExibirContratoCheckinAction $action,
    ): Response|RedirectResponse {
        try {
            $reservaResolvida = $resolver->resolver($codigo);
        } catch (ReservaNaoEncontradaException $exception) {
            return redirect()
                ->route('reserva.index')
                ->with('error', $exception->getMessage());
        }

        $usuario = Auth::user();

        if (! $usuario) {
            return redirect()->route('login');
        }

        $registro = $reservaResolvida->registro;

        $podeVisualizar =
            $usuario->isAdministrador()
            || $usuario->hasPermissao('reserva.show')
            || (int) $registro->user_id === (int) $usuario->id;

        if (! $podeVisualizar) {
            abort(403, 'Você não tem permissão para visualizar este contrato.');
        }

        try {
            $pdf = $action->handle($codigo);
        } catch (\RuntimeException $exception) {
            return redirect()
                ->route('reserva.show', ['reserva' => $codigo])
                ->with('error', $exception->getMessage());
        }

        $nomeArquivo = sprintf(
            'contrato-checkin-%s.pdf',
            preg_replace('/[^A-Za-z0-9\-]/', '', strtoupper($codigo))
        );

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$nomeArquivo.'"',
            'Content-Length' => strlen($pdf),
            'Cache-Control' => 'private, no-store, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }

    public function assinarContratoCheckin(Request $request, $codigo)
    {
        // Se for codigo MAP, abre o checkin do motorista app (GET)
        if ($this->identificadorComecaComPrefixo($codigo, 'MAP')) {
            return redirect()->route('reserva.minhas.motorista-app.show', ['id' => $codigo]);
        }

        if (ctype_digit((string) $codigo)) {
            $codigoPublico = $this->codigoReservaOperacionalPorId((int) $codigo);
            if ($codigoPublico) {
                return redirect()->route('reserva.assinarContratoCheckin', array_merge(
                    ['codigo' => $codigoPublico],
                    $request->query()
                ));
            }
        }

        // Se for codigo MAP, redireciona para o fluxo do motorista app
        if ($this->identificadorComecaComPrefixo($codigo, 'MAP')) {
            $checkinUrl = route('reserva.minhas.motorista-app.show', ['id' => $codigo]);

            return redirect()->away($checkinUrl);
        }

        $reservaBase = $this->resolverReservaOperacionalPorCodigo($codigo);
        $reserva_id = (int) ($reservaBase?->id ?? 0);
        $codigoBreadcrumb = $reservaBase?->codigoReserva?->codigo ?? (string) $codigo;
        $retorno = trim((string) $request->query('retorno', ''));
        $retornoMotoristaApp = null;
        if ($retorno !== '') {
            $codigoRetorno = $this->dadosCodigoReservaPorIdentificador($retorno);
            if ($codigoRetorno && $codigoRetorno->tipo_origem === 'motorista_app') {
                $retornoMotoristaApp = ReservaMotoristaApp::withoutGlobalScopes()
                    ->find((int) ($codigoRetorno->reserva_motorista_app_id ?? 0));
            }
        }

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Reservas', route('reserva.index')),
            new BreadCrumbItem('Detalhes da Reserva Nº'.$codigoBreadcrumb, route('reserva.show', $reserva_id)),
            new BreadCrumbItem('Assinar Contrato de Check-in'),
        ]);

        $reserva = Reserva::withoutGlobalScopes()
            ->with(['contratoCheckin', 'codigoReserva', 'empresa', 'usuario', 'opcionais', 'cupomDesconto', 'estacaoRetirada', 'estacaoDevolucao', 'transacoes'])
            ->find($reserva_id);

        if (! $reserva) {
            return redirect()
                ->route('reserva.index')
                ->with('error', 'Reserva nao encontrada.');
        }

        $contextoAssinatura = [
            'codigo' => $reserva->codigoReserva?->codigo ?? (string) $codigo,
            'tipo' => 'RES',
            'empresa' => $reserva->empresa,
            'usuario' => $reserva->usuario,
            'data_inicio' => $reserva->data_inicio,
            'data_fim' => $reserva->data_fim,
            'estacao_retirada' => $reserva->estacaoRetirada?->nome,
            'estacao_devolucao' => $reserva->estacaoDevolucao?->nome,
            'quantidade_dias' => $reserva->quantidade_dias,
            'tarifa_label' => 'Tarifa diaria',
            'tarifa_valor' => (float) ($reserva->tarifa_base ?? 0),
            'valor_total' => null,
            'taxa_entrega' => (float) ($reserva->taxa_entrega ?? 0),
            'taxa_coleta' => (float) ($reserva->taxa_coleta ?? 0),
            'plano_nome' => null,
        ];

        if ($retornoMotoristaApp) {
            $contextoAssinatura = [
                'codigo' => $retornoMotoristaApp->codigoReserva?->codigo ?? $retorno,
                'tipo' => 'MAP',
                'empresa' => $retornoMotoristaApp->empresa ?? $reserva->empresa,
                'usuario' => $retornoMotoristaApp->user ?? $reserva->usuario,
                'data_inicio' => $retornoMotoristaApp->data_inicio,
                'data_fim' => $retornoMotoristaApp->data_fim,
                'estacao_retirada' => $retornoMotoristaApp->estacaoRetirada?->nome,
                'estacao_devolucao' => $retornoMotoristaApp->estacaoRetirada?->nome,
                'quantidade_dias' => $retornoMotoristaApp->quantidade_dias,
                'tarifa_label' => 'Valor do plano',
                'tarifa_valor' => (float) ($retornoMotoristaApp->valor_periodo_contrato ?: $retornoMotoristaApp->valor_plano ?: $retornoMotoristaApp->valor_total_final),
                'valor_total' => (float) ($retornoMotoristaApp->valor_total_final ?? 0),
                'taxa_entrega' => 0,
                'taxa_coleta' => 0,
                'plano_nome' => $retornoMotoristaApp->planoMotoristaApp?->nome,
            ];
        }
        $taxaColeta = (float) ($reserva->taxa_coleta ?? 0);
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
        $percentualDesconto = (float) ($reserva->percentual_desconto ?? 0);
        if ($percentualDesconto <= 0 && $reserva->cupomDesconto) {
            $percentualDesconto = (float) ($reserva->cupomDesconto->valor ?? 0);
        }
        $diasReserva = (int) ($reserva->quantidade_dias ?? 0);
        $valorTarifaBase = (float) ($reserva->tarifa_base ?? 0) * $diasReserva;
        $valorOpcionais = (float) ($reserva->valor_opcionais ?? 0);
        if ($valorOpcionais <= 0 && $reserva->opcionais?->isNotEmpty()) {
            $valorOpcionais = (float) $reserva->opcionais->sum(fn ($opcional) => (float) ($opcional->pivot->preco_total ?? 0));
        }
        $valorTotalAtual = max(0, ($valorTarifaBase + $valorOpcionais + $taxaColeta + $taxaEntrega) * (1 - ($percentualDesconto / 100)));
        $statusPagamentoAprovado = ['succeeded', 'approved', 'paid', 'completed', 'complete', 'confirmed', 'concluido'];
        $totalPagoAprovado = (float) ($reserva->transacoes ?? collect())
            ->filter(fn ($item) => (float) ($item->valor ?? 0) > 0
                && in_array(strtolower((string) ($item->status ?? '')), $statusPagamentoAprovado, true))
            ->sum('valor');
        $saldoPendente = max(0, $valorTotalAtual - $totalPagoAprovado);
        if ($retornoMotoristaApp && (bool) $retornoMotoristaApp->pagamento_efetuado) {
            $saldoPendente = 0;
        }
        if ($saldoPendente > 0.009) {
            return redirect()
                ->route('reserva.pagamento.efetuar', ['id' => $reserva->codigoReserva?->codigo ?? $reserva_id])
                ->with('warning', 'Restam R$ '.number_format($saldoPendente, 2, ',', '.').' a pagar para liberar a assinatura do contrato.');
        }
        $redirectAfterSignUrl = $retorno !== ''
            ? route('reserva.show', $retorno)
            : route('reserva.show', $reserva->codigoReserva?->codigo ?? $reserva->id);

        return view('reserva.assinar-contrato-checkin', compact('reserva', 'breadcrumb', 'redirectAfterSignUrl', 'contextoAssinatura'))->with('titulo', 'Assinar contrato de check-in da reserva Nº '.$reserva->id);
    }

    public function salvarAssinarContratoCheckIn(
        AssinarContratoRequest $request,
        $codigo,
        FinalizarCheckinAction $finalizarCheckinAction,
    ) {
        try {
            return $finalizarCheckinAction->handle($codigo, $request);
        } catch (ReservaNaoEncontradaException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    public function criarContratoCheckIn(CriarContratoCheckInRequest $request, $codigo)
    {
        $contextoCheckin = $this->contextoCheckinPorCodigo($codigo);

        if (! $contextoCheckin) {
            return response()->json(['success' => false, 'message' => 'Reserva nao encontrada.'], 404);
        }

        $reservaContexto = $contextoCheckin['reserva'];
        $reserva_id = (int) $contextoCheckin['reserva_id'];

        if ($reserva_id <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Esta reserva motorista app ainda nao possui uma reserva operacional vinculada para fazer check-in.',
            ], 422);
        }

        $dados = $request->validated();
        $dados['reserva_id'] = $reserva_id;
        $dados['danos'] = $request->input('danos');

        // Deriva marca_id e modelo_id do veículo selecionado quando nÃƒÂ£o enviados pelo formulÃƒÂ¡rio
        if (empty($dados['marca_id']) || empty($dados['modelo_id'])) {
            $veiculo = $this->veiculoService->getById((int) $dados['veiculo_id']);
            $dados['marca_id'] = $dados['marca_id'] ?: (int) $veiculo->marca_id;
            $dados['modelo_id'] = $dados['modelo_id'] ?: (int) $veiculo->modelo_id;
        }

        // Verifica se jÃƒÂ¡ existe contrato para esta reserva (modo edição)
        $contratoExistente = $this->contratoCheckinService->getModel()->newQuery()
            ->where('reserva_id', $reserva_id)
            ->first();

        try {
            // Salva as fotos do check-in e registra em arquivos_rastreados.
            $dir = 'checkin/reserva_'.(int) $reserva_id;
            $map = [
                'foto_direita' => 'foto_direita_path',
                'foto_esquerda' => 'foto_esquerda_path',
                'foto_frente' => 'foto_frente_path',
                'foto_tras' => 'foto_tras_path',
                'foto_velocimetro' => 'foto_velocimetro_path',
                'foto_combustivel' => 'foto_combustivel_path',
            ];

            $arquivosSalvos = [];
            foreach ($map as $input => $col) {
                if ($request->hasFile($input) && $request->file($input)->isValid()) {
                    if ($contratoExistente && ! empty($contratoExistente->$col)) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($contratoExistente->$col);
                    }
                    $arquivo = \App\Support\ArquivoAnexoHelper::armazenarRastreado($request->file($input), $dir, [
                        'empresa_id' => $reservaContexto->empresa_id ?? null,
                        'user_id' => Auth::id(),
                        'modulo' => 'checkin',
                        'entidade' => 'contrato_checkin',
                        'entidade_id' => $contratoExistente?->id,
                        'campo' => $col,
                        'contexto' => 'checkin',
                    ], $contratoExistente?->$col);
                    if ($arquivo && ! empty($arquivo['path'])) {
                        $dados[$col] = $arquivo['path'];
                        $arquivosSalvos[] = $arquivo['path'];
                    }
                } elseif ($contratoExistente) {
                    $dados[$col] = $contratoExistente->$col;
                }
            }

            // Detecta e registra upgrade de categoria automaticamente
            $this->resolverDadosUpgrade($dados, (int) $reserva_id, $reservaContexto);

            if ($contratoExistente) {
                // Atualiza contrato existente
                $contratoExistente->update($dados);
                if (! empty($arquivosSalvos)) {
                    \App\Models\ArquivoRastreado::whereIn('path', $arquivosSalvos)
                        ->update(['entidade_id' => $contratoExistente->id]);
                }
                $this->sincronizarCheckinMotoristaApp($contextoCheckin, $contratoExistente->fresh(), $dados);
                $this->reservaService->adicionaLog($reserva_id, Auth::id(), [
                    'houve_upgrade' => $dados['houve_upgrade_categoria'] ?? false,
                    'motivo_upgrade' => $dados['motivo_upgrade'] ?? null,
                    'veiculo_id' => $dados['veiculo_id'] ?? null,
                ], 'Contrato de check-in editado', 'info');

                return response()->json($contratoExistente->fresh());
            } else {
                // Cria novo contrato
                $this->reservaService->adicionaLog($reserva_id, Auth::id(), [
                    'houve_upgrade' => $dados['houve_upgrade_categoria'] ?? false,
                    'motivo_upgrade' => $dados['motivo_upgrade'] ?? null,
                    'veiculo_id' => $dados['veiculo_id'] ?? null,
                ], 'Contrato de check-in criado', 'success');
                $contrato = $this->contratoCheckinService->create($dados);
                if (! empty($arquivosSalvos)) {
                    \App\Models\ArquivoRastreado::whereIn('path', $arquivosSalvos)
                        ->update(['entidade_id' => $contrato->id]);
                }
                $this->sincronizarCheckinMotoristaApp($contextoCheckin, $contrato, $dados);

                // Salva danos coletados (enviados como JSON no campo "danos")
                if (! empty($dados['danos'])) {
                    $danosData = is_string($dados['danos']) ? json_decode($dados['danos'], true) : $dados['danos'];
                    if (is_array($danosData)) {
                        $fotoIdx = 0;
                        foreach ($danosData as $item) {
                            $item['veiculo_id'] = $dados['veiculo_id'];
                            $item['reserva_id'] = $reserva_id;
                            $item['contrato_id'] = $contrato->id;
                            $dano = $this->veiculoDanoService->create($item);
                            // Salva foto se houver
                            $fotoInput = 'danos_fotos_'.$fotoIdx;
                            if ($dano && $request->hasFile($fotoInput)) {
                                $dir = 'danos/reserva_'.(int) $reserva_id.'/dano_'.(int) $dano->id;
                                $path = \App\Support\ArquivoAnexoHelper::armazenarRastreado($request->file($fotoInput), $dir, [
                                    'empresa_id' => $reservaContexto->empresa_id ?? null,
                                    'user_id' => Auth::id(),
                                    'modulo' => 'checkin',
                                    'entidade' => 'veiculo_dano',
                                    'entidade_id' => $dano->id,
                                    'campo' => 'imagem',
                                    'contexto' => 'checkin-dano',
                                ])['path'] ?? null;
                                if ($path) {
                                    $dano->imagem()->create([
                                        'caminho_imagem' => $path,
                                        'nome_imagem' => basename($path),
                                    ]);
                                }
                            }                            $fotoIdx++;
                        }
                    }
                }

                return response()->json($contrato);
            }
        } catch (\Exception $e) {
            \Log::error('criarContratoCheckIn falhou', [
                'reserva_id' => $reserva_id,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function adicionarDanoVeiculo(AdicionarDanoVeiculoRequest $request, $reserva_id)
    {
        $dados = $request->validated();
        $dados['reserva_id'] = $reserva_id;

        $fotos = $request->file('fotos', []);
        unset($dados['fotos']);

        try {
            DB::beginTransaction();

            $dano = $this->veiculoDanoService->create($dados);

            if ($dano && ! empty($fotos)) {
                $dir = 'danos/reserva_'.(int) $reserva_id.'/dano_'.(int) $dano->id;

                foreach ($fotos as $foto) {
                    $path = \App\Support\ArquivoAnexoHelper::armazenar($foto, $dir);
                    if (! $path) {
                        continue;
                    }

                    $dano->imagens()->create([
                        'caminho_imagem' => $path,
                        'nome_imagem' => basename($path),
                    ]);
                }
            }

            $this->reservaService->adicionaLog($reserva_id, Auth::id(), json_encode([
                'acao' => 'Dano de veiculo adicionado',
                'dano_id' => $dano?->id,
                'veiculo_id' => $dados['veiculo_id'] ?? null,
                'categoria_id' => $dados['categoria_id'] ?? null,
                'local_dano' => $dados['local_dano'] ?? null,
                'tipo_dano' => $dados['tipo_dano'] ?? null,
                'fotos' => is_array($fotos) ? count($fotos) : 0,
            ]));

            DB::commit();

            return response()->json($dano?->load('imagem'));
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json(['error' => 'Erro ao salvar dano: '.$e->getMessage()], 500);
        }
    }

    /**
     * Processa o array de dados substituindo valores vazios/nulos por um placeholder padrao.
     *
     * @param  array  &$data  Array de dados a ser processado
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

    private function convertBrCasoValorSejaNulo($valor)
    {
        return ! isset($valor) || is_null($valor) ? '<br>' : $valor;
    }

    // Ã¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢Â
    // FEATURE 1: Upgrade de categoria no check-in
    // Ã¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢Â

    /**
     * API: verifica disponibilidade na categoria original e retorna
     * veículos elegíveis para upgrade, se necessÃƒÂ¡rio.
     */
    public function checkinStatusUpgrade(Request $request, $reserva_id)
    {
        $reserva = $this->reservaService->getById($reserva_id, ['grupoFrota', 'modelos']);

        $temDisponibilidade = $this->upgradeVeiculoService->categoriaOriginalTemDisponibilidade($reserva);

        $payload = [
            'tem_disponibilidade_categoria_original' => $temDisponibilidade,
            'grupo_original' => [
                'id' => $reserva->grupoFrota?->id,
                'nome' => $reserva->grupoFrota?->nome,
            ],
        ];

        if (! $temDisponibilidade) {
            $grupos = $this->upgradeVeiculoService->veiculosDisponiveisParaUpgrade($reserva);
            $payload['grupos_upgrade'] = collect($grupos)->map(fn ($item) => [
                'grupo' => [
                    'id' => $item['grupo']->id,
                    'nome' => $item['grupo']->nome,
                    'ordem' => $item['grupo']->ordem,
                ],
                'veiculos' => $item['veiculos']->map(fn ($v) => [
                    'id' => $v->id,
                    'nome' => $v->nome,
                    'placa' => $v->placa_licenca,
                ])->values(),
            ])->values();

            $todos = $this->upgradeVeiculoService->veiculosDisponiveisNoPeriodo($reserva);
            $payload['veiculos_periodo'] = $todos->map(function ($v) use ($reserva) {
                $grupo = $v->grupoFrota ?? null;

                return [
                    'id' => $v->id,
                    'nome' => $v->nome,
                    'placa' => $v->placa_licenca,
                    'grupo' => [
                        'id' => $grupo?->id,
                        'nome' => $grupo?->nome,
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
     * LÃƒÂ³gica extra injetada no mÃƒÂ©todo existente via mÃƒÂ©todo auxiliar.
     */
    private function resolverDadosUpgrade(array &$dados, int $reservaId, ?Reserva $reservaContexto = null): void
    {
        $reserva = $reservaContexto ?: $this->reservaService->getById($reservaId, ['grupoFrota']);
        if (! $reserva) {
            return;
        }

        $veiculoId = (int) ($dados['veiculo_id'] ?? 0);
        if (! $veiculoId) {
            return;
        }

        $veiculo = $this->veiculoService->getById($veiculoId);
        if (! $veiculo) {
            return;
        }

        // Registra grupo original sempre
        $dados['grupo_frota_original_id'] = $reserva->grupo_frota_id;

        // Registra veiculo previsto, se existir na reserva
        if (! empty($reserva->veiculo_id)) {
            $dados['veiculo_previsto_id'] = (int) $reserva->veiculo_id;
        }

        // Blindagem: veiculo deve estar na estacao de retirada (quando aplicavel)
        if (! empty($reserva->estacao_retirada_id) && ! empty($veiculo->local_disponibilidade)) {
            if ((int) $veiculo->local_disponibilidade !== (int) $reserva->estacao_retirada_id) {
                throw ValidationException::withMessages([
                    'veiculo_id' => 'Veículo selecionado não esta disponível na estacão de retirada desta reserva.',
                ]);
            }
        }

        if (! $reserva->grupo_frota_id) {
            return;
        }

        // Mesmo grupo: sem upgrade
        if ((int) $veiculo->grupo_frota_id === (int) $reserva->grupo_frota_id) {
            return;
        }

        // Categoria original ainda tem disponibilidade Ã¢â€ â€™ aplica regras de upgrade/downgrade
        $categoriaDisponivel = $this->upgradeVeiculoService->categoriaOriginalTemDisponibilidade($reserva);

        if ($categoriaDisponivel) {
            // HÃƒÂ¡ veículos na categoria original: sÃƒÂ³ permite upgrade (nunca downgrade)
            $ehUpgrade = $this->upgradeVeiculoService->veiculoElegivelParaUpgrade($veiculo, $reserva);
            if (! $ehUpgrade) {
                throw ValidationException::withMessages([
                    'veiculo_id' => 'Nao e permitido selecionar veiculo de categoria inferior a reservada (downgrade bloqueado).',
                ]);
            }
        }

        // Categoria original sem disponibilidade (ou ÃƒÂ© upgrade): registra como upgrade
        $motivo = trim((string) ($dados['motivo_upgrade'] ?? ''));

        $dados['houve_upgrade_categoria'] = true;
        $dados['motivo_upgrade'] = $motivo !== '' ? $motivo : 'Indisponibilidade na categoria original';
        $dados['usuario_upgrade_id'] = Auth::id();
        $dados['data_hora_upgrade'] = now();
    }

    // Ã¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢Â
    // FEATURE 2: Editar reserva no show
    // Ã¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢Â

    /**
     * Exclui o contrato de check-in e redireciona para recriação.
     * Se assinado, o fato ÃƒÂ© registrado no log com nível danger.
     */
    public function excluirContratoCheckin(Request $request, $reserva_id)
    {
        $reserva = $this->reservaService->getById($reserva_id, ['contratoCheckin', 'contratoCheckout']);
        $contrato = $reserva->contratoCheckin;

        if (! $contrato) {
            return redirect()->route('reserva.show', $reserva_id)
                ->with('warning', 'Nenhum contrato de check-in encontrado.');
        }

        if ($reserva->contratoCheckout) {
            return redirect()->route('reserva.show', $reserva_id)
                ->with('warning', 'ApÃƒÂ³s o checkout, o contrato de check-in nÃƒÂ£o pode mais ser excluído. Apenas a visualização fica disponível.');
        }

        $foiAssinado = $contrato->assinado === 'S';
        $veiculoId = $contrato->veiculo_id;
        $veiculoNome = $contrato->veiculo?->nome ?? "ID {$veiculoId}";

        // Remove arquivos de fotos do storage
        $fotoCols = [
            'foto_direita_path',
            'foto_esquerda_path',
            'foto_frente_path',
            'foto_tras_path',
            'foto_velocimetro_path',
            'foto_combustivel_path',
        ];
        foreach ($fotoCols as $col) {
            if (! empty($contrato->$col)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($contrato->$col);
            }
        }

        // Remove arquivo de assinatura
        if ($foiAssinado && ! empty($contrato->assinatura)) {
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
                'acao' => 'Contrato de check-in excluído',
                'foi_assinado' => $foiAssinado,
                'veiculo' => $veiculoNome,
                'movimentacoes_canceladas' => $movimentacoesCanceladas ?? 0,
                'excluido_por' => Auth::user()?->name ?? Auth::user()?->nome ?? 'UsuÃƒÂ¡rio',
                'status_alterado_para' => $statusConfirmada?->nome ?? 'nÃƒÂ£o alterado',
            ]),
            'Contrato de check-in excluído'.($foiAssinado ? ' (estava assinado)' : '').' Ã¢â‚¬â€ status voltou para Confirmada',
            $foiAssinado ? 'danger' : 'warning'
        );

        return redirect()->route('reserva.checkin', $reserva_id)
            ->with(
                'warning',
                $foiAssinado
                    ? 'Contrato assinado excluído. Status da reserva voltou para Confirmada. Crie um novo contrato.'
                    : 'Contrato excluído. Status da reserva voltou para Confirmada. Crie um novo contrato.'
            );
    }

    /**
     * Retorna o histÃƒÂ³rico de logs da reserva em JSON (para carregamento via AJAX).
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
                // Remove chave 'acao' dos detalhes pois jÃƒÂ¡ estÃƒÂ¡ em $log->acao
                unset($detalhes['acao']);

                $nomeUsuario = $log->usuario?->name ?? $log->usuario?->nome ?? 'Sistema';

                return [
                    'id' => $log->id,
                    'acao' => $log->acao ?: ($detalhes['acao'] ?? 'Ação registrada'),
                    'nivel' => $log->nivel ?? 'info',
                    'usuario' => $nomeUsuario,
                    'data' => $log->created_at?->format('d/m/Y H:i:s'),
                    'detalhes' => $detalhes,
                ];
            });

        return response()->json(['logs' => $logs]);
    }

    // Ã¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢Â

    /**
     * Retorna os dados da reserva + opcionais em JSON para edição inline no show.
     */
    public function dadosParaEdicaoNoShow(Request $request, $id)
    {
        $this->authorize('reserva.editar_no_show');

        $reserva = $this->reservaService->getById($id, ['opcionais', 'grupoFrota', 'estacaoRetirada', 'estacaoDevolucao']);
        if (! $reserva) {
            return response()->json(['error' => 'Reserva nao encontrada.'], 404);
        }

        $estacoes = $this->estacaoService->getAllAtivos();
        $gruposVeiculo = $this->grupoFrotaService->getAllAtivosForSelectSubText('id', 'nome', 'valor_diario', null, 'asc');
        $tiposOpcionais = $this->tipoOpcionalService->getTipoOpcionais('nome', 'asc');

        return response()->json([
            'reserva' => $reserva->only([
                'id',
                'data_inicio',
                'data_fim',
                'estacao_retirada_id',
                'estacao_devolucao_id',
                'grupo_frota_id',
                'modelo_id',
                'observacoes',
                'tarifa_base',
                'valor_total_final',
                'quantidade_dias',
            ]),
            'opcionais_ids' => $reserva->opcionais->pluck('id')->toArray(),
            'opcionais_pivot' => $reserva->opcionais->map(fn ($o) => [
                'id' => $o->id,
                'nome' => $o->nome,
                'quantidade' => $o->pivot->quantidade,
                'preco_total' => $o->pivot->preco_total,
            ]),
            'estacoes' => $estacoes,
            'grupos_veiculo' => $gruposVeiculo,
            'tipos_opcionais' => $tiposOpcionais,
        ]);
    }

    /**
     * Atualiza datas, estaçÃƒÂµes, observaçÃƒÂµes e opcionais da reserva via show.
     */
    public function atualizarNoShow(Request $request, $id)
    {
        $this->authorize('reserva.editar_no_show');

        $reserva = $this->reservaService->getById($id, ['opcionais']);
        if (! $reserva) {
            return response()->json(['error' => 'Reserva nao encontrada.'], 404);
        }

        // Campos editÃƒÂ¡veis bloqueados apÃƒÂ³s certos status
        $statusBloqueados = [3, 4, 5]; // cancelada, em andamento, concluída
        if (in_array((int) $reserva->status_reserva_id, $statusBloqueados, true)) {
            return response()->json([
                'error' => 'Reserva no status atual nao pode ser editada.',
            ], 422);
        }

        $request->validate([
            'data_inicio' => 'nullable|date',
            'data_fim' => 'nullable|date|after_or_equal:data_inicio',
            'estacao_retirada_id' => 'nullable|integer|exists:estacoes,id',
            'estacao_devolucao_id' => 'nullable|integer|exists:estacoes,id',
            'observacoes' => 'nullable|string|max:1000',
            'opcionais' => 'nullable|array',
            'opcionais.*.id' => 'required|integer|exists:opcionais,id',
            'opcionais.*.quantidade' => 'required|integer|min:1',
            'opcionais.*.preco_total' => 'required|numeric|min:0',
        ]);

        $camposAntes = $reserva->only([
            'data_inicio',
            'data_fim',
            'estacao_retirada_id',
            'estacao_devolucao_id',
            'observacoes',
        ]);

        $dados = $request->only([
            'data_inicio',
            'data_fim',
            'estacao_retirada_id',
            'estacao_devolucao_id',
            'observacoes',
        ]);

        $dados = array_filter($dados, fn ($v) => $v !== null);
        $reserva->update($dados);

        // Atualiza opcionais se enviados
        if ($request->has('opcionais')) {
            $this->authorize('reserva.gerenciar_opcionais');
            $sync = [];
            foreach ($request->input('opcionais', []) as $opc) {
                $sync[$opc['id']] = [
                    'quantidade' => $opc['quantidade'],
                    'preco_total' => $opc['preco_total'],
                ];
            }
            $reserva->opcionais()->sync($sync);
        }

        $this->reservaService->adicionaLog($id, Auth::id(), json_encode([
            'acao' => 'Edicao no show',
            'antes' => $camposAntes,
            'depois' => $dados,
        ]));

        return response()->json(['success' => true, 'message' => 'Reserva atualizada com sucesso.']);
    }

    /**
     * Exibe a tela de calção assinatura do contrato.
     */
    public function calcaoCheckin(Request $request, $codigo)
    {
        $this->authorize('calcao.autorizar');

        $reserva = Reserva::withoutGlobalScopes()
            ->with(['contratoCheckin', 'grupoFrota', 'modelos', 'usuario', 'calcao', 'codigoReserva'])
            ->whereHas('codigoReserva', function ($query) use ($codigo) {
                $query->where('codigo', $codigo);
            })
            ->first();

        if (! $reserva || ! $reserva->contratoCheckin || $reserva->contratoCheckin->assinado !== 'S') {
            return redirect()->route('reserva.show', $codigo)
                ->with('error', 'O contrato de check-in deve ser assinado antes de registrar o calcao.');
        }

        $data = $this->exibirCalcaoCheckinAction->handle($reserva);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Reservas', route('reserva.index')),
            new BreadCrumbItem('Detalhes da Reserva Nº'.$codigo, route('reserva.show', $codigo)),
            new BreadCrumbItem('Calcao'),
        ]);

        return view('reserva.calcao', compact(
            'reserva', 'breadcrumb'
        ) + $data)->with('titulo', 'Calcao — Reserva Nº '.$reserva->id);
    }

    /**
     * Cria pré-autorização no Stripe e persiste o calção.
     */
    public function autorizarCalcao(AutorizarCalcaoRequest $request, $codigo)
    {
        $this->authorize('calcao.autorizar');

        $reserva = Reserva::withoutGlobalScopes()
            ->with(['contratoCheckin', 'usuario', 'calcao', 'codigoReserva'])
            ->whereHas('codigoReserva', function ($query) use ($codigo) {
                $query->where('codigo', $codigo);
            })
            ->first();

        if (! $reserva) {
            return response()->json(['error' => 'Contrato de check-in nao encontrado.'], 404);
        }

        try {
            $calcao = $this->autorizarCalcaoAction->handle($reserva, $request->input('payment_method_id'));

            return response()->json([
                'success' => true,
                'calcao_id' => $calcao->id,
                'message' => 'Calcao autorizado com sucesso. Valor pre-autorizado: R$ '.number_format((float) $calcao->valor_autorizado, 2, ',', '.'),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Falha inesperada ao autorizar calcao.'], 500);
        }
    }

    /**
     * Vincula um PaymentMethod (já criado no Stripe via JS) ao cliente da reserva.
     * Chamado após stripe.createPaymentMethod() no front - persiste o cartão no Stripe.
     */
    public function salvarCartaoCalcao(Request $request, $reserva_id)
    {
        $this->authorize('calcao.autorizar');

        $request->validate(['payment_method_id' => 'required|string']);

        $reserva = $this->reservaService->getById($reserva_id, ['usuario']);
        if (! $reserva || ! $reserva->usuario) {
            return response()->json(['error' => 'Reserva ou cliente não encontrado.'], 404);
        }

        try {
            $pm = $this->salvarCartaoCalcaoAction->handle($reserva, $request->input('payment_method_id'));

            return response()->json([
                'success' => true,
                'pm' => $pm,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Não foi possível salvar o cartão: '.$e->getMessage()], 422);
        }
    }

    /**
     * Captura (total ou parcial) o valor do calção no checkout.
     */
    public function capturarCalcao(CapturarCalcaoRequest $request, $reserva_id)
    {
        $this->authorize('calcao.capturar');

        $reserva = Reserva::withoutGlobalScopes()
            ->with('transacoes')
            ->find($reserva_id);

        if (! $reserva) {
            return response()->json(['error' => 'Reserva nao encontrada.'], 404);
        }

        try {
            $calcao = $this->capturarCalcaoAction->handle(
                $reserva,
                (float) $request->input('valor_captura'),
                $request->input('justificativa')
            );

            return response()->json([
                'success' => true,
                'message' => 'Calcao capturado com sucesso.',
                'calcao' => $calcao->only(['id', 'status', 'valor_capturado']),
            ]);
        } catch (\Throwable $e) {
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
        if (! $reserva) {
            return response()->json(['error' => 'Reserva nao encontrada.'], 404);
        }

        try {
            $this->liberarCalcaoAction->handle($reserva, $request->input('observacao', ''));

            return response()->json(['success' => true, 'message' => 'Calcao liberado com sucesso.']);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Registra calção pago presencialmente (dinheiro/pix/transferÃƒÂªncia), sem Stripe.
     * Cria registro em `calcoes` e uma `transacao` financeira positiva vinculada ÃƒÂ  reserva.
     */
    public function registrarCalcaoPresencial(Request $request, $reserva_id)
    {
        $this->authorize('calcao.autorizar');

        $request->validate([
            'metodo' => 'required|string|max:50',
            'valor' => 'nullable|numeric|min:0.01',
            'observacao' => 'nullable|string|max:500',
        ]);

        $reserva = $this->reservaService->getById((int) $reserva_id, ['contratoCheckin', 'usuario', 'calcao']);
        if (! $reserva) {
            return response()->json(['error' => 'Reserva nao encontrada.'], 404);
        }

        try {
            $calcao = $this->registrarCalcaoPresencialAction->handle(
                $reserva,
                trim((string) $request->input('metodo')),
                $request->input('valor') ? (float) $request->input('valor') : null,
                $request->input('observacao')
            );

            return response()->json([
                'success' => true,
                'message' => 'Calção presencial registrado com sucesso.',
                'calcao' => $calcao->only(['id', 'status', 'valor_calculado', 'valor_capturado']),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Falha ao registrar calção presencial: '.$e->getMessage()], 500);
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
        if (! $reserva) {
            return response()->json(['error' => 'Reserva nao encontrada.'], 404);
        }

        try {
            $this->devolverCalcaoPresencialAction->handle($reserva, $request->input('observacao'));

            return response()->json(['success' => true, 'message' => 'Calção devolvido com sucesso.']);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Falha ao devolver calção: '.$e->getMessage()], 500);
        }
    }

    // Ã¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢Â
    // Confirmar pagamento em dinheiro / criar transacao manual
    // Ã¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢Â

    public function confirmarPagamentoDinheiro(Request $request, $reserva_id)
    {
        $reserva = Reserva::withoutGlobalScopes()
            ->with('transacoes')
            ->find($reserva_id);

        if (! $reserva) {
            return response()->json(['error' => 'Reserva nao encontrada.'], 404);
        }

        try {
            $this->confirmarPagamentoDinheiroAction->handle(
                $reserva,
                $request->input('transacao_id') ? (int) $request->input('transacao_id') : null,
                (float) ($request->input('valor', 0)),
                strtolower((string) $request->input('metodo_pagamento', 'dinheiro'))
            );

            return response()->json(['success' => true, 'message' => 'Pagamento em dinheiro registrado com sucesso.']);
        } catch (\InvalidArgumentException|\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Erro ao confirmar pagamento em dinheiro.'], 500);
        }
    }

    private function normalizarPagamentoMotoristaAppQuitado(ReservaMotoristaApp $reserva): void
    {
        $reserva->loadMissing('pagamentos');

        $valorTotal = round(
            (float) ($reserva->valor_periodo_contrato ?: $reserva->valor_plano)
            + (float) ($reserva->valor_opcionais ?? 0),
            2
        );

        if ($valorTotal <= 0) {
            return;
        }

        $totalPago = (float) collect($reserva->pagamentos ?? [])
            ->filter(fn ($pagamento) => strtolower((string) ($pagamento->status ?? '')) === 'pago')
            ->sum(fn ($pagamento) => (float) ($pagamento->valor_total ?? $pagamento->valor_unitario ?? $pagamento->valor ?? 0));

        if (round($valorTotal - $totalPago, 2) > 0) {
            return;
        }

        if ((bool) $reserva->pagamento_efetuado && (string) $reserva->status_pagamento === 'pago') {
            return;
        }

        $reserva->forceFill([
            'pagamento_efetuado' => true,
            'status_pagamento' => 'pago',
            'status' => in_array((string) $reserva->status, ['pendente', 'pendente_analise', 'pendente_pagamento'], true)
                ? 'confirmada'
                : $reserva->status,
        ])->save();
    }
}
