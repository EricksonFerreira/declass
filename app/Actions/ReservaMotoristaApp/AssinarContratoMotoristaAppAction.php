<?php

namespace App\Actions\ReservaMotoristaApp;

use App\Actions\Reserva\Checkin\Contrato\Assinar\AssinarContratoCheckinMotoristaAppAction;
use App\Models\ReservaMotoristaApp;

class AssinarContratoMotoristaAppAction
{
    public function __construct(
        private AssinarContratoCheckinMotoristaAppAction $action
    ) {}

    public function handle(ReservaMotoristaApp $reserva, string $assinaturaBase64): void
    {
        $this->action->handle($reserva, $assinaturaBase64);
    }

    public function handleCheckin(ReservaMotoristaApp $reserva, string $assinaturaBase64): string
    {
        $this->action->handle($reserva, $assinaturaBase64);
        return (string) $reserva->contrato_pdf_path;
    }
}



