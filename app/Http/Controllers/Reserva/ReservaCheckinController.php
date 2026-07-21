<?php

namespace App\Http\Controllers\Reserva;

use App\Actions\Reserva\Checkin\Exibir\ExibirCheckinAction;
use App\Actions\Reserva\Checkin\Realizar\RealizarCheckinAction;
use App\Actions\Reserva\Checkin\Finalizar\FinalizarCheckinAction;
use App\Actions\Reserva\Checkin\Contrato\Exibir\ExibirContratoCheckinAction;
use App\DTO\BreadCrumbItem;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reserva\AssinarContratoRequest;
use App\Http\Requests\Reserva\CriarContratoCheckInRequest;
use App\Models\ContratoCheckin;
use App\Models\Reserva;
use App\Models\Veiculo;
use App\Services\BreadcrumbService;
use App\Services\CategoriaDanoService;
use App\Services\ContratoCheckinService;
use App\Services\ModeloService;
use App\Services\Reserva\ReservaResolver;
use App\Services\ReservaService;
use App\Services\VeiculoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Services\UpgradeVeiculoService;

class ReservaCheckinController extends Controller
{
    use ReservaHelpersTrait;

    public function __construct(
        protected ReservaService $reservaService,
        protected BreadcrumbService $breadcrumbService,
        protected ContratoCheckinService $contratoCheckinService,
        protected VeiculoService $veiculoService,
        protected ModeloService $modeloService,
        protected CategoriaDanoService $categoriaDanoService,
        protected UpgradeVeiculoService $upgradeVeiculoService,
        protected ExibirCheckinAction $exibirCheckinAction,
        protected RealizarCheckinAction $realizarCheckinAction,
        protected FinalizarCheckinAction $finalizarCheckinAction,
        protected ExibirContratoCheckinAction $exibirContratoCheckinAction,
    ) {}

    public function checkin(Request $request, $codigo)
    {
        try {
            return $this->exibirCheckinAction->handle($codigo, $request);
        } catch (\Throwable $e) {
            return redirect()
                ->route('reserva.index')
                ->with('error', $e->getMessage());
        }
    }

    public function criarContratoCheckIn(CriarContratoCheckInRequest $request, $codigo)
    {
        try {
            return $this->realizarCheckinAction->handle($codigo, $request);
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao realizar check-in: ' . $e->getMessage());
        }
    }

    public function veiculosDisponiveisCheckin(Request $request, $codigo)
    {
        $contextoCheckin = $this->contextoCheckinPorCodigo($codigo);

        if (!$contextoCheckin) {
            return response()->json(['error' => 'Reserva nao encontrada.'], 404);
        }

        $reserva = $contextoCheckin['reserva'];
        $categoriaId = $request->query('categoria_id') ? (int) $request->query('categoria_id') : null;
        $veiculoIdAtual = (int) ($reserva->veiculo_id ?? 0);

        $veiculos = Veiculo::query()
            ->with(['marca', 'modelo', 'estacao'])
            ->when($veiculoIdAtual > 0, function ($q) use ($veiculoIdAtual) {
                $q->where(function ($sub) use ($veiculoIdAtual) {
                    $sub->where('status_veiculo_id', 1)
                        ->orWhere('id', $veiculoIdAtual);
                });
            }, function ($q) {
                $q->where('status_veiculo_id', 1);
            })
            ->when($categoriaId, function ($q) use ($categoriaId) {
                $q->whereHas('modelo', fn ($m) => $m->where('categoria_id', $categoriaId));
            })
            ->orderBy('placa_licenca')
            ->get();

        return response()->json($veiculos);
    }

    public function veiculosModalCheckin(Request $request, $codigo)
    {
        $contextoCheckin = $this->contextoCheckinPorCodigo($codigo);

        if (!$contextoCheckin) {
            return response()->json(['message' => 'Reserva nao encontrada.'], 404);
        }

        $reserva = $contextoCheckin['reserva'];
        $reserva_id = (int) $contextoCheckin['reserva_id'];
        $dataInicio = \Carbon\Carbon::parse($reserva->data_inicio)->format('Y-m-d H:i:s');
        $dataFim    = \Carbon\Carbon::parse($reserva->data_fim)->format('Y-m-d H:i:s');
        $estacaoId  = (int) $reserva->estacao_retirada_id;

        $filtros = $request->only(['placa', 'nome', 'chassi', 'cor', 'modelo', 'marca_id', 'grupo_frota_id', 'tipo_frota_id', 'ativo', 'ano_de', 'ano_ate', 'kms_ate']);
        if (!empty($contextoCheckin['is_motorista_app'])) {
            $filtros['modelo_ids'] = $contextoCheckin['modelo_ids'];
            unset($filtros['grupo_frota_id']);
        }
        $filtrosVazios = collect($filtros)->filter(fn($v) => $v !== null && $v !== '')->isEmpty();

        if ($filtrosVazios) {
            $filtrosGrupo = [];
            if (!empty($reserva->grupo_frota_id)) {
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

        $toArray = static fn($v) => [
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

    public function diagnosticoDisponibilidadeCheckin(Request $request, $codigo)
    {
        $contextoCheckin = $this->contextoCheckinPorCodigo($codigo);

        if (!$contextoCheckin) {
            return response()->json(['message' => 'Reserva nao encontrada.'], 404);
        }

        $reserva = $contextoCheckin['reserva'];
        $reserva_id = (int) $contextoCheckin['reserva_id'];
        $modeloIdsPermitidos = $contextoCheckin['modelo_ids'];
        $dataInicio = \Carbon\Carbon::parse($reserva->data_inicio)->format('Y-m-d H:i:s');
        $dataFim    = \Carbon\Carbon::parse($reserva->data_fim)->format('Y-m-d H:i:s');
        $estacaoId  = (int) $reserva->estacao_retirada_id;
        $agora = now()->format('Y-m-d H:i:s');
        $limite = $dataInicio > $agora ? $dataInicio : $agora;

        $veiculos = \App\Models\Veiculo::with([
            'marca',
            'modelo',
            'grupoFrota',
            'tipoFrota',
            'statusVeiculo',
            'localDisponibilidade',
            'movimentacoes' => fn($q) => $q->with(['reserva', 'statusMovimentacao', 'localOrigem', 'localDestino']),
        ])
            ->where(function ($q) use ($estacaoId) {
                $q->where('local_disponibilidade', $estacaoId)->orWhereNull('local_disponibilidade');
            })
            ->when(!empty($modeloIdsPermitidos), fn($q) => $q->whereIn('modelo_id', $modeloIdsPermitidos))
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

                // 2. Disponibilidade
                $dispDt = $v->data_hora_disponibilidade ? \Carbon\Carbon::parse($v->data_hora_disponibilidade) : null;
                $regras[] = [
                    'nome' => 'Disponibilidade',
                    'ok' => is_null($dispDt) || $dispDt->lte(\Carbon\Carbon::parse($limite)),
                    'info' => is_null($dispDt)
                        ? 'Sem restrição — veículo disponível agora'
                        : "Disponível a partir de " . $dispDt->format('d/m/Y H:i') . ". Limite atual: " . \Carbon\Carbon::parse($limite)->format('d/m/Y H:i'),
                    'solucao' => $dispDt && $dispDt->gt(\Carbon\Carbon::parse($limite))
                        ? "Aguarde até " . $dispDt->format('d/m/Y H:i') . " ou antecipe a data de disponibilidade no cadastro do veículo."
                        : null,
                ];

                // 3. Status
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
                        \App\Enums\StatusReservaEnum::Pendente->value,
                        \App\Enums\StatusReservaEnum::Confirmada->value,
                        \App\Enums\StatusReservaEnum::Checkout->value,
                        \App\Enums\StatusReservaEnum::NoShow->value,
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

    public function checkinStatusUpgrade(Request $request, $id)
    {
        $contextoCheckin = $this->contextoCheckinPorCodigo($id);
        $reserva = $contextoCheckin['reserva'] ?? $this->reservaService->getById((int) $id, ['grupoFrota', 'modelos']);

        if (!$reserva) {
            return response()->json(['error' => 'Reserva nao encontrada.'], 404);
        }

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
            $payload['grupos_upgrade'] = collect($grupos)->map(fn($item) => [
                'grupo' => [
                    'id'    => $item['grupo']->id,
                    'nome'  => $item['grupo']->nome,
                    'ordem' => $item['grupo']->ordem,
                ],
                'veiculos' => $item['veiculos']->map(fn($v) => [
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
                    'is_upgrade' => (int) ($grupo?->ordem ?? 0) > (int) ($reserva->grupoFrota?->ordem ?? 0),
                    'is_downgrade' => (int) ($grupo?->ordem ?? 0) < (int) ($reserva->grupoFrota?->ordem ?? 0),
                ];
            })->values();
        }

        return response()->json($payload);
    }

    public function excluirContratoCheckin(Request $request, $id)
    {
        $contrato = ContratoCheckin::find($id);

        if (!$contrato) {
            return response()->json(['error' => 'Contrato nao encontrado.'], 404);
        }

        if ($contrato->pdf_path && Storage::disk('public')->exists($contrato->pdf_path)) {
            Storage::disk('public')->delete($contrato->pdf_path);
        }

        $contrato->delete();

        return response()->json(['success' => true, 'message' => 'Contrato excluido com sucesso.']);
    }

    public function assinarContratoCheckin(Request $request, $codigo, ReservaResolver $reservaResolver)
    {
        try {
            $reservaResolvida = $reservaResolver->resolver($codigo);
        } catch (\Throwable $e) {
            return redirect()
                ->route('reserva.index')
                ->with('error', $e->getMessage());
        }

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Reservas', route('reserva.index')),
            new BreadCrumbItem('Detalhes da Reserva Nº ' . $codigo, route('reserva.show', $codigo)),
            new BreadCrumbItem('Assinar Contrato de Check-in'),
        ]);

        if ($reservaResolvida->ehMotoristaApp()) {
            /** @var \App\Models\ReservaMotoristaApp $reg */
            $reg = $reservaResolvida->registro;
            $reg->loadMissing(['user', 'empresa', 'veiculo.marca', 'veiculo.modelo', 'planoMotoristaApp', 'solicitacao.planoMotoristaApp', 'estacaoRetirada', 'pagamentos']);

            $plano = $reg->planoMotoristaApp ?? $reg->solicitacao?->planoMotoristaApp;
            $valorPeriodoAtual = (float) (
                $plano?->valor_periodo
                ?? $reg->solicitacao?->valor_periodo
                ?? $reg->pagamentos?->first()?->valor_unitario
                ?? $reg->pagamentos?->first()?->valor_total
                ?? 0
            );
            $tarifaValor = (float) (
                $reg->valor_periodo_contrato
                ?: ($reg->valor_plano
                ?: $valorPeriodoAtual)
            );
            $valorOpcionais = (float) ($reg->valor_opcionais ?? 0);
            $valorTotal = round($tarifaValor + $valorOpcionais, 2);
            $opcionaisSelecionados = is_array($reg->opcionais_json ?? null) ? $reg->opcionais_json : [];
            $opcionaisMap = $plano?->opcionais?->keyBy('id') ?? collect();
            $opcionaisList = collect($opcionaisSelecionados)
                ->map(function ($item) use ($opcionaisMap) {
                    $opcionalId = (int) ($item['id'] ?? 0);
                    $opcional = $opcionaisMap->get($opcionalId);
                    $quantidade = max(1, (int) ($item['quantidade'] ?? 1));
                    $subtotal = (float) (
                        $item['preco_total']
                        ?? $opcional?->pivot?->preco_total
                        ?? ($opcional?->preco_integral ?? $opcional?->preco_por_dia ?? 0)
                    );

                    return [
                        'nome' => $opcional?->nome ?? ($item['nome'] ?? ('Opcional #' . $opcionalId)),
                        'quantidade' => $quantidade,
                        'subtotal' => $subtotal,
                    ];
                })
                ->filter(fn($item) => $item['subtotal'] > 0 || !empty($item['nome']))
                ->values()
                ->toArray();

            return view('reserva.assinar-contrato-checkin', [
                'reserva' => $reg,
                'breadcrumb' => $breadcrumb,
                'contextoAssinatura' => [
                    'empresa' => $reg->empresa,
                    'usuario' => $reg->user,
                    'codigo' => $codigo,
                    'veiculo' => $reg->veiculo,
                    'data_inicio' => $reg->data_inicio,
                    'data_fim' => $reg->data_fim,
                    'quantidade_dias' => $reg->quantidade_dias,
                    'tarifa_label' => 'Valor do Ciclo (' . ($plano?->nome ?? 'Plano') . ')',
                    'tarifa_valor' => $tarifaValor,
                    'valor_opcionais' => $valorOpcionais,
                    'opcionais_lista' => $opcionaisList,
                    'valor_total' => $valorTotal,
                    'tipo' => 'MAP',
                    'plano_nome' => $plano?->nome ?? 'Plano Motorista App',
                    'estacao_retirada' => $reg->estacaoRetirada?->nome ?? '-',
                    'estacao_devolucao' => $reg->estacaoRetirada?->nome ?? '-',
                    'caucao' => (float) ($plano?->calcao ?? 0),
                ],
                'titulo' => 'Assinar Contrato de Check-in — Reserva Nº ' . $codigo,
            ]);
        }

        /** @var \App\Models\Reserva $reserva */
        $reserva = $reservaResolvida->registro;
        $reserva->loadMissing(['usuario', 'empresa', 'veiculo.marca', 'veiculo.modelo', 'contratoCheckin', 'estacaoRetirada', 'estacaoDevolucao']);

        if (!$reserva->contratoCheckin) {
            return redirect()
                ->route('reserva.show', $codigo)
                ->with('error', 'Contrato de check-in não encontrado.');
        }

        return view('reserva.assinar-contrato-checkin', [
            'reserva' => $reserva,
            'breadcrumb' => $breadcrumb,
            'contextoAssinatura' => [
                'empresa' => $reserva->empresa,
                'usuario' => $reserva->usuario,
                'codigo' => $codigo,
                'veiculo' => $reserva->contratoCheckin?->veiculo ?? $reserva->veiculo,
                'data_inicio' => $reserva->data_inicio,
                'data_fim' => $reserva->data_fim,
                'quantidade_dias' => $reserva->quantidade_dias,
                'tarifa_label' => 'Tarifa diária',
                'tarifa_valor' => (float) ($reserva->tarifa_base ?? 0),
                'taxa_entrega' => (float) ($reserva->taxa_entrega ?? 0),
                'taxa_coleta' => (float) ($reserva->taxa_coleta ?? 0),
                'valor_total' => (float) ($reserva->valor_total_final ?? 0),
                'tipo' => 'RES',
                'estacao_retirada' => $reserva->estacaoRetirada?->nome ?? '-',
                'estacao_devolucao' => $reserva->estacaoDevolucao?->nome ?? '-',
            ],
            'titulo' => 'Assinar Contrato de Check-in — Reserva Nº ' . $codigo,
        ]);
    }

    public function salvarAssinarContratoCheckIn(AssinarContratoRequest $request, $codigo)
    {
        try {
            return $this->finalizarCheckinAction->handle($codigo, $request);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Falha ao salvar assinatura: ' . $e->getMessage()], 500);
        }
    }

    public function verContratoCheckin(Request $request, $codigo)
    {
        try {
            $pdfContent = $this->exibirContratoCheckinAction->handle($codigo);

            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="contrato-checkin-' . $codigo . '.pdf"',
            ]);
        } catch (\Throwable $e) {
            return redirect()
                ->route('reserva.index')
                ->with('error', $e->getMessage());
        }
    }
}
