<?php

namespace App\Actions\Reserva\Checkin\Exibir;

use App\Services\Reserva\ReservaResolver;
use Illuminate\Http\Request;

class ExibirCheckinAction
{
    public function __construct(
        private ExibirCheckinReservaComumAction $comum,
        private ExibirCheckinMotoristaAppAction $motoristaApp,
        private ReservaResolver $reservaResolver,
    ) {}

    public function handle(string $codigo, Request $request): mixed
    {
        $reservaResolvida = $this->reservaResolver->resolver($codigo);

        return match (true) {
            $reservaResolvida->ehComum() => $this->comum->handle($reservaResolvida, $request),
            $reservaResolvida->ehMotoristaApp() => $this->motoristaApp->handle($reservaResolvida, $request),
            default => throw new \LogicException('Tipo de reserva não suportado para exibição do check-in.'),
        };
    }
}
