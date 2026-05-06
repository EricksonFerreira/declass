<div class="doc-footer">
    <div class="doc-footer-inner">
        <div class="doc-footer-col">
            <span class="doc-footer-brand">{{ $get('documento.empresa.nome', 'Locadora') }}</span>
            @if($get('documento.empresa.site', null))
                <span class="doc-footer-sep">|</span>
                <span>{{ $get('documento.empresa.site') }}</span>
            @endif
            @if($get('documento.empresa.telefone', null))
                <span class="doc-footer-sep">|</span>
                <span>{{ $get('documento.empresa.telefone') }}</span>
            @endif
        </div>
        <div class="doc-footer-col doc-footer-center">
            <span>Processado por DEcars &mdash; {{ now()->format('d/m/Y H:i') }}</span>
            @if($get('documento.hash', null))
                <br><span class="doc-footer-hash">Hash: {{ $get('documento.hash') }}</span>
            @endif
        </div>
        <div class="doc-footer-col doc-footer-right">
            <span>Contrato {{ $get('contrato.numero', '#00000') }}</span>
            <span class="doc-footer-sep">|</span>
            <span>Página</span>
        </div>
    </div>
    @if($get('documento.qr_code', null))
        <div class="doc-footer-qr">
            <img class="qr-code" src="{{ $get('documento.qr_code') }}" alt="QR Code de validação">
        </div>
    @endif
</div>
