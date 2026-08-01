<?php

namespace App\Actions\Reserva\Checkout\Revisao\Ocorrencia;

use App\Actions\Reserva\Avaria\CriarCobranca\CriarCobrancaAvariaAction;
use App\Actions\Reserva\Avaria\EditarCobranca\EditarCobrancaAvariaAction;
use App\Models\AvariaCobranca;
use App\Models\Reserva;
use App\Models\VeiculoDanos;
use App\Services\ReservaService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RegistrarOcorrenciaCheckoutReservaComumAction
{
    public function __construct(
        private ReservaService $reservaService,
        private CriarCobrancaAvariaAction $criarCobrancaAction,
        private EditarCobrancaAvariaAction $editarCobrancaAction,
    ) {}

    public function handle(Reserva $reserva, array $dados): void
    {
        DB::transaction(function () use ($reserva, $dados) {
            $modo = $this->normalizarModoCobranca($dados['modo_cobranca'] ?? 'nao_cobrar');
            $dano = $this->obterDano($reserva, (int) ($dados['dano_id'] ?? 0));

            if (! $dano) {
                throw new \RuntimeException('Dano não encontrado para esta reserva.');
            }

            if ($modo === 'cobrar') {
                $cobranca = $this->salvarComoCobrar($reserva, $dano, $dados);
                $this->atualizarRevisao($dano, VeiculoDanos::REVISAO_COBRAR_CLIENTE, $dados['justificativa'] ?? $dados['descricao'] ?? null);
                $this->registrarLog($reserva, $dados, $modo, $dano->id, $cobranca?->id);

                return;
            }

            if ($modo === 'nao_cobrar') {
                $this->removerCobranca($reserva, $dano);
                $this->registrarLog($reserva, $dados, $modo, $dano->id, null);

                return;
            }

            $this->salvarComoNaoCobrar($dano, $dados);
            $this->atualizarRevisao($dano, VeiculoDanos::REVISAO_NAO_COBRAR, $dados['justificativa'] ?? $dados['descricao'] ?? null);
            $this->registrarLog($reserva, $dados, $modo, $dano->id, null);
        });
    }

    private function removerCobranca(Reserva $reserva, VeiculoDanos $dano): void
    {
        $cobranca = $reserva->avariaCobrancas()
            ->whereJsonContains('dano_veiculo_ids', $dano->id)
            ->first();

        if ($cobranca) {
            $cobranca->delete();
        }

        $dano->forceFill([
            'status_revisao' => VeiculoDanos::REVISAO_NAO_COBRAR,
        ])->save();
    }

    private function salvarComoCobrar(Reserva $reserva, VeiculoDanos $dano, array $dados): ?AvariaCobranca
    {
        $valor = max(0, (float) ($dados['valor'] ?? 0));
        $usarCalcao = filter_var($dados['usar_calcao'] ?? true, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
        $usarCalcao = $usarCalcao ?? true;

        if ($valor <= 0) {
            throw new \RuntimeException('Informe um valor maior que zero para cobrar o dano.');
        }

        $reservaCodigo = $reserva->codigoReserva?->codigo;
        if (blank($reservaCodigo)) {
            throw new \RuntimeException('A reserva não possui código público vinculado.');
        }

        $descricao = trim((string) ($dados['descricao'] ?? 'Cobrança pendente de revisão'));
        $cobranca = $this->obterCobrancaDaAvaria($reserva, $dano);

        if ($cobranca) {
            $cobranca = $this->editarCobrancaAction->handle($cobranca->id, [
                'dano_veiculo_ids' => [$dano->id],
                'valor' => $valor,
                'descricao' => $descricao,
                'metodo' => AvariaCobranca::METODO_MANUAL,
            ]);
        } else {
            $cobranca = $this->criarCobrancaAction->handle($reservaCodigo, [
                'dano_veiculo_ids' => [$dano->id],
                'valor' => $valor,
                'descricao' => $descricao,
                'metodo' => AvariaCobranca::METODO_MANUAL,
            ]);
        }

        $valorCalcao = $usarCalcao ? $valor : 0;

        $cobranca->forceFill([
            'status' => AvariaCobranca::STATUS_PENDENTE,
            'metodo' => AvariaCobranca::METODO_MANUAL,
            'valor' => $valor,
            'descricao' => $descricao,
            'valor_calcao' => $valorCalcao,
            'valor_pendente' => max(0, $valor - $valorCalcao),
            'gateway_response' => array_merge((array) ($cobranca->gateway_response ?? []), [
                'checkout_revisao' => [
                    'usar_calcao' => $usarCalcao,
                    'valor_solicitado' => $valor,
                    'valor_calcao' => $valorCalcao,
                    'valor_pendente' => max(0, $valor - $valorCalcao),
                ],
            ]),
        ])->save();

        $dano->forceFill([
            'status_reparo' => VeiculoDanos::REPARO_PENDENTE,
        ])->save();

        return $cobranca->fresh();
    }

    private function obterCobrancaDaAvaria(Reserva $reserva, VeiculoDanos $dano): ?AvariaCobranca
    {
        return $reserva->avariaCobrancas()
            ->whereJsonContains('dano_veiculo_ids', $dano->id)
            ->latest('id')
            ->first();
    }

    private function salvarComoNaoCobrar(VeiculoDanos $dano, array $dados): void
    {
        $justificativa = trim((string) ($dados['justificativa'] ?? $dados['descricao'] ?? ''));

        if ($justificativa === '') {
            throw new \RuntimeException('Informe uma justificativa para não cobrar este dano.');
        }

        $dano->forceFill([
            'status_reparo' => VeiculoDanos::REPARO_NAO_REQUER_REPARO,
            'nao_requer_reparo_justificativa' => $justificativa,
            'nao_requer_reparo_em' => now(),
            'nao_requer_reparo_por' => Auth::id(),
        ])->save();
    }

    private function atualizarRevisao(VeiculoDanos $dano, string $status, ?string $justificativa = null): void
    {
        $dano->forceFill([
            'status_revisao' => $status,
            'decisao_revisada_em' => now(),
            'decisao_revisada_por' => Auth::id(),
            'justificativa_decisao' => $justificativa ? trim($justificativa) : null,
        ])->save();
    }

    private function registrarLog(Reserva $reserva, array $dados, string $modo, int $danoId, ?int $cobrancaId): void
    {
        $this->reservaService->adicionaLog(
            (int) $reserva->id,
            (int) Auth::id(),
            [
                'acao' => $modo === 'cobrar'
                    ? 'Cobrança de avaria registrada como pendente'
                    : 'Dano marcado como não cobrável',
                'checkout_id' => $reserva->contratoCheckout?->id,
                'titulo' => $dados['titulo'] ?? null,
                'descricao' => $dados['descricao'] ?? null,
                'justificativa' => $dados['justificativa'] ?? null,
                'valor' => $dados['valor'] ?? null,
                'dano_id' => $danoId,
                'cobranca_avaria_id' => $cobrancaId,
                'modo_cobranca' => $modo,
            ],
            $modo === 'cobrar'
                ? 'Cobrança de avaria registrada como pendente'
                : 'Dano marcado como não cobrável',
            'warning'
        );
    }

    private function obterDano(Reserva $reserva, int $danoId): ?VeiculoDanos
    {
        if ($danoId <= 0) {
            return null;
        }

        $query = VeiculoDanos::query()->where('id', $danoId);

        $query->where(function ($q) use ($reserva) {
            $q->where('reserva_id', (int) $reserva->id);

            if (! empty($reserva->veiculo_id)) {
                $q->orWhere('veiculo_id', (int) $reserva->veiculo_id);
            }

            if (! empty($reserva->codigoReserva?->id)) {
                $q->orWhere('reserva_codigo_id', (int) $reserva->codigoReserva->id);
            }

            if (! empty($reserva->contratoCheckin?->id)) {
                $q->orWhere('contrato_id', (int) $reserva->contratoCheckin->id);
            }
        });

        return $query->first();
    }

    private function normalizarModoCobranca(string $modo): string
    {
        return in_array($modo, ['cobrar', 'nao_cobrar', 'remover_cobranca'], true) ? $modo : 'nao_cobrar';
    }
}
