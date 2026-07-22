<?php

namespace App\Observers;

use App\Models\Veiculo;
use App\Models\EmpresaAlerta;
use Carbon\Carbon;

class VeiculoManutencaoObserver
{
    public function saved(Veiculo $veiculo): void
    {
        if (!$veiculo->empresa_id) {
            return;
        }

        $this->verificarRevisaoKms($veiculo);
        $this->verificarManutencaoKms($veiculo);
        $this->verificarManutencaoData($veiculo);
        $this->verificarInspecaoData($veiculo);
    }

    private function verificarRevisaoKms(Veiculo $veiculo): void
    {
        $kms = (int) $veiculo->kms;
        $limite = $veiculo->proxima_revisao_kms ? (int) $veiculo->proxima_revisao_kms : null;
        $tipo = "manutencao_revisao_{$veiculo->id}";

        if ($limite !== null) {
            $diff = $limite - $kms;

            if ($diff <= 0) {
                $this->criarOuAtualizarAlerta(
                    $veiculo->empresa_id,
                    $tipo,
                    'critico',
                    "Revisão Excedida - {$veiculo->placa_licenca}",
                    "O veículo {$veiculo->nome} ({$veiculo->placa_licenca}) atingiu {$kms} KM e ultrapassou a quilometragem limite para revisão de {$limite} KM.",
                    ['veiculo_id' => $veiculo->id, 'kms' => $kms, 'limite' => $limite]
                );
            } elseif ($diff <= 500) {
                $this->criarOuAtualizarAlerta(
                    $veiculo->empresa_id,
                    $tipo,
                    'aviso',
                    "Próximo da Revisão - {$veiculo->placa_licenca}",
                    "O veículo {$veiculo->nome} ({$veiculo->placa_licenca}) está com {$kms} KM, faltando apenas {$diff} KM para a revisão programada de {$limite} KM.",
                    ['veiculo_id' => $veiculo->id, 'kms' => $kms, 'limite' => $limite]
                );
            } else {
                $this->resolverAlerta($veiculo->empresa_id, $tipo);
            }
        } else {
            $this->resolverAlerta($veiculo->empresa_id, $tipo);
        }
    }

    private function verificarManutencaoKms(Veiculo $veiculo): void
    {
        $kms = (int) $veiculo->kms;
        $limite = $veiculo->proxima_manutencao_kms ? (int) $veiculo->proxima_manutencao_kms : null;
        $tipo = "manutencao_geral_{$veiculo->id}";

        if ($limite !== null) {
            $diff = $limite - $kms;

            if ($diff <= 0) {
                $this->criarOuAtualizarAlerta(
                    $veiculo->empresa_id,
                    $tipo,
                    'critico',
                    "Manutenção Excedida - {$veiculo->placa_licenca}",
                    "O veículo {$veiculo->nome} ({$veiculo->placa_licenca}) atingiu {$kms} KM e ultrapassou a quilometragem limite para manutenção corretiva de {$limite} KM.",
                    ['veiculo_id' => $veiculo->id, 'kms' => $kms, 'limite' => $limite]
                );
            } elseif ($diff <= 500) {
                $this->criarOuAtualizarAlerta(
                    $veiculo->empresa_id,
                    $tipo,
                    'aviso',
                    "Próximo da Manutenção - {$veiculo->placa_licenca}",
                    "O veículo {$veiculo->nome} ({$veiculo->placa_licenca}) está com {$kms} KM, faltando apenas {$diff} KM para a próxima manutenção programada de {$limite} KM.",
                    ['veiculo_id' => $veiculo->id, 'kms' => $kms, 'limite' => $limite]
                );
            } else {
                $this->resolverAlerta($veiculo->empresa_id, $tipo);
            }
        } else {
            $this->resolverAlerta($veiculo->empresa_id, $tipo);
        }
    }

    private function verificarManutencaoData(Veiculo $veiculo): void
    {
        $data = $veiculo->data_proxima_manutencao ? Carbon::parse($veiculo->data_proxima_manutencao)->startOfDay() : null;
        $tipo = "manutencao_data_{$veiculo->id}";

        if ($data !== null) {
            $hoje = Carbon::now()->startOfDay();
            $diff = $hoje->diffInDays($data, false); // false para permitir dias negativos (atrasado)

            if ($diff <= 0) {
                $this->criarOuAtualizarAlerta(
                    $veiculo->empresa_id,
                    $tipo,
                    'critico',
                    "Manutenção Atrasada - {$veiculo->placa_licenca}",
                    "O veículo {$veiculo->nome} ({$veiculo->placa_licenca}) tinha manutenção agendada para " . $data->format('d/m/Y') . " e está com o prazo expirado.",
                    ['veiculo_id' => $veiculo->id, 'data' => $data->toDateString()]
                );
            } elseif ($diff <= 15) {
                $this->criarOuAtualizarAlerta(
                    $veiculo->empresa_id,
                    $tipo,
                    'aviso',
                    "Manutenção Próxima - {$veiculo->placa_licenca}",
                    "O veículo {$veiculo->nome} ({$veiculo->placa_licenca}) possui manutenção agendada para " . $data->format('d/m/Y') . " (restam {$diff} dias).",
                    ['veiculo_id' => $veiculo->id, 'data' => $data->toDateString()]
                );
            } else {
                $this->resolverAlerta($veiculo->empresa_id, $tipo);
            }
        } else {
            $this->resolverAlerta($veiculo->empresa_id, $tipo);
        }
    }

    private function verificarInspecaoData(Veiculo $veiculo): void
    {
        $data = $veiculo->data_proxima_inspecao ? Carbon::parse($veiculo->data_proxima_inspecao)->startOfDay() : null;
        $tipo = "inspecao_data_{$veiculo->id}";

        if ($data !== null) {
            $hoje = Carbon::now()->startOfDay();
            $diff = $hoje->diffInDays($data, false);

            if ($diff <= 0) {
                $this->criarOuAtualizarAlerta(
                    $veiculo->empresa_id,
                    $tipo,
                    'critico',
                    "Inspeção Vencida - {$veiculo->placa_licenca}",
                    "A inspeção/vistoria do veículo {$veiculo->nome} ({$veiculo->placa_licenca}) estava marcada para " . $data->format('d/m/Y') . " e está vencida.",
                    ['veiculo_id' => $veiculo->id, 'data' => $data->toDateString()]
                );
            } elseif ($diff <= 15) {
                $this->criarOuAtualizarAlerta(
                    $veiculo->empresa_id,
                    $tipo,
                    'aviso',
                    "Inspeção Próxima - {$veiculo->placa_licenca}",
                    "A inspeção/vistoria do veículo {$veiculo->nome} ({$veiculo->placa_licenca}) está agendada para " . $data->format('d/m/Y') . " (restam {$diff} dias).",
                    ['veiculo_id' => $veiculo->id, 'data' => $data->toDateString()]
                );
            } else {
                $this->resolverAlerta($veiculo->empresa_id, $tipo);
            }
        } else {
            $this->resolverAlerta($veiculo->empresa_id, $tipo);
        }
    }

    private function criarOuAtualizarAlerta(int $empresaId, string $tipo, string $nivel, string $titulo, string $mensagem, array $metadados): void
    {
        EmpresaAlerta::updateOrCreate(
            ['empresa_id' => $empresaId, 'tipo' => $tipo, 'resolvido' => false],
            [
                'nivel' => $nivel,
                'titulo' => $titulo,
                'mensagem' => $mensagem,
                'metadados' => $metadados,
            ]
        );
    }

    private function resolverAlerta(int $empresaId, string $tipo): void
    {
        EmpresaAlerta::where('empresa_id', $empresaId)
            ->where('tipo', $tipo)
            ->where('resolvido', false)
            ->update([
                'resolvido' => true,
                'resolvido_em' => now()
            ]);
    }
}
