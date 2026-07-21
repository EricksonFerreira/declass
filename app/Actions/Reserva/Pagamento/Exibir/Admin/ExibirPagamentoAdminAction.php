<?php

namespace App\Actions\Reserva\Pagamento\Exibir\Admin;

use App\Models\ReservaMotoristaApp;
use App\Services\Reserva\ReservaResolver;

class ExibirPagamentoAdminAction
{
    public function __construct(
        private ReservaResolver $reservaResolver,
        private ExibirPagamentoAdminReservaComumAction $comumAdminAction,
        private ExibirPagamentoAdminMotoristaAppAction $motoristaAppAdminAction,
    ) {}

    public function handle(string $codigo): mixed
    {
        $reservaResolvida = $this->reservaResolver->resolver($codigo);

        if ($reservaResolvida->ehComum()) {
            return $this->comumAdminAction->handle($reservaResolvida->registro);
        }

        if ($reservaResolvida->ehMotoristaApp()) {
            return $this->motoristaAppAdminAction->handle($reservaResolvida->registro);
        }

        throw new \LogicException('Tipo de reserva não suportado para exibição de pagamento admin.');
    }
}
