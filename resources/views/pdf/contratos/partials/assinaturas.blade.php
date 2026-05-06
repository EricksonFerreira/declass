<div class="signature-grid no-break">
    <div class="signature-box">
        <div class="signature-line">
            @if(!empty($get('assinatura', null)) && $get('assinatura') !== '<br>')
                <img src="{{ $get('assinatura') }}" alt="Assinatura do locatário">
            @endif
        </div>
        <div class="signature-name">{{ $get('cliente.nome', 'Locatário') }}</div>
        <div class="signature-role">Locatário</div>
        <div class="signature-doc">
            CPF: {{ $get('cliente.nif', '-') }}<br>
            CNH: {{ $get('cliente.carta_conducao.numero', '-') }}
        </div>
        @if($get('documento.data_assinatura', null))
            <div class="signature-doc" style="margin-top:2px;">
                Assinado em: {{ $get('documento.data_assinatura') }}
            </div>
        @endif
    </div>
    <div class="signature-box">
        <div class="signature-line"></div>
        <div class="signature-name">{{ $get('funcionario.nome', 'Atendente') }}</div>
        <div class="signature-role">Atendente / Responsável</div>
        <div class="signature-doc">
            Emitido em: {{ $get('documento.data_emissao', now()->format('d/m/Y H:i')) }}
        </div>
    </div>
</div>
@if($get('condutor_adicional.nome', null))
    <div class="signature-grid no-break" style="margin-top:20px">
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-name">{{ $get('condutor_adicional.nome') }}</div>
            <div class="signature-role">Condutor Adicional</div>
            <div class="signature-doc">Documento: {{ $get('condutor_adicional.documento', '-') }}</div>
        </div>
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-name">Vistoria de Entrega</div>
            <div class="signature-role">Confirmação das condições do veículo</div>
            <div class="signature-doc">Contrato: {{ $get('contrato.numero', '-') }}</div>
        </div>
    </div>
@endif
