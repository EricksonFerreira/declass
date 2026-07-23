<?php

namespace App\Actions\Reserva\Checkout\Dano\ObterDetectados;

use App\Models\ReservaCodigo;
use App\Services\Reserva\ReservaResolver;
use Illuminate\Http\JsonResponse;

class ObterDanosDetectadosAction
{
    public function __construct(
        private ObterDanosDetectadosReservaComumAction $comum,
        private ObterDanosDetectadosMotoristaAppAction $motoristaApp,
        private ReservaResolver $resolver,
    ) {}

    public function handle(int $veiculoId, string $codigo): JsonResponse
    {
        $reservaResolvida = $this->resolver->resolver($codigo);
        $reservaCodigoId = ReservaCodigo::where('codigo', $codigo)->value('id');
        if (!$reservaCodigoId) {
            $reservaCodigoId = ReservaCodigo::where('reserva_id', $reservaResolvida->registro->id)->value('id');
        }

        return match (true) {
            $reservaResolvida->ehComum() => $this->comum->handle($veiculoId, $reservaCodigoId),
            $reservaResolvida->ehMotoristaApp() => $this->motoristaApp->handle($veiculoId, $reservaCodigoId),
            default => throw new \LogicException('Tipo de reserva não suportado para obter danos detectados.'),
        };
    }
}
