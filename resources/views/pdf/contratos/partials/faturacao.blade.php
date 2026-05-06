<div class="section-title">Faturação</div>
@if(!empty($opcionais))
    <table class="opcionais-table">
        <thead>
            <tr>
                <th>Opcional</th>
                <th style="text-align:center">Qtd</th>
                <th style="text-align:right">Unit.</th>
                <th style="text-align:right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($opcionais as $opcional)
                <tr>
                    <td>{{ $opcional['nome'] ?? '-' }}</td>
                    <td style="text-align:center">{{ $opcional['quantidade'] ?? '-' }}</td>
                    <td style="text-align:right">{{ $opcional['preco_unitario'] ?? '-' }}</td>
                    <td style="text-align:right">{{ $opcional['valor_total'] ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
<div class="finance-box">
    <div class="finance-row">
        <span class="lbl">Dias / Unidades</span>
        <span class="val">{{ $get('faturacao.dias_unidades', '-') }}</span>
    </div>
    <div class="finance-row">
        <span class="lbl">Impostos / IVA</span>
        <span class="val">{{ $get('faturacao.iva_total', 'R$ 0,00') }}</span>
    </div>
    <hr style="border:none;border-top:1px solid #bfdbfe;margin:4px 0">
    <div class="finance-row total">
        <span class="lbl">TOTAL DA LOCAÇÃO</span>
        <span class="val">{{ $get('faturacao.valor_total', '-') }}</span>
    </div>
</div>

@php
    $statusFin = $get('faturacao.status_financeiro', '');
    $statusClass = match($statusFin) {
        'Pago' => 'status-pago',
        'Parcialmente pago' => 'status-parcial',
        default => 'status-aberto',
    };
@endphp

<div class="finance-box" style="background:#fefce8;border-color:#fde68a;">
    <div class="finance-row">
        <span class="lbl">Valor pago</span>
        <span class="val" style="color:#16a34a;">{{ $get('faturacao.total_pago', 'R$ 0,00') }}</span>
    </div>
    <div class="finance-row">
        <span class="lbl">Saldo pendente</span>
        <span class="val" style="color:#dc2626;">{{ $get('faturacao.saldo_pendente', 'R$ 0,00') }}</span>
    </div>
    <hr style="border:none;border-top:1px solid #fde68a;margin:4px 0">
    <div class="finance-row">
        <span class="lbl">Status financeiro</span>
        <span class="val {{ $statusClass }}">{{ $statusFin ?: '-' }}</span>
    </div>
</div>

<div style="margin-top:8px">
    <table class="info-table">
        <tr>
            <td>
                <div class="field-label">Combustível em Falta</div>
                <div class="field-value">{{ $get('veiculo.taxas.combustivel_em_falta', 'R$ 0,00') }}</div>
            </td>
            <td>
                <div class="field-label">Taxa Reabastecimento</div>
                <div class="field-value">{{ $get('veiculo.taxas.taxa_reabastecimento', 'R$ 0,00') }}</div>
            </td>
        </tr>
    </table>
</div>
