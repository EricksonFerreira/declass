<?php

namespace App\Http\Controllers;

use App\Enums\StatusReservaEnum;
use App\Http\Requests\Reserva\StoreReservaRequest;
use App\Models\CupomDesconto;
use App\Models\Empresa;
use App\Models\Feriado;
use App\Models\Estacao;
use App\Models\GrupoFrota;
use App\Models\Modelo;
use App\Models\ReservaSatisfacao;
use App\Models\Opcional;
use App\Models\Multa;
use App\Models\Reserva;
use App\Models\Transacao;
use App\Models\User;
use App\Services\ClienteStripeService;
use App\Services\CupomDescontoService;
use App\Services\GrupoFrotaService;
use App\Services\Pagamentos\GatewayEmpresaConfigService;
use App\Services\Pagamentos\GerenciadorDePagamentos;
use App\Services\PaisService;
use App\Services\NfseService;
use App\Services\RegraService;
use App\Services\ReservaService;
use App\Services\UsuarioService;
use App\Services\VeiculoService;
use App\Support\DomainNormalizer;
use App\Support\EmpresaContext;
use Carbon\Carbon;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use App\Support\SchemaCache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SiteReservaController extends Controller
{
    public function __construct(
        private readonly ReservaService $reservaService,
        private readonly GrupoFrotaService $grupoFrotaService,
        private readonly VeiculoService $veiculoService,
        private readonly CupomDescontoService $cupomDescontoService,
        private readonly UsuarioService $usuarioService,
        private readonly RegraService $regraService,
        private readonly ClienteStripeService $clienteStripeService,
        private readonly PaisService $paisService,
        private readonly NfseService $nfseService,
        private readonly GatewayEmpresaConfigService $gatewayEmpresaConfigService,
        private readonly GerenciadorDePagamentos $gerenciadorDePagamentos
    ) {
    }

    public function index(Request $request): View|RedirectResponse
    {
        $empresa = $this->resolveEmpresaAtivaByHost($request);
        if (!$empresa) {
            return $this->defaultResponse();
        }

        $this->activateEmpresaContext($empresa);

        $estacoesRetirada = $this->estacoesForEmpresa('checkin');
        $estacoesDevolucao = $this->estacoesForEmpresa('checkout');
        $paises = $this->paisService->getAllForSelectSigla();
        $moedaEmpresa = strtoupper($this->gatewayEmpresaConfigService->moeda($empresa));
        $reservaSelecionarModelo = ($empresa->reserva_selecionar_modelo ?? 'N') === 'S';

        $prefill = [
            'estacao_retirada_id' => old('estacao_retirada_id', $this->normalizeInt($request->query('retirada'))),
            'estacao_devolucao_id' => old(
                'estacao_devolucao_id',
                $this->normalizeInt($request->query('devolucao')) ?? $this->normalizeInt($request->query('retirada'))
            ),
            'data_inicio' => old('data_inicio', $this->buildDateTimeLocal(
                $request->query('data_retirada'),
                $request->query('hora_retirada')
            )),
            'data_fim' => old('data_fim', $this->buildDateTimeLocal(
                $request->query('data_devolucao'),
                $request->query('hora_devolucao')
            )),
        ];

        $prefillCompleto = $this->prefillReservaCompleta($prefill);
        $qtdVeiculosEstacao = $prefillCompleto
            ? $this->veiculoService->getQuantidadeVeiculosDisponiveisPorEstacaoEPeriodo(
                (int) $prefill['estacao_retirada_id'],
                (string) $prefill['data_inicio'],
                (string) $prefill['data_fim']
            )
            : 0;
        $initialStepServer = $prefillCompleto && $qtdVeiculosEstacao > 0 ? 2 : 1;
        $gruposFrota = $this->gruposFrotaAtivos(
            $prefillCompleto ? (int) $prefill['estacao_retirada_id'] : null,
            $prefillCompleto ? (string) $prefill['data_inicio'] : null,
            $prefillCompleto ? (string) $prefill['data_fim'] : null
        );

        return view('site-apresentacao.reserva', [
            'empresa' => $empresa,
            'estacoes' => $estacoesRetirada,
            'estacoesRetirada' => $estacoesRetirada,
            'estacoesDevolucao' => $estacoesDevolucao,
            'gruposFrota' => $gruposFrota,
            'paises' => $paises,
            'moedaEmpresa' => $moedaEmpresa,
            'simboloMoedaEmpresa' => $this->simboloMoeda($moedaEmpresa),
            'isAuth' => auth()->check(),
            'authUserName' => auth()->user()?->nome,
            'authUserEmail' => auth()->user()?->email,
            'loginRedirectUrl' => Route::has('site.reserva.login') ? route('site.reserva.login', ['step' => 4]) : null,
            'reservaSelecionarModelo' => $reservaSelecionarModelo,
            'initialStepServer' => $initialStepServer,
            'prefill' => $prefill,
        ]);
    }

    public function disponibilidade(Request $request): JsonResponse
    {
        $empresa = $this->resolveEmpresaAtivaByHost($request);
        if (!$empresa) {
            return response()->json(['message' => 'Empresa nao encontrada para este dominio.'], 404);
        }

        $this->activateEmpresaContext($empresa);

        $validator = Validator::make($request->all(), [
            'grupo_frota_id' => ['required', 'integer'],
            'estacao_retirada_id' => ['required', 'integer', 'exists:estacoes,id'],
            'estacao_devolucao_id' => ['required', 'integer', 'exists:estacoes,id'],
            'data_inicio' => ['required', 'date'],
            'data_fim' => ['required', 'date', 'after:data_inicio'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados invalidos para consultar disponibilidade.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $dataInicio = (string) $request->input('data_inicio');
        $dataFim = (string) $request->input('data_fim');
        if (!$this->dataInicioValida($dataInicio)) {
            return response()->json([
                'message' => 'A data de retirada nao pode ser anterior a agora.',
                'errors' => ['data_inicio' => ['A retirada nao pode ser antes de agora.']],
            ], 422);
        }

        if (!$this->dataFimMinimaValida($dataInicio, $dataFim)) {
            return response()->json([
                'message' => 'A data de devolucao deve ser ao menos 1 dia apos a retirada.',
                'errors' => ['data_fim' => ['A devolucao deve ser ao menos 1 dia apos a retirada.']],
            ], 422);
        }

        $grupoFrotaId = (int) $request->input('grupo_frota_id');
        $grupoFrota = GrupoFrota::query()->where('ativo', 'S')->find($grupoFrotaId);
        if (!$grupoFrota) {
            return response()->json(['message' => 'Grupo de frota nao encontrado.'], 404);
        }

        $estacaoRetiradaId = (int) $request->input('estacao_retirada_id');
        $qtdVeiculosEstacao = $this->veiculoService->getQuantidadeVeiculosDisponiveisPorEstacaoEPeriodo(
            $estacaoRetiradaId,
            $dataInicio,
            $dataFim
        );

        $modelosDisponiveis = $this->veiculoService->getModelosComVeiculosDisponiveis(
            $grupoFrotaId,
            $estacaoRetiradaId,
            $dataInicio,
            $dataFim
        );

        $modelosVinculados = collect($this->grupoFrotaService->getModelosPorGrupoFrota($grupoFrotaId));
        $avisoModelos = null;
        if ($modelosDisponiveis->isEmpty()) {
            $avisoModelos = $modelosVinculados->isNotEmpty()
                ? 'Nao ha veiculos disponiveis neste grupo de frota para a estacao e periodo selecionados.'
                : 'Este grupo de veiculos ainda nao possui modelos vinculados.';
        }

        $tipoOpcionais = $this->normalizeTipoOpcionais(
            $this->grupoFrotaService->getTiposOpcionaisComOpcionais($grupoFrotaId)
        );

        return response()->json([
            'grupo_frota' => [
                'id' => (int) $grupoFrota->id,
                'nome' => (string) $grupoFrota->nome,
                'valor_diario' => (float) $grupoFrota->valor_diario,
            ],
            'modelos' => $modelosDisponiveis->map(function ($modelo): array {
                return [
                    'id' => (int) $modelo->id,
                    'nome' => (string) $modelo->nome,
                    'imagem_url' => $modelo->imagem_url ?? $modelo->imagem ?? null,
                    'disponivel' => true,
                ];
            })->values()->all(),
            'modelos_vinculados' => $modelosVinculados->map(function ($modelo): array {
                return [
                    'id' => (int) ($modelo->id ?? 0),
                    'nome' => (string) ($modelo->nome ?? ''),
                    'imagem_url' => $modelo->imagem_url ?? $modelo->imagem ?? null,
                    'disponivel' => false,
                ];
            })->values()->all(),
            'tipo_opcional' => $tipoOpcionais,
            'aviso_modelos' => $avisoModelos,
            'qtd_veiculos_estacao' => $qtdVeiculosEstacao,
        ]);
    }

    public function estacoesDisponiveis(Request $request): JsonResponse
    {
        $empresa = $this->resolveEmpresaAtivaByHost($request);
        if (!$empresa) {
            return response()->json(['message' => 'Empresa nao encontrada para este dominio.'], 404);
        }

        $this->activateEmpresaContext($empresa);

        $validated = Validator::make($request->all(), [
            'data_inicio' => ['required', 'date'],
            'data_fim' => ['required', 'date', 'after:data_inicio'],
            'grupo_frota_id' => ['nullable', 'integer'],
            'modelo_id' => ['nullable', 'integer'],
        ])->validate();

        $ids = $this->veiculoService->getEstacaoIdsComVeiculosDisponiveisNoPeriodo(
            (string) $validated['data_inicio'],
            (string) $validated['data_fim'],
            !empty($validated['grupo_frota_id']) ? (int) $validated['grupo_frota_id'] : null,
            !empty($validated['modelo_id']) ? (int) $validated['modelo_id'] : null,
        );

        // Intersect com estacoes ativas da empresa, para evitar retornar estacoes de outro contexto
        $estacoesAtivas = collect($this->estacoesForEmpresa())->pluck('id')->map(fn ($v) => (int) $v)->all();
        $allow = array_values(array_intersect($ids, $estacoesAtivas));

        return response()->json([
            'estacao_ids' => $allow,
        ]);
    }

    public function diasDisponiveisPorEstacao(Request $request): JsonResponse
    {
        $empresa = $this->resolveEmpresaAtivaByHost($request);
        if (!$empresa) {
            return response()->json(['message' => 'Empresa nao encontrada para este dominio.'], 404);
        }

        $this->activateEmpresaContext($empresa);

        $request->validate([
            'estacao_id' => ['required', 'integer', 'exists:estacoes,id'],
            'dias' => ['nullable', 'integer', 'min:1', 'max:180'],
            'tipo' => ['nullable', 'string', 'max:20'],
        ]);

        $estacaoId = (int) $request->input('estacao_id');
        $dias = (int) ($request->input('dias') ?: 90);
        $tipo = strtolower(trim((string) ($request->input('tipo') ?: 'retirada')));
        $isDevolucao = $tipo === 'devolucao';
        $hoje = Carbon::today();

        $estacao = Estacao::with(['diasUteis'])->findOrFail($estacaoId);
        $quantidadeVeiculosAtivos = $this->veiculoService->getQuantidadeVeiculosAtivosPorEstacao([$estacaoId]);
        if (!$isDevolucao && (int) ($quantidadeVeiculosAtivos[$estacaoId] ?? 0) <= 0) {
            return response()->json([
                'estacao_id' => $estacao->id,
                'dias_disponiveis' => [],
                'mensagem' => 'A estaÃ§Ã£o selecionada nao possui veiculos ativos para retirada.',
            ]);
        }

        $proximosDias = $estacao->proximosDiasFuncionamento($dias, $hoje);
        $proximosDias = $this->removerFeriados(
            $proximosDias,
            $estacao->id,
            (bool) ($estacao->opera_feriado ?? false)
        );

        if (empty($proximosDias)) {
            return response()->json([
                'estacao_id' => $estacao->id,
                'dias_disponiveis' => [],
                'mensagem' => $isDevolucao
                    ? 'A estaÃ§Ã£o selecionada nao possui agenda de devolucao configurada para o periodo.'
                    : 'A estaÃ§Ã£o selecionada nao possui agenda de retirada configurada para o periodo.',
            ]);
        }

        return response()->json([
            'estacao_id' => $estacao->id,
            'dias_disponiveis' => $proximosDias,
            'mensagem' => null,
        ]);
    }

    private function removerFeriados(array $dados, int $estacaoId, ?bool $operaFeriado): array
    {
        if ((bool) $operaFeriado || empty($dados)) {
            return $dados;
        }

        $dataInicial = $dados[0]['data'];
        $dataFinal = end($dados)['data'];

        $feriados = Feriado::query()
            ->where('estacao_id', $estacaoId)
            ->whereBetween('data', [$dataInicial, $dataFinal])
            ->where('ativo', 'S')
            ->pluck('data')
            ->map(fn ($data) => (string) $data)
            ->all();

        if (empty($feriados)) {
            return $dados;
        }

        return array_values(array_filter($dados, static function (array $dia) use ($feriados): bool {
            return !in_array((string) ($dia['data'] ?? ''), $feriados, true);
        }));
    }
    public function agendaDisponivelPorEstacao(Request $request): JsonResponse
    {
        $empresa = $this->resolveEmpresaAtivaByHost($request);
        if (!$empresa) {
            return response()->json(['message' => 'Empresa nao encontrada para este dominio.'], 404);
        }

        $this->activateEmpresaContext($empresa);

        $request->validate([
            'estacao_id' => ['required', 'integer', 'exists:estacoes,id'],
            'tipo' => ['nullable', 'string', 'max:20'],
            'data_base' => ['nullable', 'date'],
            'limite' => ['nullable', 'integer', 'min:1', 'max:60'],
            'reserva_id' => ['nullable', 'integer'],
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

    public function verificarCupom(Request $request): JsonResponse
    {
        $empresa = $this->resolveEmpresaAtivaByHost($request);
        if (!$empresa) {
            return response()->json(['message' => 'Empresa nao encontrada para este dominio.'], 404);
        }

        $this->activateEmpresaContext($empresa);

        $validated = Validator::make($request->all(), [
            'cupom' => ['nullable', 'string', 'max:255'],
        ])->validate();

        $codigo = trim((string) ($validated['cupom'] ?? ''));
        if ($codigo === '') {
            return response()->json(['cupom' => null]);
        }

        $cupom = $this->resolveCupomValido($codigo);
        if (!$cupom) {
            return response()->json(['cupom' => null]);
        }

        return response()->json([
            'cupom' => [
                'id' => (int) $cupom->id,
                'nome' => (string) ($cupom->nome ?? ''),
                'codigo' => (string) ($cupom->codigo ?? ''),
                'valor' => (float) ($cupom->valor ?? 0),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $empresa = $this->resolveEmpresaAtivaByHost($request);
        if (!$empresa) {
            return $this->defaultResponse();
        }

        $this->activateEmpresaContext($empresa);

        $requireModelo = ($empresa->reserva_selecionar_modelo ?? 'N') === 'S';
        $usarUsuarioLogado = strtoupper((string) $request->input('usar_usuario_logado', 'S'));
        $requireCliente = !auth()->check() || $usarUsuarioLogado === 'N';
        $validated = Validator::make($request->all(), $this->storeRules($requireModelo, $requireCliente))->validate();

        $userCriado = false;
        if (auth()->check() && $usarUsuarioLogado !== 'N') {
            $user = auth()->user();
        } else {
            $user = $this->resolveReservaUser($validated, $empresa, $userCriado);
            if ($userCriado) {
                Auth::login($user);
            }
        }
        $grupo = GrupoFrota::query()->where('ativo', 'S')->find((int) $validated['grupo_frota_id']);

        if (!$requireModelo && empty($validated['modelo_id'])) {
            $modelosDisponiveis = $this->veiculoService->getModelosComVeiculosDisponiveis(
                (int) $validated['grupo_frota_id'],
                (int) $validated['estacao_retirada_id'],
                (string) $validated['data_inicio'],
                (string) $validated['data_fim']
            );

            $modeloAuto = $modelosDisponiveis->first();
            if (!$modeloAuto) {
                throw ValidationException::withMessages([
                    'modelo_id' => 'Nao ha modelos disponiveis para o grupo selecionado.',
                ]);
            }

            $validated['modelo_id'] = (int) $modeloAuto->id;
        }

        $modelo = Modelo::query()->find((int) $validated['modelo_id']);
        $estacaoRetirada = Estacao::query()->where('ativo', 'S')->find((int) $validated['estacao_retirada_id']);
        $estacaoDevolucao = Estacao::query()->where('ativo', 'S')->find((int) $validated['estacao_devolucao_id']);

        if (!$grupo || !$modelo || !$estacaoRetirada || !$estacaoDevolucao) {
            throw ValidationException::withMessages([
                'reserva' => 'Nao foi possivel validar os dados basicos da reserva.',
            ]);
        }

        $dataInicio = (string) $validated['data_inicio'];
        $dataFim = (string) $validated['data_fim'];
        $grupoFrotaId = (int) $validated['grupo_frota_id'];
        $modeloId = (int) $validated['modelo_id'];
        $estacaoRetiradaId = (int) $validated['estacao_retirada_id'];

        if (!$grupo->modelos()->where('modelos.id', $modeloId)->exists()) {
            throw ValidationException::withMessages([
                'modelo_id' => 'O modelo selecionado nao pertence ao grupo de frota informado.',
            ]);
        }

        $modelosDisponiveis = $this->veiculoService->getModelosComVeiculosDisponiveis(
            $grupoFrotaId,
            $estacaoRetiradaId,
            $dataInicio,
            $dataFim
        )->pluck('id')->map(fn ($id) => (int) $id)->all();

        if (!in_array($modeloId, $modelosDisponiveis, true)) {
            throw ValidationException::withMessages([
                'modelo_id' => 'Nao ha veiculo disponivel para o modelo selecionado neste periodo.',
            ]);
        }

        $opcionaisSelecionados = $this->parseOpcionaisPayload((string) ($validated['opcionais_payload'] ?? ''));
        $opcionaisReserva = $this->buildOpcionaisReserva($grupo, $opcionaisSelecionados);
        $cupom = $this->resolveCupomValido((string) ($validated['cupom_codigo'] ?? ''));

        $quantidadeDias = $this->calcularQuantidadeDias((string) $validated['data_inicio'], (string) $validated['data_fim']);
        $tarifaBase = (float) $grupo->valor_diario;

        $payload = [
            'estacao_retirada_id' => (int) $validated['estacao_retirada_id'],
            'estacao_devolucao_id' => (int) $validated['estacao_devolucao_id'],
            'data_inicio' => (string) $validated['data_inicio'],
            'data_fim' => (string) $validated['data_fim'],
            'grupo_frota_id' => $grupoFrotaId,
            'modelo_id' => $modeloId,
            'tarifa_base' => $tarifaBase,
            'quantidade_dias' => $quantidadeDias,
            'user_id' => (int) $user->id,
            'opcionais' => $opcionaisReserva,
            'cupom_desconto' => $cupom ? [
                'id' => (int) $cupom->id,
                'codigo' => (string) ($cupom->codigo ?? ''),
                'valor' => (float) ($cupom->valor ?? 0),
            ] : null,
            'valor_desconto' => $cupom ? (float) ($cupom->valor ?? 0) : 0.0,
            'observacoes' => null,
        ];

        $this->validateLikeAdminStoreRequest($payload, $opcionaisReserva);

        $dadosProcessados = $this->reservaService->processarDadosReserva((object) $payload);
        $reserva = $this->reservaService->create($dadosProcessados);

        if (!$reserva instanceof Reserva) {
            throw ValidationException::withMessages([
                'reserva' => 'Nao foi possivel concluir a reserva. Revise os dados e tente novamente.',
            ]);
        }

        $this->reservaService->adicionaLog(
            (int) $reserva->id,
            (int) $user->id,
            json_encode([
                'acao' => 'Reserva criada pelo site de apresentacao',
                'origem' => 'site-apresentacao',
                'reserva_id' => (int) $reserva->id,
            ])
        );

        $pagamentoUrl = Route::has('site.reserva.pagamento')
            ? route('site.reserva.pagamento', ['reserva' => $reserva->id])
            : null;

        if ($pagamentoUrl) {
            if (auth()->check()) {
                return redirect()
                    ->to($pagamentoUrl)
                    ->with('success', 'Reserva #' . $reserva->id . ' criada com sucesso.');
            }

            session()->put('url.intended', $pagamentoUrl);

            if (Route::has('login')) {
                return redirect()
                    ->route('login')
                    ->with('success', 'Reserva #' . $reserva->id . ' criada. Faca login para continuar o pagamento.');
            }
        }

        return redirect()
            ->route('site.reserva.success', ['reserva' => $reserva->id])
            ->with('success', 'Reserva #' . $reserva->id . ' criada com sucesso.');
    }

    public function success(Request $request, int $reserva): View|RedirectResponse
    {
        $empresa = $this->resolveEmpresaAtivaByHost($request);
        if (!$empresa) {
            return $this->defaultResponse();
        }

        $query = Reserva::query()->with(['estacaoRetirada', 'estacaoDevolucao', 'modelos', 'usuario', 'grupoFrota', 'veiculo', 'satisfacao']);
        if (SchemaCache::hasColumn('reservas', 'empresa_id')) {
            $query->where('empresa_id', (int) $empresa->id);
        }

        $reservaItem = $query->find($reserva);
        if (!$reservaItem) {
            return redirect()
                ->route('site.reserva.index')
                ->withErrors(['reserva' => 'Reserva nao encontrada para este dominio.']);
        }

        return view('site-apresentacao.reserva-success', [
            'empresa' => $empresa,
            'reserva' => $reservaItem,
            'urlNovaReserva' => route('site.reserva.index'),
            'urlAdmin' => route('site.admin.entry'),
            'urlPagamento' => Route::has('site.reserva.pagamento')
                ? route('site.reserva.pagamento', ['reserva' => $reservaItem->id])
                : null,
            'loginUrl' => Route::has('login') ? route('login') : $this->adminLoginFallbackUrl(),
            'isAuth' => auth()->check(),
        ]);
    }

    public function salvarSatisfacao(Request $request, int $reserva): RedirectResponse
    {
        $empresa = $this->resolveEmpresaAtivaByHost($request);
        if (!$empresa) {
            return $this->defaultResponse();
        }

        $this->activateEmpresaContext($empresa);

        $query = Reserva::query();
        if (SchemaCache::hasColumn('reservas', 'empresa_id')) {
            $query->where('empresa_id', (int) $empresa->id);
        }

        $reservaItem = $query->find($reserva);
        if (!$reservaItem) {
            return redirect()
                ->route('site.reserva.index')
                ->withErrors(['reserva' => 'Reserva nao encontrada para este dominio.']);
        }

        $validated = Validator::make($request->all(), [
            'satisfacao_geral' => ['required', 'integer', 'min:1', 'max:5'],
            'facilidade_reserva' => ['required', 'integer', 'min:1', 'max:5'],
            'nps_nota' => ['required', 'integer', 'min:0', 'max:10'],
            'comentario' => ['nullable', 'string', 'max:1000'],
        ])->validate();

        ReservaSatisfacao::updateOrCreate(
            ['reserva_id' => (int) $reservaItem->id],
            [
                'empresa_id' => (int) $empresa->id,
                'respondente_user_id' => auth()->id(),
                'satisfacao_geral' => (int) $validated['satisfacao_geral'],
                'facilidade_reserva' => (int) $validated['facilidade_reserva'],
                'nps_nota' => (int) $validated['nps_nota'],
                'comentario' => trim((string) ($validated['comentario'] ?? '')) ?: null,
            ]
        );

        return redirect()
            ->route('site.reserva.success', ['reserva' => $reservaItem->id])
            ->with('success', 'Obrigado! Seu feedback foi registrado com sucesso.');
    }

    public function loginRedirect(Request $request): RedirectResponse
    {
        $step = (int) $request->query('step', 4);
        if ($step < 1 || $step > 4) {
            $step = 4;
        }

        session()->put('url.intended', route('site.reserva.index', ['step' => $step]));

        if (Route::has('login')) {
            return redirect()->route('login');
        }

        return redirect($this->adminLoginFallbackUrl());
    }

    public function pagamento(Request $request, int $reserva): View|RedirectResponse
    {
        $empresa = $this->resolveEmpresaAtivaByHost($request);
        if (!$empresa) {
            return $this->defaultResponse();
        }

        $this->activateEmpresaContext($empresa);

        $query = Reserva::query()->with([
            'estacaoRetirada',
            'estacaoDevolucao',
            'modelos',
            'grupoFrota',
            'usuario',
            'opcionais',
            'cupomDesconto',
        ]);
        if (SchemaCache::hasColumn('reservas', 'empresa_id')) {
            $query->where('empresa_id', (int) $empresa->id);
        }

        $reservaItem = $query->find($reserva);
        if (!$reservaItem) {
            return redirect()
                ->route('site.reserva.index')
                ->withErrors(['reserva' => 'Reserva nao encontrada para este dominio.']);
        }

        if (!auth()->check()) {
            session()->put('url.intended', route('site.reserva.pagamento', ['reserva' => $reservaItem->id]));

            if (Route::has('login')) {
                return redirect()->route('login');
            }

            return redirect($this->adminLoginFallbackUrl());
        }

        if ((int) auth()->id() !== (int) $reservaItem->user_id) {
            return redirect()
                ->route('site.reserva.index')
                ->withErrors(['reserva' => 'Esta reserva nao pertence ao usuario autenticado.']);
        }

        if (isset($reservaItem->status_reserva_id) && (int) $reservaItem->status_reserva_id !== StatusReservaEnum::Pendente->value) {
            return redirect()
                ->route('site.reserva.success', ['reserva' => $reservaItem->id])
                ->withErrors(['reserva' => 'Reserva nao pode ser paga no status atual.']);
        }

        $gatewayPadrao = $this->gatewayEmpresaConfigService->gateway($empresa);
        $moedaPadrao = $this->gatewayEmpresaConfigService->moeda($empresa);
        $gatewayNome = [
            'stripe' => 'Stripe',
            'mercado_livre' => 'Mercado Livre',
            'paypal' => 'PayPal',
        ][$gatewayPadrao] ?? strtoupper((string) $gatewayPadrao);

        $metodosPagamento = [];
        $stripePublicKey = null;
        if ($gatewayPadrao === 'stripe') {
            $stripePublicKey = (string) ($empresa->stripe_public_key ?? config('services.stripe.key') ?? env('STRIPE_KEY'));
            if (!empty(auth()->user()?->stripe_id)) {
                $stripeSecret = (string) ($empresa->stripe_secret ?? config('services.stripe.secret'));
                if ($stripeSecret !== '') {
                    try {
                        $metodosPagamento = $this->clienteStripeService->getPaymentMethods(auth()->user()->stripe_id, $stripeSecret);
                    } catch (\Stripe\Exception\InvalidRequestException $e) {
                        \Log::warning('Stripe customer invalid or not found for auth user on site payment - attempting reconcile', [
                            'user_id' => auth()->id(),
                            'stripe_id' => auth()->user()?->stripe_id ?? null,
                            'exception' => $e->getMessage(),
                        ]);

                        try {
                            $novoStripeId = $this->clienteStripeService->garantirClienteValido(auth()->user()?->stripe_id ?? null, auth()->user()?->email ?? '', auth()->user()?->nome ?? 'Cliente', $stripeSecret);
                            if (!empty($novoStripeId) && $novoStripeId !== (auth()->user()?->stripe_id ?? '')) {
                                $this->usuarioService->updateUser(auth()->id(), ['stripe_id' => $novoStripeId]);
                            }

                            $metodosPagamento = $this->clienteStripeService->getPaymentMethods($novoStripeId ?? auth()->user()?->stripe_id, $stripeSecret);
                        } catch (\Exception $e2) {
                            \Log::warning('Erro ao reconciliar cliente Stripe ou ao listar métodos após reconcile (site)', ['exception' => $e2->getMessage()]);
                            $metodosPagamento = [];
                        }
                    } catch (\Exception $e) {
                        \Log::error('Erro ao consultar métodos de pagamento Stripe (site)', ['exception' => $e->getMessage()]);
                        $metodosPagamento = [];
                    }
                }
            }
        }

        $quantidadeDias = (int) ($reservaItem->quantidade_dias ?? 0);
        $tarifaBase = (float) ($reservaItem->tarifa_base ?? 0);
        $valorTarifaBase = $tarifaBase * $quantidadeDias;
        $valorOpcionais = (float) ($reservaItem->valor_opcionais ?? 0);
        if ($valorOpcionais <= 0 && $reservaItem->relationLoaded('opcionais')) {
            $valorOpcionais = (float) $reservaItem->opcionais->sum(function ($opcional) {
                return (float) ($opcional->pivot->preco_total ?? 0);
            });
        }
        $percentualDesconto = (float) ($reservaItem->percentual_desconto ?? 0);
        $valorDesconto = ($valorTarifaBase + $valorOpcionais) * ($percentualDesconto / 100);
        $valorTotalCalculado = max(0, ($valorTarifaBase + $valorOpcionais) - $valorDesconto);

        return view('site-apresentacao.pagamento', [
            'empresa' => $empresa,
            'reserva' => $reservaItem,
            'gatewayPadrao' => $gatewayPadrao,
            'gatewayNome' => $gatewayNome,
            'moedaPadrao' => $moedaPadrao,
            'metodosPagamento' => $metodosPagamento,
            'stripePublicKey' => $stripePublicKey,
            'urlVoltarCliente' => route('site.reserva.index', ['step' => 4]),
            'urlVoltarOpcionais' => route('site.reserva.index', ['step' => 3]),
            'urlVoltarVeiculo' => route('site.reserva.index', ['step' => 2]),
            'urlProcessar' => route('site.reserva.processarPagamento', ['reserva' => $reservaItem->id]),
            'urlAdicionarMetodo' => route('site.reserva.pagamento.metodo', ['reserva' => $reservaItem->id]),
            'urlLogin' => Route::has('login') ? route('login') : $this->adminLoginFallbackUrl(),
            'valorTarifaBase' => $valorTarifaBase,
            'valorOpcionais' => $valorOpcionais,
            'valorTotalCalculado' => $valorTotalCalculado,
        ]);
    }

    public function adicionarMetodoPagamento(Request $request, int $reserva): RedirectResponse
    {
        $empresa = $this->resolveEmpresaAtivaByHost($request);
        if (!$empresa) {
            return $this->defaultResponse();
        }

        $this->activateEmpresaContext($empresa);

        $reservaItem = Reserva::query()->find($reserva);
        if (!$reservaItem) {
            return redirect()
                ->route('site.reserva.index')
                ->withErrors(['reserva' => 'Reserva nao encontrada.']);
        }

        if (!auth()->check() || (int) auth()->id() !== (int) $reservaItem->user_id) {
            session()->put('url.intended', route('site.reserva.pagamento', ['reserva' => $reservaItem->id]));

            if (Route::has('login')) {
                return redirect()->route('login');
            }

            return redirect($this->adminLoginFallbackUrl());
        }

        $stripeToken = (string) $request->input('stripeToken', '');
        if ($stripeToken === '') {
            return redirect()
                ->back()
                ->withErrors(['stripeToken' => 'Token do cartao nao informado.']);
        }

        $stripeSecret = (string) ($empresa->stripe_secret ?? config('services.stripe.secret'));
        if ($stripeSecret === '') {
            return redirect()
                ->back()
                ->withErrors(['stripe' => 'Chave Stripe nao configurada para esta empresa.']);
        }

        $user = auth()->user();
        if (!$user) {
            return redirect()->back();
        }

        $stripeId = $user->stripe_id;
        if (!$stripeId) {
            try {
                $clienteStripe = $this->clienteStripeService->criarCliente($user->email, $user->nome ?? 'Cliente', $stripeSecret);
                $stripeId = (string) ($clienteStripe->id ?? '');
                if ($stripeId !== '') {
                    $this->usuarioService->updateUser($user->id, ['stripe_id' => $stripeId]);
                }
            } catch (\Throwable $e) {
                return redirect()
                    ->back()
                    ->withErrors(['stripe' => 'Nao foi possivel criar o cliente no Stripe.']);
            }
        }

        if ($stripeId === '') {
            return redirect()
                ->back()
                ->withErrors(['stripe' => 'Cliente Stripe nao encontrado.']);
        }

        try {
            $this->clienteStripeService->criarMetodoPagamento($stripeId, $stripeToken, $stripeSecret);
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withErrors(['stripe' => 'Nao foi possivel adicionar o metodo de pagamento.']);
        }

        return redirect()
            ->route('site.reserva.pagamento', ['reserva' => $reservaItem->id])
            ->with('success', 'Metodo de pagamento adicionado com sucesso.');
    }

    public function pagamentoMulta(Request $request, int $multa): View|RedirectResponse
    {
        $empresa = $this->resolveEmpresaAtivaByHost($request);
        if (!$empresa) {
            return $this->defaultResponse();
        }

        $this->activateEmpresaContext($empresa);

        $query = Multa::query()->with([
            'reserva.usuario',
            'veiculo.modelo',
            'cliente',
        ]);
        if (SchemaCache::hasColumn('multas', 'empresa_id')) {
            $query->where('empresa_id', (int) $empresa->id);
        }

        $multaItem = $query->find($multa);
        if (!$multaItem) {
            return redirect()
                ->route('site.reserva.index')
                ->withErrors(['multa' => 'Multa nao encontrada para este dominio.']);
        }

        if (!auth()->check()) {
            session()->put('url.intended', route('site.multa.pagamento', ['multa' => $multaItem->id]));

            if (Route::has('login')) {
                return redirect()->route('login');
            }

            return redirect($this->adminLoginFallbackUrl());
        }

        $usuarioMulta = $multaItem->reserva?->usuario ?? $multaItem->cliente;
        if (!$usuarioMulta || (int) auth()->id() !== (int) $usuarioMulta->id) {
            return redirect()
                ->route('site.reserva.index')
                ->withErrors(['multa' => 'Esta multa nao pertence ao usuario autenticado.']);
        }

        if (!empty($multaItem->pago_em)) {
            return redirect()
                ->route('site.reserva.index')
                ->with('success', 'Esta multa ja foi paga.');
        }

        $gatewayPadrao = $this->gatewayEmpresaConfigService->gateway($empresa);
        $moedaPadrao = $this->gatewayEmpresaConfigService->moeda($empresa);
        $gatewayNome = [
            'stripe' => 'Stripe',
            'mercado_livre' => 'Mercado Livre',
            'paypal' => 'PayPal',
        ][$gatewayPadrao] ?? strtoupper((string) $gatewayPadrao);

        $metodosPagamento = [];
        $stripePublicKey = null;
        if ($gatewayPadrao === 'stripe') {
            $stripePublicKey = (string) ($empresa->stripe_public_key ?? config('services.stripe.key') ?? env('STRIPE_KEY'));
            if (!empty(auth()->user()?->stripe_id)) {
                $stripeSecret = (string) ($empresa->stripe_secret ?? config('services.stripe.secret'));
                if ($stripeSecret !== '') {
                    try {
                        $metodosPagamento = $this->clienteStripeService->getPaymentMethods(auth()->user()->stripe_id, $stripeSecret);
                    } catch (\Throwable $e) {
                        \Log::warning('Erro ao consultar metodos Stripe para multa', [
                            'user_id' => auth()->id(),
                            'stripe_id' => auth()->user()?->stripe_id ?? null,
                            'exception' => $e->getMessage(),
                        ]);
                        $metodosPagamento = [];
                    }
                }
            }
        }

        $valorMulta = (float) ($multaItem->valor_final ?? $multaItem->valor_original ?? 0);

        return view('site-apresentacao.multa-pagamento', [
            'empresa' => $empresa,
            'multa' => $multaItem,
            'gatewayPadrao' => $gatewayPadrao,
            'gatewayNome' => $gatewayNome,
            'moedaPadrao' => $moedaPadrao,
            'metodosPagamento' => $metodosPagamento,
            'stripePublicKey' => $stripePublicKey,
              'urlVoltarPortal' => route('reserva.minhas'),
            'urlProcessar' => route('site.multa.processarPagamento', ['multa' => $multaItem->id]),
            'urlAdicionarMetodo' => route('site.multa.pagamento.metodo', ['multa' => $multaItem->id]),
            'urlLogin' => Route::has('login') ? route('login') : $this->adminLoginFallbackUrl(),
            'valorMulta' => $valorMulta,
        ]);
    }

    public function adicionarMetodoPagamentoMulta(Request $request, int $multa): RedirectResponse
    {
        $empresa = $this->resolveEmpresaAtivaByHost($request);
        if (!$empresa) {
            return $this->defaultResponse();
        }

        $this->activateEmpresaContext($empresa);

        $multaItem = Multa::query()->with(['reserva.usuario', 'cliente']);
        if (SchemaCache::hasColumn('multas', 'empresa_id')) {
            $multaItem->where('empresa_id', (int) $empresa->id);
        }
        $multaItem = $multaItem->find($multa);

          if (!$multaItem) {
              return redirect()
                  ->route('site.reserva.index')
                  ->withErrors(['multa' => 'Multa nao encontrada.']);
          }

        if (!auth()->check()) {
            session()->put('url.intended', route('site.multa.pagamento', ['multa' => $multaItem->id]));

            if (Route::has('login')) {
                return redirect()->route('login');
            }

            return redirect($this->adminLoginFallbackUrl());
        }

        $usuarioMulta = $multaItem->reserva?->usuario ?? $multaItem->cliente;
        if (!$usuarioMulta || (int) auth()->id() !== (int) $usuarioMulta->id) {
            return redirect()
                ->route('site.reserva.index')
                ->withErrors(['multa' => 'Esta multa nao pertence ao usuario autenticado.']);
        }

        $stripeToken = (string) $request->input('stripeToken', '');
        if ($stripeToken === '') {
            return redirect()
                ->back()
                ->withErrors(['stripeToken' => 'Token do cartao nao informado.']);
        }

        $stripeSecret = (string) ($empresa->stripe_secret ?? config('services.stripe.secret'));
        if ($stripeSecret === '') {
            return redirect()
                ->back()
                ->withErrors(['stripe' => 'Chave Stripe nao configurada para esta empresa.']);
        }

        $user = auth()->user();
        if (!$user) {
            return redirect()->back();
        }

        $stripeId = $user->stripe_id;
        if (!$stripeId) {
            try {
                $clienteStripe = $this->clienteStripeService->criarCliente($user->email, $user->nome ?? 'Cliente', $stripeSecret);
                $stripeId = (string) ($clienteStripe->id ?? '');
                if ($stripeId !== '') {
                    $this->usuarioService->updateUser($user->id, ['stripe_id' => $stripeId]);
                }
            } catch (\Throwable $e) {
                return redirect()
                    ->back()
                    ->withErrors(['stripe' => 'Nao foi possivel criar o cliente no Stripe.']);
            }
        }

        if ($stripeId === '') {
            return redirect()
                ->back()
                ->withErrors(['stripe' => 'Cliente Stripe nao encontrado.']);
        }

        try {
            $this->clienteStripeService->criarMetodoPagamento($stripeId, $stripeToken, $stripeSecret);
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withErrors(['stripe' => 'Nao foi possivel adicionar o metodo de pagamento.']);
        }

        return redirect()
            ->route('site.multa.pagamento', ['multa' => $multaItem->id])
            ->with('success', 'Metodo de pagamento adicionado com sucesso.');
    }

    public function processarPagamentoMulta(Request $request, int $multa): RedirectResponse
    {
        $empresa = $this->resolveEmpresaAtivaByHost($request);
        if (!$empresa) {
            return $this->defaultResponse();
        }

        $this->activateEmpresaContext($empresa);

        $query = Multa::query()->with(['reserva.usuario', 'veiculo.modelo', 'cliente']);
        if (SchemaCache::hasColumn('multas', 'empresa_id')) {
            $query->where('empresa_id', (int) $empresa->id);
        }

        $multaItem = $query->find($multa);
        if (!$multaItem) {
            return redirect()
                ->route('site.reserva.index')
                ->withErrors(['multa' => 'Multa nao encontrada.']);
        }

        if (!auth()->check()) {
            session()->put('url.intended', route('site.multa.pagamento', ['multa' => $multaItem->id]));

            if (Route::has('login')) {
                return redirect()->route('login');
            }

            return redirect($this->adminLoginFallbackUrl());
        }

        $usuarioMulta = $multaItem->reserva?->usuario ?? $multaItem->cliente;
          if (!$usuarioMulta || (int) auth()->id() !== (int) $usuarioMulta->id) {
              return redirect()
                  ->route('reserva.minhas')
                  ->withErrors(['multa' => 'Esta multa nao pertence ao usuario autenticado.']);
          }

          if (!empty($multaItem->pago_em)) {
              return redirect()
                  ->route('reserva.minhas.multa.show', ['id' => $multaItem->id])
                  ->with('success', 'Esta multa ja foi paga.');
          }

        $gateway = $this->gatewayEmpresaConfigService->gateway($empresa);
        $currency = $this->gatewayEmpresaConfigService->moeda($empresa);
        $credenciais = $this->gatewayEmpresaConfigService->credenciais($empresa, $gateway);
        $paymentType = (string) $request->input('payment_type', '');
        $paymentMethodId = (string) $request->input('payment_method_id', '');

        if ($gateway === 'mercado_livre' && $paymentType === 'pix' && $paymentMethodId === '') {
            $paymentMethodId = 'pix';
        }

        if ($gateway === 'stripe' && empty($request->payment_method)) {
            return redirect()
                ->back()
                ->withErrors(['payment_method' => 'Selecione um metodo de pagamento para Stripe.']);
        }

        $valorMulta = (float) ($multaItem->valor_final ?? $multaItem->valor_original ?? 0);
        if ($valorMulta <= 0) {
            return redirect()
                ->back()
                ->withErrors(['pagamento' => 'Valor invalido para pagamento da multa.']);
        }

        $salvarMetodo = (string) $request->input('salvar_metodo', 'S');
        $customerId = (string) (auth()->user()?->stripe_id ?? '');
        if ($gateway === 'stripe' && strtoupper($salvarMetodo) === 'N') {
            $customerId = '';
        }

        $payload = array_merge([
            'multa_id' => $multaItem->id,
            'valor' => $valorMulta,
            'currency' => $currency,
            'payment_method' => (string) $request->input('payment_method', ''),
            'customer_id' => $customerId,
            'descricao' => 'Pagamento da multa #' . $multaItem->id,
            'payer_email' => (string) (auth()->user()?->email ?? ''),
            'payment_method_id' => $paymentMethodId,
            'token' => (string) $request->input('token', ''),
            'installments' => (int) $request->input('installments', 1),
        ], $credenciais);

        try {
            $resultado = $this->gerenciadorDePagamentos
                ->resolver($gateway)
                ->cobrarPagamento($payload);
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withErrors(['pagamento' => 'Falha ao processar o pagamento. Tente novamente.']);
        }

          if (
              $gateway === 'mercado_livre'
              && ($resultado['status'] ?? '') === 'pending'
              && ($resultado['payment_method_id'] ?? '') === 'pix'
          ) {
              return redirect()
                  ->route('site.multa.pagamento', ['multa' => $multaItem->id])
                  ->with('success', 'Pix gerado. Escaneie o QR Code para concluir o pagamento.')
                  ->with('pix', $resultado);
          }

        if (($resultado['status'] ?? 'error') !== 'success') {
            return redirect()
                ->back()
                ->withErrors(['pagamento' => $resultado['message'] ?? 'Pagamento nao efetuado.']);
        }

        $multaItem->update([
            'forma_pagamento'      => $gateway === 'mercado_livre' ? 'pix' : 'cartao_credito',
            'referencia_pagamento' => $resultado['transaction_id'] ?? $resultado['id'] ?? null,
            'pago_em'              => now(),
            'pago_por_user_id'     => Auth::id(),
            'status_multa'         => 'paga',
        ]);

        Transacao::create([
            'user_id' => $multaItem->cliente_id ?: ($multaItem->reserva?->user_id ?? Auth::id()),
            'pago_por_user_id' => Auth::id(),
            'reserva_id' => $multaItem->reserva_id,
            'valor' => $valorMulta,
            'moeda' => strtoupper((string) ($resultado['currency'] ?? $currency)),
            'metodo_pagamento' => $gateway,
            'gateway_transaction_id' => $resultado['transaction_id'] ?? null,
            'status' => $resultado['raw_status'] ?? 'success',
            'info_pagamento' => [
                'tipo' => 'multa',
                'multa_id' => $multaItem->id,
                'gateway' => $gateway,
                'resultado' => $resultado,
            ],
        ]);

        if (!empty($multaItem->reserva_id)) {
            $this->reservaService->adicionaLog((int) $multaItem->reserva_id, (int) Auth::id(), json_encode([
                'acao' => 'Pagamento de multa efetuado',
                'multa_id' => $multaItem->id,
                'valor_total' => $valorMulta,
                'gateway' => $gateway,
                'usuario_pagador_id' => Auth::id(),
                'status' => $resultado['raw_status'] ?? 'success',
                'data_pagamento' => now()->format('Y-m-d H:i:s'),
            ]));
        }

          return redirect()
              ->route('reserva.minhas.multa.show', ['id' => $multaItem->id])
              ->with('success', 'Pagamento da multa efetuado com sucesso.');
      }

    public function processarPagamento(Request $request, int $reserva): RedirectResponse
    {
        $empresa = $this->resolveEmpresaAtivaByHost($request);
        if (!$empresa) {
            return $this->defaultResponse();
        }

        $this->activateEmpresaContext($empresa);

        $query = Reserva::query();
        if (SchemaCache::hasColumn('reservas', 'empresa_id')) {
            $query->where('empresa_id', (int) $empresa->id);
        }

        $reservaItem = $query->find($reserva);
        if (!$reservaItem) {
            return redirect()
                ->route('site.reserva.index')
                ->withErrors(['reserva' => 'Reserva nao encontrada.']);
        }

        if (!auth()->check() || (int) auth()->id() !== (int) $reservaItem->user_id) {
            session()->put('url.intended', route('site.reserva.pagamento', ['reserva' => $reservaItem->id]));

            if (Route::has('login')) {
                return redirect()->route('login');
            }

            return redirect($this->adminLoginFallbackUrl());
        }

        if (isset($reservaItem->status_reserva_id) && (int) $reservaItem->status_reserva_id !== StatusReservaEnum::Pendente->value) {
            return redirect()
                ->route('site.reserva.success', ['reserva' => $reservaItem->id])
                ->withErrors(['reserva' => 'Reserva nao pode ser paga no status atual.']);
        }

        $gateway = $this->gatewayEmpresaConfigService->gateway($empresa);
        $currency = $this->gatewayEmpresaConfigService->moeda($empresa);
        $credenciais = $this->gatewayEmpresaConfigService->credenciais($empresa, $gateway);
        $paymentType = (string) $request->input('payment_type', '');
        $paymentMethodId = (string) $request->input('payment_method_id', '');

        if ($gateway === 'mercado_livre' && $paymentType === 'pix' && $paymentMethodId === '') {
            $paymentMethodId = 'pix';
        }

        if ($gateway === 'stripe' && empty($request->payment_method)) {
            return redirect()
                ->back()
                ->withErrors(['payment_method' => 'Selecione um metodo de pagamento para Stripe.']);
        }

        $reservaItem->load('opcionais');
        $quantidadeDias = (int) ($reservaItem->quantidade_dias ?? 0);
        $tarifaBase = (float) ($reservaItem->tarifa_base ?? 0);
        $valorTarifaBase = $tarifaBase * $quantidadeDias;
        $valorOpcionais = (float) ($reservaItem->valor_opcionais ?? 0);
        if ($valorOpcionais <= 0 && $reservaItem->opcionais->isNotEmpty()) {
            $valorOpcionais = (float) $reservaItem->opcionais->sum(function ($opcional) {
                return (float) ($opcional->pivot->preco_total ?? 0);
            });
        }
        $percentualDesconto = (float) ($reservaItem->percentual_desconto ?? 0);
        $valorDesconto = ($valorTarifaBase + $valorOpcionais) * ($percentualDesconto / 100);
        $valorTotal = max(0, ($valorTarifaBase + $valorOpcionais) - $valorDesconto);
        $salvarMetodo = (string) $request->input('salvar_metodo', 'S');
        $customerId = (string) (auth()->user()?->stripe_id ?? '');
        if ($gateway === 'stripe' && strtoupper($salvarMetodo) === 'N') {
            $customerId = '';
        }

        $payload = array_merge([
            'reserva_id' => $reservaItem->id,
            'valor' => $valorTotal,
            'currency' => $currency,
            'payment_method' => (string) $request->input('payment_method', ''),
            'customer_id' => $customerId,
            'descricao' => 'Pagamento da reserva #' . $reservaItem->id,
            'payer_email' => (string) (auth()->user()?->email ?? ''),
            'payment_method_id' => $paymentMethodId,
            'token' => (string) $request->input('token', ''),
            'installments' => (int) $request->input('installments', 1),
        ], $credenciais);

        try {
            $resultado = $this->gerenciadorDePagamentos
                ->resolver($gateway)
                ->cobrarPagamento($payload);
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withErrors(['pagamento' => 'Falha ao processar o pagamento. Tente novamente.']);
        }

        if (
            $gateway === 'mercado_livre'
            && ($resultado['status'] ?? '') === 'pending'
            && ($resultado['payment_method_id'] ?? '') === 'pix'
        ) {
            return redirect()
                ->route('site.reserva.pagamento', ['reserva' => $reservaItem->id])
                ->with('success', 'Pix gerado. Escaneie o QR Code para concluir o pagamento.')
                ->with('pix', $resultado);
        }

        if (($resultado['status'] ?? 'error') !== 'success') {
            return redirect()
                ->back()
                ->withErrors(['pagamento' => $resultado['message'] ?? 'Pagamento nao efetuado.']);
        }

        DB::table('reservas')->where('id', $reservaItem->id)
            ->update([
                'status_reserva_id' => StatusReservaEnum::Confirmada->value,
                'pagamento_efetuado' => 'S',
                'valor_total_tarifa_base' => $valorTarifaBase,
                'valor_opcionais' => $valorOpcionais,
                'valor_total_com_desconto' => $valorDesconto,
                'percentual_desconto' => $percentualDesconto,
                'valor_total_final' => $valorTotal,
            ]);

        Transacao::create([
            'user_id' => $reservaItem->user_id,
            'pago_por_user_id' => Auth::id(),
            'reserva_id' => $reservaItem->id,
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
        $this->reservaService->adicionaLog((int) $reservaItem->id, (int) Auth::id(), $json);

        $nfse = $this->nfseService->emitirParaReserva($reservaItem);
        $nfseWarning = null;
        if ($nfse === null) {
            $nfseWarning = 'Emissao de NFS-e não configurada para esta empresa.';
        } elseif (($nfse->status ?? '') === 'erro') {
            $nfseWarning = $this->nfseService->mensagemErro($nfse) ?? 'Falha ao emitir NFS-e.';
        }

        session()->put('reserva.filtro', [
            'user_id' => [
                'valor' => (int) Auth::id(),
                'typeWhere' => 'where',
            ],
        ]);

        if (auth()->user()?->hasPermissao('reserva.index')) {
            return redirect()
                ->route('reserva.index')
                ->with('success', 'Pagamento efetuado com sucesso. Veja suas reservas abaixo.')
                ->with('warning', $nfseWarning);
        }

        return redirect()
            ->route('site.reserva.success', ['reserva' => $reservaItem->id])
            ->with('success', 'Pagamento efetuado com sucesso.')
            ->with('warning', $nfseWarning);
    }

    public function adminEntry(Request $request): View|RedirectResponse
    {
        $empresa = $this->resolveEmpresaAtivaByHost($request);
        if (!$empresa) {
            return $this->defaultResponse();
        }

        $cores = is_array($empresa->site_apresentacao_cores) ? $empresa->site_apresentacao_cores : [];

        return view('site-apresentacao.admin-entry', [
            'empresa' => $empresa,
            'corFundo' => $this->colorValue($cores, 'admin_fundo', $empresa->cor_secundaria ?: '#193C68'),
            'corTexto' => $this->colorValue($cores, 'admin_texto', '#FFFFFF'),
            'corCard' => $this->colorValue($cores, 'form_fundo', '#FFFFFF'),
            'corTextoCard' => $this->colorValue($cores, 'form_texto', '#0F172A'),
            'corBotaoFundo' => $this->colorValue($cores, 'botao_primario_fundo', $empresa->cor_terciaria ?: '#E7B93A'),
            'corBotaoTexto' => $this->colorValue($cores, 'botao_primario_texto', '#111827'),
            'loginUrl' => Route::has('login') ? route('login') : $this->adminLoginFallbackUrl(),
        ]);
    }

    private function resolveEmpresaAtivaByHost(Request $request): ?Empresa
    {
        $host = DomainNormalizer::normalize($request->getHost());
        if (!$host) {
            return null;
        }

        return Empresa::query()
            ->withoutGlobalScopes()
            ->where('ativo', 'S')
            ->whereNotNull('dominio')
            ->get()
            ->first(function (Empresa $empresa) use ($host): bool {
                return DomainNormalizer::normalize($empresa->dominio) === $host
                    && (($empresa->site_apresentacao_ativo ?? 'S') === 'S');
            });
    }

    private function activateEmpresaContext(Empresa $empresa): void
    {
        app(EmpresaContext::class)->setEmpresaId((int) $empresa->id);
    }

    private function estacoesForEmpresa(?string $tipoOperacao = null): array
    {
        $query = Estacao::query()
            ->where('ativo', 'S')
            ->orderBy('nome');

        if ($tipoOperacao === 'checkin' && SchemaCache::hasColumn('estacoes', 'checkin')) {
            $query->where(function ($subQuery) {
                $subQuery->where('checkin', 'S')->orWhere('checkin', 1);
            });
        }

        if ($tipoOperacao === 'checkout' && SchemaCache::hasColumn('estacoes', 'checkout')) {
            $query->where(function ($subQuery) {
                $subQuery->where('checkout', 'S')->orWhere('checkout', 1);
            });
        }

        return $query->get(['id', 'nome', 'cidade', 'checkin', 'checkout'])
            ->map(function (Estacao $estacao): array {
                return [
                    'id' => (int) $estacao->id,
                    'nome' => (string) $estacao->nome,
                    'cidade' => (string) ($estacao->cidade ?? ''),
                    'checkin' => (string) ($estacao->checkin ?? 'N'),
                    'checkout' => (string) ($estacao->checkout ?? 'N'),
                ];
            })
            ->values()
            ->all();
    }

    private function prefillReservaCompleta(array $prefill): bool
    {
        $estacaoRetiradaId = (int) ($prefill['estacao_retirada_id'] ?? 0);
        $estacaoDevolucaoId = (int) ($prefill['estacao_devolucao_id'] ?? 0);
        $dataInicio = (string) ($prefill['data_inicio'] ?? '');
        $dataFim = (string) ($prefill['data_fim'] ?? '');

        if ($estacaoRetiradaId <= 0 || $estacaoDevolucaoId <= 0 || trim($dataInicio) === '' || trim($dataFim) === '') {
            return false;
        }

        return $this->dataInicioValida($dataInicio)
            && $this->dataFimMinimaValida($dataInicio, $dataFim);
    }

    private function gruposFrotaAtivos(?int $estacaoRetiradaId = null, ?string $dataInicio = null, ?string $dataFim = null): array
    {
        $temDisponibilidade = $estacaoRetiradaId && !empty($dataInicio) && !empty($dataFim);
        return GrupoFrota::query()
            ->where('ativo', 'S')
            ->orderBy('nome')
            ->with(['caracteristicas', 'modelos'])
            ->get(['id', 'nome', 'valor_diario', 'imagem', 'imagens'])
            ->map(function (GrupoFrota $grupo) use ($temDisponibilidade, $estacaoRetiradaId, $dataInicio, $dataFim): array {
                $qtdVeiculosDisponiveis = null;
                if ($temDisponibilidade) {
                    $qtdVeiculosDisponiveis = $this->veiculoService->getQuantidadeVeiculosDisponiveisPorGrupoEEstacaoEPeriodo(
                        (int) $grupo->id,
                        (int) $estacaoRetiradaId,
                        (string) $dataInicio,
                        (string) $dataFim
                    );
                }

                return [
                    'id' => (int) $grupo->id,
                    'nome' => (string) $grupo->nome,
                    'valor_diario' => (float) $grupo->valor_diario,
                    'imagem_url' => $this->grupoImagemUrl($grupo),
                    'imagens_urls' => $this->grupoImagensUrls($grupo),
                    'qtd_veiculos_disponiveis' => $qtdVeiculosDisponiveis,
                    'caracteristicas' => $grupo->caracteristicas
                        ? $grupo->caracteristicas->map(function ($caracteristica): array {
                            return [
                                'id' => (int) ($caracteristica->id ?? 0),
                                'nome' => (string) ($caracteristica->nome ?? ''),
                                'valor' => (string) ($caracteristica->pivot->valor ?? ''),
                                'imagem_url' => $caracteristica->imagem_url ?? null,
                            ];
                        })->values()->all()
                        : [],
                    'modelos' => $grupo->modelos
                        ? $grupo->modelos->map(function ($modelo): array {
                            return [
                                'id' => (int) ($modelo->id ?? 0),
                                'nome' => (string) ($modelo->nome ?? ''),
                                'imagem_url' => $modelo->imagem_url ?? $modelo->imagem ?? null,
                            ];
                        })->values()->all()
                        : [],
                ];
            })
            ->values()
            ->all();
    }

    private function grupoImagemUrl(GrupoFrota $grupo): ?string
    {
        $path = is_string($grupo->imagem) ? trim($grupo->imagem) : '';
        if ($path === '') {
            $imagens = $this->grupoImagensUrls($grupo);
            return $imagens[0] ?? null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, '/')) {
            return url($path);
        }

        if (Storage::disk('public')->exists($path)) {
            return Storage::url($path);
        }

        return null;
    }

    private function grupoImagensUrls(GrupoFrota $grupo): array
    {
        $imagens = is_array($grupo->imagens ?? null) ? $grupo->imagens : [];
        $urls = [];
        foreach ($imagens as $imagem) {
            $path = is_string($imagem) ? trim($imagem) : '';
            if ($path === '') {
                continue;
            }
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                $urls[] = $path;
                continue;
            }
            if (str_starts_with($path, '/')) {
                $urls[] = url($path);
                continue;
            }
            if (Storage::disk('public')->exists($path)) {
                $urls[] = Storage::url($path);
            }
        }

        return $urls;
    }

    private function normalizeTipoOpcionais(array $tipoOpcionais): array
    {
        return collect($tipoOpcionais)
            ->map(function ($item): array {
                $tipo = $item['tipo'] ?? null;
                $opcionais = collect($item['opcionais'] ?? [])
                    ->map(function ($opcional): array {
                        return [
                            'id' => (int) $opcional->id,
                            'nome' => (string) $opcional->nome,
                            'tipo_cobranca' => (string) ($opcional->tipo_cobranca ?? ''),
                            'preco_por_dia' => $opcional->preco_por_dia !== null ? (float) $opcional->preco_por_dia : null,
                            'preco_integral' => $opcional->preco_integral !== null ? (float) $opcional->preco_integral : null,
                        ];
                    })
                    ->values()
                    ->all();

                return [
                    'tipo' => [
                        'id' => (int) ($tipo->id ?? 0),
                        'nome' => (string) ($tipo->nome ?? ''),
                    ],
                    'opcionais' => $opcionais,
                ];
            })
            ->values()
            ->all();
    }

    private function resolveCupomValido(string $codigo): ?CupomDesconto
    {
        $codigoNormalizado = trim($codigo);
        if ($codigoNormalizado === '') {
            return null;
        }

        $cupom = $this->cupomDescontoService->verificarCupom($codigoNormalizado);
        if (!$cupom) {
            return null;
        }

        if (isset($cupom->ativo) && (string) $cupom->ativo !== 'S') {
            return null;
        }

        return $cupom;
    }

    private function parseOpcionaisPayload(string $rawPayload): array
    {
        $payload = trim($rawPayload);
        if ($payload === '') {
            return [];
        }

        try {
            $decoded = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                'opcionais_payload' => 'Formato invalido para opcionais.',
            ]);
        }

        if (!is_array($decoded)) {
            return [];
        }

        $result = [];
        foreach ($decoded as $item) {
            if (!is_array($item)) {
                continue;
            }

            $id = (int) ($item['id'] ?? 0);
            $quantidade = (int) ($item['quantidade'] ?? 0);

            if ($id <= 0 || $quantidade <= 0) {
                continue;
            }

            $result[$id] = [
                'id' => $id,
                'quantidade' => $quantidade,
            ];
        }

        return array_values($result);
    }

    private function buildOpcionaisReserva(GrupoFrota $grupo, array $opcionaisSelecionados): array
    {
        if (empty($opcionaisSelecionados)) {
            return [];
        }

        $permitidos = $grupo
            ->opcionais()
            ->pluck('opcionais.id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $idsSelecionados = array_map(fn ($item) => (int) $item['id'], $opcionaisSelecionados);
        $idsInvalidos = array_values(array_diff($idsSelecionados, $permitidos));

        if (!empty($idsInvalidos)) {
            throw ValidationException::withMessages([
                'opcionais_payload' => 'Um ou mais opcionais nao pertencem ao grupo de frota selecionado.',
            ]);
        }

        $opcionais = Opcional::query()
            ->whereIn('id', $idsSelecionados)
            ->get(['id', 'nome', 'tipo_cobranca', 'preco_por_dia', 'preco_integral'])
            ->keyBy('id');

        $result = [];
        foreach ($opcionaisSelecionados as $selecionado) {
            $opcional = $opcionais->get((int) $selecionado['id']);
            if (!$opcional) {
                continue;
            }

            $result[] = [
                'id' => (int) $opcional->id,
                'nome' => (string) $opcional->nome,
                'tipo_cobranca' => (string) ($opcional->tipo_cobranca ?? ''),
                'preco_por_dia' => (float) ($opcional->preco_por_dia ?? 0),
                'preco_integral' => (float) ($opcional->preco_integral ?? 0),
                'quantidade' => (int) $selecionado['quantidade'],
            ];
        }

        return $result;
    }

    private function resolveReservaUser(array $validated, Empresa $empresa, bool &$created = false): User
    {
        $email = mb_strtolower(trim((string) $validated['email']));

        $query = User::query()->whereRaw('LOWER(email) = ?', [$email]);
        if (SchemaCache::hasColumn('users', 'empresa_id')) {
            $query->where('empresa_id', (int) $empresa->id);
        }

        $existingUser = $query->first();
        if ($existingUser instanceof User) {
            throw ValidationException::withMessages([
                'email' => 'Este e-mail ja possui cadastro. Faca login para continuar.',
            ]);
        }

        $roleClienteId = $this->regraService->getClienteRoleId();
        if (!$roleClienteId) {
            throw ValidationException::withMessages([
                'reserva' => 'Nao existe uma regra Cliente configurada para concluir a reserva.',
            ]);
        }

        $dadosUsuario = [
            'nome' => (string) $validated['nome'],
            'email' => (string) $validated['email'],
            'sexo' => (string) $validated['sexo'],
            'data_nascimento' => (string) $validated['data_nascimento'],
            'local_nascimento' => (string) $validated['local_nascimento'],
            'telefone' => (string) $validated['telefone'],
            'celular' => (string) $validated['celular'],
            'pais_sigla' => (string) $validated['pais_sigla'],
            'zip_code' => (string) $validated['zip_code'],
            'cidade' => (string) $validated['cidade'],
            'endereco' => (string) $validated['endereco'],
            'numero_identificacao' => (string) $validated['numero_identificacao'],
            'numero_carta_conducao' => (string) $validated['numero_carta_conducao'],
            'local_emissao_carta_conducao' => (string) $validated['local_emissao_carta_conducao'],
            'pais_emissao_carta_conducao' => (string) $validated['pais_emissao_carta_conducao'],
            'data_emissao_carta_conducao' => (string) $validated['data_emissao_carta_conducao'],
            'data_validade_carta_conducao' => $validated['data_validade_carta_conducao'] ?? null,
            'role_id' => (int) $roleClienteId,
            'acesso_todas_estacoes' => true,
            'password' => 'Tmp@' . random_int(100000, 999999),
        ];

        try {
            $clienteStripe = $this->clienteStripeService->criarCliente(
                (string) $dadosUsuario['email'],
                (string) $dadosUsuario['nome']
            );
            $dadosUsuario['stripe_id'] = (string) ($clienteStripe->id ?? '');
        } catch (\Throwable) {
            // Reserva publica nao deve falhar apenas por indisponibilidade do gateway.
        }

        try {
            $user = $this->usuarioService->createUser($dadosUsuario);
            $created = $user instanceof User;

            return $user;
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                'email' => 'Nao foi possivel criar o usuario da reserva com o e-mail informado.',
            ]);
        }
    }

    private function validateLikeAdminStoreRequest(array $payload, array $opcionaisReserva): void
    {
        $storeRules = (new StoreReservaRequest())->rules();

        $dados = [
            'estacao_retirada_id' => $payload['estacao_retirada_id'],
            'data_inicio' => $payload['data_inicio'],
            'estacao_devolucao_id' => $payload['estacao_devolucao_id'],
            'data_fim' => $payload['data_fim'],
            'veiculo_id' => null,
            'modelo_id' => $payload['modelo_id'],
            'cupom_desconto' => $payload['cupom_desconto']['codigo'] ?? null,
            'opcionais' => array_map(fn ($item) => (int) $item['id'], $opcionaisReserva),
            'user_id' => $payload['user_id'],
        ];

        Validator::make($dados, $storeRules)->validate();
    }

    private function storeRules(bool $requireModelo = true, ?bool $requireCliente = null): array
    {
        $requireCliente = $requireCliente ?? !auth()->check();
        $required = fn (array $rules) => $requireCliente ? array_merge(['required'], $rules) : array_merge(['nullable'], $rules);

        return [
            'estacao_retirada_id' => ['required', 'integer', 'exists:estacoes,id'],
            'data_inicio' => [
                'required',
                'date',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (!$this->dataInicioValida((string) $value)) {
                        $fail('A retirada nao pode ser antes de agora.');
                    }
                },
            ],
            'estacao_devolucao_id' => ['required', 'integer', 'exists:estacoes,id'],
            'data_fim' => [
                'required',
                'date',
                'after:data_inicio',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $inicio = (string) request()->input('data_inicio', '');
                    if ($inicio !== '' && !$this->dataFimMinimaValida($inicio, (string) $value)) {
                        $fail('A devolucao deve ser ao menos 1 dia apos a retirada.');
                    }
                },
            ],
            'grupo_frota_id' => ['required', 'integer', 'exists:grupos_frota,id'],
            'modelo_id' => array_merge([$requireModelo ? 'required' : 'nullable'], ['integer', 'exists:modelos,id']),
            'cupom_codigo' => ['nullable', 'string', 'max:255'],
            'opcionais_payload' => ['nullable', 'string'],
            'nome' => $required(['string', 'max:255']),
            'email' => $required(['string', 'max:150', 'email']),
            'sexo' => $required(['string', Rule::in(['M', 'F', 'O'])]),
            'data_nascimento' => $required(['date', 'before:' . Carbon::now()->subYears(16)->format('Y-m-d')]),
            'local_nascimento' => $required(['string', 'max:100']),
            'telefone' => $required(['string', 'max:20']),
            'celular' => $required(['string', 'max:20', 'regex:/^\(\d{2}\) \d{5}-\d{4}$/']),
            'pais_sigla' => $required(['string', 'max:2']),
            'zip_code' => $required(['string', 'max:20']),
            'cidade' => $required(['string', 'max:100']),
            'endereco' => $required(['string', 'max:255']),
            'numero_identificacao' => $required(['string', 'max:20']),
            'numero_carta_conducao' => $required(['string', 'max:20']),
            'local_emissao_carta_conducao' => $required(['string', 'max:100']),
            'pais_emissao_carta_conducao' => $required(['string', 'max:2']),
            'data_emissao_carta_conducao' => $required(['date', 'before:' . Carbon::now()->format('Y-m-d')]),
            'data_validade_carta_conducao' => ['nullable', 'date'],
        ];
    }

    private function calcularQuantidadeDias(string $dataInicio, string $dataFim): int
    {
        $inicio = strtotime($dataInicio);
        $fim = strtotime($dataFim);
        if ($inicio === false || $fim === false || $fim <= $inicio) {
            return 1;
        }

        return (int) max(1, ceil(($fim - $inicio) / 86400));
    }

    private function buildDateTimeLocal(mixed $date, mixed $time): ?string
    {
        $data = trim((string) ($date ?? ''));
        $hora = trim((string) ($time ?? ''));
        if ($data === '' || $hora === '') {
            return null;
        }

        try {
            return Carbon::parse($data . ' ' . $hora)->format('Y-m-d\TH:i');
        } catch (\Throwable) {
            return null;
        }
    }

    private function normalizeInt(mixed $value): ?int
    {
        $int = filter_var($value, FILTER_VALIDATE_INT);

        return $int !== false ? (int) $int : null;
    }

    private function dataInicioValida(string $dataInicio): bool
    {
        $inicio = $this->parseLocalDateTime($dataInicio);
        if (!$inicio) {
            return false;
        }

        $agora = Carbon::now($this->resolveTimezone())->startOfMinute();
        return $inicio->greaterThanOrEqualTo($agora);
    }

    private function dataFimMinimaValida(string $dataInicio, string $dataFim): bool
    {
        $inicio = $this->parseLocalDateTime($dataInicio);
        $fim = $this->parseLocalDateTime($dataFim);
        if (!$inicio || !$fim) {
            return false;
        }

        return $fim->greaterThanOrEqualTo($inicio->copy()->addDay());
    }

    private function parseLocalDateTime(string $value): ?Carbon
    {
        $tz = $this->resolveTimezone();
        try {
            return Carbon::createFromFormat('Y-m-d\TH:i', $value, $tz);
        } catch (\Throwable) {
            try {
                return Carbon::parse($value, $tz);
            } catch (\Throwable) {
                return null;
            }
        }
    }

    private function resolveTimezone(): string
    {
        return 'America/Sao_Paulo';
    }

    private function colorValue(array $cores, string $key, string $default): string
    {
        $value = $cores[$key] ?? null;
        if (!is_string($value)) {
            return $default;
        }

        $trimmed = trim($value);
        if ($trimmed === '') {
            return $default;
        }

        if ($trimmed === 'transparent') {
            return 'transparent';
        }

        return preg_match('/^#[A-Fa-f0-9]{6}$/', $trimmed) ? strtoupper($trimmed) : $default;
    }

    private function simboloMoeda(string $moeda): string
    {
        return match (strtoupper($moeda)) {
            'USD' => '$',
            'EUR' => 'EUR',
            default => 'R$',
        };
    }

    private function defaultResponse(): RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->route('home');
        }

        if (Route::has('site.apresentacao')) {
            return redirect()->route('site.apresentacao');
        }

        if (Route::has('login')) {
            return redirect()->route('login');
        }

        return redirect($this->adminHomeFallbackUrl());
    }

    private function adminHomeFallbackUrl(): string
    {
        $prefix = trim((string) config('app.admin_prefix', 'admin'), '/');
        $path = $prefix === '' ? '/home' : '/' . $prefix . '/home';

        return url($path);
    }

    private function adminLoginFallbackUrl(): string
    {
        $prefix = trim((string) config('app.admin_prefix', 'admin'), '/');
        $path = $prefix === '' ? '/login' : '/' . $prefix . '/login';

        return url($path);
    }
}

