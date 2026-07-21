<?php

namespace App\Actions\Reserva\Checkin\Contrato\Gerar;

use App\Models\Reserva;
use App\Models\ReservaMotoristaApp;
use App\Services\Reserva\ReservaResolver;

class GerarContratoCheckinAction
{
    public function __construct(
        private GerarContratoCheckinReservaComumAction $comum,
        private GerarContratoCheckinMotoristaAppAction $motoristaApp,
        private ReservaResolver $reservaResolver,
    ) {}

    public function handle(string $codigo): string
    {
        $reservaResolvida = $this->reservaResolver->resolver($codigo);

        return match (true) {
            $reservaResolvida->ehComum() => $this->comum->handle($reservaResolvida->registro),
            $reservaResolvida->ehMotoristaApp() => $this->motoristaApp->handle($reservaResolvida->registro),
            default => throw new \LogicException('Tipo de reserva não suportado para geração do contrato de check-in.'),
        };
    }

    public function gerarPdf($reserva): string
    {
        if ($reserva instanceof Reserva) {
            return $this->comum->handle($reserva);
        }
        if ($reserva instanceof ReservaMotoristaApp) {
            return $this->motoristaApp->handle($reserva);
        }
        throw new \InvalidArgumentException('Tipo de reserva inválido para geração de PDF.');
    }
}
