<?php

namespace App\Services;

use App\Models\SaldoMovimentacao;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SaldoService
{
    public function creditar(User $user, float $valor, array $meta = []): ?SaldoMovimentacao
    {
        $valor = round($valor, 2);
        if ($valor <= 0) {
            return null;
        }

        return DB::transaction(function () use ($user, $valor, $meta) {
            $user->refresh();
            $saldoAnterior = (float) ($user->saldo_atual ?? 0);
            $saldoAtual = $saldoAnterior + $valor;

            $user->saldo_atual = $saldoAtual;
            $user->save();

            return SaldoMovimentacao::create([
                'user_id' => $user->id,
                'empresa_id' => $meta['empresa_id'] ?? $user->empresa_id,
                'reserva_id' => $meta['reserva_id'] ?? null,
                'tipo' => 'credito',
                'origem' => $meta['origem'] ?? null,
                'valor' => $valor,
                'saldo_anterior' => $saldoAnterior,
                'saldo_atual' => $saldoAtual,
                'observacoes' => $meta['observacoes'] ?? null,
            ]);
        });
    }

    public function debitar(User $user, float $valor, array $meta = []): ?SaldoMovimentacao
    {
        $valor = round($valor, 2);
        if ($valor <= 0) {
            return null;
        }

        return DB::transaction(function () use ($user, $valor, $meta) {
            $user->refresh();
            $saldoAnterior = (float) ($user->saldo_atual ?? 0);
            $saldoAtual = max(0, $saldoAnterior - $valor);

            $user->saldo_atual = $saldoAtual;
            $user->save();

            return SaldoMovimentacao::create([
                'user_id' => $user->id,
                'empresa_id' => $meta['empresa_id'] ?? $user->empresa_id,
                'reserva_id' => $meta['reserva_id'] ?? null,
                'tipo' => 'debito',
                'origem' => $meta['origem'] ?? null,
                'valor' => $valor,
                'saldo_anterior' => $saldoAnterior,
                'saldo_atual' => $saldoAtual,
                'observacoes' => $meta['observacoes'] ?? null,
            ]);
        });
    }
}
