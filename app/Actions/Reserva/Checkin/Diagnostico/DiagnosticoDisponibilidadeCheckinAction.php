<?php

namespace App\Actions\Reserva\Checkin\Diagnostico;

use App\Http\Controllers\Reserva\ReservaHelpersTrait;
use Illuminate\Http\Request;
use App\Services\ReservaService;
use App\Services\VeiculoService;
use App\Services\ModeloService;
use App\Services\UpgradeVeiculoService;

class DiagnosticoDisponibilidadeCheckinAction
{
    use ReservaHelpersTrait;

    public function __construct(
        protected ReservaService $reservaService,
        protected VeiculoService $veiculoService,
        protected ModeloService $modeloService,
        protected UpgradeVeiculoService $upgradeVeiculoService
    ) {}

    public function handle(Request $request, $codigo)
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
}
