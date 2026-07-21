<div class="section-title">Depósito de Garantia (Caução)</div>
<table class="detail-table">
    <tbody>
        <tr>
            <td><strong>Valor Exigido no Plano</strong><br>R$ {{ number_format((float) $get('valores.caucao', $get('caucao.valor', 0)), 2, ',', '.') }}</td>
            <td><strong>Status da Caução</strong><br>{{ $get('caucao.status', 'Em Aberto / Pré-autorizado') }}</td>
            <td><strong>Método</strong><br>{{ $get('caucao.metodo', 'Cartão de Crédito / Presencial') }}</td>
        </tr>
    </tbody>
</table>
