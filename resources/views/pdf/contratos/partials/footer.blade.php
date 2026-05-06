<div class="doc-footer">
    Documento processado pelo sistema DEcars
    &bull; {{ $get('documento.empresa.nome', '') }}
    &bull; {{ now()->format('d/m/Y') }}
    @if($get('documento.empresa.site', null))
        &bull; {{ $get('documento.empresa.site') }}
    @endif
    @if($get('documento.hash', null))
        <br>Hash de validação: {{ $get('documento.hash') }}
    @endif
    @if($get('documento.qr_code', null))
        <br>
        <img class="qr-code" src="{{ $get('documento.qr_code') }}" alt="QR Code de validação">
    @endif
</div>
