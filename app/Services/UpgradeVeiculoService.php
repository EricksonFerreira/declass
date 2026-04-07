<?php

namespace App\Services;

use App\Models\GrupoFrota;
use App\Models\Veiculo;
use App\Models\Reserva;
use Illuminate\Support\Collection;

/**
 * Centraliza as regras de upgrade de categoria no check-in.
 *
 * Hierarquia de categorias: campo `ordem` em grupos_frota.
 * Maior valor de `ordem` = categoria melhor (elegível para upgrade).
 */
class UpgradeVeiculoService
{
    /**
     * Verifica se existem veículos disponíveis na categoria original da reserva.
     */
    public function categoriaOriginalTemDisponibilidade(Reserva $reserva): bool
    {
        $grupoFrotaId = $reserva->grupo_frota_id;
        if (!$grupoFrotaId) {
            return false;
        }

        $dataInicio = $reserva->data_inicio;
        $dataFim    = $reserva->data_fim;
        $estacaoId  = $reserva->estacao_retirada_id ? (int) $reserva->estacao_retirada_id : null;

        $disponiveis = $this->veiculosDisponiveisNoGrupo($grupoFrotaId, $dataInicio, $dataFim, $reserva->id, $estacaoId);

        return $disponiveis->isNotEmpty();
    }

    /**
     * Retorna veículos disponíveis em um grupo para o período,
     * excluindo a reserva atual.
     */
    public function veiculosDisponiveisNoGrupo(
        int $grupoFrotaId,
        ?string $dataInicio,
        ?string $dataFim,
        int $reservaAtualId,
        ?int $estacaoId = null
    ): Collection {
        return Veiculo::where('grupo_frota_id', $grupoFrotaId)
            ->where('ativo', 'S')
            ->when($estacaoId, fn ($q) => $q->where('local_disponibilidade', $estacaoId))
            ->get()
            ->filter(function (Veiculo $veiculo) use ($dataInicio, $dataFim, $reservaAtualId) {
                return $this->veiculoEstaLivreNoPeriodo($veiculo, $dataInicio, $dataFim, $reservaAtualId);
            });
    }

    /**
     * Retorna grupos elegíveis para upgrade em relação ao grupo original.
     * Elegível = ordem MAIOR que o grupo original.
     */
    public function gruposElegiveisParaUpgrade(int $grupoFrotaOriginalId): Collection
    {
        $grupoOriginal = GrupoFrota::find($grupoFrotaOriginalId);
        if (!$grupoOriginal) {
            return collect();
        }

        $ordemOriginal = (int) ($grupoOriginal->ordem ?? 0);

        return GrupoFrota::where('ativo', 'S')
            ->where('ordem', '>', $ordemOriginal)
            ->where('id', '!=', $grupoFrotaOriginalId)
            ->orderBy('ordem')
            ->get();
    }

    /**
     * Retorna veículos disponíveis de grupos superiores ao original,
     * agrupados por grupo.
     */
    public function veiculosDisponiveisParaUpgrade(
        Reserva $reserva
    ): array {
        $gruposSuperiores = $this->gruposElegiveisParaUpgrade((int) $reserva->grupo_frota_id);
        $resultado = [];
        $estacaoId = $reserva->estacao_retirada_id ? (int) $reserva->estacao_retirada_id : null;

        foreach ($gruposSuperiores as $grupo) {
            $veiculos = $this->veiculosDisponiveisNoGrupo(
                $grupo->id,
                $reserva->data_inicio,
                $reserva->data_fim,
                $reserva->id,
                $estacaoId
            );

            if ($veiculos->isNotEmpty()) {
                $resultado[] = [
                    'grupo'    => $grupo,
                    'veiculos' => $veiculos,
                ];
            }
        }

        return $resultado;
    }

    /**
     * Verifica se um veículo específico é elegível como upgrade para a reserva.
     * Bloqueio: não permite downgrade (grupo com ordem menor ou igual ao original).
     */
    /**
     * Retorna todos os veiculos disponiveis no periodo da reserva (na estacao de retirada, quando existir).
     * Usado como fallback operacional quando nao existe disponibilidade na categoria original.
     */
    public function veiculosDisponiveisNoPeriodo(Reserva $reserva): Collection
    {
        $estacaoId = $reserva->estacao_retirada_id ? (int) $reserva->estacao_retirada_id : null;

        return Veiculo::with('grupoFrota')
            ->where('ativo', 'S')
            ->when($estacaoId, fn ($q) => $q->where('local_disponibilidade', $estacaoId))
            ->get()
            ->filter(function (Veiculo $veiculo) use ($reserva) {
                return $this->veiculoEstaLivreNoPeriodo(
                    $veiculo,
                    $reserva->data_inicio,
                    $reserva->data_fim,
                    (int) $reserva->id
                );
            })
            ->values();
    }

    public function veiculoElegivelParaUpgrade(Veiculo $veiculo, Reserva $reserva): bool
    {
        $grupoOriginal = GrupoFrota::find($reserva->grupo_frota_id);
        if (!$grupoOriginal) {
            return false;
        }

        $grupoVeiculo = GrupoFrota::find($veiculo->grupo_frota_id);
        if (!$grupoVeiculo) {
            return false;
        }

        return (int) $grupoVeiculo->ordem > (int) $grupoOriginal->ordem;
    }

    /**
     * Verifica se um veículo está livre (sem sobreposição de reserva/checkin) no período.
     */
    private function veiculoEstaLivreNoPeriodo(
        Veiculo $veiculo,
        ?string $dataInicio,
        ?string $dataFim,
        int $reservaExcluirId
    ): bool {
        if (!$dataInicio || !$dataFim) {
            return true;
        }

        $conflito = \App\Models\Reserva::where('veiculo_id', $veiculo->id)
            ->where('id', '!=', $reservaExcluirId)
            ->whereHas('statusReserva', fn ($q) => $q->whereNotIn('id', [3, 5]))
            ->where(function ($q) use ($dataInicio, $dataFim) {
                $q->whereBetween('data_inicio', [$dataInicio, $dataFim])
                  ->orWhereBetween('data_fim', [$dataInicio, $dataFim])
                  ->orWhere(function ($q2) use ($dataInicio, $dataFim) {
                      $q2->where('data_inicio', '<=', $dataInicio)
                         ->where('data_fim', '>=', $dataFim);
                  });
            })
            ->exists();

        return !$conflito;
    }
}
