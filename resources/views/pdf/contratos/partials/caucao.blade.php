@if(!empty($caucao) && is_array($caucao))
    <div class="section-title">Caução</div>
    <table class="detail-table">
        <tbody>
            <tr>
                <td><strong>Valor</strong><br>{{ $caucao['valor_calculado'] ?? $caucao['valor_autorizado'] ?? $caucao['valor_calculado'] ?? '-' }}</td>
            </tr>
        </tbody>
    </table>
@endif
