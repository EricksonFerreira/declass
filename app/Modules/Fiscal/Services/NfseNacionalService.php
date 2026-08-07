<?php

namespace App\Modules\Fiscal\Services;

use App\Modules\Fiscal\Gateways\NfseGatewayInterface;
use App\Modules\Fiscal\DTO\DpsDTO;
use App\Models\OperacaoFiscal;
use App\Models\DocumentoFiscal;
use Illuminate\Support\Facades\Log;

class NfseNacionalService
{
    public function __construct(
        protected NfseGatewayInterface $gateway
    ) {}

    public function emitirParaOperacao(OperacaoFiscal $operacao): DocumentoFiscal
    {
        // 1. Build DPS DTO
        $dps = new DpsDTO();
        $dps->prestador = [
            'CNPJ' => $operacao->empresa->cnpj ?? '00000000000000',
            'IM' => $operacao->empresa->nfse_inscricao_municipal ?? null,
        ];

        $usuario = $operacao->locacao ? $operacao->locacao->usuario : null;
        $doc = $usuario?->numero_identificacao ?? '';
        $dps->tomador = [
            'CPF' => strlen($doc) === 11 ? $doc : null,
            'CNPJ' => strlen($doc) === 14 ? $doc : null,
            'xNome' => $usuario?->nome ?? 'Consumidor',
            'email' => $usuario?->email ?? null,
        ];

        $dps->codigoTributacao = '99.04.01';
        $dps->valorOperacao = $operacao->valor_operacao;

        // Sum IBS and CBS
        $ibs = $operacao->tributos()->where('tributo', 'IBS')->sum('valor');
        $cbs = $operacao->tributos()->where('tributo', 'CBS')->sum('valor');
        $dps->ibs = $ibs;
        $dps->cbs = $cbs;

        // Vehicle info
        $veiculo = $operacao->locacao ? $operacao->locacao->veiculo : null;
        $ncm = '87032310';
        $descricao = 'Locacao de veiculo';
        if ($veiculo) {
            $classificacao = $veiculo->classificacaoFiscal ?? null;
            $ncm = $classificacao?->ncm ?: '87032310';
            $descricao = $veiculo->marca . ' ' . $veiculo->modelo;
        }

        $dps->bens[] = [
            'ncm' => $ncm,
            'descricao' => $descricao,
            'quantidade' => 1,
        ];

        // 2. Create DPS Document Record
        $documento = DocumentoFiscal::create([
            'empresa_id' => $operacao->empresa_id,
            'operacao_fiscal_id' => $operacao->id,
            'tipo' => 'DPS',
            'numero_dps' => 'DPS-' . $operacao->id . '-' . time(),
            'codigo_tributacao' => '99.04.01',
            'status' => 'PENDENTE',
        ]);

        try {
            $response = $this->gateway->emitir($dps);

            if ($response->sucesso) {
                $documento->update([
                    'status' => 'AUTORIZADA',
                    'numero_nfse' => $response->numeroNfse,
                    'chave_acesso' => $response->chaveAcesso,
                    'protocolo' => $response->protocolo,
                    'xml' => $response->xml,
                    'xml_retorno' => $response->xmlRetorno,
                    'emitida_em' => now(),
                ]);
                $operacao->update(['status' => 'AUTORIZADA']);
            } else {
                $documento->update([
                    'status' => 'REJEITADA',
                    'erro_codigo' => $response->erroCodigo,
                    'erro_mensagem' => $response->erroMensagem,
                ]);
                $operacao->update(['status' => 'REJEITADA']);
            }
        } catch (\Throwable $e) {
            Log::error('Erro ao emitir NFS-e Nacional na operacao ' . $operacao->id, [
                'message' => $e->getMessage(),
            ]);

            $documento->update([
                'status' => 'REJEITADA',
                'erro_mensagem' => $e->getMessage(),
            ]);
            $operacao->update(['status' => 'REJEITADA']);
        }

        return $documento;
    }
}
