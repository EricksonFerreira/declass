@if(!empty($caucao) && is_array($caucao))
    <div class="section-title">Caução</div>
    <table class="detail-table">
        <tbody>
            <tr>
                <td><strong>Status</strong><br>{{ $caucao['status'] ?? '-' }}</td>
                <td><strong>Método</strong><br>{{ $caucao['metodo'] ?? '-' }}</td>
                <td><strong>Valor calculado</strong><br>{{ $caucao['valor_calculado'] ?? '-' }}</td>
                <td><strong>Valor autorizado</strong><br>{{ $caucao['valor_autorizado'] ?? '-' }}</td>
                <td><strong>Valor capturado</strong><br>{{ $caucao['valor_capturado'] ?? '-' }}</td>
            </tr>
        </tbody>
    </table>
@endif
