<?php

namespace App\Modules\Fiscal\DTO;

class NfseResponseDTO
{
    public function __construct(
        public bool $sucesso,
        public ?string $numeroNfse = null,
        public ?string $chaveAcesso = null,
        public ?string $protocolo = null,
        public ?string $xml = null,
        public ?string $xmlRetorno = null,
        public ?string $erroCodigo = null,
        public ?string $erroMensagem = null
    ) {}
}
