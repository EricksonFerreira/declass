<?php

namespace App\Actions\Reserva\Avaria\Exibir;

use App\DTO\Reserva\ReservaResolvida;
use App\Models\AvariaCobranca;
use App\Models\VeiculoDanos;

class ExibirCobrancaAvariaMotoristaAppAction
{
    public function handle(ReservaResolvida $reservaResolvida): array
    {
        $reserva = $reservaResolvida->reservaMotoristaApp();

        $reserva->load([
            'user',
            'veiculo',
            'planoMotoristaApp',
            'codigoReserva',
        ]);

        if (! $reserva->checkout_realizado_em) {
            throw new \DomainException(
                'Finalize o check-out antes de registrar cobrança por avarias.'
            );
        }

        $danos = VeiculoDanos::query()
            ->where('reserva_codigo_id', $reserva->codigoReserva->id)
            ->with([
                'dano',
                'categoria',
                'imagens',
            ])
            ->orderByDesc('id')
            ->get();

        $cobrancas = AvariaCobranca::query()
            ->where('reserva_codigo_id', $reserva->codigoReserva->id)
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return [
            'reservaResolvida' => $reservaResolvida,
            'reserva' => $reserva,
            'danos' => $danos,
            'valorSugerido' => (float) $danos->sum(
                fn (VeiculoDanos $dano) => (float) ($dano->custo_estimado ?? 0)
            ),
            'cobrancas' => $cobrancas,
        ];
    }
}
