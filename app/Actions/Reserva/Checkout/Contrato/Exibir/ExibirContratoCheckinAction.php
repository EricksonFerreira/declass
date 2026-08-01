<?php

namespace App\Actions\Reserva\Checkin\Contrato\Exibir;

use App\Models\Reserva;
use App\Models\ReservaMotoristaApp;
use App\Services\Reserva\ReservaResolver;

class ExibirContratoCheckinAction
{
    public function __construct(
        private ExibirContratoCheckinReservaComumAction $comum,
        private ExibirContratoCheckinMotoristaAppAction $motoristaApp,
        private ReservaResolver $reservaResolver,
    ) {}

    public function handle(string $codigo): string
    {
        $reservaResolvida = $this->reservaResolver->resolver($codigo);

        return match (true) {
            $reservaResolvida->ehComum() => $this->comum->gerarPdf($reservaResolvida->registro),
            $reservaResolvida->ehMotoristaApp() => $this->motoristaApp->gerarPdf($reservaResolvida->registro),
            default => throw new \LogicException('Tipo de reserva não suportado para exibição do contrato de check-in.'),
        };
    }

    public function gerarPdf($reserva): string
    {
        if ($reserva instanceof Reserva) {
            return $this->comum->gerarPdf($reserva);
        }
        if ($reserva instanceof ReservaMotoristaApp) {
            return $this->motoristaApp->gerarPdf($reserva);
        }
        throw new \InvalidArgumentException('Tipo de reserva inválido para geração de PDF.');
    }
}
