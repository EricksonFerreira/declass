<?php

namespace App\Actions\Reserva\Checkin\Realizar;

use App\DTO\Reserva\ReservaResolvida;
use App\Enums\TipoReservaEnum;
use App\Models\Reserva;
use App\Services\Reserva\ReservaResolver;
use Illuminate\Http\Request;

class RealizarCheckinAction
{
    public function __construct(
        private RealizarCheckinReservaComumAction $comum,
        private ReservaResolver $reservaResolver,
    ) {}

    public function handle(string $codigo, Request $request): mixed
    {
        $reservaResolvida = $this->reservaResolver->resolver($codigo);

        if ($reservaResolvida->ehMotoristaApp()) {
            $registro = $reservaResolvida->registro;
            $operacionalId = $registro->codigoReserva?->reserva_id
                ?? data_get($registro->documentos_json, 'checkin.reserva_operacional_id');
            if ($operacionalId) {
                $reservaOperacional = Reserva::find($operacionalId);
                if ($reservaOperacional) {
                    $reservaResolvida = new ReservaResolvida(
                        (string) $reservaOperacional->id,
                        TipoReservaEnum::COMUM,
                        $reservaOperacional
                    );
                    return $this->comum->handle($reservaResolvida, $request);
                }
            }
        }

        return $this->comum->handle($reservaResolvida, $request);
    }
}
