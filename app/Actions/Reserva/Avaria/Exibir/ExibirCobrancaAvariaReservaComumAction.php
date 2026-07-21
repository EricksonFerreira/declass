<?php

namespace App\Actions\Reserva\Avaria\Exibir;

use App\DTO\Reserva\ReservaResolvida;
use App\Models\AvariaCobranca;
use App\Models\VeiculoDanos;

class ExibirCobrancaAvariaReservaComumAction
{
    public function handle(ReservaResolvida $reservaResolvida): array
    {
        $reserva = $reservaResolvida->reservaComum();

        $reserva->load([
            'usuario',
            'veiculo',
            'calcao',
            'contratoCheckout',
            'codigoReserva',
        ]);

        if (! $reserva->contratoCheckout) {
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
