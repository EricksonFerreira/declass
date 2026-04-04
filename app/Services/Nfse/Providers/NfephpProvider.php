<?php

namespace App\Services\Nfse\Providers;

use App\Models\Empresa;
use App\Models\Nfse;

class NfephpProvider implements NfseProviderInterface
{
    public function emitir(Empresa $empresa, array $payload): array
    {
        return [
            'status' => 'error',
            'response' => [
                'message' => 'Provedor NFePHP ainda nao configurado. Defina certificado e parametros para emissao.',
            ],
        ];
    }

    public function cancelar(Empresa $empresa, Nfse $nfse, string $motivo): array
    {
        return [
            'status' => 'error',
            'response' => [
                'message' => 'Provedor NFePHP ainda nao configurado para cancelamento.',
            ],
        ];
    }

    public function baixarXml(Empresa $empresa, string $nfseId, string $tipo = 'xml'): array
    {
        return [
            'status' => 'error',
            'message' => 'Download indisponivel para NFePHP ate a configuracao do provedor.',
        ];
    }
}
