<?php

namespace App\Modules\Fiscal\Gateways;

use App\Modules\Fiscal\DTO\DpsDTO;
use App\Modules\Fiscal\DTO\NfseResponseDTO;

interface NfseGatewayInterface
{
    public function emitir(DpsDTO $dps): NfseResponseDTO;

    public function consultar(string $chave): NfseResponseDTO;

    public function cancelar(string $chave, string $motivo): NfseResponseDTO;
}
