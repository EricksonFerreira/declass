<div class="section-title">Estado da Viatura</div>
<div class="viatura-images">
    <img src="{{ asset('assets/imgs/carro/frente.jpg') }}" alt="Frente">
    <img src="{{ asset('assets/imgs/carro/atras.jpg') }}" alt="Trás">
    <img src="{{ asset('assets/imgs/carro/cima.jpg') }}" alt="Cima">
    <img src="{{ asset('assets/imgs/carro/esquerda.jpg') }}" alt="Esquerda">
    <img src="{{ asset('assets/imgs/carro/direita.jpg') }}" alt="Direita">
</div>
@php
    $itensEstado = $get('estado_viatura.itens', []);
@endphp
@if(is_countable($itensEstado) && count($itensEstado) > 0)
    <div class="dano-box">
        <table>
            @foreach($itensEstado as $item)
                <tr>
                    <td><strong>{{ $item['local'] ?? '-' }}</strong></td>
                    <td>
                        <div>
                            {{ $item['localizacao'] ?? '' }}
                            —
                            {{ $item['observacoes'] ?? '' }}
                            {{ $formatarTipoDano($item['tipo_dano'] ?? null) }}
                        </div>
                        @if(!empty($item['correcao']))
                            <div style="margin-top:3px;font-size:8px;color:#047857;">
                                <strong>Correção:</strong> {{ $item['correcao'] }}
                            </div>
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
@else
    <div class="muted" style="font-style:italic;margin-top:4px">
        Sem danos registrados.
    </div>
@endif
<div class="section-title" style="margin-top:8px">Funcionário Responsável</div>
<table class="info-table">
    <tr>
        <td>
            <div class="field-label">Nome</div>
            <div class="field-value">{{ $get('funcionario.nome', '-') }}</div>
        </td>
        <td>
            <div class="field-label">Data Emissão</div>
            <div class="field-value">{{ $get('documento.data_emissao', now()->format('d/m/Y H:i')) }}</div>
        </td>
    </tr>
</table>
