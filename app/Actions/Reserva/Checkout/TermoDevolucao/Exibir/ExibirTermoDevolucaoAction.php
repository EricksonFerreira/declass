<?php

namespace App\Actions\Reserva\Checkout\TermoDevolucao\Exibir;

use App\Services\Reserva\ReservaResolver;

class ExibirTermoDevolucaoAction
{
    public function __construct(
        private ExibirTermoDevolucaoReservaComumAction $comum,
        private ExibirTermoDevolucaoMotoristaAppAction $motoristaApp,
        private ReservaResolver $reservaResolver,
    ) {}

    public function handle(string $codigo): ?\Symfony\Component\HttpFoundation\Response
    {
        $reservaResolvida = $this->reservaResolver->resolver($codigo);

        return match (true) {
            $reservaResolvida->ehComum() => $this->comum->servirPdf($reservaResolvida->registro),
            $reservaResolvida->ehMotoristaApp() => $this->motoristaApp->servirPdf($reservaResolvida->registro),
            default => throw new \LogicException('Tipo de reserva não suportado para exibição do termo de devolução.'),
        };
    }
}
