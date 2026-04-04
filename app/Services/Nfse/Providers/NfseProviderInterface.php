<?php

namespace App\Services\Nfse\Providers;

use App\Models\Empresa;
use App\Models\Nfse;

interface NfseProviderInterface
{
    /**
     * @return array{status:string,response?:array}
     */
    public function emitir(Empresa $empresa, array $payload): array;

    /**
     * @return array{status:string,response?:array}
     */
    public function cancelar(Empresa $empresa, Nfse $nfse, string $motivo): array;

    /**
     * @return array{status:string,response?:array}
     */
    public function consultar(Empresa $empresa, string $nfseId): array;
}
