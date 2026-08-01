<div class="section-title">Faturação</div>
@if(!empty($opcionais))
    <table class="opcionais-table">
        <thead>
    <tr>
        <th>Opcional</th>
        <th style="text-align:center">Cobrança</th>
        <th style="text-align:center">Qtd.</th>
        <th style="text-align:center">Período</th>
        <th style="text-align:right">Valor unitário</th>
        <th style="text-align:right">Total Por dia</th>
    </tr>
</thead>

<tbody>
    @forelse($opcionais as $opcional)
        <tr>
            <td>{{ $opcional['nome'] ?? '-' }}</td>

            <td style="text-align:center">
                {{ $opcional['tipo_cobranca_formatado'] ?? '-' }}
            </td>

            <td style="text-align:center">
                {{ $opcional['quantidade'] ?? 0 }}
            </td>

            <td style="text-align:center">
                {{ $opcional['periodo'] ?? '-' }}
            </td>

            <td style="text-align:right">
                {{ $opcional['preco_unitario'] ?? '-' }}

                @if(($opcional['tipo_cobranca'] ?? null) === 'por_dia')
                    /dia
                @endif
            </td>

            <td style="text-align:right">
                {{ $opcional['valor_total'] ?? '-' }}
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="6" style="text-align:center">
                Nenhum opcional contratado.
            </td>
        </tr>
    @endforelse
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
