<?php

namespace App\Modules\Fiscal\Actions;

use App\Models\DocumentoFiscal;
use App\Modules\Fiscal\Gateways\NfseGatewayInterface;

class CancelarNfseAction
{
    public function __construct(
        protected NfseGatewayInterface $gateway
    ) {}

    public function handle(DocumentoFiscal $documento, string $motivo): void
    {
        if (empty($documento->chave_acesso)) {
            throw new \RuntimeException('Documento fiscal nao possui chave de acesso para cancelamento.');
        }

        $response = $this->gateway->cancelar($documento->chave_acesso, $motivo);

        if ($response->sucesso) {
            $documento->update([
                'status' => 'CANCELADO',
                'cancelada_em' => now(),
                'xml_retorno' => $response->xmlRetorno,
            ]);

            if ($documento->operacaoFiscal) {
                $documento->operacaoFiscal->update(['status' => 'CANCELADA']);
            }
        } else {
            $documento->update([
                'erro_codigo' => $response->erroCodigo,
                'erro_mensagem' => $response->erroMensagem,
            ]);
            throw new \RuntimeException('Erro ao cancelar NFS-e: ' . $response->erroMensagem);
        }
    }
}
