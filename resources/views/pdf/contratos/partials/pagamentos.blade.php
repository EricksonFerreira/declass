@if(!empty($pagamentos))
    <div class="section-title">Pagamentos e Transações</div>
    <table class="detail-table">
        <thead>
            <tr>
                <th>Data</th>
                <th>Valor</th>
                <th>Método</th>
                <th>Status</th>
                <th>Confirmado em</th>
                <th>Pago por</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pagamentos as $pagamento)
                <tr>
                    <td>{{ $pagamento['data'] ?? '-' }}</td>
                    <td class="money">{{ $pagamento['valor'] ?? '-' }}</td>
                    <td>{{ $pagamento['metodo'] ?? '-' }}</td>
                    <td>{{ $pagamento['status'] ?? '-' }}</td>
                    <td>{{ $pagamento['confirmado_em'] ?? '-' }}</td>
                    <td>{{ $pagamento['pago_por'] ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
