@if(!empty($caucao) && is_array($caucao))
    <div class="section-title">Depósito de Garantia (Caução)</div>
    <table class="detail-table">
        <tbody>
            <tr>
                <td><strong>Valor</strong><br>R$ {{ number_format((float) ($caucao['valor'] ?? $get('valores.caucao', 0)), 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
@else
    <div class="section-title">Depósito de Garantia (Caução)</div>
    <table class="detail-table">
        <tbody>
            <tr>
                <td><strong>Valor</strong><br>R$ {{ number_format((float) $get('valores.caucao', 0), 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
@endif
