<?php

namespace App\Modules\Fiscal\DTO;

class NfseDTO
{
    public function __construct(
        public ?int $id,
        public int $empresaId,
        public int $operacaoFiscalId,
        public string $tipo,
        public string $status,
        public ?string $numeroDps = null,
        public ?string $numeroNfse = null,
        public ?string $chaveAcesso = null,
        public ?string $xml = null,
        public ?string $xmlRetorno = null,
        public ?string $protocolo = null,
        public ?string $erroCodigo = null,
        public ?string $erroMensagem = null
    ) {}
}
