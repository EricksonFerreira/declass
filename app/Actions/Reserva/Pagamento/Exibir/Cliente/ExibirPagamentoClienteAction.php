<?php

namespace App\Actions\Reserva\Pagamento\Exibir\Cliente;

use App\Actions\Reserva\Pagamento\Exibir\Cliente\ExibirPagamentoClienteMotoristaAppAction;
use App\Actions\Reserva\Pagamento\Exibir\Cliente\ExibirPagamentoClienteReservaComumAction;
use App\Models\ReservaMotoristaApp;
use App\Services\Reserva\ReservaResolver;

class ExibirPagamentoClienteAction
{
    public function __construct(
        private ReservaResolver $reservaResolver,
        private ExibirPagamentoClienteReservaComumAction $comumClienteAction,
        private ExibirPagamentoClienteMotoristaAppAction $motoristaAppClienteAction,
    ) {}

    public function handle(string $codigo): mixed
    {
        $reservaResolvida = $this->reservaResolver->resolver($codigo);

        if ($reservaResolvida->ehComum()) {
            return $this->comumClienteAction->handle($reservaResolvida->registro);
        }

        if ($reservaResolvida->ehMotoristaApp()) {
            return $this->motoristaAppClienteAction->handle($reservaResolvida->registro);
        }

        throw new \LogicException('Tipo de reserva não suportado para exibição de pagamento do cliente.');
    }
}
