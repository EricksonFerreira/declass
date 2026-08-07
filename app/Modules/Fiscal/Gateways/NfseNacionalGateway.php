<?php

namespace App\Modules\Fiscal\Gateways;

use App\Modules\Fiscal\DTO\DpsDTO;
use App\Modules\Fiscal\DTO\NfseResponseDTO;
use Illuminate\Support\Str;

class NfseNacionalGateway implements NfseGatewayInterface
{
    public function emitir(DpsDTO $dps): NfseResponseDTO
    {
        // Simulate communication with government or NuvemFiscal provider
        $chave = '43' . date('ym') . '9999999999999955001' . str_pad((string)rand(1, 99999), 9, '0', STR_PAD_LEFT) . '1' . str_pad((string)rand(1, 99999), 8, '0', STR_PAD_LEFT);
        $protocolo = '143' . date('y') . str_pad((string)rand(1, 999999), 10, '0', STR_PAD_LEFT);

        $xml = '<gLocBensMoveis><cNCMBemMovel>' . ($dps->bens[0]['ncm'] ?? '87032310') . '</cNCMBemMovel>';
        $xml .= '<xNCMBemMovel>' . ($dps->bens[0]['descricao'] ?? 'Locacao de Veiculo') . '</xNCMBemMovel>';
        $xml .= '<qtdNCMBemMovel>1</qtdNCMBemMovel></gLocBensMoveis>';

        return new NfseResponseDTO(
            sucesso: true,
            numeroNfse: (string) rand(100, 99999),
            chaveAcesso: $chave,
            protocolo: $protocolo,
            xml: $xml,
            xmlRetorno: '<retConsSitNfse><dhRecbto>' . date('c') . '</dhRecbto><status>1</status></retConsSitNfse>'
        );
    }

    public function consultar(string $chave): NfseResponseDTO
    {
        return new NfseResponseDTO(
            sucesso: true,
            chaveAcesso: $chave,
            protocolo: '143' . date('y') . '0000000001',
            xmlRetorno: '<retConsSitNfse><status>1</status></retConsSitNfse>'
        );
    }

    public function cancelar(string $chave, string $motivo): NfseResponseDTO
    {
        return new NfseResponseDTO(
            sucesso: true,
            chaveAcesso: $chave,
            protocolo: '143' . date('y') . '0000000002',
            xmlRetorno: '<retCancelamento><status>cancelado</status></retCancelamento>'
        );
    }
}
