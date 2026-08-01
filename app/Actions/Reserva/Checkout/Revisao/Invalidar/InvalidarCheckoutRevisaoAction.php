<?php

namespace App\Actions\Reserva\Checkout\Revisao\Invalidar;

use App\Models\Reserva;
use App\Services\ReservaService;
use Illuminate\Support\Facades\Auth;

class InvalidarCheckoutRevisaoAction
{
    public function __construct(
        private ReservaService $reservaService,
    ) {}

    public function handle(Reserva $reserva, string $justificativa): void
    {
        $this->reservaService->adicionaLog(
            (int) $reserva->id,
            (int) Auth::id(),
            [
                'acao' => 'Checkout invalidado na revisão',
                'checkout_id' => $reserva->contratoCheckout?->id,
                'justificativa' => $justificativa,
                'status_operacional' => 'Em conferencia',
            ],
            'Checkout invalidado na revisão',
            'danger'
        );
    }
}
