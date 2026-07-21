<div class="section-title">Valores do Plano e Opcionais</div>
<table class="detail-table">
    <tbody>
        <tr>
            <td>Valor do Período ({{ $get('contrato.periodicidade_label', 'Ciclo') }})</td>
            <td class="money">R$ {{ number_format((float) $get('valores.valor_periodo', 0), 2, ',', '.') }}</td>
        </tr>
        @if(!empty($opcionais))
            @foreach($opcionais as $opcional)
                <tr>
                    <td>Opcional: {{ $opcional['nome'] ?? '-' }} (x{{ $opcional['quantidade'] ?? 1 }})</td>
                    <td class="money">R$ {{ number_format((float) ($opcional['valor'] ?? 0), 2, ',', '.') }}</td>
                </tr>
            @endforeach
        @endif
        <tr>
            <td>Valor Opcionais</td>
            <td class="money">R$ {{ number_format((float) $get('valores.valor_opcionais', 0), 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td><strong>Total do Ciclo Atual</strong></td>
            <td class="money">
                <strong>R$ {{ number_format((float) $get('valores.valor_total', 0), 2, ',', '.') }}</strong>
            </td>
        </tr>
    </tbody>
</table>
