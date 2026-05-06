@php
    $fin = $data['financeiro_detalhado'] ?? [];
@endphp
<div class="section-title">Valores Detalhados</div>
<table class="detail-table">
    <tbody>
        <tr>
            <td>Grupo</td>
            <td class="money">{{ $fin['grupo'] ?? '-' }}</td>
        </tr>
        <tr>
            <td>Tarifa diária</td>
            <td class="money">{{ $fin['tarifa_diaria'] ?? '-' }}</td>
        </tr>
        <tr>
            <td>{{ $fin['dias_label'] ?? 'Dias x tarifa' }}</td>
            <td class="money">{{ $fin['valor_tarifa_base'] ?? '-' }}</td>
        </tr>
        <tr>
            <td>Opcionais</td>
            <td class="money">{{ $fin['opcionais'] ?? '-' }}</td>
        </tr>
        <tr>
            <td>Retirada fora do expediente</td>
            <td class="money">{{ $fin['taxa_coleta'] ?? '-' }}</td>
        </tr>
        <tr>
            <td>Devolução fora do expediente</td>
            <td class="money">{{ $fin['taxa_entrega'] ?? '-' }}</td>
        </tr>
        <tr>
            <td>Subtotal</td>
            <td class="money">{{ $fin['subtotal'] ?? '-' }}</td>
        </tr>
        <tr>
            <td>Desconto {{ $fin['desconto_label'] ?? '' }}</td>
            <td class="money">- {{ $fin['desconto_valor'] ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Total</strong></td>
            <td class="money">
                <strong>{{ $fin['total'] ?? $get('faturacao.valor_total', '-') }}</strong>
            </td>
        </tr>
    </tbody>
</table>
