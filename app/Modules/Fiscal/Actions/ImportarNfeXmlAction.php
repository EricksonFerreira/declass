<?php

namespace App\Modules\Fiscal\Actions;

use App\Models\DocumentoFiscal;
use App\Models\CreditoTributario;
use SimpleXMLElement;
use Exception;

class ImportarNfeXmlAction
{
    public function handle(string $xmlContent, int $empresaId): DocumentoFiscal
    {
        try {
            $xml = new SimpleXMLElement($xmlContent);
        } catch (Exception $e) {
            throw new Exception("XML inválido ou corrompido: " . $e->getMessage());
        }

        // Register XML namespaces
        $xml->registerXPathNamespace('nfe', 'http://www.portalfiscal.inf.br/nfe');

        // Extract Key
        $chNFe = (string) ($xml->xpath('//nfe:infNFe/@Id')[0] ?? '');
        $chNFe = preg_replace('/[^0-9]/', '', $chNFe); // Only digits

        if (empty($chNFe)) {
            // Fallback if no namespace is matched
            $chNFe = (string) ($xml->infNFe['Id'] ?? '');
            $chNFe = preg_replace('/[^0-9]/', '', $chNFe);
        }

        if (empty($chNFe)) {
            throw new Exception("Chave da NF-e não encontrada no arquivo XML.");
        }

        // Extract Number, Emitter, and Totals
        $nNF = (string) ($xml->xpath('//nfe:ide/nfe:nNF')[0] ?? ($xml->infNFe->ide->nNF ?? '0'));
        $xNome = (string) ($xml->xpath('//nfe:emit/nfe:xNome')[0] ?? ($xml->infNFe->emit->xNome ?? 'Fornecedor Desconhecido'));
        $vNF = (float) ($xml->xpath('//nfe:total/nfe:ICMSTot/nfe:vNF')[0] ?? ($xml->infNFe->total->ICMSTot->vNF ?? 0.00));

        // Under Reform layout or fallback to PIS/COFINS
        $vCBS = (float) ($xml->xpath('//nfe:total/nfe:ICMSTot/nfe:vCBS')[0] ?? ($xml->infNFe->total->ICMSTot->vCBS ?? 0.00));
        $vIBS = (float) ($xml->xpath('//nfe:total/nfe:ICMSTot/nfe:vIBS')[0] ?? ($xml->infNFe->total->ICMSTot->vIBS ?? 0.00));
        
        $vCOFINS = (float) ($xml->xpath('//nfe:total/nfe:ICMSTot/nfe:vCOFINS')[0] ?? ($xml->infNFe->total->ICMSTot->vCOFINS ?? 0.00));
        $vPIS = (float) ($xml->xpath('//nfe:total/nfe:ICMSTot/nfe:vPIS')[0] ?? ($xml->infNFe->total->ICMSTot->vPIS ?? 0.00));

        if ($vCBS <= 0 && $vCOFINS > 0) {
            $vCBS = $vCOFINS;
        }
        if ($vIBS <= 0 && $vPIS > 0) {
            $vIBS = $vPIS;
        }

        // If no taxes are found, default to simulated rates for testing (9% CBS / 17.7% IBS)
        if ($vCBS <= 0 && $vIBS <= 0 && $vNF > 0) {
            $vCBS = round($vNF * 0.09, 2);
            $vIBS = round($vNF * 0.177, 2);
        }

        // 1. Create or Find DocumentoFiscal representing this input
        $documento = DocumentoFiscal::firstOrCreate(
            ['chave_acesso' => $chNFe],
            [
                'empresa_id' => $empresaId,
                'numero' => $nNF,
                'tipo' => 'ENTRADA',
                'modelo' => '55',
                'serie' => '1',
                'xml_completo' => $xmlContent,
                'status' => 'AUTORIZADO',
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        // 2. Register CBS Tax Credit
        if ($vCBS > 0) {
            CreditoTributario::create([
                'empresa_id' => $empresaId,
                'tributo' => 'CBS',
                'valor_original' => $vCBS,
                'valor_utilizado' => 0.00,
                'saldo' => $vCBS,
                'origem' => "NF-e nº {$nNF} Emitter: {$xNome}",
                'data_apropriacao' => now(),
                'status' => 'DISPONIVEL'
            ]);
        }

        // 3. Register IBS Tax Credit
        if ($vIBS > 0) {
            CreditoTributario::create([
                'empresa_id' => $empresaId,
                'tributo' => 'IBS',
                'valor_original' => $vIBS,
                'valor_utilizado' => 0.00,
                'saldo' => $vIBS,
                'origem' => "NF-e nº {$nNF} Emitter: {$xNome}",
                'data_apropriacao' => now(),
                'status' => 'DISPONIVEL'
            ]);
        }

        return $documento;
    }
}
